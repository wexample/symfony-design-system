<script>
import DataTable from "../../partials/data-table.vue";
import Pagination from "../../partials/pagination.vue";
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";
import DateService from "@wexample/symfony-loader/js/Services/DateService";

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-abstract-entity-table",

  mixins: [AbstractEntityCollectionVueMixin],
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
      // Set to null to fetch the whole collection in a single request.
      pageLength: 10
    };
  },

  created() {
    this.columns = this.processColumns(this.getColumnsConfiguration());
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
    }
  }
};
</script>
