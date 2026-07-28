<script>
import AbstractEntityTable from './abstract-entity-table.vue';

export default {
  extends: AbstractEntityTable,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-demo-entity-table",

  methods: {
    getEntityClass() {
      return null;
    },

    async refreshEntitiesCollection() {
      this.entities = [
        { name: 'Alpha', status: 'Active',   amount: '42.00 €',  created: '2026-01-10' },
        { name: 'Beta',  status: 'Pending',  amount: '128.50 €', created: '2026-02-18' },
        { name: 'Gamma', status: 'Inactive', amount: '7.99 €',   created: '2026-03-25' },
      ];
    },

    getColumnsConfiguration() {
      return [
        { key: 'name',    label: 'Name' },
        { key: 'status',  label: 'Status', align: 'center' },
        { key: 'amount',  label: 'Amount', align: 'right' },
        { key: 'created', label: 'Created' },
      ];
    },
  },
};
</script>
