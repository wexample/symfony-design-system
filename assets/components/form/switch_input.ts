import Field from '../../js/Class/Field';

export default class extends Field {
  private checkboxEl?: HTMLInputElement;
  private switchEl?: HTMLElement;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.checkboxEl = this.el.querySelector('.switch--checkbox') as HTMLInputElement;
    this.switchEl = this.el.querySelector('.switch') as HTMLElement;

    this.checkboxEl?.addEventListener('change', this.onCheckboxChange);
  }

  protected async deactivateListeners(): Promise<void> {
    this.checkboxEl?.removeEventListener('change', this.onCheckboxChange);
  }

  private onCheckboxChange = (): void => {
    this.switchEl?.setAttribute('data-checked', String(this.checkboxEl?.checked ?? false));
  };
}
