<script>
import Bar from "../partials/bar.vue";
import { stringToKebab } from '@wexample/js-helpers/Helper/String';

// One result, drawn as a bar. This is the row every kind of result gets unless
// a component named after its type is registered — `search-result-invoice` for
// an `invoice` — which extends this one and says what differs: an icon, a
// label for the type, a line more. The result itself is an entity, so what is
// read here is read the way any entity is.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-search-search-result',

  components: {
    Bar
  },

  emits: ['select'],

  props: {
    result: {
      type: Object,
      required: true
    }
  },

  computed: {
    type() {
      return this.readString('type');
    },

    title() {
      return this.readString('title');
    },

    subtitle() {
      return this.readString('subtitle') || null;
    },

    // What the row is opened by. Null when the kind declared no route: the bar
    // then stands as a div and stops pretending to be a link.
    href() {
      return this.readString('url') || null;
    },

    icon() {
      return this.readString('icon') || null;
    },

    // The raw type, until a component of that type says better.
    typeLabel() {
      return this.type;
    },

    cssClass() {
      return ['search-result', 'search-result--' + stringToKebab(this.type)];
    }
  },

  methods: {
    // Through the entity's accessor when it has one: the generated class
    // declares a schema, not fields, so the values live in its data.
    readString(name) {
      const value = typeof this.result.getDataValue === 'function'
        ? this.result.getDataValue(name)
        : this.result[name];

      return typeof value === 'string' ? value : '';
    },

    onClick(event) {
      this.$emit('select', { result: this.result, event });
    }
  }
};
</script>
