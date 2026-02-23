export default {
  data() {
    return {
      asyncComponentLoaded: false,
      asyncComponentLoading: false,
      asyncComponentError: null as unknown,
    };
  },

  async mounted() {
    await this.loadAsyncComponent();
  },

  methods: {
    async asyncComponentLoad(): Promise<void> {
      // Override in component to load required async data.
    },

    async loadAsyncComponent(forceRefresh: boolean = false): Promise<void> {
      if (this.asyncComponentLoading) {
        return;
      }

      if (this.asyncComponentLoaded && !forceRefresh) {
        return;
      }

      this.asyncComponentLoading = true;
      this.asyncComponentError = null;

      try {
        await this.asyncComponentLoad();
        this.asyncComponentLoaded = true;
      } catch (error) {
        this.asyncComponentLoaded = false;
        this.asyncComponentError = error;
        throw error;
      } finally {
        this.asyncComponentLoading = false;
      }
    },

    resetAsyncComponentState(): void {
      this.asyncComponentLoaded = false;
      this.asyncComponentLoading = false;
      this.asyncComponentError = null;
    },
  },
};
