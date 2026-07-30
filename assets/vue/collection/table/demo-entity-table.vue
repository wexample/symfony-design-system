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
        { name: 'Alpha', status: 'Active',   amount: '42.00 €',  created: '2026-01-10T08:30:00' },
        { name: 'Beta',  status: 'Pending',  amount: '128.50 €', created: '2026-02-18T14:15:00' },
        { name: 'Gamma', status: 'Inactive', amount: '7.99 €',   created: '2026-03-25T09:45:00' },
      ];
    },

    getColumnsConfiguration() {
      return [
        { key: 'name',    label: 'Name' },
        { key: 'status',  label: 'Status', align: 'center' },
        { key: 'amount',  label: 'Amount', align: 'right' },
        { key: 'created', label: 'Created', secondary: true, format: (v) => this.cellFormatterDateOnly(v) },
        {
          label: false,
          align: 'center',
          embed: 'modal',
          embedOptions: { closeOnEscape: true, closeOnOverlayClick: true },
          actions: [
            { name: 'show', route: 'wexample_design_system_generic_dialog_modal_test_simple' },
            {
              name: 'edit',
              route: 'wexample_design_system_generic_dialog_modal_test_medium',
              embed: 'panel',
            },
          ],
        },
      ];
    },
  },
};
</script>
