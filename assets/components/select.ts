import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private selectEl?: HTMLSelectElement;

  protected async activateListeners(): Promise<void> {
    this.selectEl = this.el.querySelector('.select--input') as HTMLSelectElement;
    this.selectEl?.addEventListener('change', this.onChange);
  }

  protected async deactivateListeners(): Promise<void> {
    this.selectEl?.removeEventListener('change', this.onChange);
  }

  private onChange = (): void => {
    const value = this.selectEl?.value ?? '';
    this.el.dataset.value = value;
    this.el.dispatchEvent(
      new CustomEvent('select:change', { bubbles: true, detail: { value } })
    );
  };
}
