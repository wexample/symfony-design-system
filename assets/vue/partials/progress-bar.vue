<script>
// The twin of partials/progress.html.twig, and the one that can be driven. Its
// vocabulary is the one the cli progress already speaks — update, advance,
// finish, a value that may be written '54%' — so a routine that drives a bar in
// a terminal drives this one without being rewritten.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-progress-bar',

  props: {
    // A count, or a share written as '54%'.
    current: {
      type: [Number, String],
      default: 0
    },
    total: {
      type: Number,
      default: 100
    },
    label: {
      type: String,
      default: null
    },
    showValue: {
      type: Boolean,
      default: false
    },
    // Work whose end is not known. The bar sweeps and says no number, which is
    // the honest reading of not knowing.
    indeterminate: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: 'info'
    },
    size: {
      type: String,
      default: null
    }
  },

  emits: ['change', 'finish'],

  data() {
    return {
      // Held rather than read straight from the prop, since the methods below
      // are how this is meant to be driven. The prop stays the way in.
      value: 0,
      currentTotal: this.total,
      currentLabel: this.label
    };
  },

  created() {
    this.value = this.normalize(this.current);
  },

  watch: {
    current(value) {
      this.value = this.normalize(value);
    },

    total(value) {
      this.setTotal(value);
    },

    label(value) {
      this.currentLabel = value;
    }
  },

  computed: {
    classes() {
      return [
        'progress',
        `progress--${this.type}`,
        this.size ? `progress--${this.size}` : null,
        this.indeterminate ? 'progress--indeterminate' : null
      ];
    },

    ratio() {
      return this.currentTotal > 0 ? this.value / this.currentTotal : 0;
    },

    percent() {
      return Math.round(this.ratio * 100);
    },

    style() {
      return this.indeterminate ? null : { '--progress-value': this.ratio };
    },

    isComplete() {
      return this.currentTotal > 0 && this.value >= this.currentTotal;
    }
  },

  methods: {
    // Accepts a count or a share written as '54%', and never answers outside the
    // bounds.
    normalize(value) {
      let count = value;

      if (typeof value === 'string') {
        const text = value.trim();
        count = text.endsWith('%')
          ? (parseFloat(text) / 100) * this.currentTotal
          : parseFloat(text);
      }

      if (Number.isNaN(count) || count === null || count === undefined) {
        return this.value;
      }

      return Math.max(0, Math.min(this.currentTotal, count));
    },

    // Moves to a position. The label travels with it, since what a bar is doing
    // usually changes when it moves.
    update(current, label = null) {
      this.value = this.normalize(current);

      if (label !== null) {
        this.currentLabel = label;
      }

      this.$emit('change', this.value);

      if (this.isComplete) {
        this.$emit('finish');
      }

      return this.value;
    },

    // One step further. What a step is worth is the caller's business: nothing by
    // default means one unit, which is what to call where neither the unit nor
    // the total means anything — a hundred of them fill the bar.
    advance(step = 1, label = null) {
      const amount = typeof step === 'string' && step.trim().endsWith('%')
        ? (parseFloat(step) / 100) * this.currentTotal
        : Number(step);

      return this.update(this.value + (Number.isNaN(amount) ? 1 : amount), label);
    },

    finish(label = null) {
      return this.update(this.currentTotal, label);
    },

    reset(label = null) {
      return this.update(0, label);
    },

    // Learning the total on the way is the ordinary case: a job counts its work
    // before it knows how much there is. What was already done keeps its place.
    setTotal(total) {
      this.currentTotal = Math.max(0, Number(total) || 0);
      this.value = Math.min(this.value, this.currentTotal);

      return this.currentTotal;
    }
  }
};
</script>
