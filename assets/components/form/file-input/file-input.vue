<script>
import FormField from '../../_abstract/form-field/form-field.vue';
import { fileIcon } from '../../../js/Helper/FileIconHelper';

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.file_input.';

// A file field shaped as a place to drop it on, as its Twig twin: the file
// dragged there or chosen with the button, named once taken, then a chip
// each — the image itself when it is one —, which takes it out.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-file-input-file-input',
  emits: ['update:modelValue'],

  props: {
    multiple: {
      type: Boolean,
      default: false
    },
    accept: {
      type: String,
      default: ''
    },
    // What the field holds: the files chosen, or — once frozen — what the
    // model keeps, a stored name or a path.
    modelValue: {
      type: [Array, String],
      default: null
    }
  },

  data() {
    return {
      files: [],
      previews: [],
      dragDepth: 0
    };
  },

  computed: {
    frozenText() {
      if (Array.isArray(this.modelValue)) {
        return this.modelValue.map((file) => file?.name ?? String(file)).join(', ');
      }

      return this.modelValue ?? '';
    },

    namesText() {
      if (this.files.length > 1) {
        return this.t('count').replace('%count%', String(this.files.length));
      }

      return this.files[0]?.name ?? this.t(this.multiple ? 'hint_many' : 'hint_one');
    },

    namesTitle() {
      return this.files.map((file) => file.name).join(', ');
    }
  },

  beforeUnmount() {
    this.previewsClear();
  },

  methods: {
    t(key) {
      return this.trans(`${T}${key}`);
    },

    icon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    fileIconHtml(file) {
      return this.icon(fileIcon(file.name));
    },

    // Anywhere on the zone opens the browser's picker, the button included —
    // but not the control itself, which opens it on its own.
    onZoneClick(event) {
      if (event.target !== this.$refs.control) {
        this.$refs.control?.click();
      }
    },

    onDragEnter() {
      this.dragDepth++;
    },

    onDragLeave() {
      this.dragDepth = Math.max(0, this.dragDepth - 1);
    },

    // What is dropped becomes the control's — one file only, unless the field
    // takes several —, as if it had been picked.
    onDrop(event) {
      this.dragDepth = 0;

      const dropped = Array.from(event.dataTransfer?.files ?? []);

      if (dropped.length) {
        this.take(this.multiple ? dropped : dropped.slice(0, 1));
      }
    },

    onChange(event) {
      this.take(Array.from(event?.target?.files ?? []), false);
    },

    // One file taken out, the others kept.
    removeFile(index) {
      this.take(this.files.filter((_, position) => position !== index));
    },

    // The files held, written back into the control — so the form sends
    // them — unless they come from it, and told.
    take(files, write = true) {
      const control = this.$refs.control;

      if (write && control) {
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        control.files = transfer.files;
      }

      this.previewsClear();
      this.files = files;
      this.previews = files.map((file) => (file.type.startsWith('image/') ? URL.createObjectURL(file) : null));
      this.$emit('update:modelValue', files);
    },

    previewsClear() {
      this.previews.forEach((url) => url && URL.revokeObjectURL(url));
      this.previews = [];
    }
  }
};
</script>
