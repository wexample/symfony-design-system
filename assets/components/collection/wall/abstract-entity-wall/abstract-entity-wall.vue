<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import { wallSpan } from "../../../../js/Helper/WallHelper";
import LoadMore from "../../../load-more/load-more.vue";
import Media from "../../../media/media.vue";
import Spinner from "../../../spinner/spinner.vue";

const translated = buildTranslatedBindings({
  resolvedLoadingLabel: [
    'loadingLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.loading'
  ],
  resolvedEmptyLabel: [
    'emptyLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.no_data'
  ],
  resolvedLoadMoreLabel: [
    'loadMoreLabel',
    'WexampleSymfonyDesignSystemBundle.components.collection.wall.abstract-entity-wall.abstract-entity-wall::load_more'
  ]
});

// A collection read as pictures hung on a wall (css/shapes/_wall.scss), each
// as tall as its ratio says, and read as it is scrolled: the next page is
// added under the others when the foot of the wall nears the screen. The
// table's sibling, on the same mixin and the same three ways of keeping up.
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-wall-abstract-entity-wall-abstract-entity-wall",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    LoadMore,
    Media,
    Spinner
  },

  props: {
    ...translated.props
  },

  data() {
    return {
      pageLength: 24,
      // How far below the screen the foot of the wall is when the next page
      // is asked for: read before it is reached.
      loadMoreMargin: '400px',
      moreObserver: null,
      observedMore: null,
      moreInView: false
    };
  },

  computed: {
    ...translated.computed,

    tiles() {
      return this.entities.map((entity) => {
        const tile = this.getTileConfiguration(entity);

        return {
          key: this.getEntityKey(entity),
          entity,
          span: wallSpan(Number(tile.width) || null, Number(tile.height) || null),
          ...tile
        };
      });
    },

    hasTiles() {
      return this.tiles.length > 0;
    }
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

    /**
     * What to draw of the entity, the only thing the wall knows of it:
     *
     *   { src, srcset, sizes, alt, width, height, href, caption, class }
     *
     * `width` and `height` are the picture's own, which give the tile its
     * ratio; without them it stands square. No `src` leaves the frame's
     * placeholder in its place.
     */
    getTileConfiguration(entity) {
      return {
        src: entity?.src ?? null,
        srcset: null,
        sizes: null,
        alt: entity?.name ?? '',
        width: entity?.width ?? null,
        height: entity?.height ?? null,
        href: null,
        caption: entity?.name ?? entity?.title ?? null,
        class: null
      };
    },

    // The foot of the wall comes and goes with what is left to read.
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
    }
  }
};
</script>
