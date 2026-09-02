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
      const currentId = currentEntity ? currentEntity.id : null;
      const previousId = previousEntity ? previousEntity.id : null;

      if (currentId && currentId !== previousId) {
        this.syncEntityLiveUpdatesConnection();
      }
    },
  },

  methods: {
    getLiveUpdateEntityId() {
      return this.entity ? this.entity.id : this.entityId;
    },

    getLiveUpdateEntityName() {
      return stringToKebab(this.getEntityRepository().constructor.getEntityName());
    },

    getLiveUpdateEntityAction(): string {
      throw new Error(`${this.$options.name || 'Component'} must implement getLiveUpdateEntityAction()`);
    },

    getLiveUpdateTopic() {
      const id = this.getLiveUpdateEntityId();
      if (!id) {
        return null;
      }

      return this.app
        .getService(LiveUpdatesService)
        .topic(
          'entity',
          this.getLiveUpdateEntityName(),
          this.getLiveUpdateEntityAction(),
          id
        );
    },

    getLiveUpdateHandlers() {
      return {};
    },

    shouldConnectEntityLiveUpdates() {
      return !!this.getLiveUpdateEntityId();
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
          id: this.getLiveUpdateEntityId(),
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
