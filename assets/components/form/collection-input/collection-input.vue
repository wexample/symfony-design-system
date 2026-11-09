<script>
import FormField from '../../_abstract/form-field/form-field.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.collection.';

let nextKey = 0;

// Several values of one kind, an item each, in their order, held as a list:
// one more added from `template`, any taken off, moved up and down. Each item
// is drawn by the `entry` slot — `{ value, update, itemName, index }` —, any
// field: the collection knows the list, not what each item is. With no slot,
// a text. Posted each under its index (`name[0]`, `name[1]`), by the form.
export default {
  extends: FormField,
  template: '#vue-template-wexample-symfony-design-system-bundle-components-form-collection-input-collection-input',
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    // What an item added starts from: an example to fill in.
    template: {
      type: null,
      default: ''
    },
    allowAdd: {
      type: Boolean,
      default: true
    },
    allowDelete: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      // Each item's key, kept with it as it moves: the field drawing it stays
      // the same element, its own state with it.
      keys: this.modelValue.map(() => nextKey++)
    };
  },

  computed: {
    items() {
      return Array.isArray(this.modelValue) ? this.modelValue : [];
    },

    editable() {
      return !this.disabled && !this.readonly;
    }
  },

  watch: {
    // Another list from outside: as many keys as items.
    modelValue(items) {
      if (items.length !== this.keys.length) {
        this.keys = items.map((item, index) => this.keys[index] ?? nextKey++);
      }
    }
  },

  methods: {
    t(key) {
      return this.trans(`${DOMAIN}${key}`);
    },

    itemName(index) {
      return this.name ? `${this.name}[${index}]` : null;
    },

    emitItems(items, keys) {
      this.keys = keys;
      this.$emit('update:modelValue', items);
    },

    update(index, value) {
      const items = [...this.items];

      items[index] = value;
      this.$emit('update:modelValue', items);
    },

    // A copy of the example, so that items added from it do not share it.
    add() {
      const value = this.template !== null && typeof this.template === 'object' ? JSON.parse(JSON.stringify(this.template)) : this.template;

      this.emitItems([...this.items, value], [...this.keys, nextKey++]);
    },

    remove(index) {
      this.emitItems(this.items.filter((item, at) => at !== index), this.keys.filter((key, at) => at !== index));
    },

    // One place up or down, never past either end.
    move(index, direction) {
      const to = direction === 'up' ? index - 1 : index + 1;

      if (to < 0 || to >= this.items.length) {
        return;
      }

      const items = [...this.items];
      const keys = [...this.keys];

      [items[index], items[to]] = [items[to], items[index]];
      [keys[index], keys[to]] = [keys[to], keys[index]];
      this.emitItems(items, keys);
    }
  }
};
</script>
