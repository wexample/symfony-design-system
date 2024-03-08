import { createApp } from 'vue/dist/vue.esm-bundler';
import AppService from '../class/AppService';

import Component from '../class/Component';
import App from '../class/App';
import ComponentInterface from '../interfaces/RenderData/ComponentInterface';
import { pathToTagName } from '../helpers/StringHelper';

export default class VueService extends AppService {
  protected componentRegistered: { [key: string]: object } = {};
  protected elTemplates: HTMLElement;
  public vueRenderDataCache: { [key: string]: ComponentInterface } = {};
  public static serviceName: string = 'vue';

  protected globalMixin: object = {
    props: {},

    methods: {},

    async updated() {
      await this.rootComponent.forEachTreeRenderNode((renderNode) => {
        if (this === this.$root) {
          renderNode.updateMounting();
        }
      });
    },
  };

  public renderedTemplates: { [key: string]: boolean } = {};

  constructor(app: App) {
    super(app);

    this.elTemplates = document.getElementById('vue-templates');
  }

  createApp(config, options: any = {}) {
    let app = createApp(config, options);

    Object.entries(this.componentRegistered).forEach((data) => {
      app.component(data[0], data[1]);
    });

    return app;
  }

  inherit(vueComponent, rootComponent: Component) {

    return vueComponent;
  }

  createVueAppForComponent(component: Component) {
    let vue = this.initComponent(component, component);
    let app = this.createApp(vue, component.renderData.options.props);

    return app;
  }

  initComponent(vueComponent: Component, rootComponent: Component): object {
    const name = vueComponent.renderData.options.name

    if (!this.componentRegistered[name]) {
      let vueClassDefinition = this.app.getBundleClassDefinition(name) as any;

      if (!vueClassDefinition) {
        this.app.services.prompt.systemError(
          'Missing vue definition for ":class"',
          {
            ':class': name,
          }
        );
      } else {
        let comName = pathToTagName(name);
        let id = `vue-template-${vueComponent.renderData.options.domId}`;

        vueClassDefinition.template = document.getElementById(id);

        vueClassDefinition.props = {
          ...vueClassDefinition.props,
          ...{
            rootComponent: {
              type: Object,
              default: rootComponent,
            },
            translations: {
              type: Object,
              default: rootComponent.translations[`INCLUDE|${comName}`],
            },
          },
        };

        vueClassDefinition.mixins = (vueClassDefinition.mixins || []).concat([
          this.globalMixin,
        ]);

        if (!vueClassDefinition.template) {
          this.app.services.prompt.systemError(
            `Unable to load vue component as template item #:id has not been found.`,
            {
              ':id': id
            },
            undefined,
            true
          );
        }

        this.componentRegistered[name] = vueClassDefinition;

        this.inherit(vueClassDefinition, rootComponent);
      }
    }

    return this.componentRegistered[name];
  }
}
