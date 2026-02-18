import data from '@emoji-mart/data';
import { Picker } from 'emoji-mart';
import Component from '@wexample/symfony-loader/js/Class/Component';

type EmojiSelection = {
  native?: string;
};

export default class extends Component {
  private inputEl: HTMLInputElement | null = null;
  private toggleEl: HTMLButtonElement | null = null;
  private panelEl: HTMLElement | null = null;
  private pickerEl: HTMLElement | null = null;
  private onToggleProxy: EventListener | null = null;
  private onDocumentClickProxy: EventListener | null = null;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.inputEl = this.el.querySelector('.form--input-emoji-picker');
    this.toggleEl = this.el.querySelector('.form--emoji-picker-toggle');
    this.panelEl = this.el.querySelector('.form--emoji-picker-panel');

    if (!this.inputEl || !this.toggleEl || !this.panelEl) {
      return;
    }

    this.onToggleProxy = this.onToggle.bind(this);
    this.toggleEl.addEventListener('click', this.onToggleProxy);
    this.onDocumentClickProxy = this.onDocumentClick.bind(this);
    this.syncToggleLabel();
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    if (this.toggleEl && this.onToggleProxy) {
      this.toggleEl.removeEventListener('click', this.onToggleProxy);
    }

    if (this.onDocumentClickProxy) {
      document.removeEventListener('click', this.onDocumentClickProxy);
    }
  }

  private onToggle(event: Event) {
    event.preventDefault();
    event.stopPropagation();

    if (!this.panelEl || !this.toggleEl) {
      return;
    }

    if (this.panelEl.hidden) {
      this.openPanel();
      return;
    }

    this.closePanel();
  }

  private openPanel() {
    if (!this.panelEl || !this.toggleEl) {
      return;
    }

    if (!this.pickerEl) {
      const picker = new Picker({
        data,
        onEmojiSelect: (emoji: EmojiSelection) => {
          this.onEmojiSelect(emoji);
        },
      });
      this.pickerEl = picker as unknown as HTMLElement;
      this.panelEl.appendChild(this.pickerEl);
    }

    this.panelEl.hidden = false;
    this.toggleEl.setAttribute('aria-expanded', 'true');

    if (this.onDocumentClickProxy) {
      document.addEventListener('click', this.onDocumentClickProxy);
    }
  }

  private closePanel() {
    if (!this.panelEl || !this.toggleEl) {
      return;
    }

    this.panelEl.hidden = true;
    this.toggleEl.setAttribute('aria-expanded', 'false');

    if (this.onDocumentClickProxy) {
      document.removeEventListener('click', this.onDocumentClickProxy);
    }
  }

  private onDocumentClick(event: Event) {
    const target = event.target as Node | null;

    if (!target || this.el.contains(target)) {
      return;
    }

    this.closePanel();
  }

  private onEmojiSelect(emoji: EmojiSelection) {
    if (!this.inputEl || !emoji.native) {
      return;
    }

    this.inputEl.value = emoji.native;
    this.syncToggleLabel();
    this.inputEl.dispatchEvent(new Event('input', { bubbles: true }));
    this.inputEl.dispatchEvent(new Event('change', { bubbles: true }));
    this.closePanel();
  }

  private syncToggleLabel() {
    if (!this.inputEl || !this.toggleEl) {
      return;
    }

    const value = this.inputEl.value?.trim();
    this.toggleEl.textContent = value || '😀';
  }
}
