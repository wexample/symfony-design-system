import { stringToKebab } from '@wexample/js-helpers/Helper/String';
import LiveUpdatesService from '@wexample/symfony-loader/js/Services/LiveUpdatesService';
import AbstractEntityManipulatorMixin from "@wexample/js-api/Vue/AbstractEntityManipulatorMixin";

const AbstractEntityLiveUpdatesVueMixin = {
  mixins: [AbstractEntityManipulatorMixin],

  data() {
    return {
      liveConnection: null,
    };
  },

  mounted() {
    this.syncEntityLiveUpdatesConnection();
  },

  beforeDestroy() {
    this.disconnectEntityLiveUpdates();
  },

  beforeUnmount() {
    this.disconnectEntityLiveUpdates();
  },

  watch: {
    entity(currentEntity, previousEntity) {
      const currentSecureId = currentEntity ? currentEntity.secureId : null;
      const previousSecureId = previousEntity ? previousEntity.secureId : null;

      if (currentSecureId && currentSecureId !== previousSecureId) {
        this.syncEntityLiveUpdatesConnection();
      }
    },
  },

  methods: {
    getLiveUpdateEntitySecureId() {
      return this.entity ? this.entity.secureId : this.entitySecureId;
    },

    getLiveUpdateEntityName() {
      return stringToKebab(this.getEntityRepository().constructor.getEntityName());
    },

    getLiveUpdateEntityAction(): string {
      throw new Error(`${this.$options.name || 'Component'} must implement getLiveUpdateEntityAction()`);
    },

    getLiveUpdateTopic() {
      const secureId = this.getLiveUpdateEntitySecureId();
      if (!secureId) {
        return null;
      }

      return this.app
        .getService(LiveUpdatesService)
        .topic(
          'entity',
          this.getLiveUpdateEntityName(),
          this.getLiveUpdateEntityAction(),
          secureId
        );
    },

    getLiveUpdateHandlers() {
      return {};
    },

    shouldConnectEntityLiveUpdates() {
      return !!this.getLiveUpdateEntitySecureId();
    },

    syncEntityLiveUpdatesConnection() {
      if (this.shouldConnectEntityLiveUpdates()) {
        this.connectEntityLiveUpdates();
        return;
      }

      this.disconnectEntityLiveUpdates();
    },

    // Backward compatibility with previous API name.
    syncLiveUpdatesConnection() {
      this.syncEntityLiveUpdatesConnection();
    },

    connectEntityLiveUpdates() {
      if (!this.shouldConnectEntityLiveUpdates()) {
        this.disconnectEntityLiveUpdates();
        return;
      }

      const topic = this.getLiveUpdateTopic();
      if (!topic) {
        this.disconnectEntityLiveUpdates();
        return;
      }

      this.disconnectEntityLiveUpdates();

      this.liveConnection = this.app.getService(LiveUpdatesService).connect({
        owner: this,
        topics: topic,
        metadata: {
          entityName: this.getLiveUpdateEntityName(),
          secureId: this.getLiveUpdateEntitySecureId(),
          action: this.getLiveUpdateEntityAction(),
        },
        onMessage: (_connection, payload) => this.onEntityLiveMessage(payload),
      });
    },

    disconnectEntityLiveUpdates() {
      if (!this.liveConnection) {
        return;
      }

      this.liveConnection.close();
      this.liveConnection = null;
    },

    resolveLiveUpdateEventName(payload) {
      return payload.entity_type;
    },

    onEntityLiveMessage(payload) {
      const eventName = this.resolveLiveUpdateEventName(payload);
      const handlers = this.getLiveUpdateHandlers();
      handlers[eventName]?.call(this, payload);
    },
  },
};

export default AbstractEntityLiveUpdatesVueMixin;
