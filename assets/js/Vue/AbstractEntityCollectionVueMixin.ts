import AbstractEntityManipulatorVueMixin from './AbstractEntityManipulatorVueMixin';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';

const AbstractEntityCollectionVueMixin = {
  mixins: [AbstractEntityManipulatorVueMixin],

  data() {
    return {
      entities: [],
      collectionRefreshHandlers: [],
    };
  },

  mounted() {
    this.refreshEntitiesCollection();
    this.registerCollectionRefreshEvents();
  },

  beforeDestroy() {
    this.unregisterCollectionRefreshEvents();
  },

  methods: {
    getEntitiesFetchParams() {
      return undefined;
    },

    async refreshEntitiesCollection() {
      const fetchParams = this.getEntitiesFetchParams();
      this.entities = fetchParams
        ? await this.getEntityRepository().fetchList(fetchParams)
        : await this.getEntityRepository().fetchList();
    },

    getCollectionRefreshEvents() {
      return [];
    },

    registerCollectionRefreshEvents() {
      const events = this.getCollectionRefreshEvents();
      if (!events || !events.length) {
        return;
      }

      this.collectionRefreshHandlers = events.map((eventName) => {
        const handler = () => this.refreshEntitiesCollection();
        this.app.getServiceOrFail(EventsService).listen(eventName, handler);
        return { eventName, handler };
      });
    },

    unregisterCollectionRefreshEvents() {
      if (!this.collectionRefreshHandlers || !this.collectionRefreshHandlers.length) {
        return;
      }

      this.collectionRefreshHandlers.forEach(({ eventName, handler }) => {
        this.app.getServiceOrFail(EventsService).forget(eventName, handler);
      });

      this.collectionRefreshHandlers = [];
    },
  },
};

export default AbstractEntityCollectionVueMixin;
