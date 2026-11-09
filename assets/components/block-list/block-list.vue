<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';
import { confirmAskFor } from '../../js/Helper/ConfirmHelper';
import Disclosure from '../disclosure/disclosure.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.block-list.block-list::';

const translated = buildTranslatedBindings({
  resolvedUpLabel: ['upLabel', `${DOMAIN}up`],
  resolvedDownLabel: ['downLabel', `${DOMAIN}down`],
  resolvedRemoveLabel: ['removeLabel', `${DOMAIN}remove`],
  resolvedEmptyLabel: ['emptyLabel', `${DOMAIN}empty`]
});

// An ordered list of things edited one by one: each a line folded on its
// body, moved up and down, taken off, more added under the list. It says what
// was asked and changes nothing itself: the caller answers `move` and
// `remove` with its api, then hands the list back as it now stands.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-block-list-block-list',

  components: {
    Disclosure
  },

  emits: ['move', 'remove'],

  props: {
    items: {
      type: Array,
      required: true
    },
    // What tells an item from the others, kept across a reorder.
    itemKey: {
      type: String,
      default: 'id'
    },
    // The item opened — the one just added, for its form to be filled.
    openKey: {
      type: [String, Number],
      default: null
    },
    // Read only: no arrows, no bin, nothing added.
    readonly: {
      type: Boolean,
      default: false
    },
    // The question asked before an item is taken off; none, it goes at once.
    removeConfirm: {
      type: String,
      default: null
    },
    ...translated.props
  },

  computed: {
    ...translated.computed
  },

  methods: {
    keyOf(item) {
      return String(item?.[this.itemKey] ?? '');
    },

    isOpen(item) {
      return this.openKey !== null && this.openKey !== undefined && this.keyOf(item) === String(this.openKey);
    },

    renderIcon(name) {
      return this.app.getServiceOrFail(IconService).icon(name);
    },

    // One place up or down: the direction, and the place it goes to.
    move(item, index, direction) {
      const to = direction === 'up' ? index - 1 : index + 1;

      if (to < 0 || to >= this.items.length) {
        return;
      }

      this.$emit('move', item, direction, to);
    },

    async remove(item, event) {
      if (!(await confirmAskFor(this.app, event.currentTarget))) {
        return;
      }

      this.$emit('remove', item);
    }
  }
};
</script>
