import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private toggleEl?: HTMLElement;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.toggleEl = this.el.querySelector('.zone-collapsible--toggle') as HTMLElement;
    this.toggleEl?.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.toggleEl?.removeEventListener('click', this.onToggle);

    await super.deactivateListeners();
  }

  private onToggle = (event: Event): void => {
    event.preventDefault();

    const collapsed = this.el.classList.toggle('zone-collapsible--collapsed');
    this.toggleEl?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    // A region only remembers if it was named: without an id there is no key to
    // write under, and a page that did not name it did not ask for memory.
    const id = this.el.dataset.zoneId;
    if (id) {
      this.app.persistUiState(`ui.layout.zone.${id}`, !collapsed);
    }
  };
}
