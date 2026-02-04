import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private itemsEl?: HTMLElement;

  attachHtmlElements() {
    super.attachHtmlElements();
    this.itemsEl = this.el.querySelector('.toast-stack--items') as HTMLElement;
  }

  addToast(html: string) {
    if (!this.itemsEl) {
      return;
    }

    const item = document.createElement('div');
    item.className = 'toast-stack--item';
    item.innerHTML = html;
    this.itemsEl.appendChild(item);
  }
}
