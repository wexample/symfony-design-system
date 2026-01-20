<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-data-table',

  props: {
    rows: {
      type: Array,
      required: true,
      default: () => []
    },
    columns: {
      type: Array,
      default: () => []
    },
    showHeader: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    getColumnKey(column) {
      return typeof column === 'string' ? column : column?.key;
    },

    getColumnLabel(column) {
      if (typeof column === 'string') {
        return column;
      }

      return column?.label ?? column?.key ?? '';
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

      return row[key] ?? '';
    }
  }
};
</script>
