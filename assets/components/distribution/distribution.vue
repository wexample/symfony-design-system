<script>
import Status from '../status/status.vue';

// The twin of components/distribution/distribution.html.twig: same parts, same
// shares, same legend. What the browser can do beyond the server is take a
// click on a part that leads nowhere — a table beside it filtering itself, for
// instance — which is what `select` is for.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-distribution-distribution',

  components: {
    Status
  },

  props: {
    // Each one: { value, label, type, href, title }. Only the value is owed.
    segments: {
      type: Array,
      default: () => []
    },
    // What the parts are counted out of. Below their sum it is not a total but
    // a mistake, and the parts are the thing that was counted.
    total: {
      type: Number,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    showValue: {
      type: Boolean,
      default: false
    },
    // The parts said in words, under the bar that says them in proportion.
    legend: {
      type: Boolean,
      default: true
    },
    compact: {
      type: Boolean,
      default: false
    }
  },

  emits: ['select'],

  computed: {
    classes() {
      return [
        'distribution',
        this.compact ? 'distribution--compact' : null
      ];
    },

    parts() {
      return this.segments.map((segment) => {
        const part = {
          value: Math.max(0, Number(segment.value) || 0),
          label: segment.label ?? null,
          // A part with no state is the neutral one.
          type: segment.type ?? 'skipped',
          href: segment.href ?? null
        };

        part.percent = this.currentTotal > 0
          ? Math.round((part.value / this.currentTotal) * 1000) / 10
          : 0;
        part.title = segment.title ?? this.buildTitle(part);

        return part;
      });
    },

    // A part worth nothing is not drawn: it would be a lie the width of its
    // minimum. It keeps its place in the legend, where nothing is something
    // worth reading.
    drawnParts() {
      return this.parts.filter((part) => part.value > 0);
    },

    sum() {
      return this.segments.reduce(
        (total, segment) => total + Math.max(0, Number(segment.value) || 0),
        0
      );
    },

    currentTotal() {
      return Math.max(Number(this.total) || 0, this.sum);
    },

    rest() {
      return Math.max(0, this.currentTotal - this.sum);
    }
  },

  methods: {
    // What a part says under the pointer, and to a reader who cannot see the
    // bar: what it is, how many, and what share of the whole.
    buildTitle(part) {
      const count = `${part.value} (${part.percent}%)`;

      return part.label === null ? count : `${part.label} — ${count}`;
    }
  }
};
</script>
