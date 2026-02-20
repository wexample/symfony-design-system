export default {
  data() {
    return {
      asyncComponentLoaded: false,
      asyncComponentLoading: false,
      asyncComponentError: null as unknown,
      asyncComponentData: {} as Record<string, unknown>,
    };
  },

  async mounted() {
    await this.loadAsyncComponent();
  },

  methods: {
    async asyncComponentLoadMap(): Promise<Record<string, unknown>> {
      return {};
    },

    async asyncComponentLoad(): Promise<void> {
      const data = await this.asyncComponentLoadMap();

      if (data && typeof data === 'object' && !Array.isArray(data)) {
        this.asyncComponentData = {
          ...this.asyncComponentData,
          ...data,
        };
      }
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
      this.asyncComponentData = {};
    },
  },
};
