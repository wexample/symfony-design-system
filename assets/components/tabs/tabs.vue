<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-tabs',

  props: {
    app: {
      type: Object,
      required: true
    },
    items: {
      type: Array,
      required: true,
      default: () => []
    },
    activeKey: {
      type: String,
      default: ''
    },
    fill: {
      type: Boolean,
      default: false
    },
    compact: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    resolvedItems() {
      return this.items.filter((item) => item && item.visible !== false);
    },

    resolvedTabsClass() {
      return {
        'tabs--fill': this.fill,
        'tabs--compact': this.compact
      };
    }
  },

  methods: {
    isActive(item) {
      return item.key === this.activeKey;
    },

    onClickTab(event, item) {
      event.preventDefault();

      if (!item || !item.key || item.disabled) {
        return;
      }

      if (this.isActive(item)) {
        return;
      }

      this.$emit('change', item.key, item);
    },

    getItemIcon(item) {
      if (!item?.icon) {
        return '';
      }

      const iconService = this.app.getServiceOrFail('icon');
      return iconService.icon(item.icon);
    }
  }
};
</script>
