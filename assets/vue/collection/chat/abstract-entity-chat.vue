<script>
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../js/Helper/TranslationHelper";
import DateService from "@wexample/symfony-loader/js/Services/DateService";
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
    LoadMore
  },

  props: {
    ...translated.props
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
        date: this.formatMessageDate(this.getMessageDate(entity)),
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

    formatMessageDate(value) {
      return value ? this.app.getService(DateService).formatDateTime(value) : '';
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    },

    // What the composer turns into before it is sent. Only a subclass knows
    // which entity that is and which of its fields carries the text.
    buildMessageEntity() {
      throw new Error('buildMessageEntity(content) must be implemented.');
    },

    async submitMessage() {
      if (!this.canSubmit) {
        return;
      }

      const content = this.draft.trim();
      const command = this.parseSlashCommand(content);
      this.isSubmitting = true;

      try {
        if (!command || !await this.runSlashCommand(command)) {
          await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
        }

        this.draft = '';
        await this.refreshEntitiesCollection();
      } finally {
        this.isSubmitting = false;
      }
    },

    // What the composer accepts besides text, by name and without the slash.
    // Each value is called with whatever text was left around the command.
    getSlashCommands() {
      return {};
    },

    // A command stands either first or last in the message, and is separated
    // from the text by a space. A slash in the middle of a sentence is text.
    parseSlashCommand(content) {
      const leading = content.match(/^\/([\w-]+)(?:\s+([\s\S]+))?$/);

      if (leading) {
        return {name: leading[1], text: (leading[2] ?? '').trim()};
      }

      const trailing = content.match(/^([\s\S]+?)\s+\/([\w-]+)$/);

      return trailing ? {name: trailing[2], text: trailing[1].trim()} : null;
    },

    // False when nothing answers to that name, and the message is then sent as
    // it was typed: an unknown command is text like any other.
    async runSlashCommand(command) {
      const handler = this.getSlashCommands()[command.name];

      if (!handler) {
        return false;
      }

      await handler.call(this, command.text);

      return true;
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
