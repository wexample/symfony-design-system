import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  protected overlayEnabled: boolean = true;
  private buttonEl?: HTMLButtonElement;
  private panelEl?: HTMLElement;
  private itemLinks: HTMLElement[] = [];

  private onButtonClick = (event: Event) => {
    event.preventDefault();
    (this as any).overlayToggle(event);
  };

  private onItemClick = () => {
    (this as any).overlayClose();
  };

  protected async activateListeners(): Promise<void> {
    this.buttonEl = this.el.querySelector('.button--menu') as HTMLButtonElement;
    this.panelEl = this.el.querySelector('.button-menu__panel') as HTMLElement;
    this.itemLinks = Array.from(
      this.el.querySelectorAll('.button-menu__link')
    ) as HTMLElement[];

    if (!this.buttonEl || !this.panelEl) {
      throw new Error('Button menu elements not found.');
    }

    this.buttonEl.addEventListener('click', this.onButtonClick);

    this.itemLinks.forEach((link) => {
      link.addEventListener('click', this.onItemClick);
    });
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.buttonEl) {
      this.buttonEl.removeEventListener('click', this.onButtonClick);
    }

    this.itemLinks.forEach((link) => {
      link.removeEventListener('click', this.onItemClick);
    });
  }

  overlayOnOpen(): void {
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'true');
    }
    if (this.panelEl) {
      this.panelEl.removeAttribute('hidden');
    }
  }

  overlayOnClose(): void {
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'false');
    }
    if (this.panelEl) {
      this.panelEl.setAttribute('hidden', 'hidden');
    }
  }

  overlayOnEscape(): void {
    (this as any).overlayClose();
    this.buttonEl?.focus();
  }
}
