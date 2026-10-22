import Component from '@wexample/symfony-loader/js/Class/Component';

// Every property back to its initial value on the host, so nothing the page
// set on an ancestor is inherited inside. The page may still size the host:
// its own rules on the element win over this one.
const ISOLATED_HOST_STYLE = ':host { all: initial; display: block; }';

export default class extends Component {
  protected async mounted(): Promise<void> {
    await super.mounted();

    const template = this.el.querySelector<HTMLTemplateElement>(':scope > template');

    if (!template || this.el.shadowRoot) {
      return;
    }

    const root = this.el.attachShadow({ mode: 'open' });
    const style = document.createElement('style');

    style.textContent = ISOLATED_HOST_STYLE;
    root.append(style, template.content.cloneNode(true));
    template.remove();
  }
}
