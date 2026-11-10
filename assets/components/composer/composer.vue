<script>
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';
import { fileIcon } from '../../js/Helper/FileIconHelper';
import { mentionFound, mentionHandle, mentionInsert, mentionMatches, mentionQuery } from '../../js/Helper/MentionHelper';
import Avatar from '../avatar/avatar.vue';

// Where the composer's own words are.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.composer.composer::';

const translated = buildTranslatedBindings({
  resolvedPlaceholder: ['placeholder', `${DOMAIN}placeholder`],
  resolvedSubmitLabel: ['submitLabel', `${DOMAIN}submit`]
});

// What writes a message: a field growing with its lines, the files going with
// it, the button sending it, and under them what can be done besides. It knows
// nothing of where a message goes: `submit(content, files)` is handed by
// whoever does — a chat, a form of a page —, awaited, and the field is
// emptied once it returns; it keeps its words and files if it throws.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-composer-composer',

  components: {
    Avatar
  },

  props: {
    ...translated.props,

    // async (content, files, { mentions }) => void: where the message goes.
    // `files` are the browser's File objects attached to it, none unless
    // files are accepted. A message may be files alone. `mentions`: the
    // people it names, of those offered (`mentions`).
    submit: {
      type: Function,
      required: true
    },

    // Held still from outside, besides while it sends.
    disabled: {
      type: Boolean,
      default: false
    },

    // Whether files go with a message: a paperclip by the send button, files
    // dropped on the composer, each listed above the field as a chip.
    acceptsAttachments: {
      type: Boolean,
      default: false
    },

    // The `accept` of the picker: types, extensions. Null takes any file.
    attachmentAccept: {
      type: String,
      default: null
    },

    // How many files a message takes at most. Null for no limit.
    attachmentLimit: {
      type: Number,
      default: null
    },

    // What can be done besides writing, under the field: [{ key, icon,
    // label, onClick | href, confirm: { title, message }, flat, iconOnly,
    // end, pressed }]. Flat by default; `flat: false` keeps a button's
    // ground, for an action that should read as one. `iconOnly` says the
    // label only under the pointer, `end` sets the action at the end of the
    // row, `pressed` states a toggle. `onClick` is awaited, the other
    // actions held still meanwhile; `confirm` is asked first.
    actions: {
      type: Array,
      default: () => []
    },
    // Who can be named in a message — [{ id, label, handle, src, emoji }]:
    // `@` and what follows offer those it matches, chosen with the arrows,
    // put in with Enter or Tab, left with Escape.
    mentions: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      draft: '',
      isSubmitting: false,
      // The files going with the next message, a preview for each image, and
      // how deep a drag is over the composer (children fire their own).
      attachments: [],
      attachmentPreviews: [],
      attachDragDepth: 0,
      // The action under way, by its key: the others wait for it.
      runningAction: null,
      // The mention being typed — where its `@` is, what follows it — and
      // the person the arrows stand on among those it matches.
      mention: null,
      mentionIndex: 0
    };
  },

  computed: {
    ...translated.computed,

    isBusy() {
      return this.disabled || this.isSubmitting;
    },

    // A text, or files alone.
    canSubmit() {
      return !this.isBusy && (Boolean(this.draft.trim()) || this.attachments.length > 0);
    },

    resolvedActions() {
      return this.actions.map((action, index) => ({
        ...action,
        key: action.key ?? index,
        iconHtml: this.renderIcon(action.icon)
      }));
    },

    submitIconHtml() {
      return this.renderIcon('ph:bold/paper-plane-right');
    },

    attachIconHtml() {
      return this.renderIcon('ph:bold/paperclip');
    },

    removeIconHtml() {
      return this.renderIcon('ph:bold/x');
    },

    attachLabel() {
      return this.trans(`${DOMAIN}attach`);
    },

    attachRemoveLabel() {
      return this.trans(`${DOMAIN}attach_remove`);
    },

    // The people the mention being typed matches.
    mentionOffered() {
      return this.mention ? mentionMatches(this.mentions, this.mention.query) : [];
    },

    attachmentsFull() {
      return this.attachmentLimit !== null && this.attachments.length >= this.attachmentLimit;
    }
  },

  beforeUnmount() {
    this.clearAttachments();
  },

  methods: {
    // What whoever holds the composer does to it: puts the caret in it,
    // hands it words to send — a message being rewritten —, empties it.
    focus() {
      this.$refs.input?.focus();
    },

    setDraft(content) {
      this.draft = content ?? '';
    },

    clear() {
      this.draft = '';
      this.clearAttachments();
    },

    // The caret moved or the words changed: the mention under it, if any.
    readMention() {
      const input = this.$refs.input;

      if (!this.mentions.length || !input) {
        this.mention = null;

        return;
      }

      const found = mentionQuery(this.draft, input.selectionStart ?? this.draft.length);

      if (found?.start !== this.mention?.start || found?.query !== this.mention?.query) {
        this.mentionIndex = 0;
      }

      this.mention = found;
    },

    // The arrows, Enter, Tab and Escape choose among the people offered while
    // a mention is typed; Enter sends otherwise, Shift+Enter breaks the line.
    onKeydown(event) {
      if (this.mentionOffered.length) {
        const count = this.mentionOffered.length;

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
          event.preventDefault();
          this.mentionIndex = (this.mentionIndex + (event.key === 'ArrowDown' ? 1 : count - 1)) % count;

          return;
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
          event.preventDefault();
          this.chooseMention(this.mentionOffered[this.mentionIndex]);

          return;
        }

        if (event.key === 'Escape') {
          event.preventDefault();
          this.mention = null;

          return;
        }
      }

      if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey && !event.altKey && !event.metaKey) {
        event.preventDefault();
        this.submitMessage();
      }
    },

    chooseMention(person) {
      const input = this.$refs.input;
      const caret = input?.selectionStart ?? this.draft.length;
      const { text, caret: after } = mentionInsert(this.draft, this.mention.start, caret, person);

      this.draft = text;
      this.mention = null;
      this.$nextTick(() => {
        input?.focus();
        input?.setSelectionRange(after, after);
      });
    },

    mentionHandleOf(person) {
      return mentionHandle(person);
    },

    async submitMessage() {
      if (!this.canSubmit) {
        return;
      }

      this.isSubmitting = true;
      this.mention = null;

      try {
        await this.submit(this.draft.trim(), [...this.attachments], { mentions: mentionFound(this.draft, this.mentions) });
        this.clear();
      } finally {
        this.isSubmitting = false;
        // The field was disabled while the message travelled, which took the
        // caret out of it, and whoever just wrote a line is about to write
        // the next one.
        this.$nextTick(() => this.focus());
      }
    },

    actionClass(action) {
      return {
        'button--flat': action.flat !== false,
        'composer--action--end': Boolean(action.end)
      };
    },

    async runAction(action) {
      if (action.confirm?.message && !(await this.askConfirmation(action.confirm))) {
        return;
      }

      this.runningAction = action.key;

      try {
        await action.onClick?.();
      } finally {
        this.runningAction = null;
      }
    },

    async askConfirmation(confirm) {
      const confirmService = this.app.services.confirm;

      if (!confirmService) {
        return window.confirm(confirm.message);
      }

      return (await confirmService.confirm({
        title: confirm.title || undefined,
        message: confirm.message,
        preset: 'ok_cancel'
      })) === 'ok';
    },

    attachmentIconHtml(file) {
      return this.renderIcon(fileIcon(file.name));
    },

    addAttachments(files) {
      const limit = this.attachmentLimit;
      const added = limit === null ? files : files.slice(0, Math.max(0, limit - this.attachments.length));

      this.attachments = [...this.attachments, ...added];
      this.attachmentPreviews = [
        ...this.attachmentPreviews,
        ...added.map((file) => (file.type.startsWith('image/') ? URL.createObjectURL(file) : null))
      ];
    },

    removeAttachment(index) {
      const preview = this.attachmentPreviews[index];

      if (preview) {
        URL.revokeObjectURL(preview);
      }

      this.attachments = this.attachments.filter((_, position) => position !== index);
      this.attachmentPreviews = this.attachmentPreviews.filter((_, position) => position !== index);
    },

    clearAttachments() {
      this.attachmentPreviews.forEach((url) => url && URL.revokeObjectURL(url));
      this.attachments = [];
      this.attachmentPreviews = [];
    },

    onAttachPicked(event) {
      this.addAttachments([...(event.target.files ?? [])]);
      // Picked again, the same file is a change again.
      event.target.value = '';
    },

    // Only a drag carrying files, only where files are taken.
    isAttachDrag(event) {
      return this.acceptsAttachments && !this.isBusy && [...(event.dataTransfer?.types ?? [])].includes('Files');
    },

    onAttachDragEnter(event) {
      if (this.isAttachDrag(event)) {
        event.preventDefault();
        this.attachDragDepth++;
      }
    },

    onAttachDragOver(event) {
      if (this.isAttachDrag(event)) {
        event.preventDefault();
      }
    },

    onAttachDragLeave(event) {
      if (this.isAttachDrag(event)) {
        this.attachDragDepth = Math.max(0, this.attachDragDepth - 1);
      }
    },

    onAttachDrop(event) {
      if (!this.isAttachDrag(event)) {
        return;
      }

      event.preventDefault();
      this.attachDragDepth = 0;
      this.addAttachments([...(event.dataTransfer.files ?? [])]);
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    }
  }
};
</script>
