<script>
// The actions of what a zone holds, sent to the head of the nearest zone that
// has one — a bar (`zone--head`) holding a `data-zone-head-actions`: a panel of
// the tool dock (components/tool-dock), a chat's column —: adding one to a
// list, switching a chat's sound. Out of such a zone — the same list on a page
// of its own —, drawn where it is written, in a bar of its own
// (`fallbackClass`). A component holding them draws them once, wherever it is
// shown.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-head-actions-zone-head-actions',

  props: {
    // The bar they stand in when no head takes them.
    fallbackClass: {
      type: String,
      default: 'toolbar toolbar--compact'
    }
  },

  data() {
    return {
      // Looked for once in the page: nothing drawn before, so nothing jumps.
      resolved: false,
      target: null
    };
  },

  mounted() {
    // The nearest head, climbing from where the actions are written: the
    // first ancestor holding a head among its own children.
    for (let node = this.$el.parentElement; node && !this.target; node = node.parentElement) {
      this.target = node.querySelector(':scope > .zone--head [data-zone-head-actions]');
    }

    this.resolved = true;
  }
};
</script>
