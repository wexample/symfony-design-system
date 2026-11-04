<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import DateDisplay from "../../../date-display/date-display.vue";
import LoadMore from "../../../load-more/load-more.vue";
import Spinner from "../../../spinner/spinner.vue";

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.collection.console.abstract-entity-console.abstract-entity-console::';

const translated = buildTranslatedBindings({
  resolvedLoadingLabel: [
    'loadingLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.loading'
  ],
  resolvedEmptyLabel: [
    'emptyLabel',
    'WexampleSymfonyDesignSystemBundle.common.system::frontend.no_data'
  ],
  resolvedLoadMoreLabel: ['loadMoreLabel', `${DOMAIN}load_more`],
  resolvedLoadOlderLabel: ['loadOlderLabel', `${DOMAIN}load_older`]
});

// The parts of a line, in the order they are read.
const LINE_PARTS = ['kind', 'ref', 'text', 'end'];

// The third reading of a collection: one line per event, read as they
// arrive — what a run writes of itself, what a job answered. The table's
// and the bar list's sibling: same mixin, same three ways of keeping up, a
// shape that fits a flow rather than a set — nothing to sort, nothing to
// act on but the line itself, a colour for the kind and that is all.
//
// Read the latest first by default, more of it under the last line held: the
// api's own order, its freshest page first. Read the other way
// (`isNewestFirst()` false), the console opens on its last page, stays at
// its foot as lines arrive, and the way back up stands above the oldest
// line held — a thread's reading, for an api sorted oldest first.
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-console-abstract-entity-console",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    DateDisplay,
    LoadMore,
    Spinner
  },

  props: {
    ...translated.props
  },

  data() {
    return {
      pageLength: 50,
      // Read oldest first, how far the console was from its foot when earlier
      // lines were asked for; null means it goes to the foot.
      consoleScrollAnchor: null
    };
  },

  computed: {
    ...translated.computed,

    newestFirst() {
      return this.isNewestFirst();
    },

    lines() {
      return this.entities.map((entity) => this.buildLine(entity));
    },

    hasLines() {
      return this.lines.length > 0;
    },

    // Whether the console goes on past what it holds, whichever way it reads.
    hasMoreLines() {
      return this.newestFirst ? this.hasNewerEntities : this.hasOlderEntities;
    },

    isLoadingMore() {
      return this.newestFirst ? this.isLoadingNewer : this.isLoadingOlder;
    },

    loadMoreLabel() {
      return this.newestFirst ? this.resolvedLoadMoreLabel : this.resolvedLoadOlderLabel;
    }
  },

  watch: {
    entities() {
      if (!this.newestFirst) {
        this.$nextTick(() => this.settleConsoleScroll());
      }
    }
  },

  methods: {
    // Which way the console reads: the latest line first, as the api sorts
    // its freshest page first; or oldest first, the api sorting that way,
    // the console then standing at its foot.
    isNewestFirst() {
      return true;
    },

    appendsPages() {
      return this.isNewestFirst();
    },

    startsAtLastPage() {
      return !this.isNewestFirst();
    },

    /**
     * What to read on the line, the entity being the only thing the console
     * knows of it:
     *
     *   { time, tone, kind, ref, text, end, href, onClick, class }
     *
     * `time` is when it happened, redrawn as the page stays open. `tone` is
     * the state the line is read in — info, success, warning, error — or
     * nothing. `kind` names the event, `ref` what it concerns (a slot, a
     * file), `text` what it says, `end` what stands at the far end. Each of
     * the four is a string, or `{ label, href, onClick, class }` when it
     * leads somewhere or acts: a slot's id opening the slot. `href` makes
     * the whole line the link, `onClick(entity)` makes it act — not both,
     * and a line that is a link holds no part that acts.
     */
    getLineConfiguration(entity) {
      return {
        time: entity?.dateCreated ?? null,
        tone: null,
        kind: null,
        ref: null,
        text: entity?.name ?? entity?.title ?? '',
        end: null,
        href: null,
        onClick: null,
        class: null
      };
    },

    buildLine(entity) {
      const configuration = this.getLineConfiguration(entity);

      return {
        key: this.getEntityKey(entity),
        entity,
        time: configuration.time ?? null,
        tone: configuration.tone ?? null,
        href: configuration.href ?? null,
        onClick: configuration.onClick ?? null,
        class: configuration.class ?? null,
        parts: LINE_PARTS
          .map((role) => this.buildLinePart(role, configuration[role]))
          .filter(Boolean)
      };
    },

    // A part as a string, or as what it is with where it leads.
    buildLinePart(role, part) {
      if (part === null || part === undefined || part === '') {
        return null;
      }

      const spec = typeof part === 'object' ? part : { label: part };

      return {
        role,
        label: String(spec.label ?? ''),
        href: spec.href ?? null,
        onClick: spec.onClick ?? null,
        class: spec.class ?? null
      };
    },

    lineTag(line) {
      return line.href ? 'a' : 'div';
    },

    lineClass(line) {
      return [
        line.tone ? `console--line--${line.tone}` : null,
        line.onClick ? 'console--line--clickable' : null,
        line.class
      ];
    },

    partTag(line, part) {
      if (part.href) {
        return 'a';
      }

      // No button inside a line that is a link: the part still acts.
      return part.onClick && !line.href ? 'button' : 'span';
    },

    partClass(part) {
      return [
        `console--${part.role}`,
        part.href || part.onClick ? 'console--part' : null,
        part.class
      ];
    },

    onLineClick(line) {
      if (line.onClick) {
        line.onClick(line.entity);
      }
    },

    onPartClick(line, part, event) {
      if (!part.onClick) {
        return;
      }

      event.stopPropagation();
      part.onClick(line.entity);
    },

    async loadMoreLines() {
      if (this.newestFirst) {
        await this.loadNewerEntities();

        return;
      }

      // Earlier lines are added above what is being read, so the console
      // must stay on the same line: what is held is the distance to the foot.
      const lines = this.$refs.lines;

      this.consoleScrollAnchor = lines ? lines.scrollHeight - lines.scrollTop : null;

      await this.loadOlderEntities();
    },

    settleConsoleScroll() {
      const lines = this.$refs.lines;

      if (!lines) {
        return;
      }

      if (this.consoleScrollAnchor === null) {
        lines.scrollTop = lines.scrollHeight;

        return;
      }

      lines.scrollTop = lines.scrollHeight - this.consoleScrollAnchor;
      this.consoleScrollAnchor = null;
    }
  }
};
</script>
