<script>
// The actions of what a zone holds, sent to the head of the nearest zone that
// has one — a bar (`zone--head`) holding a `data-zone-head-actions`: a panel of
// the tool dock (components/tool-dock), a chat's column —: adding one to a
// list, switching a chat's sound. Out of such a zone — the same list on a page
// of its own —, in the page's bar or its tabs where the page made room for them, else
// drawn where it is written, in a bar of its own (`fallbackClass`). A component holding them draws them once, wherever it is
// shown.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-zone-head-actions-zone-head-actions',

  props: {
    // The bar they stand in when no head takes them.
    fallbackClass: {
      type: String,
      default: 'toolbar toolbar--compact'
    },
    // Whether the page's bar takes them when no zone heads them: not for
    // what belongs to one zone — a widget's settings —, drawn in its own bar
    // rather than lost among the page's.
    page: {
      type: Boolean,
      default: true
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
    // first ancestor holding a head among its own children, or first in its
    // content — a zone's head drawn by what it shows, a widget.
    for (let node = this.$el.parentElement; node && !this.target; node = node.parentElement) {
      this.target = node.querySelector(':scope > .zone--head [data-zone-head-actions], :scope > .zone--content > .zone--head [data-zone-head-actions]');
    }

    // No zone heading it: the page's own bar, under the header, where the
    // page made room for them — its toolbar (`page_toolbar_right`), or its
    // tabs' end (page_tabs, `actions`).
    if (this.page) {
      this.target ??= document.querySelector('.toolbar--top [data-zone-head-actions], .page-tabs [data-zone-head-actions]');
    }
    this.resolved = true;
  }
};
</script>
