import Component from '@wexample/symfony-loader/js/Class/Component';

export default class extends Component {
  private buttonEl?: HTMLButtonElement;
  private panelEl?: HTMLElement;
  private itemLinks: HTMLElement[] = [];

  private onButtonClick = (event: Event) => {
    event.preventDefault();
    this.toggle();
  };

  private onDocumentClick = (event: Event) => {
    let target = event.target as Node;
    if (!this.el || !target) {
      return;
    }

    if (!this.el.contains(target)) {
      this.close();
    }
  };

  private onDocumentKeydown = (event: KeyboardEvent) => {
    if (event.key !== 'Escape') {
      return;
    }

    if (this.isOpen()) {
      this.close();
      this.buttonEl?.focus();
    }
  };

  private onItemClick = () => {
    this.close();
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
    document.addEventListener('click', this.onDocumentClick);
    document.addEventListener('keydown', this.onDocumentKeydown);

    this.itemLinks.forEach((link) => {
      link.addEventListener('click', this.onItemClick);
    });
  }

  protected async deactivateListeners(): Promise<void> {
    if (this.buttonEl) {
      this.buttonEl.removeEventListener('click', this.onButtonClick);
    }

    document.removeEventListener('click', this.onDocumentClick);
    document.removeEventListener('keydown', this.onDocumentKeydown);

    this.itemLinks.forEach((link) => {
      link.removeEventListener('click', this.onItemClick);
    });
  }

  private isOpen(): boolean {
    return this.el.classList.contains('is-open');
  }

  private open(): void {
    if (this.isOpen()) {
      return;
    }

    this.el.classList.add('is-open');
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'true');
    }
    if (this.panelEl) {
      this.panelEl.removeAttribute('hidden');
    }
  }

  private close(): void {
    if (!this.isOpen()) {
      return;
    }

    this.el.classList.remove('is-open');
    if (this.buttonEl) {
      this.buttonEl.setAttribute('aria-expanded', 'false');
    }
    if (this.panelEl) {
      this.panelEl.setAttribute('hidden', 'hidden');
    }
  }

  private toggle(): void {
    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  }
}
