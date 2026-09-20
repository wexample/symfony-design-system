import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private checked: boolean = false;

  private onToggle = () => {
    this.checked = !this.checked;
    this.el.setAttribute('data-checked', String(this.checked));
    this.el.querySelector('.switch--pill')?.setAttribute('aria-checked', String(this.checked));
  };

  protected async activateListeners(): Promise<void> {
    this.checked = this.el.dataset.checked === 'true';
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
