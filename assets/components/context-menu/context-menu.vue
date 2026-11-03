<script>
import ButtonMenu from '../button-menu/button-menu.vue';

// Room kept between the panel and the window's edge, once it is turned back
// from it.
const WINDOW_MARGIN = 8;

// A menu opened by a right click on what it wraps — or the keyboard's own
// key for it, or Shift+F10 —, where the pointer is: the same menu as a
// button-menu's, its items, its panel, what an item does (a link, a post asked
// first, a toggle, a branch), only opened otherwise. What it wraps is drawn
// as it is (its default slot). A right click with Shift held keeps the
// browser's own menu: copying a link, inspecting.
//
// The items may depend on what was clicked — a row of a list, a node of a
// stage —: `target`, { target, event }, is said before the menu opens, and
// the items given in answer, in the same tick, are the ones shown. None, and
// nothing opens.
export default {
  extends: ButtonMenu,

  template: '#vue-template-wexample-symfony-design-system-bundle-components-context-menu-context-menu',

  emits: ['target'],

  props: {
    // Off, the browser's own menu, as if there were none.
    disabled: {
      type: Boolean,
      default: false
    },
    // What names the menu for whoever reads the page aloud.
    label: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      pointX: 0,
      pointY: 0,
      // What had the focus when the menu opened, given it back once closed.
      returnFocus: null,
      // Opened from the keyboard: its first item takes the focus, as a
      // menu's does — not under a pointer, which has nothing to tab from.
      fromKeyboard: false
    };
  },

  computed: {
    panelClasses() {
      return [
        'button-menu--panel',
        'context-menu--panel',
        this.filterable ? 'button-menu--panel--long' : null
      ];
    },

    panelStyle() {
      return { left: `${this.pointX}px`, top: `${this.pointY}px` };
    }
  },

  mounted() {
    window.addEventListener('blur', this.close);
    window.addEventListener('resize', this.close);
    window.addEventListener('scroll', this.onScroll, true);
  },

  beforeUnmount() {
    window.removeEventListener('blur', this.close);
    window.removeEventListener('resize', this.close);
    window.removeEventListener('scroll', this.onScroll, true);
  },

  methods: {
    async onContextMenu(event) {
      if (this.disabled || event.shiftKey) {
        return;
      }

      event.preventDefault();
      await this.openAt(event.clientX, event.clientY, event.target, event);
    },

    // The keyboard's menu key, or Shift+F10: opened under what has the focus.
    async onKeydown(event) {
      if (this.disabled || this.isOpen || !(event.key === 'ContextMenu' || (event.shiftKey && event.key === 'F10'))) {
        return;
      }

      event.preventDefault();
      const rect = event.target.getBoundingClientRect();
      await this.openAt(rect.left, rect.bottom, event.target, event);
    },

    // Opens the menu at a point of the window, for what is at `target`: the
    // page asked first which items it holds for it.
    async openAt(x, y, target = null, event = null) {
      this.close();
      this.$emit('target', { target, event });
      await this.$nextTick();

      if (!this.items.length) {
        return;
      }

      this.pointX = x;
      this.pointY = y;
      this.fromKeyboard = event?.type === 'keydown';
      this.returnFocus = document.activeElement;
      this.open();
    },

    // Opened without a point — by an agent —: at the start of what it wraps.
    open() {
      if (!this.pointX && !this.pointY) {
        const rect = this.$el.querySelector(':scope > :not(.button-menu--panel)')?.getBoundingClientRect();

        if (rect) {
          this.pointX = rect.left;
          this.pointY = rect.top;
        }
      }

      ButtonMenu.methods.open.call(this);
      this.$nextTick(() => {
        if (this.fromKeyboard && !this.filterable) {
          this.$refs.panel?.querySelector('a:not([disabled]), button:not(:disabled)')?.focus();
        }
      });
    },

    close() {
      if (!this.isOpen) {
        return;
      }

      ButtonMenu.methods.close.call(this);
      this.pointX = 0;
      this.pointY = 0;
    },

    // A press anywhere but in the panel closes it: what it wraps included.
    onDocumentMouseDown(event) {
      if (!this.$refs.panel?.contains(event.target)) {
        this.close();
      }
    },

    onDocumentKeyDown(event) {
      if (event.key === 'Escape') {
        this.close();
        this.returnFocus?.focus?.();
      }
    },

    // The page moving under the panel leaves it pointing at nothing — but the
    // panel's own list scrolling is reading it.
    onScroll(event) {
      if (this.isOpen && !this.$refs.panel?.contains(event.target)) {
        this.close();
      }
    },

    // At the point, turned back from the window's edges it would cross.
    updatePlacement() {
      const panel = this.$refs.panel;

      if (!panel) {
        return;
      }

      const rect = panel.getBoundingClientRect();
      const right = window.innerWidth - WINDOW_MARGIN;
      const bottom = window.innerHeight - WINDOW_MARGIN;

      if (this.pointX + rect.width > right) {
        this.pointX = Math.max(WINDOW_MARGIN, this.pointX - rect.width);
      }

      if (this.pointY + rect.height > bottom) {
        this.pointY = Math.max(WINDOW_MARGIN, this.pointY - rect.height);
      }
    },

    getAgentLabel() {
      return this.label || undefined;
    },

    getAgentFrameElement() {
      return this.$el;
    }
  }
};
</script>
