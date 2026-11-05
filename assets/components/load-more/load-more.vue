<script>
import Dots from '../dots/dots.vue';

// Where a collection goes on: a line across it, quiet — rules either side, a
// word in the middle saying what is past it and how many, a caret saying which
// way. Not a button to press first: the sign that there is more, which a
// press, Enter, or — `auto` — the line coming into sight reads in. While it
// is read, the dots take the word's place, the line keeping its height.
// A dense region — a console — draws it as one of its own lines (load-more.scss).
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-load-more-load-more',

  components: {
    Dots
  },

  emits: ['load'],

  props: {
    label: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    // How many are past it, when the collection knows; nothing said else.
    count: {
      type: Number,
      default: null
    },
    // Read in as soon as the line comes into sight, a press still taking it.
    auto: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    iconHtml() {
      return this.icon ? this.app.getServiceOrFail('icon').icon(this.icon) : '';
    },

    shownCount() {
      return Number.isFinite(this.count) && this.count > 0 ? this.count : null;
    }
  },

  watch: {
    // Read: the line looked at again once what came is laid out — still in
    // sight, what came did not fill the room and the next part is asked; out
    // of sight, nothing until it comes back.
    isLoading(loading) {
      if (!loading && this.observer) {
        this.$nextTick(() => {
          this.observer.unobserve(this.$el);
          this.observer.observe(this.$el);
        });
      }
    }
  },

  mounted() {
    if (!this.auto || typeof IntersectionObserver === 'undefined') {
      return;
    }

    this.observer = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.isIntersecting)) {
        this.request();
      }
    });
    this.observer.observe(this.$el);
  },

  beforeUnmount() {
    this.observer?.disconnect();
  },

  methods: {
    request() {
      if (!this.isLoading) {
        this.$emit('load');
      }
    }
  }
};
</script>
