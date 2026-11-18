import PollingService, {
  type PollingHandle,
  type PollingStartOptions,
} from '@wexample/symfony-loader/js/Services/PollingService';

/**
 * A component reading something again on a clock, through the page's
 * PollingService: `this.poll({ read, intervalMs, onResult, until, key })`,
 * the component its owner — every poll it started stops when it goes.
 *
 * Polling is chosen where the component is designed — what it shows has no
 * live source, or none worth a connection held open —, never a fallback.
 */
export default {
  beforeUnmount() {
    this.app?.getService?.(PollingService)?.stopOwner(this);
  },

  methods: {
    poll<T>(options: Omit<PollingStartOptions<T>, 'owner'>): PollingHandle {
      return (this.app.getServiceOrFail(PollingService) as PollingService).start({ ...options, owner: this });
    },
  },
};
