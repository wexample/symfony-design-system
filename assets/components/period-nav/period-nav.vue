<script>
const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.period.';

const pad = (value) => String(value).padStart(2, '0');

// A period as it is written in an address: « 2026 », « 2026-10 », or the
// last days up to today, « 30d ». Anything else is no period — an address
// typed by hand, an old link —, and is left.
function periodParse(text) {
  const rolling = /^(\d{1,3})d$/.exec(String(text ?? ''));

  if (rolling) {
    return { unit: 'days', days: Number(rolling[1]), year: null, month: null };
  }

  const match = /^(\d{4})(?:-(\d{2}))?$/.exec(String(text ?? ''));

  if (!match || (match[2] && (Number(match[2]) < 1 || Number(match[2]) > 12))) {
    return null;
  }

  return match[2]
    ? { unit: 'month', year: Number(match[1]), month: Number(match[2]) }
    : { unit: 'year', year: Number(match[1]), month: null };
}

function periodKey(period) {
  if (period.unit === 'days') {
    return `${period.days}d`;
  }

  return period.unit === 'month' ? `${period.year}-${pad(period.month)}` : String(period.year);
}

// What a period is offered as, among the kinds: its unit, or its days.
function periodOption(period) {
  return period.unit === 'days' ? periodKey(period) : period.unit;
}

let uidCounter = 0;

// A period to look at, a year or a month: the one before, the one after,
// the current one — or the last days up to today (`rolling`: 7, 30, 90),
// which have neither. What it shows is the caller's — a list, a chart —,
// told on each change with its bounds (`from` inclusive, `to` exclusive,
// ISO dates), through `change` and through a `period-change` event bubbling
// from the element, for what is no Vue component of the page — a chart.
// Kept in the address (`hashKey`), it survives a reload and a link; bounded
// (`min`, `max`, written as periods), it stops there.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-period-nav-period-nav',

  props: {
    // « 2026 » or « 2026-10 »; this year, or this month, otherwise.
    value: {
      type: String,
      default: null
    },
    // The kinds of period offered, the first one by default.
    units: {
      type: Array,
      default: () => ['month', 'year']
    },
    min: {
      type: String,
      default: null
    },
    max: {
      type: String,
      default: null
    },
    hashKey: {
      type: String,
      default: null
    },
    // Numbers of days offered as « the last N days », beside the units.
    rolling: {
      type: Array,
      default: () => []
    }
  },

  emits: ['change'],

  data() {
    return {
      uid: `period-nav-${++uidCounter}`,
      current: this.initial()
    };
  },

  computed: {
    options() {
      return [...this.units, ...this.rolling.map((days) => `${days}d`)];
    },

    selected() {
      return periodOption(this.current);
    },

    label() {
      if (this.current.unit === 'days') {
        return this.trans(`${T}last_days`, { '%days%': this.current.days });
      }

      const locale = (document.documentElement.lang || undefined)?.replace('_', '-');
      const date = new Date(this.current.year, (this.current.month ?? 1) - 1, 1);

      return this.current.unit === 'month'
        ? new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' }).format(date)
        : String(this.current.year);
    },

    hasPrevious() {
      return this.current.unit !== 'days' && this.allowed(this.shifted(-1));
    },

    hasNext() {
      return this.current.unit !== 'days' && this.allowed(this.shifted(1));
    },

    isToday() {
      return this.current.unit === 'days' || periodKey(this.current) === periodKey(this.now(this.current.unit));
    }
  },

  mounted() {
    if (this.hashKey) {
      window.addEventListener('hashchange', this.onHashChange);
    }

    // What starts listening after this announced its period asks for it.
    document.addEventListener('period-request', this.onPeriodRequest);
    this.announce(false);
  },

  beforeUnmount() {
    window.removeEventListener('hashchange', this.onHashChange);
    document.removeEventListener('period-request', this.onPeriodRequest);
  },

  methods: {
    t(key) {
      return this.trans(`${T}${key}`);
    },

    icon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    optionLabel(option) {
      const rolling = periodParse(option);

      return rolling ? this.trans(`${T}unit_days`, { '%days%': rolling.days }) : this.t(`unit_${option}`);
    },

    initial() {
      const fromHash = this.hashKey ? periodParse(new URLSearchParams(window.location.hash.slice(1)).get(this.hashKey)) : null;
      const given = fromHash ?? periodParse(this.value);

      return given && this.options.includes(periodOption(given)) ? given : this.first();
    },

    first() {
      return this.units.length ? this.now(this.units[0]) : periodParse(`${this.rolling[0]}d`);
    },

    now(unit) {
      const date = new Date();

      return { unit, year: date.getFullYear(), month: unit === 'month' ? date.getMonth() + 1 : null };
    },

    shifted(delta) {
      const { unit, year, month } = this.current;

      if (unit === 'year') {
        return { unit, year: year + delta, month: null };
      }

      const index = year * 12 + (month - 1) + delta;

      return { unit, year: Math.floor(index / 12), month: (index % 12) + 1 };
    },

    // Within `min` and `max`, compared on their start: a month of a year
    // allowed is allowed.
    allowed(period) {
      const start = periodKey({ ...period, month: period.month ?? 1, unit: 'month' });
      const min = periodParse(this.min);
      const max = periodParse(this.max);

      return (!min || start >= periodKey({ ...min, month: min.month ?? 1, unit: 'month' }))
        && (!max || start <= periodKey({ ...max, month: max.month ?? 12, unit: 'month' }));
    },

    step(delta) {
      this.go(this.shifted(delta));
    },

    today() {
      this.go(this.now(this.current.unit));
    },

    // Another kind of period, around the same time: the year of the month
    // shown, or its first month — the current one in the current year. The
    // last days end today, wherever the period stood.
    setUnit(option) {
      const rolling = periodParse(option);

      if (rolling) {
        this.go(rolling);

        return;
      }

      const unit = option;
      const now = this.now('month');
      const year = this.current.year ?? now.year;
      const month = year === now.year ? now.month : 1;

      this.go({ unit, year, month: unit === 'month' ? month : null });
    },

    go(period) {
      if (period.unit !== 'days' && !this.allowed(period)) {
        return;
      }

      this.current = period;
      this.announce(true);
    },

    // The period and its bounds, to whoever listens; in the address, without
    // a step in the history for each click.
    announce(write) {
      const { from, to } = this.bounds();
      const iso = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

      if (write && this.hashKey) {
        const params = new URLSearchParams(window.location.hash.slice(1));
        params.set(this.hashKey, periodKey(this.current));
        window.history.replaceState(window.history.state, '', `#${params.toString()}`);
      }

      const detail = { ...this.current, key: periodKey(this.current), from: iso(from), to: iso(to) };

      this.$emit('change', detail);
      this.$el.dispatchEvent(new CustomEvent('period-change', { bubbles: true, detail }));
    },

    bounds() {
      const { unit, year, month, days } = this.current;

      if (unit === 'days') {
        const today = new Date();
        const to = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);

        return { from: new Date(to.getFullYear(), to.getMonth(), to.getDate() - days), to };
      }

      return {
        from: new Date(year, unit === 'month' ? month - 1 : 0, 1),
        to: unit === 'month' ? new Date(year, month, 1) : new Date(year + 1, 0, 1),
      };
    },

    onPeriodRequest() {
      this.announce(false);
    },

    onHashChange() {
      const period = periodParse(new URLSearchParams(window.location.hash.slice(1)).get(this.hashKey));

      if (period && this.options.includes(periodOption(period)) && periodKey(period) !== periodKey(this.current)) {
        this.go(period);
      }
    }
  }
};
</script>
