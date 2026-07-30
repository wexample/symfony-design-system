import AbstractEntityManipulatorVueMixin from './AbstractEntityManipulatorVueMixin';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';

const AbstractEntityCollectionVueMixin = {
  mixins: [AbstractEntityManipulatorVueMixin],

  data() {
    return {
      entities: [],
      collectionRefreshHandlers: [],
      isLoading: false,
      page: 0,
      pagination: null,
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

    // Null disables pagination: the collection is fetched in a single request.
    getPageLength() {
      return null;
    },

    async refreshEntitiesCollection() {
      this.isLoading = true;
      try {
        const length = this.getPageLength();
        const fetchParams = {
          ...(this.getEntitiesFetchParams() ?? {}),
          ...(length ? { page: this.page, length } : {}),
        };

        const result = await this.getEntityRepository().fetchListPaginated(fetchParams);

        this.entities = result.items;
        this.pagination = result.pagination;
      } finally {
        this.isLoading = false;
      }

      await this.clampPageToAvailableResults();
    },

    // Deleting the last rows of a page can leave us past the end: fall back to
    // the last page that still holds results.
    async clampPageToAvailableResults() {
      if (this.entities.length || this.page === 0) {
        return;
      }

      const target = Math.max(0, (this.pagination?.pagesCount ?? 1) - 1);

      if (target >= this.page) {
        return;
      }

      this.page = target;
      await this.refreshEntitiesCollection();
    },

    async goToPage(page) {
      const target = Math.max(0, Number(page) || 0);

      if (target === this.page) {
        return;
      }

      this.page = target;

      await this.refreshEntitiesCollection();
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
