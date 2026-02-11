import Component from '@wexample/symfony-loader/js/Class/Component';
import ModalService from '@wexample/symfony-loader/js/Services/ModalService';

export default class extends Component {
  private linkEl?: HTMLAnchorElement;

  protected async mounted(): Promise<void> {
    this.linkEl = this.el as HTMLAnchorElement;
    this.linkEl.addEventListener('click', this.onClick);
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    this.linkEl?.removeEventListener('click', this.onClick);
    await super.unmounted();
  }

  private onClick = (event: Event) => {
    const href = this.linkEl?.getAttribute('href');
    if (!href || href === '#') {
      return;
    }

    event.preventDefault();
    (this.app.getService(ModalService) as ModalService).get(href);
  };
}
