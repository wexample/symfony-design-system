<script>
import DateDisplay from '../date-display/date-display.vue';
import Spinner from '../spinner/spinner.vue';
import ButtonTarget from '../button-target/button-target.vue';
import Marker from '../marker/marker.vue';
import Pagination from '../pagination/pagination.vue';
import FilePath from '../file-path/file-path.vue';
import buildTranslatedBindings from "../../js/Helper/TranslationHelper";

const translated = buildTranslatedBindings({
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
    // One row in two on a faint ground, for wide tables read across.
    striped: {
      type: Boolean,
      default: false
    },
    // The whole row lit under the pointer.
    hover: {
      type: Boolean,
      default: false
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
    // What can be done to the ticked rows: { key, label, icon, href, token }.
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
    // Rows shown at once, the others a page turn away. Paged here, on the rows
    // the table was given: a list the server pages hands one page at a time
    // and a pagination of its own. 0 shows them all.
    pageSize: {
      type: Number,
      default: 0
    }
  },

  emits: ['update:selected', 'bulk-action'],

  data() {
    return {
      ownSelected: [],
      bulkActionIndex: '',
      page: 0
    };
  },

  computed: {
    ...translated.computed,

    pagesCount() {
      return this.pageSize > 0 ? Math.ceil((this.rows || []).length / this.pageSize) : 0;
    },

    pageOffset() {
      return this.pageSize > 0 ? this.page * this.pageSize : 0;
    },

    // The rows on screen: all of them, or the current page.
    visibleRows() {
      const rows = this.rows || [];

      return this.pageSize > 0 ? rows.slice(this.pageOffset, this.pageOffset + this.pageSize) : rows;
    },

    selectedKeys() {
      return this.selected ?? this.ownSelected;
    },

    selectableKeys() {
      return (this.rows || [])
        .map((row, index) => (this.isGroupRow(row) ? null : this.getRowKey(row, index)))
        .filter((key) => key !== null);
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
    // What is no longer on screen cannot be acted on: a refresh or a page turn
    // drops the keys it took away.
    rows() {
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

    isRowSelected(row, index) {
      return this.selectedKeys.includes(this.getRowKey(row, index));
    },

    setSelected(keys) {
      this.ownSelected = keys;
      this.$emit('update:selected', keys);
    },

    toggleRow(row, index, checked) {
      const key = this.getRowKey(row, index);
      const others = this.selectedKeys.filter((selected) => selected !== key);

      this.setSelected(checked ? [...others, key] : others);
    },

    toggleAll(checked) {
      this.setSelected(checked ? [...this.selectableKeys] : []);
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
      const keys = [...this.selectedKeys];

      this.$emit('bulk-action', {
        action,
        keys,
        rows: this.rows.filter((row, index) => keys.includes(this.getRowKey(row, index)))
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
      return Array.isArray(this.rows) && this.rows.length > 0;
    },
    getRowKey(row, index) {
      return this.rowKey ? this.rowKey(row) : index;
    },
    hasCellActions(column) {
      return Boolean(column?.action || (Array.isArray(column?.actions) && column.actions.length));
    },

    getCellActions(row, column) {
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
      if (column?.secondary) classes.push('table--cell--secondary');
      return classes.length ? classes.join(' ') : undefined;
    }
  }
};
</script>
