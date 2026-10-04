import Component from '@wexample/symfony-loader/js/Class/Component';
import { clipboardCopy } from '../../js/Helper/ClipboardHelper';

// How long the check stays once the value is copied.
const COPIED_DURATION = 2000;

export default class extends Component {
  private button?: HTMLButtonElement | null;

  private timer?: number;

  protected async mounted(): Promise<void> {
    this.button = this.el.querySelector<HTMLButtonElement>('[data-fallback-copy]');

    // No clipboard outside a secure context — a site served over plain http:
    // a button there would copy nothing, so there is none.
    if (this.button && !navigator.clipboard) {
      this.button.hidden = true;
    }

    this.button?.addEventListener('click', this.onCopy);
    await super.mounted();
  }

  protected async unmounted(): Promise<void> {
    this.button?.removeEventListener('click', this.onCopy);
    window.clearTimeout(this.timer);
    await super.unmounted();
  }

  // Says it copied only when the clipboard took it.
  private onCopy = async (): Promise<void> => {
    const button = this.button;

    if (!button || !(await clipboardCopy(button.dataset.fallbackCopy ?? ''))) {
      return;
    }

    const title = button.title;
    button.classList.add('is-copied');
    button.title = button.dataset.fallbackCopied ?? title;
    window.clearTimeout(this.timer);
    this.timer = window.setTimeout(() => {
      button.classList.remove('is-copied');
      button.title = title;
    }, COPIED_DURATION);
  };
}
