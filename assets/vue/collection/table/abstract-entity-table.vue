<script>
import DataTable from "../../partials/data-table.vue";
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";
import DateService from "@wexample/symfony-loader/js/Services/DateService";

export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-abstract-entity-table",

  mixins: [AbstractEntityCollectionVueMixin],
  components: {
    DataTable
  },

  data() {
    return {
      columns: [],
      showHeader: true
    };
  },

  created() {
    this.columns = this.processColumns(this.getColumnsConfiguration());
  },

  methods: {
    cellFormatterDateTime(value) {
      return this.app.getService(DateService).formatDateTime(value);
    },

    cellFormatterDateTimeFull(value) {
      return this.app.getService(DateService).formatDateTimeFull(value);
    },

    cellFormatterDateOnly(value) {
      return this.app.getService(DateService).formatDateOnly(value);
    },

    cellFormatterDateShort(value) {
      return this.app.getService(DateService).formatDateShort(value);
    },

    cellFormatterMonthYear(value) {
      return this.app.getService(DateService).formatMonthYear(value);
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

    getColumnLabel(columnKey) {
      return columnKey ?? '';
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

        if (typeof column === 'object' && column.key && !column.label) {
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
