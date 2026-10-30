<script>
import { assistanceWriteText } from '@wexample/js-api/Helper/Assistance';

// A field narrowing a list to what matches its text, as a table's search:
// what is typed goes out through v-model, nothing is kept here. An agent
// searching writes the text in letter by letter, the field framed meanwhile
// (`search-input.ai.yml`).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-search-input-search-input',

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    // What the field is for, to assistive technologies and to an agent; the
    // placeholder when not said.
    label: {
      type: String,
      default: ''
    }
  },

  emits: ['update:modelValue'],

  computed: {
    resolvedLabel() {
      return this.label || this.placeholder;
    },

    iconHtml() {
      return this.app.getServiceOrFail('icon').icon('ph:bold/magnifying-glass');
    }
  },

  methods: {
    onInput(event) {
      this.$emit('update:modelValue', event.target.value);
    },

    getAgentLabel() {
      return this.resolvedLabel || undefined;
    },

    getAgentState() {
      return { value: this.modelValue };
    },

    getAgentFrameElement() {
      return this.$el;
    },

    getAgentActions() {
      return {
        get: () => ({ value: this.modelValue }),
        set: async ({ value }) => {
          if (value === undefined || value === null) {
            throw new Error('set needs a value: the text to search for.');
          }

          await this.writeAssisted(String(value));

          return { value: this.modelValue };
        },
        clear: async () => {
          await this.writeAssisted('');

          return { value: this.modelValue };
        }
      };
    },

    // Spelled out as a person types, the field framed while it is written in.
    async writeAssisted(value) {
      const frames = this.app.services.agentTools?.pointer.frames;

      frames?.show(this.$el);

      try {
        this.$emit('update:modelValue', '');
        await assistanceWriteText((written) => this.$emit('update:modelValue', written), value);
        // What was written is read back once whoever holds the value took it.
        await this.$nextTick();
      } finally {
        frames?.hide(this.$el);
      }
    }
  }
};
</script>
