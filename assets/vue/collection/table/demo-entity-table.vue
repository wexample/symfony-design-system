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
        { name: 'Alpha', status: 'Active', created: '2026-01-10' },
        { name: 'Beta', status: 'Pending', created: '2026-02-18' },
        { name: 'Gamma', status: 'Inactive', created: '2026-03-25' },
      ];
    },

    getColumnsConfiguration() {
      return [
        { key: 'name',    label: 'Name' },
        { key: 'status',  label: 'Status' },
        { key: 'created', label: 'Created' },
      ];
    },
  },
};
</script>
