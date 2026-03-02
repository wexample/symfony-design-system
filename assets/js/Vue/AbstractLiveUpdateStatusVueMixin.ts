import { LiveUpdatesServiceEvents } from '@wexample/symfony-loader/js/Services/LiveUpdatesService';

const AbstractLiveUpdateStatusVueMixin = {
  data() {
    return {
      liveConnectionsCount: 0,
      liveStatusHandler: null,
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
    liveActivityColor() {
      if (this.liveActivityState === 'sending') {
        return '#2563eb';
      }

      if (this.liveActivityState === 'receiving') {
        return '#16a34a';
      }

      return '#9ca3af';
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
      this.liveConnectionsCount = this.app.getService('liveUpdates').getStatus().total;
      this.liveStatusHandler = (event) => {
        this.liveConnectionsCount = event.detail.status.total;
      };

      eventsService.listen(LiveUpdatesServiceEvents.STATUS_CHANGED, this.liveStatusHandler);
    },

    unregisterLiveStatusListener() {
      if (!this.liveStatusHandler) {
        return;
      }

      this.app.services.events.forget(LiveUpdatesServiceEvents.STATUS_CHANGED, this.liveStatusHandler);
      this.liveStatusHandler = null;
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
  },
};

export default AbstractLiveUpdateStatusVueMixin;
