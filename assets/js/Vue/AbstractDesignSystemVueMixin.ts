const AbstractDesignSystemVueMixin = {
  props: {
    app: Object,
  },

  data() {
    return {
      appReadyPromise: undefined as Promise<void> | undefined,
    };
  },

  beforeCreate() {
    const options = this.$options as {
      mounted?: Array<() => unknown> | (() => unknown);
      _appReadyWrapped?: boolean;
    };

    if (options._appReadyWrapped) {
      return;
    }

    const originalMounted = options.mounted
      ? Array.isArray(options.mounted)
        ? options.mounted
        : [options.mounted]
      : [];

    options.mounted = [
      async () => {
        await this.waitForAppReady();
        for (const hook of originalMounted) {
          await hook.call(this);
        }
      },
    ];

    options._appReadyWrapped = true;
  },

  methods: {
    waitForAppReady(): Promise<void> {
      if (!this.appReadyPromise) {
        this.appReadyPromise = new Promise<void>((resolve) => {
          this.app.ready(() => resolve());
        });
      }

      return this.appReadyPromise;
    },
  },
};

export default AbstractDesignSystemVueMixin;
