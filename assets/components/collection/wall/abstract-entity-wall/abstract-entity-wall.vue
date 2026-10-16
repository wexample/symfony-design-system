<script>
import AbstractEntityScrollCollectionVueMixin from "../../../../js/Vue/AbstractEntityScrollCollectionVueMixin";
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
// added under the others when the foot of the wall nears the screen
// (AbstractEntityScrollCollectionVueMixin). The table's sibling, on the same
// collection and the same three ways of keeping up.
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-wall-abstract-entity-wall-abstract-entity-wall",

  mixins: [AbstractEntityScrollCollectionVueMixin],

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
      pageLength: 24
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

  methods: {
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
    }
  }
};
</script>
