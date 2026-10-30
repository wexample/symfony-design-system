<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';

// A name of the design system's icons, `ph:bold/check`; anything else is a
// mark written as it is — an emoji, « ✓ ».
const ICON_NAME = /^[\w-]+:[\w-]+\/[\w-]+$/;

// The twin of item-list.html.twig: lines each led by a mark.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-item-list-item-list',

  props: {
    // [{ label, icon, tone }].
    items: {
      type: Array,
      required: true
    },
    // What a tone colours: the mark, or the whole line.
    toned: {
      type: String,
      default: 'mark'
    }
  },

  methods: {
    isIconName(icon) {
      return typeof icon === 'string' && ICON_NAME.test(icon);
    },

    iconHtml(icon) {
      return this.app.getServiceOrFail(IconService).icon(icon);
    },

    itemClass(item) {
      return ['item-list--item', item.tone ? `item-list--item--${item.tone}` : null];
    }
  }
};
</script>
