import Component from '../js/class/Component';
import ComponentInterface from '../js/interfaces/RenderData/ComponentInterface';

export default class extends Component {
  loadFirstRenderData(renderData: ComponentInterface) {
    // First component render data is stored into service,
    // in order to reuse sub components definitions
    // as components ids in vue template stay the same.
    let name = renderData.options.vueComName;
    let cache = this.app.services.vue.vueRenderDataCache;

    if (cache[name]) {
      renderData.components = cache[name].components;
    } else {
      cache[name] = renderData;
    }

    super.loadFirstRenderData(renderData);
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    if (!this.app.services.vue) {
      this.app.services.prompt.systemError(
        'page_message.error.vue_service_missing'
      );

      return;
    }

    this.app.services.vue.createVueAppForComponent(this).mount(this.el);
  }
}
