const appReadyPromiseByInstance = new WeakMap<object, Promise<void>>();

const AbstractDesignSystemVueMixin = {
  methods: {
    asyncComponentPromisesLoad() {
      return [
        this.waitForAppReady(),
      ];
    },

    waitForAppReady(): Promise<void> {
      const instance = this as object;
      const cached = appReadyPromiseByInstance.get(instance);
      if (cached) {
        return cached;
      }

      const promise = new Promise<void>((resolve) => {
        this.app.ready(() => resolve());
      });

      appReadyPromiseByInstance.set(instance, promise);
      return promise;
    },

    async runWhenAppReady(callback: () => unknown | Promise<unknown>) {
      await this.waitForAppReady();
      return callback.call(this);
    },
  },
};

export default AbstractDesignSystemVueMixin;
