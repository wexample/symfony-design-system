import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private inputEl?: HTMLInputElement;

  private onInputChange = (event: Event) => {
    let target = event.target as HTMLInputElement;
    let files = target?.files;

    if (!files || files.length === 0) {
      return;
    }

    this.app.services.events.trigger(
      this.getEventName(),
      {
        component: this,
        files,
        file: files[0],
        input: target,
        form: this.el
      },
      this.el
    );

    if (this.shouldResetOnChange()) {
      target.value = '';
    }
  };

  protected async activateListeners(): Promise<void> {
    this.inputEl = this.findInputEl();
    this.inputEl.addEventListener('change', this.onInputChange);
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.inputEl) {
      this.inputEl.removeEventListener('change', this.onInputChange);
    }
  }

  private findInputEl(): HTMLInputElement {
    let input = this.el.querySelector('.upload-handler__input') as HTMLInputElement;

    if (!input) {
      input = this.el.querySelector('input[type="file"]') as HTMLInputElement;
    }

    if (!input) {
      throw new Error('Upload handler input element not found.');
    }

    return input;
  }

  private getEventName(): string {
    return this.options?.eventName || 'upload-handler:change';
  }

  private shouldResetOnChange(): boolean {
    return this.options?.resetOnChange === true;
  }
}
