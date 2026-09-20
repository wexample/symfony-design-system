<script>
import buildTranslatedBindings from '../../js/Helper/TranslationHelper';

const translated = buildTranslatedBindings({
  resolvedLabel: [
    'label',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.pagination.label'
  ],
  resolvedPreviousLabel: [
    'previousLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.pagination.previous'
  ],
  resolvedNextLabel: [
    'nextLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.pagination.next'
  ],
  resolvedPageLabel: [
    'pageLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.pagination.page'
  ],
  resolvedEllipsisLabel: [
    'ellipsisLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.pagination.ellipsis'
  ]
});

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-pagination',

  props: {
    ...translated.props,
    // Zero indexed, matching the API.
    page: {
      type: Number,
      default: 0
    },
    pagesCount: {
      type: Number,
      default: null
    },
    // Tri-state: true, false, or null when the API did not tell.
    hasMore: {
      default: null
    },
    // Forces the prev/next form even when the page count is known.
    compact: {
      type: Boolean,
      default: false
    },
    maxVisiblePages: {
      type: Number,
      default: 7
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },

  emits: ['change'],

  computed: {
    ...translated.computed,

    // Without a page count there is nothing to enumerate, so the pager falls
    // back to prev/next.
    isCompact() {
      return this.compact || !this.pagesCount;
    },

    isVisible() {
      if (this.pagesCount) {
        return this.pagesCount > 1;
      }

      return this.page > 0 || this.hasMore === true;
    },

    hasPrevious() {
      return this.page > 0;
    },

    hasNext() {
      if (this.pagesCount) {
        return this.page < this.pagesCount - 1;
      }

      return this.hasMore !== false;
    },

    compactStatus() {
      const current = `${this.resolvedPageLabel} ${this.page + 1}`;

      return this.pagesCount ? `${current} / ${this.pagesCount}` : current;
    },

    // Page numbers to render, with null standing for an ellipsis gap.
    visiblePages() {
      const count = this.pagesCount;

      if (!count || count <= 1) {
        return count === 1 ? [0] : [];
      }

      const max = Math.max(5, this.maxVisiblePages);

      if (count <= max) {
        return Array.from({ length: count }, (value, index) => index);
      }

      // Reserve three slots for the first page, the last page and one ellipsis.
      const side = Math.floor((max - 3) / 2);
      const wanted = new Set([0, count - 1, this.page]);

      for (let offset = 1; offset <= side; offset++) {
        wanted.add(this.page - offset);
        wanted.add(this.page + offset);
      }

      const sorted = [...wanted]
        .filter((entry) => entry >= 0 && entry < count)
        .sort((left, right) => left - right);

      const output = [];
      let previous = null;

      sorted.forEach((entry) => {
        if (previous !== null && entry - previous > 1) {
          output.push(null);
        }

        output.push(entry);
        previous = entry;
      });

      return output;
    }
  },

  methods: {
    select(page) {
      if (this.disabled || page === this.page || page < 0) {
        return;
      }

      if (this.pagesCount && page > this.pagesCount - 1) {
        return;
      }

      this.$emit('change', page);
    }
  }
};
</script>
