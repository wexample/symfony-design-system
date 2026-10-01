<script>
import DateDisplay from '../date-display/date-display.vue';
import Spinner from '../spinner/spinner.vue';
import ButtonTarget from '../button-target/button-target.vue';
import Marker from '../marker/marker.vue';
import Pagination from '../pagination/pagination.vue';
import FilePath from '../file-path/file-path.vue';
import FilterBar from '../filter-bar/filter-bar.vue';
import { filterMatches, filterTextMatches } from '../../js/Helper/FilterHelper';
import { sortApply, sortAria, sortNext, sortValueAt } from '../../js/Helper/SortHelper';
import { filterSelected } from '../../js/Helper/FilterHelper';
import { rowLinkClick } from '../../js/Helper/RowLinkHelper';
import buildTranslatedBindings from "../../js/Helper/TranslationHelper";

const translated = buildTranslatedBindings({
  resolvedEmptyFilteredLabel: [
    'emptyFilteredLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.table.empty_filtered'
  ],
  resolvedLoadingLabel: [
    'loadingLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.loading'
  ],
  resolvedEmptyLabel: [
    'emptyLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.no_data'
  ]
});

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-data-table-data-table',

  components: {
    ButtonTarget,
    DateDisplay,
    FilePath,
    FilterBar,
    Pagination,
    Spinner,
    // Registered as `capsule`: `marker` is an svg element, which vue refuses
    // as a component id and renders as itself — an invisible one.
    Capsule: Marker
  },

  props: {
    rows: {
      type: Array,
      required: true,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    // Names a row across refreshes. With it, a re-read collection patches its
    // rows in place; without it, position is all a row has, and every refresh
    // redraws them all.
    rowKey: {
      type: Function,
      default: null
    },
    ...translated.props,
    columns: {
      type: Array,
      default: () => []
    },
    app: {
      type: Object,
      required: true
    },
    showHeader: {
      type: Boolean,
      default: false
    },
    // The header stays in view while the rows scroll under it. The page says
    // how far from the top it stops, through --table-sticky-top.
    sticky: {
      type: Boolean,
      default: false
    },
    // The table scrolls sideways on its own instead of overflowing to what
    // scrolls around it: for a container that does not scroll.
    scroll: {
      type: Boolean,
      default: false
    },
    // One row in two on a faint ground, for wide tables read across.
    striped: {
      type: Boolean,
      default: true
    },
    // The whole row lit under the pointer.
    hover: {
      type: Boolean,
      default: true
    },
    // Rows that can be ticked, a box at the head of each. What names a row in
    // the selection is its key, so a table that selects wants a `rowKey`.
    selectable: {
      type: Boolean,
      default: false
    },
    // The ticked keys, for a parent that holds them (`v-model:selected`).
    // Left null, the table holds them itself.
    selected: {
      type: Array,
      default: null
    },
    // What can be done to the ticked rows: { key, label, icon, href, token,
    // class } — `class` for the one that leads, `button--invert`.
    // One marked `all` acts on every row, ticked or not, and can be pressed
    // with nothing ticked — "fix all" beside "fix selected".
    // One with an href posts them there, as the server table does; every one
    // is also emitted as `bulk-action`, for a parent that acts itself.
    bulkActions: {
      type: Array,
      default: () => []
    },
    // `buttons` when there are few, `select` when there are enough to crowd
    // the bar.
    bulkActionsMode: {
      type: String,
      default: 'buttons'
    },
    // The name the ticked keys are posted under.
    selectionName: {
      type: String,
      default: 'ids[]'
    },
    // How many rows the table holds, at the end of the bar above it.
    showCount: {
      type: Boolean,
      default: false
    },
    // How many there are in all, for a table holding one page of them — the
    // api says it; left null, the table counts the rows it holds.
    total: {
      type: Number,
      default: null
    },
    // A filter bar at the head of the table: [{ key, label, options, multiple }].
    // What it holds comes back through `v-model:filter-values`, for the page to
    // ask its api with — the rows it gets back are already the narrowed ones.
    filters: {
      type: Array,
      default: () => []
    },
    // The filters' values, for a parent that holds them. Left null, the table
    // holds them itself.
    filterValues: {
      type: Object,
      default: null
    },
    // The table narrows the rows it was given itself, reading each row's field
    // of a filter's key: for a list held whole in the page rather than asked
    // for.
    filterRows: {
      type: Boolean,
      default: false
    },
    // Rows shown at once, the others a page turn away. Paged here, on the rows
    // the table was given: a list the server pages hands one page at a time
    // and a pagination of its own. 0 shows them all.
    pageSize: {
      type: Number,
      default: 0
    },
    // The order the list opens on, and comes back to: { key, direction }, the
    // key a column's `sortKey` (or `key`) when it is `sortable`. Its header
    // says so from the start — one always knows what the list is sorted by.
    defaultSort: {
      type: Object,
      default: null
    },
    // The order pressed on a header, for a parent that holds it
    // (`v-model:sort`) — always a state, the default one once the presses come
    // back to it. Left null, the table holds it itself.
    sort: {
      type: Object,
      default: null
    },
    // The table orders the rows it was given itself, all of them before
    // paging: for a list held whole in the page. Left false, it only says what
    // was pressed, for the page to ask its api with — a table holding one page
    // of a list cannot sort the list.
    sortRows: {
      type: Boolean,
      default: false
    },
    // The locale words are compared in; the page's when left empty.
    sortLocale: {
      type: String,
      default: null
    },
    // A search box at the head of the table, narrowing the rows it holds to
    // those whose shown text holds what is typed — case and accents aside —
    // with the filters, before the order and the pages. A column is searched
    // unless it says `searchable: false`; actions, status and date columns
    // never are, their text being an icon, a state or a worded instant.
    searchable: {
      type: Boolean,
      default: false
    },
    // The table narrows the rows it holds with what is typed itself: for a
    // list held whole in the page. Left false, it only says what was typed
    // (`update:search`), for the page to ask its api with — a table holding
    // one page of a list cannot search the list.
    searchRows: {
      type: Boolean,
      default: true
    },
    // What the box says it searches, in place of the generic word.
    searchPlaceholder: {
      type: String,
      default: ''
    },
    // Why the rows could not be read, in place of them, with a way to try
    // again (`retry`): never a blank table.
    error: {
      type: String,
      default: ''
    }
  },

  emits: ['update:selected', 'bulk-action', 'update:filterValues', 'update:sort', 'update:search', 'retry'],

  data() {
    return {
      ownSelected: [],
      bulkActionIndex: '',
      ownFilterValues: {},
      ownSort: null,
      search: '',
      // Said to a screen reader once a header is pressed, not on load.
      statusMessage: '',
      page: 0
    };
  },

  computed: {
    ...translated.computed,

    resolvedFilterValues() {
      return this.filterValues ?? this.ownFilterValues;
    },

    resolvedSort() {
      return this.sort ?? this.ownSort ?? this.defaultSort;
    },

    // The rows the table works on: all it was given, or those passing its
    // filters when it narrows them itself, in its own order when it sorts them
    // — the whole list, so that the pages cut it after.
    shownRows() {
      let rows = this.filterRows
        ? (this.rows || []).filter((row) => this.isGroupRow(row) || filterMatches(row, this.resolvedFilterValues))
        : (this.rows || []);

      if (this.searchable && this.searchRows && this.search.trim()) {
        rows = this.dropEmptyGroups(rows.filter((row) => this.isGroupRow(row) || this.rowMatchesSearch(row)));
      }

      if (!this.sortRows || !this.resolvedSort) {
        return rows;
      }

      const column = this.columns.find((entry) => this.isColumnSortable(entry)
        && this.getColumnSortKey(entry) === this.resolvedSort.key);

      return sortApply(rows, this.resolvedSort, {
        locale: this.resolvedSortLocale,
        value: (row, key) => (typeof column?.sortValue === 'function' ? column.sortValue(row) : sortValueAt(row, key)),
        isFixed: (row) => this.isGroupRow(row)
      });
    },

    // Server locales write `fr_FR`, Intl wants `fr-FR`.
    resolvedSortLocale() {
      return this.sortLocale
        || this.app?.layout?.vars?.locale
        || (typeof document !== 'undefined' ? document.documentElement.lang : '')
        || null;
    },

    // Where a row's key comes from when the table is given no `rowKey`: its
    // place among the rows it was given, which neither a filter nor an order
    // moves.
    rowIndexes() {
      return new Map((this.rows || []).map((row, index) => [row, index]));
    },

    hasBar() {
      return this.searchable || this.hasBulkActions || this.showCount || this.filters.length > 0;
    },

    searchedColumns() {
      return this.columns.filter((column) => typeof column === 'string' || (
        column?.searchable !== false
        && !this.hasCellActions(column)
        && !['status', 'date', 'html'].includes(column?.cell)
      ));
    },

    searchIconHtml() {
      return this.renderIcon('ph:bold/magnifying-glass');
    },

    pagesCount() {
      return this.pageSize > 0 ? Math.ceil(this.shownRows.length / this.pageSize) : 0;
    },

    pageOffset() {
      return this.pageSize > 0 ? this.page * this.pageSize : 0;
    },

    // The rows on screen: all of them, or the current page.
    visibleRows() {
      const rows = this.shownRows;

      return this.pageSize > 0 ? rows.slice(this.pageOffset, this.pageOffset + this.pageSize) : rows;
    },

    selectedKeys() {
      return this.selected ?? this.ownSelected;
    },

    selectableKeys() {
      return this.shownRows
        .filter((row) => !this.isGroupRow(row))
        .map((row) => this.getRowKey(row));
    },

    totalCount() {
      return this.total ?? this.shownRows.filter((row) => !this.isGroupRow(row)).length;
    },

    selectedCount() {
      return this.selectedKeys.length;
    },

    allSelected() {
      return this.selectableKeys.length > 0 && this.selectedCount === this.selectableKeys.length;
    },

    someSelected() {
      return this.selectedCount > 0 && !this.allSelected;
    },

    hasBulkActions() {
      return this.selectable && this.bulkActions.length > 0;
    }
  },

  watch: {
    // What is no longer on screen cannot be acted on: a refresh, a filter or a
    // page turn drops the keys it took away.
    shownRows() {
      // Fewer rows than before can leave the page past the last one.
      if (this.pagesCount && this.page > this.pagesCount - 1) {
        this.page = this.pagesCount - 1;
      }

      const present = new Set(this.selectableKeys);
      const kept = this.selectedKeys.filter((key) => present.has(key));

      if (kept.length !== this.selectedKeys.length) {
        this.setSelected(kept);
      }
    }
  },

  methods: {
    // A row carrying only a group is the line between two runs of rows, given
    // a word: it spans the table and names what follows.
    isGroupRow(row) {
      return Boolean(row) && row.group !== undefined;
    },

    isPathCell(column) {
      return column?.cell === 'path';
    },

    // Where a cell leads when its column names a route: the same `route`,
    // `params` and `target` an action takes, read off the column.
    getCellLink(row, column) {
      if (!column?.route) {
        return null;
      }

      const parameters = typeof column.params === 'function'
        ? column.params(row, column)
        : (column.params ?? {});

      return {
        href: this.app.getServiceOrFail('routing').path(column.route, parameters),
        target: column.target ?? '',
        targetOptions: column.targetOptions ?? {},
      };
    },

    isMarkerCell(column) {
      return column?.cell === 'status';
    },

    // A type name, or { type, count, title, label }: the circle says which
    // state, the count how many of it.
    getMarker(value) {
      if (!value) {
        return null;
      }

      return typeof value === 'object' ? value : { type: value };
    },

    getEmptyColspan() {
      return (this.columns && this.columns.length ? this.columns.length : 1)
        + (this.selectable ? 1 : 0);
    },

    transTable(name, args = {}) {
      return this.trans(`WexampleSymfonyDesignSystemBundle.common.system::frontend.table.${name}`, args);
    },

    isRowSelected(row) {
      return this.selectedKeys.includes(this.getRowKey(row));
    },

    // Narrowed differently, the list starts again from its first page.
    setFilterValues(values) {
      this.ownFilterValues = values;
      this.page = 0;
      this.$emit('update:filterValues', values);
    },

    // Sortable only when the column says so: an actions, a selection or a
    // computed status column has no order of its own.
    isColumnSortable(column) {
      return typeof column === 'object' && column?.sortable === true;
    },

    // What the column is sorted on, which is not always what it shows: a date
    // shown worded sorts on its value, a name shown "First Last" on the last.
    getColumnSortKey(column) {
      return column?.sortKey ?? this.getColumnKey(column);
    },

    isColumnSorted(column) {
      return this.isColumnSortable(column) && this.resolvedSort?.key === this.getColumnSortKey(column);
    },

    getSortAria(column) {
      return this.isColumnSortable(column) ? sortAria(this.resolvedSort, this.getColumnSortKey(column)) : null;
    },

    getSortIcon(column) {
      return {
        ascending: 'ph:bold/caret-up',
        descending: 'ph:bold/caret-down'
      }[this.getSortAria(column)] ?? 'ph:bold/caret-up-down';
    },

    // Ascending, descending, then back to the default order. Sorted
    // differently, the list starts again from its first page; the ticked rows
    // stay ticked, being named by their key and not their place.
    toggleSort(column) {
      const next = sortNext(this.resolvedSort, this.getColumnSortKey(column), this.defaultSort);
      const sorted = next && this.columns.find((entry) => this.isColumnSortable(entry)
        && this.getColumnSortKey(entry) === next.key);

      this.ownSort = next;
      this.page = 0;
      this.statusMessage = next
        ? this.transTable(`sorted_${next.direction}`, { '%column%': sorted ? this.getColumnLabel(sorted) : next.key })
        : this.transTable('sort_reset');
      this.$emit('update:sort', next);
    },

    setSelected(keys) {
      this.ownSelected = keys;
      this.$emit('update:selected', keys);
    },

    toggleRow(row, checked) {
      const key = this.getRowKey(row);
      const others = this.selectedKeys.filter((selected) => selected !== key);

      this.setSelected(checked ? [...others, key] : others);
    },

    toggleAll(checked) {
      this.setSelected(checked ? [...this.selectableKeys] : []);
    },

    canRunBulkAction(action) {
      return Boolean(action) && (action.all || this.selectedCount > 0);
    },

    applyBulkSelect() {
      const action = this.bulkActions[this.bulkActionIndex];

      if (action) {
        this.runBulkAction(action);
      }
    },

    // Told to the parent in any case; posted too when the action has an
    // address, the way the server table posts it.
    runBulkAction(action) {
      const keys = action.all ? [...this.selectableKeys] : [...this.selectedKeys];

      this.$emit('bulk-action', {
        action,
        keys,
        rows: this.shownRows.filter((row) => !this.isGroupRow(row) && keys.includes(this.getRowKey(row)))
      });

      if (!action.href) {
        return;
      }

      const form = document.createElement('form');
      form.method = 'post';
      form.action = action.href;

      const fields = keys.map((key) => [this.selectionName, key]);
      if (action.token) {
        fields.push(['_token', action.token]);
      }

      fields.forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = String(value);
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
    },
    hasRows() {
      return this.shownRows.length > 0;
    },

    // Nothing to show because the filters leave nothing, which is not the
    // same as nothing at all: the way out is to loosen them.
    // Nothing because of the filters or the search, and the way out of them.
    isFilteredEmpty() {
      return !this.hasRows() && (
        (this.searchable && this.search.trim() !== '')
        || this.filters.some((filter) => filterSelected(this.resolvedFilterValues, filter.key).length > 0)
      );
    },

    clearFilters() {
      if (this.search !== '') {
        this.search = '';
        this.$emit('update:search', '');
      }
      this.setFilterValues({});
    },

    rowMatchesSearch(row) {
      return filterTextMatches(this.search, ...this.searchedColumns.map((column) => String(this.getCellValue(row, column) ?? '')));
    },

    // A group heading with none of its rows left goes too.
    dropEmptyGroups(rows) {
      return rows.filter((row, index) => !this.isGroupRow(row)
        || (index + 1 < rows.length && !this.isGroupRow(rows[index + 1])));
    },

    // Searched differently, the list starts again from its first page, and
    // says how much it found.
    onSearchInput(event) {
      this.search = event.target.value;
      this.page = 0;
      this.$emit('update:search', this.search);

      // What the api finds is not known yet: the page says it once it is.
      if (!this.searchRows) {
        return;
      }

      this.statusMessage = this.search.trim()
        ? this.transTable('results', { '%count%': this.shownRows.filter((row) => !this.isGroupRow(row)).length })
        : '';
    },

    getRowKey(row) {
      return this.rowKey ? this.rowKey(row) : this.rowIndexes.get(row);
    },
    // One action in the whole row, and a link: the row is pressed as that link.
    isRowLink(row) {
      if (this.isGroupRow(row)) {
        return false;
      }

      const actions = this.columns
        .filter((column) => this.hasCellActions(column))
        .flatMap((column) => this.getCellActions(row, column));

      return actions.length === 1 && Boolean(actions[0].href) && actions[0].method !== 'post';
    },

    onRowClick(event) {
      rowLinkClick(event);
    },

    hasCellActions(column) {
      return column?.cell === 'actions'
        || Boolean(column?.action || (Array.isArray(column?.actions) && column.actions.length));
    },

    getCellActions(row, column) {
      // An actions cell whose column declares none reads them off the row,
      // ready made — the server table's contract: { href, icon, label, method,
      // token, target, target_options }, the addresses already resolved.
      if (column?.cell === 'actions' && !column.actions && !column.action) {
        const own = row?.[this.getColumnKey(column)];

        return (Array.isArray(own) ? own : []).map((action) => ({
          href: action.href ?? '',
          target: action.target ?? '',
          targetOptions: action.targetOptions ?? action.target_options ?? {},
          method: String(action.method ?? 'get').toLowerCase(),
          token: action.token,
          label: action.label,
          icon: action.icon ?? '',
        })).filter((entry) => entry.icon);
      }

      const actions = column?.actions
          ? (Array.isArray(column.actions) ? column.actions : [column.actions])
          : (column?.action ? [column.action] : []);

      if (!actions.length) {
        return [];
      }

      const routingService = this.app.getServiceOrFail('routing');
      const defaultIcons = {
        show: 'ph:bold/eye',
        edit: 'ph:bold/pencil-simple',
      };

      return actions.map((action) => {
        const actionName = typeof action === 'string'
            ? action
            : (action?.name || action?.action);

        const iconName = typeof action === 'object'
            ? (action.icon || defaultIcons[actionName])
            : defaultIcons[actionName];

        const route = typeof action === 'object'
            ? action.route
            : undefined;

        const routeName = route || (column?.routePrefix && actionName
            ? `${column.routePrefix}_${actionName}`
            : undefined);

        const params = typeof action === 'object' && action.params !== undefined
            ? action.params
            : column?.params;

        const parameters = typeof params === 'function'
            ? params(row, column, action)
            : (params ?? {});

        const href = routeName
            ? routingService.path(routeName, parameters)
            : '';

        const target = typeof action === 'object' && action.target !== undefined
            ? action.target
            : column?.target;

        const targetOptions = typeof action === 'object' && action.targetOptions !== undefined
            ? action.targetOptions
            : column?.targetOptions;

        const method = typeof action === 'object' && action.method
            ? String(action.method).toLowerCase()
            : 'get';

        return {
          href,
          target: target ?? '',
          targetOptions: targetOptions ?? {},
          method,
          token: typeof action === 'object' ? action.token : undefined,
          label: typeof action === 'object' ? action.label : undefined,
          icon: iconName ?? '',
        };
      }).filter((entry) => entry.icon);
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    },

    getCellIcon(row, column) {
      if (!column?.icon) {
        return '';
      }

      const icon = typeof column.icon === 'function'
          ? column.icon(row, column)
          : column.icon;

      if (!icon) {
        return '';
      }

      const iconService = this.app.getServiceOrFail('icon');
      return iconService.icon(icon);
    },

    getColumnKey(column, index) {
      if (typeof column === 'string') {
        return column;
      }

      if (column?.key) {
        return column.key;
      }

      if (column?.action) {
        return `action-${column.action}`;
      }

      if (Array.isArray(column?.actions)) {
        return `actions-${column.actions.join('-')}`;
      }

      return `column-${index}`;
    },

    getColumnLabel(column) {
      if (typeof column === 'string') {
        return this.trans(`@vue::table.column.${column}.title`);
      }

      if (column?.label === false) {
        return '';
      }

      if (column?.label !== undefined && column?.label !== null) {
        return column.label;
      }

      if (column?.key) {
        return this.trans(`@vue::table.column.${column.key}.title`);
      }

      return column?.key ?? '';
    },

    getCellValue(row, column) {
      const key = this.getColumnKey(column);
      if (!row || !key) {
        return '';
      }

      if (key.includes('.')) {
        return key.split('.').reduce((value, part) => {
          if (value === null || value === undefined) {
            return '';
          }
          return value[part];
        }, row) ?? '';
      }

      const value = row[key] ?? '';

      if (typeof column?.format === 'function') {
        return column.format(value, row, column);
      }

      return value;
    },

    getCellHref(row, column) {
      const href = column?.href;
      if (!href) {
        return '';
      }

      if (typeof href === 'function') {
        return href(row, column);
      }

      if (typeof href === 'string') {
        return href;
      }

      if (typeof href === 'object' && href.route) {
        const parameters =
            typeof href.parameters === 'function'
                ? href.parameters(row, column)
                : href.parameters ?? {};

        const routingService = this.app.getServiceOrFail('routing');
        return routingService.path(href.route, parameters);
      }

      return '';
    },

    isHtmlCell(column) {
      return column?.html === true || column?.cell === 'html';
    },

    // A date the reader is meant to situate rather than read: the cell hands it
    // to the component that owns its own redraw, so "2 min ago" stays true while
    // the page is left open — which a formatted string cannot.
    isDateCell(column) {
      return column?.cell === 'date';
    },

    getDateFormat(column) {
      return column?.dateFormat ?? 'auto';
    },

    getDateTitleFormat(column) {
      return column?.dateTitleFormat ?? 'date_time_full';
    },

    getColumnClass(column) {
      const classes = [];
      if (column?.className) classes.push(column.className);
      if (column?.align) classes.push(`table--cell--${column.align}`);
      if (column?.cell === 'date') classes.push('table--cell--nowrap');
      if (column?.width) classes.push(`table--cell--width-${column.width}`);
      if (column?.secondary) classes.push('table--cell--secondary');
      return classes.length ? classes.join(' ') : undefined;
    }
  }
};
</script>
