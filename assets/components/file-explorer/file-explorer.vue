<script>
import Tree from '../tree/tree.vue';
import TreeRowDirectory from '../tree-row-directory/tree-row-directory.vue';
import DateDisplay from '../date-display/date-display.vue';
import Spinner from '../spinner/spinner.vue';
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';
import { fileExtension, fileIcon } from '../../js/Helper/FileIconHelper';
import { uiStateGet, uiStateSet } from '../../js/Helper/UiStateHelper';
import { bytesFormatBytes } from '@wexample/js-helpers/Helper/Bytes';

const translated = buildTranslatedBindings({
  resolvedRootLabel: ['rootLabel', 'WexampleSymfonyDesignSystemBundle.common.system::frontend.explorer.root']
});

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.explorer.';

// A file explorer the width of a page: the folders on the side, what the
// current folder holds as a list or a grid, the way back and forth above, what
// is selected below. It knows nothing of where the files come from: it asks
// `loadChildren` for a folder's content — the contract the tree already has —
// and takes back file entities, or anything with their fields: { name, path,
// type: directory | file, hasChildren, size, modifiedAt }.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-file-explorer-file-explorer',

  components: {
    DateDisplay,
    Spinner,
    Tree,
    TreeRowDirectory
  },

  props: {
    ...translated.props,
    // Called with the folder opened — an item, or { path, type } when it is
    // reached by its path — or null for the root, and a page number. Answers
    // { items, total }: total is what the folder holds in all, which is how the
    // explorer knows there is more to ask for.
    loadChildren: {
      type: Function,
      required: true
    },
    // Where it opens: a folder's path, or null for the root.
    initialPath: {
      type: String,
      default: null
    },
    // Names the explorer so its view, its order and the folders open in its
    // tree come back with the page.
    stateId: {
      type: String,
      default: null
    },
    // The folders on the side; off, the explorer is the listing alone.
    showTree: {
      type: Boolean,
      default: true
    },
    defaultView: {
      type: String,
      default: 'list'
    }
  },

  emits: ['navigate', 'select', 'open'],

  data() {
    return {
      path: this.initialPath,
      backStack: [],
      forwardStack: [],
      items: [],
      total: 0,
      page: 0,
      loading: false,
      failed: false,
      selectedKeys: [],
      anchorIndex: null,
      cursorIndex: -1,
      filter: '',
      view: this.stateValue('view', this.defaultView),
      sort: this.stateValue('sort', { key: 'name', direction: 1 }),
      rootDirectories: [],
      treeGeneration: 0
    };
  },

  computed: {
    ...translated.computed,

    // The path cut into the folders leading to it, the root first.
    segments() {
      const parts = (this.path || '').split('/').filter(Boolean);
      const segments = [{ label: this.resolvedRootLabel, path: null }];

      parts.forEach((part, index) => {
        segments.push({ label: part, path: parts.slice(0, index + 1).join('/') });
      });

      return segments;
    },

    // What the folder holds, narrowed by the filter and in the order asked for,
    // the folders always before the files.
    shownItems() {
      const needle = this.filter.trim().toLowerCase();
      const items = needle
        ? this.items.filter((item) => String(item.name ?? '').toLowerCase().includes(needle))
        : [...this.items];
      const { key, direction } = this.sort;
      const value = (item) => {
        if (key === 'size') return this.isDirectory(item) ? -1 : Number(item.size ?? 0);
        if (key === 'modifiedAt') return item.modifiedAt ? new Date(item.modifiedAt).getTime() : 0;
        if (key === 'kind') return this.isDirectory(item) ? '' : fileExtension(String(item.name ?? ''));

        return String(item.name ?? '');
      };

      return items.sort((a, b) => {
        const folders = Number(this.isDirectory(b)) - Number(this.isDirectory(a));

        if (folders !== 0) {
          return folders;
        }

        const left = value(a);
        const right = value(b);
        const compared = typeof left === 'number' && typeof right === 'number'
          ? left - right
          : String(left).localeCompare(String(right), undefined, { numeric: true, sensitivity: 'base' });

        return compared * direction;
      });
    },

    selectedItems() {
      return this.shownItems.filter((item) => this.selectedKeys.includes(this.itemKey(item)));
    },

    hasMore() {
      return this.items.length < this.total;
    },

    status() {
      const count = this.trans(`${T}count`, { '%count%': this.shownItems.length });

      if (!this.selectedItems.length) {
        return count;
      }

      const size = this.selectedItems
        .filter((item) => !this.isDirectory(item))
        .reduce((sum, item) => sum + Number(item.size ?? 0), 0);

      return `${count} · ${this.trans(`${T}selected`, { '%count%': this.selectedItems.length, '%size%': this.formatBytes(size) })}`;
    },

    treeStateId() {
      return this.stateId ? `${this.stateId}.tree` : null;
    }
  },

  mounted() {
    this.load(true);

    if (this.showTree) {
      this.loadRootDirectories();
    }
  },

  methods: {
    t(key, parameters = {}) {
      return this.trans(`${T}${key}`, parameters);
    },

    stateValue(name, fallback) {
      return this.stateId ? uiStateGet(this.app, `ui.explorer.${this.stateId}.${name}`, fallback) : fallback;
    },

    remember(name, value) {
      if (this.stateId) {
        uiStateSet(this.app, `ui.explorer.${this.stateId}.${name}`, value);
      }
    },

    itemKey(item) {
      return item.path ?? item.id ?? item.name;
    },

    isDirectory(item) {
      return item?.type === 'directory';
    },

    iconHtml(item) {
      return this.app.getServiceOrFail('icon').icon(fileIcon(String(item.name ?? ''), this.isDirectory(item)));
    },

    renderIcon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    formatBytes(bytes) {
      return bytes > 0 ? bytesFormatBytes(bytes, 1) : '0 B';
    },

    sizeLabel(item) {
      return this.isDirectory(item) ? '' : this.formatBytes(Number(item.size ?? 0));
    },

    kindLabel(item) {
      if (this.isDirectory(item)) {
        return this.t('folder');
      }

      const extension = fileExtension(String(item.name ?? ''));

      return extension ? extension.toUpperCase() : this.t('file');
    },

    // The folder being shown, as the loader expects one.
    currentFolder() {
      return this.path ? { path: this.path, type: 'directory', name: this.segments[this.segments.length - 1].label } : null;
    },

    async load(reset) {
      this.loading = true;
      this.failed = false;

      if (reset) {
        this.page = 0;
      }

      try {
        const result = await this.loadChildren(this.currentFolder(), this.page);
        const items = result?.items ?? [];

        this.items = reset ? items : [...this.items, ...items];
        this.total = result?.total ?? this.items.length;
      } catch (error) {
        this.failed = true;
      } finally {
        this.loading = false;
      }
    },

    loadMore() {
      this.page += 1;
      this.load(false);
    },

    // The folders alone, for the tree: files open in the listing.
    async loadDirectories(item, page) {
      const result = await this.loadChildren(item, page);
      const items = result?.items ?? [];
      const directories = items.filter((entry) => this.isDirectory(entry));

      return {
        items: directories,
        total: Math.max(directories.length, (result?.total ?? items.length) - (items.length - directories.length))
      };
    },

    async loadRootDirectories() {
      this.rootDirectories = (await this.loadDirectories(null, 0)).items;
    },

    navigate(path, record = true) {
      const target = path || null;

      if (target === this.path) {
        return;
      }

      if (record) {
        this.backStack.push(this.path);
        this.forwardStack = [];
      }

      this.path = target;
      this.filter = '';
      this.clearSelection();
      this.load(true);
      this.$emit('navigate', this.path);
    },

    goBack() {
      if (this.backStack.length) {
        this.forwardStack.push(this.path);
        this.navigate(this.backStack.pop(), false);
      }
    },

    goForward() {
      if (this.forwardStack.length) {
        this.backStack.push(this.path);
        this.navigate(this.forwardStack.pop(), false);
      }
    },

    goUp() {
      if (this.path) {
        this.navigate(this.segments[this.segments.length - 2].path);
      }
    },

    refresh() {
      this.load(true);

      if (this.showTree) {
        this.treeGeneration += 1;
        this.loadRootDirectories();
      }
    },

    setView(view) {
      this.view = view;
      this.remember('view', view);
    },

    sortBy(key) {
      this.sort = { key, direction: this.sort.key === key ? -this.sort.direction : 1 };
      this.remember('sort', this.sort);
    },

    sortMark(key) {
      if (this.sort.key !== key) {
        return '';
      }

      return this.sort.direction > 0 ? 'ph:bold/caret-up' : 'ph:bold/caret-down';
    },

    clearSelection() {
      this.selectedKeys = [];
      this.anchorIndex = null;
      this.cursorIndex = -1;
      this.$emit('select', []);
    },

    // A click selects the item; with ctrl or cmd it is added or taken away,
    // with shift everything from the last plain click to it.
    select(index, event = {}) {
      const item = this.shownItems[index];
      const key = this.itemKey(item);

      if (event.shiftKey && this.anchorIndex !== null) {
        const [from, to] = [this.anchorIndex, index].sort((a, b) => a - b);
        this.selectedKeys = this.shownItems.slice(from, to + 1).map((entry) => this.itemKey(entry));
      } else if (event.ctrlKey || event.metaKey) {
        this.selectedKeys = this.selectedKeys.includes(key)
          ? this.selectedKeys.filter((selected) => selected !== key)
          : [...this.selectedKeys, key];
        this.anchorIndex = index;
      } else {
        this.selectedKeys = [key];
        this.anchorIndex = index;
      }

      this.cursorIndex = index;
      this.$emit('select', this.selectedItems);
    },

    isSelected(item) {
      return this.selectedKeys.includes(this.itemKey(item));
    },

    // A folder is walked into; a file is handed to the page, which knows what
    // opening it means — a preview, a download, an editor.
    open(item) {
      if (this.isDirectory(item)) {
        this.navigate(item.path);
      } else {
        this.$emit('open', item);
      }
    },

    // How many items stand on one line of the grid, read off the drawn items.
    gridColumns() {
      const cells = this.$refs.main?.querySelectorAll('.file-explorer--cell');

      if (!cells?.length) {
        return 1;
      }

      const top = cells[0].offsetTop;
      let columns = 0;

      while (columns < cells.length && cells[columns].offsetTop === top) {
        columns += 1;
      }

      return Math.max(columns, 1);
    },

    onKeyDown(event) {
      const count = this.shownItems.length;
      const step = this.view === 'grid' ? this.gridColumns() : 1;
      const moves = {
        ArrowDown: step,
        ArrowUp: -step,
        ArrowRight: this.view === 'grid' ? 1 : 0,
        ArrowLeft: this.view === 'grid' ? -1 : 0
      };

      if (event.key in moves && moves[event.key] !== 0 && count) {
        event.preventDefault();
        const next = Math.min(Math.max((this.cursorIndex < 0 ? -moves[event.key] : this.cursorIndex) + moves[event.key], 0), count - 1);
        this.select(next, { shiftKey: event.shiftKey });
        this.$nextTick(() => this.$refs.main?.querySelector('.is-cursor')?.scrollIntoView({ block: 'nearest' }));
      } else if (event.key === 'Enter' && this.shownItems[this.cursorIndex]) {
        event.preventDefault();
        this.open(this.shownItems[this.cursorIndex]);
      } else if (event.key === 'Backspace' && !event.target.closest('input')) {
        event.preventDefault();
        this.goUp();
      } else if ((event.ctrlKey || event.metaKey) && event.key === 'a' && count) {
        event.preventDefault();
        this.selectedKeys = this.shownItems.map((item) => this.itemKey(item));
        this.$emit('select', this.selectedItems);
      } else if (event.key === 'Escape') {
        this.clearSelection();
      }
    },

    onTreeSelect(items) {
      const item = items?.[0];

      if (item && this.isDirectory(item)) {
        this.navigate(item.path);
      }
    }
  }
};
</script>
