import AbstractDesignSystemVueMixin from './AbstractDesignSystemVueMixin';

const AbstractEntityManipulatorVueMixin = {
  mixins: [AbstractDesignSystemVueMixin],

  methods: {
    getEntityClass() {
      throw new Error('getEntityClass() must be implemented.');
    },

    getApiClient() {
      if (this.app?.syrtis) {
        return this.app.syrtis;
      }

      if (this.$apiClient) {
        return this.$apiClient;
      }

      if (this.app?.apiClient) {
        return this.app.apiClient;
      }

      throw new Error('API client is missing. Override getApiClient() or provide app.syrtis.');
    },

    getEntityRepository(entityType = null) {
      const client = this.getApiClient();
      const entity = entityType || this.getEntityClass();

      if (client?.getRepository) {
        return client.getRepository(entity);
      }

      if (client?.getEntityManager) {
        const manager = client.getEntityManager();
        if (manager?.get) {
          return manager.get(entity);
        }
      }

      throw new Error('Unable to resolve entity repository from API client.');
    },
  },
};

export default AbstractEntityManipulatorVueMixin;
