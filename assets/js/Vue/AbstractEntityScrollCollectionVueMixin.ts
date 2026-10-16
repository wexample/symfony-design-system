import AbstractEntityCollectionVueMixin from './AbstractEntityCollectionVueMixin';

// A collection read as it is scrolled: each page added under the ones held
// (appendsPages()), the next one asked for when the foot of the collection —
// the element its template marks `ref="more"` — nears the screen, or on its
// button. A wall of pictures, a history of commits.
const AbstractEntityScrollCollectionVueMixin = {
  mixins: [AbstractEntityCollectionVueMixin],

  data() {
    return {
      // How far below the screen the foot of the collection is when the next
      // page is asked for: read before it is reached.
      loadMoreMargin: '400px',
      moreObserver: null,
      observedMore: null,
      moreInView: false,
    };
  },

  mounted() {
    if (typeof IntersectionObserver !== 'undefined') {
      this.moreObserver = new IntersectionObserver(
        (entries) => this.onMoreIntersection(entries),
        { rootMargin: this.loadMoreMargin }
      );
    }

    this.observeMore();
  },

  updated() {
    this.observeMore();
  },

  beforeUnmount() {
    this.moreObserver?.disconnect();
    this.moreObserver = null;
  },

  methods: {
    appendsPages() {
      return true;
    },

    // The foot of the collection comes and goes with what is left to read.
    observeMore() {
      const more = this.$refs.more ?? null;

      if (!this.moreObserver || more === this.observedMore) {
        return;
      }

      if (this.observedMore) {
        this.moreObserver.unobserve(this.observedMore);
      }
      if (more) {
        this.moreObserver.observe(more);
      }
      this.observedMore = more;
      this.moreInView = false;
    },

    onMoreIntersection(entries) {
      this.moreInView = entries.some((entry) => entry.isIntersecting);

      if (this.moreInView) {
        this.loadNewerEntities();
      }
    },

    // A page too short to push the foot off the screen calls for the next
    // one at once: the observer only speaks when the foot crosses its edge.
    async loadNewerEntities() {
      if (this.isLoadingNewer || !this.hasNewerEntities) {
        return;
      }

      await AbstractEntityCollectionVueMixin.methods.loadNewerEntities.call(this);

      this.$nextTick(() => {
        if (this.moreInView) {
          this.loadNewerEntities();
        }
      });
    },
  },
};

export default AbstractEntityScrollCollectionVueMixin;
