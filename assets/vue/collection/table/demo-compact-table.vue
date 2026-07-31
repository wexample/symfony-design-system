<script>
import AbstractEntityTable from './abstract-entity-table.vue';

const DEMO_ROWS = Array.from({ length: 23 }, (value, index) => ({
  reference: `REF-${String(index + 1).padStart(3, '0')}`,
  label: `Untracked entry ${index + 1}`,
}));

export default {
  extends: AbstractEntityTable,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-table-demo-compact-table",

  data() {
    return {
      pageLength: 5,
      paginationPosition: 'both',
    };
  },

  methods: {
    getEntityClass() {
      return null;
    },

    // Mimics an endpoint that skips the count: without a total the pager
    // degrades to prev/next.
    async refreshEntitiesCollection() {
      const length = this.getPageLength();
      const offset = this.page * length;

      this.entities = DEMO_ROWS.slice(offset, offset + length);
      this.pagination = {
        page: this.page,
        length,
        total: null,
        pagesCount: null,
        hasMore: offset + length < DEMO_ROWS.length,
      };
    },

    getColumnsConfiguration() {
      return [
        { key: 'reference', label: 'Reference' },
        { key: 'label', label: 'Label', secondary: true },
      ];
    },
  },
};
</script>
