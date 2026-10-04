import Component from '@wexample/symfony-loader/js/Class/Component';
import { clipboardCopy } from '../../js/Helper/ClipboardHelper';

// How long the check — or the cross — stays once the button was used.
const COPY_BUTTON_FEEDBACK = 2000;

export default class extends Component {
  private timer?: number;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.el.addEventListener('click', this.onClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onClick);
    window.clearTimeout(this.timer);

    await super.deactivateListeners();
  }

  // The value given, or the target's text as it reads when clicked.
  private get text(): string {
    const { copyValue, copyTarget } = this.el.dataset;

    if (copyValue !== undefined) {
      return copyValue;
    }

    return (copyTarget && document.getElementById(copyTarget)?.textContent?.trim()) || '';
  }

  private onClick = async (): Promise<void> => {
    const copied = await clipboardCopy(this.text);
    const button = this.el as HTMLButtonElement;
    const title = button.getAttribute('aria-label') ?? '';
    const said = (copied ? button.dataset.copyDone : button.dataset.copyFailed) ?? '';

    button.classList.toggle('is-copied', copied);
    button.classList.toggle('is-failed', !copied);
    button.title = said;
    button.querySelector('.copy-button--status')!.textContent = said;

    window.clearTimeout(this.timer);
    this.timer = window.setTimeout(() => {
      button.classList.remove('is-copied', 'is-failed');
      button.title = title;
      button.querySelector('.copy-button--status')!.textContent = '';
    }, COPY_BUTTON_FEEDBACK);
  };
}
