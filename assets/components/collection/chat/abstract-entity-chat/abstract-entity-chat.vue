<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import AgentToolsService from "@wexample/symfony-loader/js/Services/AgentToolsService";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import {markdownRender} from "../../../../js/Helper/MarkdownHelper";
import {uiStateGet, uiStateSet} from "../../../../js/Helper/UiStateHelper";
import DateDisplay from "../../../date-display/date-display.vue";
import LoadMore from "../../../load-more/load-more.vue";
import Dots from "../../../dots/dots.vue";

// The chats open in the page, whatever draws them: one conversation may be
// shown twice — in the dock, and again over the veil of a modal —, and one
// of them at a time listens to it and makes itself heard: the one in front,
// or the first opened when none is behind a veil. The others wait, and the
// one handed the conversation back reads what it missed.
const chatsOpen = new Set();

const syncChats = () => chatsOpen.forEach((chat) => chat.syncListening());
document.addEventListener('overlay:change', syncChats);

// How long what an action shows — a toast once a request is answered — is
// waited for before the agent is told what came of it.
const NOTIFICATIONS_SETTLE_MS = 600;

// How long the thread shows an answer is coming, at most — and how recent the
// last line waiting for one has to be, the thread opened again, to say so.
const PENDING_ANSWER_MS = 2 * 60 * 1000;

// How long a press said to leave the page is given to do it.
const NAVIGATION_GRACE_MS = 2000;

// What a message from the other side sounds like, bundled with the chat
// (sounds/CREDITS.md).
const SOUND_MESSAGE = new URL('../../../../sounds/chat-message.mp3', import.meta.url).href;

