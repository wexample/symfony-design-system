import Component from '@wexample/symfony-loader/js/Class/Component';
import { floatingPlace, type FloatingSide } from '../../js/Helper/FloatingHelper';

// What an element says it wants, read off its attributes:
//   data-tooltip            the text
//   data-tooltip-title      a heading above it, for a richer one
//   data-tooltip-placement  top (default), bottom, left, right
//   data-tooltip-variant    `light` for a tooltip on the page's ground
const SELECTOR = '[data-tooltip]';

// Long enough not to flash under a pointer passing by, short enough to come
// when it is waited for. Once one is shown, the next comes at once: moving
// along a row of icons reads each of them without waiting again.
const DELAY_MS = 400;
const WARM_MS = 600;

export default class extends Component {
  private titleEl?: HTMLElement;
  private textEl?: HTMLElement;
  private anchor: HTMLElement | null = null;
  private showTimer: number | null = null;
  private warmUntil = 0;

  attachHtmlElements() {
    super.attachHtmlElements();
    this.titleEl = this.el.querySelector('.tooltip--title') as HTMLElement;
    this.textEl = this.el.querySelector('.tooltip--text') as HTMLElement;
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    document.addEventListener('pointerover', this.onPointerOver);
    document.addEventListener('pointerout', this.onPointerOut);
    document.addEventListener('focusin', this.onFocusIn);
    document.addEventListener('focusout', this.onFocusOut);
    document.addEventListener('keydown', this.onKeyDown);
    // Whatever moves the page moves the anchor from under the tooltip.
    window.addEventListener('scroll', this.hide, true);
    window.addEventListener('resize', this.hide);
  }

  protected async deactivateListeners(): Promise<void> {
    document.removeEventListener('pointerover', this.onPointerOver);
    document.removeEventListener('pointerout', this.onPointerOut);
    document.removeEventListener('focusin', this.onFocusIn);
    document.removeEventListener('focusout', this.onFocusOut);
    document.removeEventListener('keydown', this.onKeyDown);
    window.removeEventListener('scroll', this.hide, true);
    window.removeEventListener('resize', this.hide);

    await super.deactivateListeners();
  }

  private anchorOf(target: EventTarget | null): HTMLElement | null {
    const el = (target as Element | null)?.closest?.(SELECTOR) as HTMLElement | null;

    return el && el.dataset.tooltip ? el : null;
  }

  private onPointerOver = (event: PointerEvent): void => {
    // A finger has no hover: a tooltip it raised would stay under it.
    if (event.pointerType === 'touch') {
      return;
    }

    const anchor = this.anchorOf(event.target);

    if (anchor && anchor !== this.anchor) {
      this.schedule(anchor);
    }
  };

  private onPointerOut = (event: PointerEvent): void => {
    const anchor = this.anchorOf(event.target);

    if (anchor && !anchor.contains(event.relatedTarget as Node | null)) {
      this.hide();
    }
  };

  private onFocusIn = (event: FocusEvent): void => {
    const anchor = this.anchorOf(event.target);

    if (anchor) {
      this.schedule(anchor);
    }
  };

  private onFocusOut = (): void => {
    this.hide();
  };

  private onKeyDown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape' && this.anchor) {
      this.hide();
    }
  };

  private schedule(anchor: HTMLElement): void {
    this.clearTimer();

    const delay = Date.now() < this.warmUntil ? 0 : DELAY_MS;
    this.showTimer = window.setTimeout(() => this.show(anchor), delay);
  }

  private show(anchor: HTMLElement): void {
    this.showTimer = null;

    if (!anchor.isConnected || !this.titleEl || !this.textEl) {
      return;
    }

    this.release();
    this.anchor = anchor;

    const title = anchor.dataset.tooltipTitle ?? '';
    this.titleEl.textContent = title;
    this.titleEl.hidden = title === '';
    this.textEl.textContent = anchor.dataset.tooltip ?? '';
    this.el.classList.toggle('tooltip--light', anchor.dataset.tooltipVariant === 'light');

    // Shown before it is placed: a hidden box has no size to place.
    this.el.hidden = false;
    floatingPlace(anchor, this.el, {
      placement: (anchor.dataset.tooltipPlacement as FloatingSide) || 'top',
    });
    this.el.classList.add('is-visible');

    const described = anchor.getAttribute('aria-describedby');
    anchor.setAttribute('aria-describedby', described ? `${described} ${this.el.id}` : this.el.id);
  }

  private hide = (): void => {
    this.clearTimer();

    if (!this.anchor) {
      return;
    }

    this.release();
    this.el.classList.remove('is-visible');
    this.el.hidden = true;
    this.warmUntil = Date.now() + WARM_MS;
  };

  // The anchor gives back what it was described by before.
  private release(): void {
    if (!this.anchor) {
      return;
    }

    const rest = (this.anchor.getAttribute('aria-describedby') ?? '')
      .split(' ')
      .filter((id) => id && id !== this.el.id)
      .join(' ');

    if (rest) {
      this.anchor.setAttribute('aria-describedby', rest);
    } else {
      this.anchor.removeAttribute('aria-describedby');
    }

    this.anchor = null;
  }

  private clearTimer(): void {
    if (this.showTimer !== null) {
      window.clearTimeout(this.showTimer);
      this.showTimer = null;
    }
  }
}
