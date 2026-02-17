import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';

export default class extends Component {
  private contentEl?: HTMLElement;

  async init() {
    OverlayMixin.apply(this);
    await super.init();
  }

  attachHtmlElements() {
    super.attachHtmlElements();

    this.contentEl = this.el.querySelector('.overlay-content') as HTMLElement;

    const layoutBody = this.options?.layoutBody;
    if (this.contentEl && layoutBody !== undefined) {
      this.contentEl.innerHTML = layoutBody || '';
    }

    const className = this.options?.className;
    if (className) {
      this.el.classList.add(className);
    }
  }

  async open(options: { instant?: boolean } = {}) {
    await (this as any).overlayOpen(options.instant);
  }

  async close(options: { instant?: boolean } = {}) {
    await (this as any).overlayClose(options.instant);
  }
}
