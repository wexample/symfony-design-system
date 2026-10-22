<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import Bar from "../../../bar/bar.vue";
import DateDisplay from "../../../date-display/date-display.vue";
import Pagination from "../../../pagination/pagination.vue";
import Spinner from "../../../spinner/spinner.vue";

const translated = buildTranslatedBindings({
  resolvedLoadingLabel: [
    'loadingLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.loading'
  ],
  resolvedEmptyLabel: [
    'emptyLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.no_data'
  ]
});

// The other reading of a collection: one line per row instead of one cell per
// column. It is the table's sibling and not its relative — same mixin, same
// three ways of keeping up, a shape that fits what has a name rather than
// figures.
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-bar-list-abstract-entity-bar-list",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    Bar,
    DateDisplay,
    Pagination,
    Spinner
  },

  props: {
    ...translated.props,

    // How the rows are told apart: `divided` by a line, `surfaces` by each bar
    // standing on its own block, `plain` by the gap alone.
    variant: {
      type: String,
      default: 'divided'
    }
  },

  data() {
    return {
      // Set to null to fetch the whole collection in a single request.
      pageLength: 10
    };
  },

  computed: {
    ...translated.computed,

    bars() {
      return this.entities.map((entity) => ({
        key: this.getEntityKey(entity),
        entity,
        ...this.getBarConfiguration(entity)
      }));
    },

    itemsClass() {
      return this.variant === 'divided'
          ? 'stack--vertical stack--divided'
          : 'stack--vertical';
    },

    // What a bar carries of the list it belongs to: on its own surface, it is
    // the bar itself that becomes the block.
    barClass() {
      return this.variant === 'surfaces' ? 'block block--padded' : null;
    },

    hasBars() {
      return this.bars.length > 0;
    }
  },

  methods: {
    /**
     * What to read on the row, the entity being the only thing the list knows
     * of it:
     *
     *   { title, subtitle, icon, avatar, href, date, class }
     *
     * `class` is what this row has that its neighbours do not — a tone, most
     * often — and rides beside the class the list gives them all.
     *
     * `date` is the far end's usual tenant and is handed to the component that
     * redraws itself, so "2 min ago" stays true on a page left open. Anything
     * else at that end is the `trailing` slot, which a subclass fills by
     * overriding the `bar_list_item_trailing` block of the template.
     */
    getBarConfiguration(entity) {
      return {
        title: entity?.name ?? entity?.title ?? '',
        subtitle: null,
        icon: null,
        avatar: null,
        href: null,
        date: null,
        class: null
      };
    }
  }
};
</script>
