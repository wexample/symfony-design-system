import { LiveUpdatesServiceEvents } from '@wexample/symfony-loader/js/Services/LiveUpdatesService';

const AbstractLiveUpdateStatusVueMixin = {
  data() {
    return {
      liveConnectionsCount: 0,
      liveStatus: {
        total: 0,
        connecting: 0,
        open: 0,
        error: 0,
        hasActiveConnection: false,
      },
      liveStatusHandler: null,
      liveReconnectingHandler: null,
      liveReconnectedHandler: null,
      liveReconnectStoppedHandler: null,
      liveIsReconnecting: false,
      sendStartHandler: null,
      receiveHandler: null,
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
      if (this.liveIsReconnecting) {
        return 'offline';
      }

      if (this.liveStatus.open > 0) {
        return 'online';
      }

      if (this.liveStatus.error > 0) {
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

    registerLiveStatusListener() {
      if (this.liveStatusHandler) {
        return;
      }

      const eventsService = this.app.services.events;
      this.updateLiveStatus(this.app.getService('liveUpdates').getStatus());
      this.liveStatusHandler = (event) => {
        this.updateLiveStatus(event.detail.status);
      };

      this.liveReconnectingHandler = () => {
        this.liveIsReconnecting = true;
      };
      this.liveReconnectedHandler = () => {
        this.liveIsReconnecting = false;
      };
      this.liveReconnectStoppedHandler = () => {
        this.liveIsReconnecting = false;
      };

      eventsService.listen(LiveUpdatesServiceEvents.STATUS_CHANGED, this.liveStatusHandler);
      eventsService.listen(
        LiveUpdatesServiceEvents.CONNECTION_RECONNECTING,
        this.liveReconnectingHandler
      );
      eventsService.listen(
        LiveUpdatesServiceEvents.CONNECTION_RECONNECTED,
        this.liveReconnectedHandler
      );
      eventsService.listen(
        LiveUpdatesServiceEvents.CONNECTION_RECONNECT_STOPPED,
        this.liveReconnectStoppedHandler
      );
    },

    unregisterLiveStatusListener() {
      if (!this.liveStatusHandler) {
        return;
      }

      this.app.services.events.forget(LiveUpdatesServiceEvents.STATUS_CHANGED, this.liveStatusHandler);
      if (this.liveReconnectingHandler) {
        this.app.services.events.forget(
          LiveUpdatesServiceEvents.CONNECTION_RECONNECTING,
          this.liveReconnectingHandler
        );
      }
      if (this.liveReconnectedHandler) {
        this.app.services.events.forget(
          LiveUpdatesServiceEvents.CONNECTION_RECONNECTED,
          this.liveReconnectedHandler
        );
      }
      if (this.liveReconnectStoppedHandler) {
        this.app.services.events.forget(
          LiveUpdatesServiceEvents.CONNECTION_RECONNECT_STOPPED,
          this.liveReconnectStoppedHandler
        );
      }
      this.liveStatusHandler = null;
      this.liveReconnectingHandler = null;
      this.liveReconnectedHandler = null;
      this.liveReconnectStoppedHandler = null;
      this.liveIsReconnecting = false;
    },

    registerLiveActivityListeners() {
      if (this.sendStartHandler || this.receiveHandler) {
        return;
      }

      this.sendStartHandler = () => {
        this.setLiveActivityState('sending');
      };

      this.receiveHandler = () => {
        this.setLiveActivityState('receiving');
      };

      this.app.services.events.listen(this.getLiveUpdateSendStartEventName(), this.sendStartHandler);
      this.app.services.events.listen(LiveUpdatesServiceEvents.CONNECTION_MESSAGE, this.receiveHandler);
    },

    unregisterLiveActivityListeners() {
      if (this.sendStartHandler) {
        this.app.services.events.forget(this.getLiveUpdateSendStartEventName(), this.sendStartHandler);
        this.sendStartHandler = null;
      }

      if (this.receiveHandler) {
        this.app.services.events.forget(LiveUpdatesServiceEvents.CONNECTION_MESSAGE, this.receiveHandler);
        this.receiveHandler = null;
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
        open: status?.open || 0,
        error: status?.error || 0,
        hasActiveConnection: !!status?.hasActiveConnection,
      };
      // Expose only healthy active streams in the UI count.
      this.liveConnectionsCount = this.liveStatus.open;
    },
  },
};

export default AbstractLiveUpdateStatusVueMixin;
