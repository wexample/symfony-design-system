<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import AgentToolsService from "@wexample/symfony-loader/js/Services/AgentToolsService";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import DateDisplay from "../../../date-display/date-display.vue";
import LoadMore from "../../../load-more/load-more.vue";
import Dots from "../../../dots/dots.vue";

// Where the chat's own words are, for a line it writes itself.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-entity-chat.abstract-entity-chat::';

const translated = buildTranslatedBindings({
  resolvedPlaceholder: [
    'placeholder',
    'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-entity-chat.abstract-entity-chat::composer.placeholder'
  ],
  resolvedSubmitLabel: [
    'submitLabel',
    'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-entity-chat.abstract-entity-chat::composer.submit'
  ],
  resolvedLoadOlderLabel: [
    'loadOlderLabel',
    'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-entity-chat.abstract-entity-chat::thread.load_older'
  ]
});

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-chat-abstract-entity-chat",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    DateDisplay,
    Dots,
    LoadMore
  },

  props: {
    ...translated.props,

    // Both are handed over by slash_commands_js() from the chat's vue_config:
    // what the composer accepts besides text is declared on the server, and the
    // browser only receives the list.
    slashCommandGroup: {
      type: String,
      default: null
    },

    slashCommands: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      draft: '',
      isSubmitting: false,
      // How far the thread was from its bottom when older messages were asked
      // for. Null means the thread should simply go to the bottom.
      threadScrollAnchor: null,
      // A message was sent and its answer has not arrived yet: the thread
      // shows it is coming (expectsReply()).
      awaitingReply: false
    };
  },

  computed: {
    ...translated.computed,

    // The thread reads this and nothing else: what a message is made of is
    // decided by the projections below, never by the shape of the entity.
    messages() {
      return this.entities.filter((entity) => this.isMessageShown(entity)).map((entity, index) => ({
        key: this.getMessageKey(entity, index),
        author: this.getMessageAuthor(entity),
        content: this.readToolCall(entity)
          ? this.getToolCallContent(this.readToolCall(entity))
          : this.getMessageContent(entity),
        date: this.getMessageDate(entity),
        iconHtml: this.renderIcon(this.getMessageIcon(entity)),
        variant: this.getMessageVariant(entity)
      }));
    },

    submitIconHtml() {
      return this.renderIcon('ph:bold/paper-plane-right');
    },

    canSubmit() {
      return !this.isSubmitting && Boolean(this.draft.trim());
    }
  },

  watch: {
    entities(entities) {
      // The answer is in once the thread ends on a message someone else wrote
      // — not on the lines a tool adds on the way.
      const last = entities[entities.length - 1];
      if (this.awaitingReply && last && !this.isOwnMessage(last) && !this.isToolLine(last)) {
        this.awaitingReply = false;
      }

      this.$nextTick(() => this.settleThreadScroll());
    }
  },

  methods: {
    // Projections. A subclass overrides the ones its entity does not already
    // answer to under these names.
    getMessageKey(entity, index) {
      return entity?.id ?? entity?.secureId ?? index;
    },

    getMessageAuthor(entity) {
      return entity?.author ?? '';
    },

    getMessageContent(entity) {
      return entity?.content ?? '';
    },

    getMessageDate(entity) {
      return entity?.createdAt ?? null;
    },

    getMessageIcon() {
      return 'ph:bold/user';
    },

    // Names the kind of line this is, so the thread can draw a tool call or a
    // system notice differently from a spoken turn. Null keeps the plain row.
    getMessageVariant() {
      return null;
    },

    // Whether a message of the thread is drawn: one that is only plumbing —
    // what the page answered to a tool — stays in the thread and out of sight.
    isMessageShown(entity) {
      return !this.isToolResult(entity);
    },

    // Tool calls. The agent asks the page to run one of its tools with a line
    // of the thread (AgentToolsService); the page runs it once, however many
    // times the line reaches it, and answers with another line. A subclass
    // says how its entity carries both, and where the answer is sent.

    // The call a line carries — `{name, arguments}` —, null for any other line.
    // Read from the entity, or from the raw message a live topic carries.
    readToolCall() {
      return null;
    },

    // Whether a line is what the page answered to a call.
    isToolResult() {
      return false;
    },

    isToolLine(entity) {
      return Boolean(this.readToolCall(entity)) || this.isToolResult(entity);
    },

    // What names a call, the same each time it reaches the page.
    getToolCallId(entity) {
      return entity?.secureId ?? entity?.id ?? null;
    },

    // Tells the agent what came of a call: `results` is `[value]`, or
    // `[{error}]`.
    async postToolResult() {
      throw new Error('postToolResult(call, callId, results) must be implemented by a chat whose agent calls tools.');
    },

    // By tool name, what draws its call in the thread: `(call) => string`.
    // None by default: a call is a short line naming the tool.
    getToolRenderers() {
      return {};
    },

    getToolCallContent(call) {
      const render = this.getToolRenderers()[call.name];

      return render ? render(call) : `${this.trans(`${DOMAIN}thread.tool`)} · ${call.name}`;
    },

    // A call arrives on the live topic like any line of the thread.
    onLiveSourceMessage(payload, source) {
      AbstractEntityCollectionVueMixin.methods.onLiveSourceMessage.call(this, payload, source);

      const event = this.getLiveSource()?.event;
      const entity = payload?.data?.entity ?? payload?.data;

      if (entity && (!event || payload?.event === event) && this.readToolCall(entity)) {
        this.runToolCall(entity);
      }
    },

    async runToolCall(entity) {
      const call = this.readToolCall(entity);
      const callId = this.getToolCallId(entity);

      if (!call || !callId) {
        return;
      }

      // Null when the call already ran here, and the agent has its answer.
      const outcome = await this.app.getServiceOrFail(AgentToolsService).run(callId, call.name, call.arguments ?? {});
      if (outcome === null) {
        return;
      }

      await this.postToolResult(
        call,
        callId,
        [outcome.error !== undefined ? {error: outcome.error} : outcome.value]
      );
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    },

    // What the composer turns into before it is sent. Only a subclass knows
    // which entity that is and which of its fields carries the text.
    buildMessageEntity() {
      throw new Error('buildMessageEntity(content) must be implemented.');
    },

    // What the thread hangs from, since a browser opens the page before a single
    // message exists: the only name it can subscribe to is the one already there.
    // `{entityName, id, event}`, the event being what the server publishes on it
    // when a message is written. Null leaves the thread without live updates.
    // The thread the chat listens to, in the words the collection speaks: a chat
    // is a collection watching the session it belongs to, like any other.
    getLiveSource() {
      return this.getLiveThread();
    },

    // Kept as the chat's own word for it, since a thread is what a chat calls
    // the thing it is the collection of.
    getLiveThread() {
      return null;
    },

    // Whether a message sent here is answered by someone else — an agent —,
    // the thread showing the answer is coming until it arrives. A chat whose
    // messages wait for nobody leaves it off.
    expectsReply() {
      return false;
    },

    // Whether a message of the thread was written by whoever reads it: what
    // ends the wait for an answer is a message that is not.
    isOwnMessage() {
      return true;
    },

    async submitMessage() {
      if (!this.canSubmit) {
        return;
      }

      const content = this.draft.trim();
      const frontCommand = this.findFrontSlashCommand(content);
      this.isSubmitting = true;

      try {
        if (frontCommand) {
          await this.runFrontSlashCommand(frontCommand.command, frontCommand.text);
        } else {
          // A command running on the server travels as the message it was typed
          // in: the endpoint reads the slash and answers it, so the composer has
          // one way out and only one.
          await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
          this.awaitingReply = this.expectsReply();
        }

        this.draft = '';
        await this.refreshEntitiesCollection();
      } finally {
        this.isSubmitting = false;
        // The field was disabled while the message travelled, which took the
        // caret out of it, and whoever just wrote a line is about to write the
        // next one.
        this.$nextTick(() => this.$refs.input?.focus());
      }
    },

    // A command stands either first or last in the message, and is separated
    // from the text by a space. A slash in the middle of a sentence is text.
    // The server parses the same way, on the message it receives.
    parseSlashCommand(content) {
      const leading = content.match(/^\/([\w-]+)(?:\s+([\s\S]+))?$/);

      if (leading) {
        return {name: leading[1], text: (leading[2] ?? '').trim()};
      }

      const trailing = content.match(/^([\s\S]+?)\s+\/([\w-]+)$/);

      return trailing ? {name: trailing[2], text: trailing[1].trim()} : null;
    },

    // The only thing the browser does with the list: recognise what it must
    // keep for itself. Everything else is sent and answered server-side.
    findFrontSlashCommand(content) {
      const parsed = this.parseSlashCommand(content);

      if (!parsed) {
        return null;
      }

      const command = this.slashCommands.find(
        (candidate) => candidate.name === parsed.name && candidate.frontOnly
      );

      return command ? {command, text: parsed.text} : null;
    },

    // A front-only command names the class that runs it, resolved the same way
    // a render node resolves its own: by the asset name it was bundled under.
    async runFrontSlashCommand(command, text) {
      const definition = this.app.getBundleClassDefinition(command.handler);

      if (!definition) {
        throw new Error(`Slash command "${command.name}" points at an unknown class: ${command.handler}.`);
      }

      await new definition().run(this, text);
    },

    // Older messages are added above what is being read, so the thread must stay
    // on the same line: what is held constant is the distance to the bottom,
    // since everything that appeared is higher up.
    async loadOlderMessages() {
      const thread = this.$refs.thread;

      this.threadScrollAnchor = thread ? thread.scrollHeight - thread.scrollTop : null;

      await this.loadOlderEntities();
    },

    settleThreadScroll() {
      const thread = this.$refs.thread;

      if (!thread) {
        return;
      }

      if (this.threadScrollAnchor === null) {
        thread.scrollTop = thread.scrollHeight;

        return;
      }

      thread.scrollTop = thread.scrollHeight - this.threadScrollAnchor;
      this.threadScrollAnchor = null;
    }
  }
};
</script>
