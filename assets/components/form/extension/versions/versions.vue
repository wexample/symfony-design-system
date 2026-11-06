<script>
import DiffText from '../../../diff-text/diff-text.vue';
import { FORM_DEFINITION_ID_PLACEHOLDER } from '../../../../js/Helper/FormDefinitionHelper';
import { fieldVersionsNormalize, fieldVersionsFetch } from '../../../../js/Helper/FieldVersionsHelper';
import { formSuccessEvent } from '@wexample/symfony-loader/js/Constants/FormEvents';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.field_versions.';

// What the field holds while no version is read: the draft.
export const FIELD_VERSIONS_DRAFT = 'draft';

// The `versions` extension of a field (FieldExtensionsHelper): its earlier
// versions, read at `url` — `[{ label, content, date, author }]`, the latest
// first, without the current one; the record's id put where the form's
// action has it (`__id__`) by the form holding the field —, drawn as the
// field's footer: the version read, the draft first, alone while there is no
// earlier one; a version chosen is shown in the field and compared with the
// draft (diff-text); « take this version back » makes it the draft, nothing
// saved. The draft is never lost: kept aside while a version is read, given
// back when the draft is chosen again. The field is reached two ways: as the
// vue field's value (`modelValue`, the base hands it), or as the element of
// the page it is drawn beside (`target`, its id — the twig theme), written
// to as a person would (an `input` event an editor follows).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-extension-versions-versions',

  components: {
    DiffText
  },

  emits: ['update:modelValue'],

  // What the form holding the field says: the record sent, the form saved.
  inject: {
    dynamicFormContext: { default: null }
  },

  props: {
    // Where the versions are read, the record's id in it or its placeholder.
    url: {
      type: String,
      default: null
    },
    modelValue: {
      type: String,
      default: null
    },
    // The id of the field's control, when the field is the page's.
    target: {
      type: String,
      default: null
    },
    // `unified` or `split`, the diff's.
    mode: {
      type: String,
      default: 'unified'
    }
  },

  data() {
    return {
      versions: [],
      chosen: FIELD_VERSIONS_DRAFT,
      // The draft kept aside while a version is read.
      kept: null,
      // What the field held when the bar was drawn: the version saved.
      initial: null,
      // What the field holds now, followed as it is typed in.
      held: this.modelValue ?? '',
      loading: false
    };
  },

  watch: {
    modelValue(value) {
      if (!this.target) {
        this.held = value ?? '';
      }
    },

    // The form saved, the page staying: what the field holds is the current
    // version, the earlier ones one more.
    'dynamicFormContext.saved'() {
      this.refresh();
    },

    resolvedUrl() {
      this.refresh();
    }
  },

  computed: {
    // The address for the record shown: none while the record is not known.
    resolvedUrl() {
      if (!this.url) {
        return null;
      }

      if (this.url.includes(FORM_DEFINITION_ID_PLACEHOLDER)) {
        const id = this.dynamicFormContext?.id ?? null;

        return id ? this.url.replace(FORM_DEFINITION_ID_PLACEHOLDER, id) : null;
      }

      return this.url;
    },

    version() {
      return this.chosen === FIELD_VERSIONS_DRAFT ? null : this.versions[Number(this.chosen)] ?? null;
    },

    // The draft: what the field holds, or what was kept aside while a version is read.
    draft() {
      return this.version ? (this.kept ?? '') : this.held;
    },

    // « draft » once it differs from the version saved; the current one otherwise.
    draftLabel() {
      return this.t(this.draft === this.initial ? 'current' : 'draft');
    },

    choices() {
      return [
        { value: FIELD_VERSIONS_DRAFT, label: this.draftLabel },
        ...this.versions.map((version, index) => ({ value: String(index), label: version.label }))
      ];
    }
  },

  created() {
    // Not reactive: nothing drawn reads it.
    this.successEventName = null;
  },

  async mounted() {
    // The page's field: followed as a person types in it; its form saved,
    // the versions read again.
    const element = this.targetElement();
    element?.addEventListener('input', this.onTargetInput);
    this.held = this.read();

    const formName = element?.closest('form')?.getAttribute('name');

    if (formName) {
      this.successEventName = formSuccessEvent(formName);
      document.addEventListener(this.successEventName, this.refresh);
    }

    await this.refresh();
  },

  beforeUnmount() {
    this.targetElement()?.removeEventListener('input', this.onTargetInput);

    if (this.successEventName) {
      document.removeEventListener(this.successEventName, this.refresh);
    }
  },

  methods: {
    t(key, parameters = {}) {
      return this.trans(`${DOMAIN}${key}`, parameters);
    },

    targetElement() {
      return this.target ? document.getElementById(this.target) : null;
    },

    // What the field holds now.
    read() {
      return this.target ? (this.targetElement()?.value ?? '') : (this.modelValue ?? '');
    },

    // The versions read at their address, what the field holds the version
    // saved: at the start, and once the form is saved — the page staying —,
    // the draft then the current version, no version read any more.
    async refresh() {
      if (!this.resolvedUrl) {
        this.versions = [];

        return;
      }

      this.loading = true;
      this.versions = fieldVersionsNormalize(await fieldVersionsFetch(this.resolvedUrl));
      this.kept = null;
      this.chosen = FIELD_VERSIONS_DRAFT;
      this.initial = this.read();
      this.loading = false;
    },

    onTargetInput(event) {
      this.held = event?.target?.value ?? '';
    },

    // The field given a content, as a person would type it.
    write(content) {
      this.held = content;

      if (!this.target) {
        this.$emit('update:modelValue', content);

        return;
      }

      const element = this.targetElement();

      if (element) {
        element.value = content;
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
      }
    },

    // A version read: shown in the field, the draft kept aside; the draft
    // chosen again: given back as it was.
    choose(value) {
      const wasDraft = this.chosen === FIELD_VERSIONS_DRAFT;
      this.chosen = value;

      if (this.version) {
        if (wasDraft) {
          this.kept = this.read();
        }

        this.write(this.version.content ?? '');
      } else if (this.kept !== null) {
        this.write(this.kept);
        this.kept = null;
      }
    },

    onChoice(event) {
      this.choose(event?.target?.value ?? FIELD_VERSIONS_DRAFT);
    },

    // The version read becomes the draft: nothing saved, the earlier draft let go.
    take() {
      if (!this.version) {
        return;
      }

      const content = this.version.content ?? '';
      this.kept = null;
      this.chosen = FIELD_VERSIONS_DRAFT;
      this.write(content);
    }
  }
};
</script>
