<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';

// The twin of components/button-menu: same classes, same panel, and above all the
// same item shape — { icon, label, href, class, newWindow } — so a menu written
// for one side can be handed to the other without being rewritten. What it adds
// is a select event, because in a vue page an item often does something rather
// than leading somewhere.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-button-menu-button-menu',

  props: {
    icon: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    items: {
      type: Array,
      default: () => []
    },
    // Which edge of the button the panel lines up with, and which side of it the
    // panel opens on. Both are starting points: a panel that would leave the
    // window flips to the other side.
    menuAlign: {
      type: String,
      default: 'left'
    },
    menuVertical: {
      type: String,
      default: 'bottom'
    },
    itemsAlign: {
      type: String,
      default: 'left'
    },
    // An icon on its own says it opens something by what it is; anything with a
    // word keeps a caret. Null follows that rule, a boolean overrides it.
    caret: {
      type: Boolean,
      default: null
    },
    buttonClass: {
      type: String,
      default: null
    },
    wrapperClass: {
      type: String,
      default: null
    }
  },

  emits: ['select', 'open', 'close'],

  data() {
    return {
      isOpen: false,
      // What the panel resolved to this time round, which is not always what the
      // props asked for.
      align: this.menuAlign,
      vertical: this.menuVertical
    };
  },

  beforeUnmount() {
    this.unwatchDocument();
  },

  computed: {
    wrapperClasses() {
      return ['button-menu', this.wrapperClass, this.isOpen ? 'is-open' : null];
    },

    buttonClasses() {
      return ['button', 'button--menu', this.buttonClass];
    },

    panelClasses() {
      return [
        'button-menu--panel',
        `button-menu--panel--${this.align}`,
        this.vertical === 'top' ? 'button-menu--panel--top' : null
      ];
    },

    listClasses() {
      return ['button-menu--list', `button-menu--list--items-${this.itemsAlign}`];
    },

    iconHtml() {
      return this.icon ? this.renderIcon(this.icon) : null;
    },

    hasCaret() {
      return this.caret ?? Boolean(this.label);
    }
  },

  methods: {
    renderIcon(name) {
      return this.app.getServiceOrFail(IconService).icon(name);
    },

    itemHref(item) {
      return item.href ?? '#';
    },

    itemAttributes(item) {
      return item.newWindow
        ? { target: '_blank', rel: 'noopener noreferrer', ...(item.attr ?? {}) }
        : (item.attr ?? {});
    },

    toggle() {
      return this.isOpen ? this.close() : this.open();
    },

    open() {
      this.isOpen = true;
      document.addEventListener('mousedown', this.onDocumentMouseDown);
      document.addEventListener('keydown', this.onDocumentKeyDown);
      // Measured once it is on screen, since a hidden panel has no size.
      this.$nextTick(() => this.updatePlacement());
      this.$emit('open');
    },

    close() {
      if (!this.isOpen) {
        return;
      }

      this.isOpen = false;
      this.unwatchDocument();
      this.align = this.menuAlign;
      this.vertical = this.menuVertical;
      this.$emit('close');
    },

    unwatchDocument() {
      document.removeEventListener('mousedown', this.onDocumentMouseDown);
      document.removeEventListener('keydown', this.onDocumentKeyDown);
    },

    onDocumentMouseDown(event) {
      if (!this.$el?.contains(event.target)) {
        this.close();
      }
    },

    onDocumentKeyDown(event) {
      if (event.key === 'Escape') {
        this.close();
        this.$refs.button?.focus();
      }
    },

    // An item leading nowhere is an action: the click is stopped and handed to
    // whoever placed the menu.
    onItemClick(item, event) {
      if (!item.href) {
        event.preventDefault();
      }

      this.$emit('select', item);
      this.close();
    },

    // The asked-for side is kept unless it is the only one that does not fit.
    updatePlacement() {
      const button = this.$refs.button;
      const panel = this.$refs.panel;

      if (!button || !panel) {
        return;
      }

      const buttonRect = button.getBoundingClientRect();
      const panelRect = panel.getBoundingClientRect();

      const fitsLeft = buttonRect.left + panelRect.width <= window.innerWidth;
      const fitsRight = buttonRect.right - panelRect.width >= 0;
      const fitsBottom = buttonRect.bottom + panelRect.height <= window.innerHeight;
      const fitsTop = buttonRect.top - panelRect.height >= 0;

      this.align = this.resolveSide(this.menuAlign, 'left', 'right', fitsLeft, fitsRight);
      this.vertical = this.resolveSide(this.menuVertical, 'bottom', 'top', fitsBottom, fitsTop);
    },

    resolveSide(asked, first, second, firstFits, secondFits) {
      if (asked === first) {
        return firstFits || !secondFits ? first : second;
      }

      return secondFits || !firstFits ? second : first;
    }
  }
};
</script>
