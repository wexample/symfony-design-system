import AbstractEntityManipulatorVueMixin from './AbstractEntityManipulatorVueMixin';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';
import LiveUpdatesService from '@wexample/symfony-loader/js/Services/LiveUpdatesService';

const AbstractEntityCollectionVueMixin = {
  mixins: [AbstractEntityManipulatorVueMixin],

  data() {
    return {
      entities: [],
      collectionRefreshHandlers: [],
      isLoading: false,
      isLoadingOlder: false,
      page: 0,
      // Read backwards, which page is the topmost one displayed. Null until the
      // first read, since that is what says how many pages there are.
      oldestLoadedPage: null,
      pagination: null,
      // What keeps the collection true after the first read. A collection may
      // declare none, one or several of the three: they are not alternatives,
      // they are what happens to be available where it stands.
      liveConnection: null,
      pollingTimer: null,
    };
  },

  computed: {
    hasOlderEntities() {
      return this.oldestLoadedPage !== null && this.oldestLoadedPage > 0;
    },
  },

  mounted() {
    this.refreshEntitiesCollection();
    this.registerCollectionRefreshEvents();
    // The app has to be up before a subscription can be asked for, which is not
    // true of the two others.
    this.runWhenAppReady(() => this.connectToLiveSource());
    this.startCollectionPolling();
  },

  beforeDestroy() {
    this.stopWatchingCollection();
  },

  beforeUnmount() {
    this.stopWatchingCollection();
  },

  methods: {
    getEntitiesFetchParams() {
      return undefined;
    },

    // Null disables pagination: the collection is fetched in a single request.
    getPageLength() {
      return null;
    },

    // A collection read backwards opens on its last page and grows upwards, so
    // "more" means older. The order the api answers in does not change: what
    // changes is where the reading starts and which way it walks.
    startsAtLastPage() {
      return false;
    },

    getEntityKey(entity) {
      return entity?.id ?? entity?.secureId ?? entity;
    },

    // Two reads can overlap when the collection grew in between: what is already
    // held wins, and only the unknown is kept.
    filterUnknownEntities(items) {
      const known = new Set(this.entities.map((entity) => this.getEntityKey(entity)));

      return items.filter((entity) => !known.has(this.getEntityKey(entity)));
    },

    async fetchEntitiesPage(page) {
      const length = this.getPageLength();

      return this.getEntityRepository().fetchListPaginated({
        ...(this.getEntitiesFetchParams() ?? {}),
        // A zero length is how the api is told to drop its own limit. Saying
        // nothing would leave the server's default in force, and a collection
        // that believes it holds everything would silently hold a first page.
        ...(length ? { page, length } : { length: 0 }),
      });
    },

    async refreshEntitiesCollection() {
      const reversed = this.startsAtLastPage();

      this.isLoading = true;
      try {
        // A negative page is read by the api as counted back from the end, which
        // is the only way to ask for the freshest slice without first asking how
        // many there are.
        const result = await this.fetchEntitiesPage(reversed ? -1 : this.page);

        this.pagination = result.pagination;

        if (reversed) {
          this.receiveLastPage(result.items, result.pagination.page);
        } else {
          this.entities = result.items;
        }
      } finally {
        this.isLoading = false;
      }

      if (!reversed) {
        await this.clampPageToAvailableResults();
      }
    },

    // Refreshing a collection read backwards must not throw away the pages the
    // reader has scrolled back to: what the last page brings is added to them.
    receiveLastPage(items, page) {
      if (this.oldestLoadedPage === null || page < this.oldestLoadedPage) {
        this.oldestLoadedPage = page;
        this.entities = items;

        return;
      }

      this.entities = [...this.entities, ...this.filterUnknownEntities(items)];
    },

    async loadOlderEntities() {
      if (this.isLoadingOlder || !this.hasOlderEntities) {
        return;
      }

      const target = this.oldestLoadedPage - 1;

      this.isLoadingOlder = true;
      try {
        const result = await this.fetchEntitiesPage(target);

        this.oldestLoadedPage = target;
        this.entities = [...this.filterUnknownEntities(result.items), ...this.entities];
      } finally {
        this.isLoadingOlder = false;
      }
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

    // --- What brings the collection back ---
    //
    // Three ways in, and a collection picks what its situation offers:
    //
    //   - an app event, for a change this page caused itself;
    //   - a live source, where the server has a topic to publish on;
    //   - polling, where it has none, or where a round trip a minute is
    //     cheaper than a subscription held open.
    //
    // All three end in the same call, so nothing downstream knows which one
    // rang.

    getCollectionRefreshEvents() {
      return [];
    },

    /**
     * The entity whose topic this collection listens to, and the event on it
     * worth a redraw:
     *
     *   { entityName: 'process', id: this.processId, event: 'run-changed' }
     *
     * Note whose topic it is: a collection listens to what it is the collection
     * *of* — a process for its runs, a session for its messages — and never to
     * each of its own rows. A row that does not exist yet has no topic, and it
     * is precisely the row that appears which the reader is waiting for.
     *
     * Omit `event` to redraw on anything published there.
     */
    getLiveSource() {
      return null;
    },

    async connectToLiveSource() {
      const source = this.getLiveSource();

      if (!source) {
        return;
      }

      this.liveConnection = await this.app
        .getServiceOrFail(LiveUpdatesService)
        .connectToEntity({
          entityName: source.entityName,
          id: source.id,
          owner: this,
          onMessage: (connection, payload) => this.onLiveSourceMessage(payload, source),
        });
    },

    /**
     * The collection is asked for again rather than patched with what arrived:
     * the row just published is also the one whoever caused it already has, and
     * asking again is shorter than telling the two apart. It is also what keeps
     * the server the only one deciding what the collection holds.
     */
    onLiveSourceMessage(payload, source) {
      if (!source.event || payload?.event === source.event) {
        this.refreshEntitiesCollection();
      }
    },

    // Milliseconds between two readings, or null to ask only when something
    // says to. A collection that polls says how often it is worth it.
    getPollingIntervalMs() {
      return null;
    },

    startCollectionPolling() {
      const interval = this.getPollingIntervalMs();

      if (!interval || this.pollingTimer) {
        return;
      }

      this.pollingTimer = setInterval(() => {
        // A page nobody is looking at is a page nobody needs read to them.
        if (document.hidden) {
          return;
        }

        this.refreshEntitiesCollection();
      }, interval);
    },

    stopCollectionPolling() {
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer);
        this.pollingTimer = null;
      }
    },

    stopWatchingCollection() {
      this.unregisterCollectionRefreshEvents();
      this.stopCollectionPolling();
      this.liveConnection?.close();
      this.liveConnection = null;
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
