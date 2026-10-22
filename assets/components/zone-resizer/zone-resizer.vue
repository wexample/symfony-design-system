<script>
import { attachZoneResize } from '../../js/Helper/ZoneResizeHelper';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-resizer-zone-resizer',

  props: {
    // Which edge of the region the handle lies on: the far one by default, the
    // one a column steals its width from when it grows.
    edge: {
      type: String,
      default: 'end'
    },
    // Left alone, the axis is read from the split the region stands in.
    axis: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      detach: null
    };
  },

  mounted() {
    // The same helper the script-mounted twin calls: everything it does is read
    // the dom and write a custom property, which is the same job either side.
    this.detach = attachZoneResize(this.$el, this.app);
  },

  beforeUnmount() {
    this.detach?.();
  },

  computed: {
    orientation() {
      return this.axis === 'y' ? 'horizontal' : 'vertical';
    }
  }
};
</script>
