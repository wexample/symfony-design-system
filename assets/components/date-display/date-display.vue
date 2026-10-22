<script>
import DateService from '@wexample/symfony-loader/js/Services/DateService';

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-date-display-date-display',

  props: {
    value: {
      type: [String, Number, Date],
      default: null
    },
    // One of the names the PHP and JavaScript date services share.
    format: {
      type: String,
      default: 'auto'
    },
    // Format of the tooltip, or false to leave the date without one.
    titleFormat: {
      type: [String, Boolean],
      default: 'date_time_full'
    }
  },

  data() {
    return {
      // What the text is relative to. The value never changes, what it means does.
      now: new Date(),
      redrawTimeout: null
    };
  },

  computed: {
    dateService() {
      return this.app.getService(DateService);
    },

    text() {
      return this.dateService.format(this.value, this.format, undefined, this.now);
    },

    machineValue() {
      const date = new Date(this.value);

      return Number.isNaN(date.getTime()) ? null : date.toISOString();
    },

    title() {
      return this.titleFormat === false
        ? null
        : this.dateService.format(this.value, this.titleFormat, undefined, this.now);
    }
  },

  watch: {
    value() {
      this.scheduleRedraw();
    }
  },

  mounted() {
    this.scheduleRedraw();
  },

  beforeUnmount() {
    window.clearTimeout(this.redrawTimeout);
  },

  methods: {
    // Vue owns this element, so the shared ticker of the service is left out of
    // it: only the cadence is borrowed.
    scheduleRedraw() {
      window.clearTimeout(this.redrawTimeout);

      const delay = this.dateService.refreshDelayMs(this.value);

      if (delay === null) {
        return;
      }

      this.redrawTimeout = window.setTimeout(() => {
        this.now = new Date();
        this.scheduleRedraw();
      }, delay);
    }
  }
};
</script>
