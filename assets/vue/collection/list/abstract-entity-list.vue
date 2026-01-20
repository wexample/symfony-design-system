<script>
import DataTable from "../../partials/data-table.vue";
import AbstractEntityCollectionVueMixin from "../../../js/Vue/AbstractEntityCollectionVueMixin";

export default {
  template: "#vue-template-design-system-abstract-entity-list",

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
