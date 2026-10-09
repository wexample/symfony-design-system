<script>
const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.period.';

const pad = (value) => String(value).padStart(2, '0');

// A period as it is written in an address: « 2026 », « 2026-10 ». Anything
// else is no period — an address typed by hand, an old link —, and is left.
function periodParse(text) {
  const match = /^(\d{4})(?:-(\d{2}))?$/.exec(String(text ?? ''));

  if (!match || (match[2] && (Number(match[2]) < 1 || Number(match[2]) > 12))) {
    return null;
  }

  return match[2]
    ? { unit: 'month', year: Number(match[1]), month: Number(match[2]) }
    : { unit: 'year', year: Number(match[1]), month: null };
}

function periodKey(period) {
  return period.unit === 'month' ? `${period.year}-${pad(period.month)}` : String(period.year);
}

let uidCounter = 0;

// A period to look at, a year or a month: the one before, the one after,
// the current one. What it shows is the caller's — a list, a chart —, told
// on each change with its bounds (`from` inclusive, `to` exclusive, ISO
// dates). Kept in the address (`hashKey`), it survives a reload and a link;
// bounded (`min`, `max`, written as periods), it stops there.
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
    label() {
      const locale = (document.documentElement.lang || undefined)?.replace('_', '-');
      const date = new Date(this.current.year, (this.current.month ?? 1) - 1, 1);

      return this.current.unit === 'month'
        ? new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' }).format(date)
        : String(this.current.year);
    },

    hasPrevious() {
      return this.allowed(this.shifted(-1));
    },

    hasNext() {
      return this.allowed(this.shifted(1));
    },

    isToday() {
      return periodKey(this.current) === periodKey(this.now(this.current.unit));
    }
  },

  mounted() {
    if (this.hashKey) {
      window.addEventListener('hashchange', this.onHashChange);
    }

    this.announce(false);
  },

  beforeUnmount() {
    window.removeEventListener('hashchange', this.onHashChange);
  },

  methods: {
    t(key) {
      return this.trans(`${T}${key}`);
    },

    icon(name) {
      return this.app.getServiceOrFail('icon').icon(name);
    },

    initial() {
      const fromHash = this.hashKey ? periodParse(new URLSearchParams(window.location.hash.slice(1)).get(this.hashKey)) : null;
      const given = fromHash ?? periodParse(this.value);

      return given && this.units.includes(given.unit) ? given : this.now(this.units[0]);
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
    // shown, or its first month — the current one in the current year.
    setUnit(unit) {
      const now = this.now('month');
      const month = this.current.year === now.year ? now.month : 1;

      this.go({ unit, year: this.current.year, month: unit === 'month' ? month : null });
    },

    go(period) {
      if (!this.allowed(period)) {
        return;
      }

      this.current = period;
      this.announce(true);
    },

    // The period and its bounds, to whoever listens; in the address, without
    // a step in the history for each click.
    announce(write) {
      const { unit, year, month } = this.current;
      const from = new Date(year, unit === 'month' ? month - 1 : 0, 1);
      const to = unit === 'month' ? new Date(year, month, 1) : new Date(year + 1, 0, 1);
      const iso = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

      if (write && this.hashKey) {
        const params = new URLSearchParams(window.location.hash.slice(1));
        params.set(this.hashKey, periodKey(this.current));
        window.history.replaceState(window.history.state, '', `#${params.toString()}`);
      }

      this.$emit('change', { ...this.current, key: periodKey(this.current), from: iso(from), to: iso(to) });
    },

    onHashChange() {
      const period = periodParse(new URLSearchParams(window.location.hash.slice(1)).get(this.hashKey));

      if (period && this.units.includes(period.unit) && periodKey(period) !== periodKey(this.current)) {
        this.go(period);
      }
    }
  }
};
</script>
