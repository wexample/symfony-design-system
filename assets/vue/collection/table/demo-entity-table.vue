<script>
import AbstractEntityTable from './abstract-entity-table.vue';

const STATUSES = ['Active', 'Pending', 'Inactive'];
const DEMO_ROWS = Array.from({ length: 37 }, (value, index) => ({
  name: `Item ${String(index + 1).padStart(2, '0')}`,
  status: STATUSES[index % STATUSES.length],
  amount: `${(index + 1) * 7.5} €`,
  created: `2026-${String((index % 12) + 1).padStart(2, '0')}-15T09:45:00`,
}));

export default {
  extends: AbstractEntityTable,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-demo-entity-table",

  methods: {
    getEntityClass() {
      return null;
    },

    // Stands in for the API: slices a fixed dataset and reports the same
    // pagination meta a paginated endpoint would return.
    async refreshEntitiesCollection() {
      const length = this.getPageLength();
      const offset = this.page * length;

      this.entities = DEMO_ROWS.slice(offset, offset + length);
      this.pagination = {
        page: this.page,
        length,
        total: DEMO_ROWS.length,
        pagesCount: Math.ceil(DEMO_ROWS.length / length),
        hasMore: offset + length < DEMO_ROWS.length,
      };
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
