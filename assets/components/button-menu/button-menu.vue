<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';

// The twin of components/button-menu: same classes, same panel, and above all
// the same item shape — { type, icon, label, trailingIcon, count, href,
// newWindow, checked, box, radio, tone, items, class } — so a menu written for one side can be
// handed to the other without being rewritten. What an item is follows from
// what it carries: a target makes a link, a state makes a toggle, children make
// a branch. What this adds over the server is the events, because in a vue page
// an item does something rather than leading somewhere.
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

  emits: ['select', 'toggle', 'open', 'close'],

  data() {
    return {
      isOpen: false,
      // Which branch is held open by a click. Hovering and tabbing open one
      // through the stylesheet alone; this is for the touch that has neither.
      openSubmenu: null,
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
    },

    // Drawn once and handed to every checked toggle, the mark being the same
    // one on all of them.
    checkGlyph() {
      return this.renderIcon('ph:bold/check');
    }
  },

  methods: {
    renderIcon(name) {
      return this.app.getServiceOrFail(IconService).icon(name);
    },

    // What an item is, out of what it carries. The caller may say it outright
    // with `type`; it never has to.
    itemType(item) {
      if (item.type) {
        return item.type;
      }

      if (item.separator) {
        return 'separator';
      }

      if (item.items && item.items.length) {
        return 'submenu';
      }

      return item.checked === undefined ? 'link' : 'toggle';
    },

    // A link goes somewhere and is an anchor; a toggle and a branch go nowhere
    // and are buttons, which is also what makes them reachable by keyboard
    // without a target to pretend to have.
    itemTag(item) {
      return this.isAnchor(item) ? 'a' : 'button';
    },

    // A link, or a toggle keeping its state in the address it leads to — a
    // filter of a server table — as its twig twin draws it.
    isAnchor(item) {
      const type = this.itemType(item);

      return type === 'link' || (type === 'toggle' && Boolean(item.href));
    },

    itemRole(item) {
      return this.itemType(item) === 'toggle' ? 'menuitemcheckbox' : 'menuitem';
    },

    itemClasses(item, index) {
      return [
        'button-menu--item',
        this.itemType(item) === 'submenu' ? 'button-menu--item--submenu' : null,
        this.openSubmenu === index ? 'is-open' : null
      ];
    },

    linkClasses(item) {
      const type = this.itemType(item);

      return [
        'button-menu--link',
        type === 'toggle' ? 'button-menu--toggle' : null,
        type === 'toggle' && item.checked ? 'is-checked' : null,
        // A box drawn before it is ticked, round for a choice of one, coloured
        // by a tone — the twig twin's same three.
        type === 'toggle' && (item.box || item.radio) ? 'button-menu--toggle--box' : null,
        type === 'toggle' && item.radio ? 'button-menu--toggle--radio' : null,
        type === 'toggle' && item.tone ? `button-menu--toggle--${item.tone}` : null,
        type === 'submenu' ? 'button-menu--submenu' : null,
        item.class
      ];
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
      this.openSubmenu = null;
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
    // whoever placed the menu. A toggle and a branch keep the panel open — a
    // menu of filters is read by the handful, not one at a time, and a branch
    // that closed what it opens would open nothing.
    onItemClick(item, index, event) {
      const type = this.itemType(item);

      if (!this.isAnchor(item) || !item.href) {
        event.preventDefault();
      }

      if (item.disabled) {
        return;
      }

      if (type === 'submenu') {
        this.openSubmenu = this.openSubmenu === index ? null : index;
        this.$nextTick(() => this.placeSubmenu(event));

        return;
      }

      // Followed, not flipped: the page it leads to comes back with the state.
      if (type === 'toggle' && item.href) {
        this.close();

        return;
      }

      if (type === 'toggle') {
        // Flipped here and said out loud after: a menu that waited for its
        // owner to hand the state back would leave the mark behind the click.
        item.checked = !item.checked;
        this.$emit('toggle', { item, checked: item.checked });

        return;
      }

      this.$emit('select', item);
      this.close();
    },

    // A branch opens to the right of the panel unless the window ends there.
    placeSubmenu(event) {
      const row = event?.currentTarget ?? event?.target;
      const list = row?.parentElement?.querySelector('.button-menu--sublist');

      if (!list) {
        return;
      }

      list.classList.remove('button-menu--sublist--left');

      if (list.getBoundingClientRect().right > window.innerWidth) {
        list.classList.add('button-menu--sublist--left');
      }
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
