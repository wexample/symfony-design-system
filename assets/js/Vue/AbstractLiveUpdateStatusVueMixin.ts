import LiveUpdatesService from '@wexample/symfony-loader/js/Services/LiveUpdatesService';

// Consumes the live-updates connection registry held by the loader's
// LiveUpdatesService (js-api Common/LiveUpdates/LiveUpdatesConnectionRegistry),
// which is where every live connection of an app is declared. The mixin maps
// the registry's native contract to the widget's state and owns nothing.
const AbstractLiveUpdateStatusVueMixin = {
  data() {
    return {
      liveConnectionsCount: 0,
      liveStatus: {
        total: 0,
        connecting: 0,
        reconnecting: 0,
        open: 0,
        error: 0,
        reconnectStopped: 0,
        hasActiveConnection: false,
      },
      liveRegistryUnsubscribe: null,
      sendStartHandler: null,
      liveActivityState: 'idle',
      liveActivityTimeout: null,
    };
  },

  mounted() {
    this.registerLiveStatusListener();
    this.registerLiveActivityListeners();
  },

  beforeDestroy() {
    this.unregisterLiveStatusListener();
    this.unregisterLiveActivityListeners();
  },

  beforeUnmount() {
    this.unregisterLiveStatusListener();
    this.unregisterLiveActivityListeners();
  },

  computed: {
    liveActivityClass() {
      if (this.liveConnectionState === 'offline') {
        return 'is-offline';
      }

      if (this.liveActivityState === 'sending') {
        return 'is-sending';
      }

      if (this.liveActivityState === 'receiving') {
        return 'is-receiving';
      }

      return 'is-idle';
    },

    liveConnectionState() {
      if (this.liveStatus.reconnecting > 0) {
        return 'offline';
      }

      if (this.liveStatus.open > 0) {
        return 'online';
      }

      if (this.liveStatus.error > 0 || this.liveStatus.reconnectStopped > 0) {
        return 'offline';
      }

      return 'idle';
    },
  },

  methods: {
    getLiveUpdateSendStartEventName() {
      return 'chat-message-composer:send-start';
    },

    getLiveUpdateActivityResetDelay() {
      return 600;
    },

    // Feature-detected: an app not running the live updates service simply
    // gets no registry, and the widget stays idle at 0.
    getLiveUpdatesRegistry() {
      const service = this.app.services[LiveUpdatesService.serviceName];

      return service ? service.getRegistry() : null;
    },

    registerLiveStatusListener() {
      if (this.liveRegistryUnsubscribe) {
        return;
      }

      const registry = this.getLiveUpdatesRegistry();
      if (!registry) {
        return;
      }

      this.updateLiveStatus(registry.getAggregatedStatus());

      this.liveRegistryUnsubscribe = registry.onEvent((event) => {
        this.updateLiveStatus(event.aggregated);

        if (event.type === 'connection-message') {
          this.setLiveActivityState('receiving');
        }
      });
    },

    unregisterLiveStatusListener() {
      if (this.liveRegistryUnsubscribe) {
        this.liveRegistryUnsubscribe();
        this.liveRegistryUnsubscribe = null;
      }
    },

    registerLiveActivityListeners() {
      if (this.sendStartHandler) {
        return;
      }

      // 'sending' is a composer/UI signal, not a connection event.
      this.sendStartHandler = () => {
        this.setLiveActivityState('sending');
      };

      this.app.services.events.listen(this.getLiveUpdateSendStartEventName(), this.sendStartHandler);
    },

    unregisterLiveActivityListeners() {
      if (this.sendStartHandler) {
        this.app.services.events.forget(this.getLiveUpdateSendStartEventName(), this.sendStartHandler);
        this.sendStartHandler = null;
      }

      if (this.liveActivityTimeout) {
        clearTimeout(this.liveActivityTimeout);
        this.liveActivityTimeout = null;
      }
    },

    setLiveActivityState(state) {
      this.liveActivityState = state;

      if (this.liveActivityTimeout) {
        clearTimeout(this.liveActivityTimeout);
      }

      this.liveActivityTimeout = setTimeout(() => {
        this.liveActivityState = 'idle';
        this.liveActivityTimeout = null;
      }, this.getLiveUpdateActivityResetDelay());
    },

    updateLiveStatus(status) {
      this.liveStatus = {
        total: status?.total || 0,
        connecting: status?.connecting || 0,
        reconnecting: status?.reconnecting || 0,
        open: status?.open || 0,
        error: status?.error || 0,
        reconnectStopped: status?.reconnectStopped || 0,
        hasActiveConnection: !!status?.hasActiveConnection,
      };
      // Expose only healthy active streams in the UI count.
      this.liveConnectionsCount = this.liveStatus.open;
    },
  },
};

export default AbstractLiveUpdateStatusVueMixin;
