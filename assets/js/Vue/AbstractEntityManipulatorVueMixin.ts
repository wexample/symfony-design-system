import AbstractDesignSystemVueMixin from './AbstractDesignSystemVueMixin';
import ApiService from '@wexample/symfony-loader/js/Services/ApiService';
import EntityService from '@wexample/symfony-loader/js/Services/EntityService';

const AbstractEntityManipulatorVueMixin = {
  mixins: [AbstractDesignSystemVueMixin],

  methods: {
    getEntityClass() {
      throw new Error('getEntityClass() must be implemented.');
    },

    getApiClient() {
      return this.app.getService(ApiService).getClient();
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

    buildEntityPath(options: { entity: unknown; action?: string; params?: Record<string, unknown> }) {
      return this.app.getService(EntityService).entityPath(options);
    },
  },
};

export default AbstractEntityManipulatorVueMixin;
