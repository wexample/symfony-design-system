<script>
import TreeNode from "../tree-node/tree-node.vue";
import { uiStateGet, uiStateHas, uiStateSet } from "../../js/Helper/UiStateHelper";
import { locationQueryParamGet, locationQueryParamSet } from "@wexample/js-helpers/Helper/Location";

// How many open branches a tree keeps in mind. Past this the oldest are
// dropped: a session is not the place to hoard every folder ever opened.
const OPEN_KEYS_MAX = 200;

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-tree-tree',

  components: {
    // Declared here and nowhere else: components are registered on the app, so a
    // node can nest another node without listing itself, which would send the
    // registration walker round in circles.
    TreeNode
  },

  props: {
    items: {
      type: Array,
      required: true,
      default: () => []
    },

    // Maps an item type to the component rendering its row. Names, not component
    // objects, so the map survives the JSON transport props take from Twig.
    rowComponents: {
      type: Object,
      default: () => ({})
    },

    // Called with a node and a page number when that node opens, and answers the
    // level below as { items, total } — total being what the level holds in all,
    // which is how the tree knows there is a rest to offer. Left out, the tree
    // only shows the children it was handed.
    loadChildren: {
      type: Function,
      default: null
    },

    // Whether shift and ctrl extend the selection. Left off, a click replaces
    // what was selected whatever is held down.
    allowSelectMultiple: {
      type: Boolean,
      default: false
    },

    // Names the tree so which branches are open is remembered, and comes back
    // with the page. Without one it opens as its items say, every time.
    stateId: {
      type: String,
      default: null
    },

    // Says which value of an item names it from one load to the next — called
    // with the item and the key of its parent. Left out, getTreeItemKey() reads
    // `key`, then `id`, then falls back on the path of labels — enough for a
    // tree whose data has neither.
    itemKey: {
      type: Function,
      default: null
    },

    // Names the query parameter the selected item is written to. Given one, what
    // is selected is what the page shows: it goes into the address, comes back
    // with a reload, a shared link or the back button, and is selected again —
    // with the same `select` a click sends, so the page has one way to react.
    routeParam: {
      type: String,
      default: null
    },

    // Which items are worth an address. Left out, every one is; a tree whose
    // folders only fold says so here, and a click on a folder leaves the
    // address alone.
    routable: {
      type: Function,
      default: null
    }
  },

  emits: ['select', 'action'],

  data() {
    return {
      // An object rather than the items themselves: what goes down the provide
      // stays the same reference, and every depth sees it change.
      selection: {
        items: [],
        // Where a range starts. The last click that was not a range, so that
        // shifting twice from the same place widens instead of walking.
        anchor: null
      },
      // The keys of the open branches, in the order they were opened. Seeded by
      // the nodes that mount open, so the first thing remembered is what the
      // visitor saw and not only what they clicked.
      openState: {
        keys: this.readOpenKeys()
      },
      // What the address asks for: the key to select, and the branches that
      // have to open for it to be drawn. Held apart from what is remembered,
      // since it is not a preference and must not be written down as one.
      routeState: {
        key: null,
        reveal: []
      }
    };
  },

  created() {
    // Which item each drawn row stands for, held outside data: it is read when a
    // range is asked for, and making it reactive would redraw the tree on every
    // node that mounts.
    this.rowItems = new Map();
    // The same, by key: what a key in the address resolves to once drawn.
    this.keyItems = new Map();

    if (this.routeParam) {
      this.readRoute();
      this.onPopState = () => this.readRoute(true);
      window.addEventListener('popstate', this.onPopState);
    }
  },

  beforeUnmount() {
    if (this.onPopState) {
      window.removeEventListener('popstate', this.onPopState);
    }
  },

  provide() {
    // Passed down rather than drilled: every depth needs it and the recursion has
    // no business carrying it.
    return {
      treeRowComponents: this.rowComponents,
      treeLoadChildren: this.loadChildren,
      treeSelection: this.selection,
      treeRegisterRow: this.registerRow,
      treeUnregisterRow: this.unregisterRow,
      treeItemKey: this.getTreeItemKey,
      treeIsRemembered: this.isRemembered,
      treeIsOpen: this.isOpenKey,
      treeSetOpen: this.setOpenKey,
      treeRouteState: this.routeState
    };
  },

  methods: {
    // The one question every tree answers the same way and every dataset
    // differently: what names this item. A component extending the tree
    // overrides this; a page passes `item-key`.
    getTreeItemKey(item, parentKey) {
      if (this.itemKey) {
        return String(this.itemKey(item, parentKey));
      }

      const own = item.key ?? item.id;

      if (own !== undefined && own !== null) {
        return String(own);
      }

      const label = item.label ?? item.name ?? '';

      return parentKey ? `${parentKey}/${label}` : label;
    },

    stateKey() {
      return `ui.tree.${this.stateId}.open`;
    },

    readOpenKeys() {
      return this.stateId ? uiStateGet(this.app, this.stateKey(), []) : [];
    },

    // Whether this tree has something to restore. When it has not — never named,
    // or never touched — the nodes open as their items say.
    isRemembered() {
      return !!this.stateId && uiStateHas(this.app, this.stateKey());
    },

    isOpenKey(key) {
      return this.openState.keys.includes(key);
    },

    setOpenKey(key, open, persist = true) {
      const keys = this.openState.keys.filter(existing => existing !== key);

      if (open) {
        keys.push(key);
      }

      this.openState.keys = keys.slice(-OPEN_KEYS_MAX);

      if (persist && this.stateId) {
        uiStateSet(this.app, this.stateKey(), this.openState.keys);
      }
    },

    // The branches above an item, from the root down, given its key. The default
    // reads a key that is a path — `a/b/c.md` is under `a` and `a/b` — which is
    // what files and documents are. A tree whose keys are not paths overrides
    // this, or its selected item can only be restored where it is already drawn.
    getTreeItemAncestorKeys(key) {
      const parts = key.split('/');

      return parts.slice(0, -1).map((_, index) => parts.slice(0, index + 1).join('/'));
    },

    // Reads what the address asks for. On a reload the branches open as the
    // nodes mount; on the back button they are already drawn, so the ones that
    // are open select at once and the closed ones open through `reveal`.
    readRoute(fromHistory = false) {
      const key = locationQueryParamGet(this.routeParam) || null;

      this.routeState.key = key;
      this.routeState.reveal = key ? this.getTreeItemAncestorKeys(key) : [];

      if (fromHistory && key && this.keyItems.has(key)) {
        this.selectFromRoute(this.keyItems.get(key));
      }

      if (fromHistory && !key) {
        this.selection.items = [];
        this.$emit('select', []);
      }
    },

    selectFromRoute(item) {
      this.selection.items = [item];
      this.selection.anchor = item;
      this.$emit('select', this.selection.items);
    },

    registerRow(el, item, key) {
      this.rowItems.set(el, item);

      if (key === null || key === undefined) {
        return;
      }

      this.keyItems.set(key, item);

      // The item the address named has just been drawn: it is selected as a
      // click would, and the page opens it the way it opens one.
      if (key === this.routeState.key && !this.selection.items.includes(item)) {
        this.selectFromRoute(item);
      }
    },

    unregisterRow(el, key) {
      this.rowItems.delete(el);

      if (key !== null && key !== undefined) {
        this.keyItems.delete(key);
      }
    },

    // Clicking a row selects it and says so. What that means is the caller's to
    // decide — the tree only holds which ones they are.
    onSelect({ item, key, range, toggle }) {
      if (this.allowSelectMultiple && range) {
        this.selection.items = this.itemsBetweenAnchorAnd(item);
      } else if (this.allowSelectMultiple && toggle) {
        this.selection.items = this.selection.items.includes(item)
          ? this.selection.items.filter(selected => selected !== item)
          : [...this.selection.items, item];
        this.selection.anchor = item;
      } else {
        this.selection.items = [item];
        this.selection.anchor = item;
      }

      // One item picked is one place to be; a range or a set picked by hand is a
      // selection and not a place, so the address is left as it was.
      if (this.routeParam && this.selection.items.length === 1 && key
        && (!this.routable || this.routable(item))) {
        this.routeState.key = key;
        locationQueryParamSet(this.routeParam, key);
      }

      this.$emit('select', this.selection.items);
    },

    itemsBetweenAnchorAnd(item) {
      const visible = this.visibleItems();
      const from = visible.indexOf(this.selection.anchor);
      const to = visible.indexOf(item);

      // No anchor, or one that has since been folded away: the range has nothing
      // to stretch from and the click stands on its own.
      if (-1 === from || -1 === to) {
        return [item];
      }

      return visible.slice(Math.min(from, to), Math.max(from, to) + 1);
    },

    // Display order is the only order a range can mean, and the DOM is where it
    // is written: a node knows neither its siblings nor the levels unfolded above
    // it. Rows carrying no item, such as the one offering the rest of a level,
    // fall out on their own.
    visibleItems() {
      return Array.from(this.$el.querySelectorAll('.tree--row'))
        .map(el => this.rowItems.get(el))
        .filter(item => undefined !== item);
    }
  }
};
</script>
