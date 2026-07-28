<script>
import AbstractEntityTable from './abstract-entity-table.vue';

export default {
  extends: AbstractEntityTable,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-demo-dates-table",

  methods: {
    getEntityClass() {
      return null;
    },

    async refreshEntitiesCollection() {
      this.entities = [
        { label: 'Alpha', date: '2026-01-10T08:30:00' },
        { label: 'Beta',  date: '2026-02-18T14:15:00' },
        { label: 'Gamma', date: '2026-03-25T09:45:00' },
      ];
    },

    getColumnsConfiguration() {
      return [
        { key: 'label',  label: 'Label' },
        { key: 'date',   label: 'Date only',      secondary: true, format: (v) => this.cellFormatterDateOnly(v) },
        { key: 'date',   label: 'Date time',      secondary: true, format: (v) => this.cellFormatterDateTime(v) },
        { key: 'date',   label: 'Date time full', secondary: true, format: (v) => this.cellFormatterDateTimeFull(v) },
        { key: 'date',   label: 'Month year',     secondary: true, format: (v) => this.cellFormatterMonthYear(v) },
        { key: 'date',   label: 'Relative',       secondary: true, format: (v) => this.cellFormatterRelative(v) },
      ];
    },
  },
};
</script>
