<script>
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../js/Helper/TranslationHelper";
import DateDisplay from "../../partials/date-display.vue";
import LoadMore from "../../partials/load-more.vue";

const translated = buildTranslatedBindings({
  resolvedPlaceholder: [
    'placeholder',
    'WexampleSymfonyDesignSystemBundle.vue.collection.chat.abstract-entity-chat::composer.placeholder'
  ],
  resolvedSubmitLabel: [
    'submitLabel',
    'WexampleSymfonyDesignSystemBundle.vue.collection.chat.abstract-entity-chat::composer.submit'
  ],
  resolvedLoadOlderLabel: [
    'loadOlderLabel',
    'WexampleSymfonyDesignSystemBundle.vue.collection.chat.abstract-entity-chat::thread.load_older'
  ]
});

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-chat-abstract-entity-chat",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    DateDisplay,
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
      threadScrollAnchor: null
    };
  },

  computed: {
    ...translated.computed,

    // The thread reads this and nothing else: what a message is made of is
    // decided by the projections below, never by the shape of the entity.
    messages() {
      return this.entities.map((entity, index) => ({
        key: this.getMessageKey(entity, index),
        author: this.getMessageAuthor(entity),
        content: this.getMessageContent(entity),
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
    entities() {
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
