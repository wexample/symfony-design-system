<script>
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../js/Helper/TranslationHelper";
import DateService from "@wexample/symfony-loader/js/Services/DateService";

const translated = buildTranslatedBindings({
  resolvedPlaceholder: [
    'placeholder',
    'WexampleSymfonyDesignSystemBundle.vue.collection.chat.abstract-entity-chat::composer.placeholder'
  ],
  resolvedSubmitLabel: [
    'submitLabel',
    'WexampleSymfonyDesignSystemBundle.vue.collection.chat.abstract-entity-chat::composer.submit'
  ]
});

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-chat-abstract-entity-chat",

  mixins: [AbstractEntityCollectionVueMixin],

  props: {
    ...translated.props
  },

  data() {
    return {
      draft: '',
      isSubmitting: false
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
      this.$nextTick(() => this.scrollThreadToBottom());
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
      this.isSubmitting = true;

      try {
        await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
        this.draft = '';
        await this.refreshEntitiesCollection();
      } finally {
        this.isSubmitting = false;
      }
    },

    scrollThreadToBottom() {
      const thread = this.$refs.thread;

      if (thread) {
        thread.scrollTop = thread.scrollHeight;
      }
    }
  }
};
</script>
