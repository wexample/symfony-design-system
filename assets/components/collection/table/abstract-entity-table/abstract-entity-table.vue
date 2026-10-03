<script>
import DataTable from "../../../data-table/data-table.vue";
import Pagination from "../../../pagination/pagination.vue";
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import DateService from "@wexample/symfony-loader/js/Services/DateService";
import { sortToQuery } from "../../../../js/Helper/SortHelper";
import { uiStateGet, uiStateSet } from "../../../../js/Helper/UiStateHelper";

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-abstract-entity-table",

  mixins: [AbstractEntityCollectionVueMixin],

  props: {
    // What the page narrows the collection to, sent along with the query: an
    // establishment, a patient — keys the api declares.
    filters: {
      type: Object,
      default: () => ({})
    }
  },
  components: {
    DataTable,
    Pagination
  },

  data() {
    return {
      columns: [],
      showHeader: true,
      // The header stays in view while the rows scroll under it.
      sticky: false,
      // The whole row rather than the width of its columns.
      fill: false,
      // What the table says when the api holds nothing; its own default when null.
      emptyLabel: null,
      // Its pages below at the foot of the page when the rows leave room
      // above it, rather than right under a short list.
      pagesAtFoot: false,
      // Set to null to fetch the whole collection in a single request.
      pageLength: 10,
      // A search box above the rows, its words sent to the api as `search`.
      searchable: false,
      searchPlaceholder: '',
      // How many the api holds in all, above the rows.
      showCount: false,
      // The order the list opens on: { key, direction }, the key a `sortable`
      // column's `sortKey` (or `key`). Sent to the api as `sort`, `-key` when
      // it runs down — the api must allow that key.
      defaultSort: null,
      sort: null,
      search: '',
      // Words typed in a row make one request, once the typing pauses.
      searchDelayMs: 300,
      searchTimer: null,
      // Menus in the table's bar the reader narrows the list with,
      // `{ key, label, options: [{ value, label }], multiple }`: what they hold
      // is sent to the api with the query, under each filter's key.
      barFilters: [],
      barFilterValues: {}
    };
  },

  created() {
    this.columns = this.processColumns(this.getColumnsConfiguration());
    this.restoreTableState();
  },

  beforeUnmount() {
    clearTimeout(this.searchTimer);
  },

  methods: {
    // `format` is one of the names the PHP and JavaScript date services share,
    // so a column reads the same whichever side rendered it.
    cellFormatterDate(value, format) {
      return this.app.getService(DateService).format(value, format);
    },

    cellFormatterDateTime(value) {
      return this.cellFormatterDate(value, 'date_time');
    },

    cellFormatterDateTimeFull(value) {
      return this.cellFormatterDate(value, 'date_time_full');
    },

    cellFormatterDateOnly(value) {
      return this.cellFormatterDate(value, 'date');
    },

    cellFormatterDateShort(value) {
      return this.cellFormatterDate(value, 'date_short');
    },

    cellFormatterMonthYear(value) {
      return this.cellFormatterDate(value, 'month_year');
    },

    cellFormatterRelative(value) {
      return this.cellFormatterDate(value, 'relative');
    },

    getEntityValue(entity, propertyPath) {
      if (!entity || !propertyPath) {
        return '';
      }

      const parts = propertyPath.split('.');
      let value = entity;

      for (const part of parts) {
        if (value === null || value === undefined) {
          return '';
        }
        value = value[part];
      }

      return value ?? '';
    },

    getEntityName() {
      return this.getEntityClass?.()?.entityName ?? null;
    },

    getColumnLabel(columnKey) {
      if (columnKey === false) {
        return '';
      }

      const entityName = this.getEntityName();
      if (entityName) {
        return this.trans(`@entity.${entityName}::field.${columnKey}`);
      }

      return this.trans(`@vue::table.column.${columnKey}.title`);
    },

    processColumns(rawColumns) {
      if (!rawColumns || !Array.isArray(rawColumns)) {
        return [];
      }

      return rawColumns.map((column) => {
        if (typeof column === 'string') {
          return {
            key: column,
            label: this.getColumnLabel(column)
          };
        }

        if (
          typeof column === 'object' &&
          column.key &&
          (column.label === undefined || column.label === null)
        ) {
          return {
            ...column,
            label: this.getColumnLabel(column.key)
          };
        }

        return column;
      });
    },

    getColumnsConfiguration() {
      return [];
    },

    // Where the table's state is kept — its order, its filters, its page —, so
    // the reader finds the list as they left it: a key of the interface's
    // state (`ui_state`, kept in the session), or null to keep nothing.
    getStateKey() {
      return null;
    },

    restoreTableState() {
      const key = this.getStateKey();
      if (!key) {
        return;
      }

      const state = uiStateGet(this.app, `ui.table.${key}`, null);
      if (state) {
        this.sort = state.sort ?? this.sort;
        this.barFilterValues = state.filters ?? this.barFilterValues;
        this.page = state.page ?? this.page;
      }

      this.$watch(
        () => ({ sort: this.sort, filters: this.barFilterValues, page: this.page }),
        (value) => uiStateSet(this.app, `ui.table.${key}`, value),
        { deep: true }
      );
    },

    getCollectionQuery() {
      // A filter of several values goes as one, its values joined by commas.
      const barFilters = Object.fromEntries(Object.entries(this.barFilterValues ?? {})
        .map(([key, value]) => [key, Array.isArray(value) ? value.join(',') : value]));
      const query = { ...this.filters, ...barFilters };
      const sort = sortToQuery(this.sort ?? this.defaultSort);

      if (this.search.trim()) {
        query.search = this.search.trim();
      }

      if (sort) {
        query.sort = sort;
      }

      return query;
    },

    // A new order or a new search reads the list again from its first page:
    // the page the reader was on is not the same page anymore.
    onSortChange(sort) {
      this.sort = sort;
      this.page = 0;
      this.refreshEntitiesCollection();
    },

    onBarFilterValuesChange(values) {
      this.barFilterValues = values;
      this.page = 0;
      this.refreshEntitiesCollection();
    },

    onSearchChange(search) {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        this.search = search;
        this.page = 0;
        this.refreshEntitiesCollection();
      }, search ? this.searchDelayMs : 0);
    }
  }
};
</script>