// Whether messages make that sound, kept for the reader across pages.
const UI_STATE_SOUND = 'ui.chat.sound';

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
      awaitingReply: false,
      soundEnabled: true,
      // The messages a sound was made for: one live message may be published
      // more than once.
      soundedMessages: new Set()
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
        // Drawn from markdown, or null for a line shown as the text it is.
        html: this.isMessageMarkdown(entity) ? markdownRender(this.getMessageContent(entity)) : null,
        date: this.getMessageDate(entity),
        iconHtml: this.renderIcon(this.getMessageIcon(entity)),
        variant: this.getMessageVariant(entity)
      }));
    },

    submitIconHtml() {
      return this.renderIcon('ph:bold/paper-plane-right');
    },

    // The bar above the thread holds what concerns the conversation itself,
    // and is drawn when it holds anything.
    hasToolbar() {
      return this.hasSound();
    },

    soundIconHtml() {
      return this.renderIcon(this.soundEnabled ? 'ph:bold/speaker-high' : 'ph:bold/speaker-slash');
    },

    soundLabel() {
      return this.trans(`${DOMAIN}toolbar.${this.soundEnabled ? 'sound_off' : 'sound_on'}`);
    },

    canSubmit() {
      return !this.isSubmitting && Boolean(this.draft.trim());
    }
  },

  mounted() {
    chatsOpen.add(this);
    this.soundEnabled = uiStateGet(this.app, UI_STATE_SOUND, true);
    this.sendHeldToolResults();
    this.runWhenAppReady(() => syncChats());
  },

  beforeUnmount() {
    chatsOpen.delete(this);
    syncChats();
  },

  watch: {
    // An answer that never comes — the agent stopped on an error — does not
    // leave the thread waiting for ever: past a while, it stops saying so.
    awaitingReply(waiting) {
      window.clearTimeout(this.awaitingTimer);

      if (waiting) {
        this.awaitingTimer = window.setTimeout(() => {
          this.awaitingReply = false;
        }, PENDING_ANSWER_MS);
      }
    },

    entities(entities) {
      // The answer is in once the thread ends on a message someone else wrote
      // — not on the lines a tool adds on the way.
      const last = entities[entities.length - 1];
      if (this.awaitingReply && last && !this.isOwnMessage(last) && !this.isToolLine(last)) {
        this.awaitingReply = false;
      } else if (!this.awaitingReply && last && this.isAnswerPending(last)) {
        this.awaitingReply = true;
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

    // Whether a line is written in markdown and drawn from it: what the other
    // side says — an agent writes markdown —, never what the reader typed,
    // nor the lines a tool adds.
    isMessageMarkdown(entity) {
      return !this.isOwnMessage(entity) && !this.isToolLine(entity);
    },

    // Whether a message of the thread is drawn: one that is only plumbing —
    // what the page answered to a tool, what the user did in the page —
    // stays in the thread and out of sight.
    isMessageShown(entity) {
      return !this.isToolResult(entity) && !this.isUserEvent(entity);
    },

    // Whether a line is what the page said the user did (AgentUserEvents),
    // sent with the message that followed.
    isUserEvent() {
      return false;
    },

    // What the user did in the page since the agent last heard from it,
    // taken to be sent with what goes to it now: nothing for a chat whose
    // messages wait for nobody.
    takeUserEvents() {
      if (!this.expectsReply()) {
        return [];
      }

      return this.app.services.agentTools?.events.take() ?? [];
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
    // `[{error}]`; `userEvents` what the user did in the page meanwhile, for
    // a chat that passes it on.
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

      if (!entity || (event && payload?.event !== event)) {
        return;
      }

      if (this.readToolCall(entity)) {
        this.runToolCall(entity);
      } else if (!this.isOwnMessage(entity) && !this.isToolResult(entity) && !this.isUserEvent(entity)) {
        this.playMessageSound(entity);
      }
    },

    async runToolCall(entity) {
      const call = this.readToolCall(entity);
      const callId = this.getToolCallId(entity);

      if (!call || !callId) {
        return;
      }

      // Null when the call already ran here, and the agent has its answer.
      // Made for this conversation: what it leaves in the page is its own.
      const agentTools = this.app.getServiceOrFail(AgentToolsService);
      const conversation = this.getConversationKey();
      const startedAt = Date.now();
      const outcome = await agentTools.run(callId, call.name, call.arguments ?? {}, {caller: conversation ?? undefined});
      if (outcome === null) {
        return;
      }

      const results = [outcome.error !== undefined ? {error: outcome.error} : outcome.value];

      // A call leaving the page is answered from the page reached, once the
      // conversation is open there again to hear what comes next. A press
      // that ends up not leaving — a form sent in the background — is
      // answered from here after all.
      if (outcome.navigation && conversation) {
        agentTools.held.hold(conversation, {callId, call, results});

        let leaving = false;
        const onLeave = () => {
          leaving = true;
        };
        window.addEventListener('beforeunload', onLeave, {once: true});
        outcome.navigation.go();

        window.setTimeout(() => {
          window.removeEventListener('beforeunload', onLeave);

          if (!leaving) {
            this.sendHeldToolResults();
          }
        }, NAVIGATION_GRACE_MS);

        return;
      }

      // A call that only read has nothing to wait for.
      await this.postToolResult(
        call,
        callId,
        this.withNotifications(results, outcome.readOnly ? [] : await this.collectNotifications(startedAt)),
        this.takeUserEvents()
      );
      outcome.navigation?.go();
    },

    // What the page told the reader in toasts from that moment on — what an
    // action did, as the reader saw it —, once it had time to say it.
    async collectNotifications(since) {
      await new Promise((resolve) => window.setTimeout(resolve, NOTIFICATIONS_SETTLE_MS));

      return this.app.services.toast?.shownSince(since) ?? [];
    },

    withNotifications(results, notifications) {
      if (!notifications.length) {
        return results;
      }

      return results.map((result) => (
        result && typeof result === 'object' && !result.error ? {...result, notifications} : result
      ));
    },

    // Whether a message from the other side — a person, an agent — arriving
    // while the chat is open makes a sound, the reader free to turn it off.
    // A chat nobody else writes in has none to make.
    hasSound() {
      return true;
    },

    toggleSound() {
      this.soundEnabled = !this.soundEnabled;
      uiStateSet(this.app, UI_STATE_SOUND, this.soundEnabled);
    },

    // Whether this chat stands behind an open overlay's veil.
    isCovered() {
      return Boolean(this.app.services.overlay?.isCovered(this.$el));
    },

    // Whether this chat is the one of its conversation that listens and is
    // heard: not behind a veil, or the only one; the first opened among
    // equals — the dock's, before the one over the veil while no veil is up.
    isHeard() {
      const conversation = this.getConversationKey();
      const peers = [...chatsOpen].filter((chat) => chat.getConversationKey() === conversation);
      const front = peers.filter((chat) => !chat.isCovered());

      return (front.length ? front : peers)[0] === this;
    },

    // The subscription follows the hearing: handed the conversation back —
    // the veil gone —, the chat reads what was said meanwhile.
    syncListening() {
      if (this.isHeard()) {
        if (!this.liveConnection && this.getLiveSource()) {
          AbstractEntityCollectionVueMixin.methods.connectToLiveSource.call(this);
          this.refreshEntitiesCollection({silent: true});
        }
      } else if (this.liveConnection) {
        this.liveConnection.close();
        this.liveConnection = null;
      }
    },

    // Asked by the collection as it mounts: only the chat that is heard
    // subscribes, the others wait to be.
    connectToLiveSource() {
      if (this.isHeard()) {
        return AbstractEntityCollectionVueMixin.methods.connectToLiveSource.call(this);
      }
    },

    // Once per message, however many times it is published. The browser may
    // refuse a sound the reader has not yet allowed by acting in the page:
    // then there is none, and nothing else changes.
    playMessageSound(entity) {
      const key = entity?.id ?? entity?.secureId;

      if (!this.hasSound() || !this.soundEnabled || !key || this.soundedMessages.has(key) || !this.isHeard()) {
        return;
      }

      this.soundedMessages.add(key);
      new Audio(SOUND_MESSAGE).play().catch(() => {});
    },

    // What names the conversation across pages: the answers of calls that
    // left a page are held under it. Null for a chat that cannot tell.
    getConversationKey() {
      return null;
    },

    // The answers held for this conversation by the page it was open in
    // before, sent from here, with where the page now is.
    async sendHeldToolResults() {
      const conversation = this.getConversationKey();
      if (!conversation) {
        return;
      }

      const agentTools = this.app.getServiceOrFail(AgentToolsService);

      if (!agentTools.held.has(conversation)) {
        return;
      }

      // What the page reached says on arriving — the flashes of the action,
      // as toasts — is part of the answer.
      const notifications = await this.collectNotifications(performance.timeOrigin);
      const arrived = {url: window.location.href, title: document.title};

      // The agent answers from what is sent here: the thread says so.
      this.awaitingReply = this.expectsReply();

      for (const held of agentTools.held.take(conversation)) {
        await this.postToolResult(
          held.call,
          held.callId,
          this.withNotifications(
            held.results.map((result) => (result && typeof result === 'object' && !result.error ? {...result, arrived} : result)),
            notifications
          ),
          this.takeUserEvents()
        );
      }
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    },

    // What the composer turns into before it is sent. Only a subclass knows
    // which entity that is and which of its fields carries the text.
    buildMessageEntity() {
      throw new Error('buildMessageEntity(content) must be implemented.');
    },

    // Sends what the reader wrote, with what they did in the page since the
    // other side last heard from it (`userEvents`): a chat whose other side
    // reads it passes it on; this one writes the message alone.
    async postMessage(content, userEvents) {
      await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
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

    // A thread opened — a page reached after an agent's action, a reload —
    // whose last line still waits for the other side: the reader's own, or a
    // tool's, written lately. Older than that, the answer is not coming, and
    // the thread does not pretend it is.
    isAnswerPending(last) {
      if (!this.expectsReply() || (!this.isOwnMessage(last) && !this.isToolLine(last))) {
        return false;
      }

      const date = this.getMessageDate(last);

      return Boolean(date) && Date.now() - new Date(date).getTime() < PENDING_ANSWER_MS;
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
          await this.postMessage(content, this.takeUserEvents());
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
