import Component from '@wexample/symfony-loader/js/Class/Component';
import { floatingPlace, type FloatingSide } from '../../js/Helper/FloatingHelper';

type TourStep = {
  // A selector of the element the step is about. Missing, or matching
  // nothing on the page, the step is shown in the middle of the window.
  target?: string;
  title?: string;
  body?: string;
  placement?: FloatingSide;
};

type TourStatus = 'completed' | 'dismissed';

// Room around the lit element, so its edge does not touch the dimming.
const SPOTLIGHT_PADDING = 6;

export default class extends Component {
  private steps: TourStep[] = [];
  private index = 0;
  private anchor: Element | null = null;
  private frame: number | null = null;
  private els: Record<string, HTMLElement> = {};

  attachHtmlElements() {
    super.attachHtmlElements();

    const find = (selector: string) => this.el.querySelector(selector) as HTMLElement;
    this.els = {
      spotlight: find('.tour--spotlight'),
      popover: find('.tour--popover'),
      progress: find('.tour--progress'),
      title: find('.tour--title'),
      body: find('.tour--body'),
      previous: find('.tour--previous'),
      next: find('.tour--next'),
      done: find('.tour--done'),
      close: find('.tour--close'),
    };
    this.steps = (this.options?.steps as TourStep[]) ?? [];
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.els.previous?.addEventListener('click', this.onPrevious);
    this.els.next?.addEventListener('click', this.onNext);
    this.els.done?.addEventListener('click', this.onDone);
    this.els.close?.addEventListener('click', this.onClose);
    document.addEventListener('click', this.onStartClick);

    // Not replayed to whoever finished or dismissed it: that is what they
    // said, and a tour showing again is a tour ignored.
    if (this.options?.auto !== false && !this.options?.status && this.steps.length) {
      window.setTimeout(() => this.start(), 500);
    }
  }

  protected async deactivateListeners(): Promise<void> {
    this.stopFollowing();
    this.els.previous?.removeEventListener('click', this.onPrevious);
    this.els.next?.removeEventListener('click', this.onNext);
    this.els.done?.removeEventListener('click', this.onDone);
    this.els.close?.removeEventListener('click', this.onClose);
    document.removeEventListener('click', this.onStartClick);

    await super.deactivateListeners();
  }

  public start(): void {
    if (!this.steps.length) {
      return;
    }

    this.el.hidden = false;
    this.startFollowing();
    this.show(0);
  }

  private show(index: number): void {
    this.index = Math.max(0, Math.min(index, this.steps.length - 1));
    const step = this.steps[this.index];
    const last = this.index === this.steps.length - 1;

    this.els.title.textContent = step.title ?? '';
    this.els.title.hidden = !step.title;
    this.els.body.textContent = step.body ?? '';
    this.els.progress.textContent = (this.els.progress.dataset.label ?? '')
      .replace('%current%', String(this.index + 1))
      .replace('%total%', String(this.steps.length));
    this.els.previous.hidden = this.index === 0;
    this.els.next.hidden = last;
    this.els.done.hidden = !last;

    this.anchor = step.target ? document.querySelector(step.target) : null;
    this.anchor?.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });
    this.place();

    (last ? this.els.done : this.els.next).focus({ preventScroll: true });
  }

  // Lights the step's element and sets the card beside it; with nothing to
  // point at, the card stands in the middle and the whole page is dimmed.
  private place = (): void => {
    this.frame = null;
    const { spotlight, popover } = this.els;
    const step = this.steps[this.index];

    if (!this.anchor || !this.anchor.isConnected) {
      spotlight.hidden = true;
      this.el.classList.add('tour--centered');
      popover.style.left = '';
      popover.style.top = '';

      return;
    }

    this.el.classList.remove('tour--centered');
    const rect = this.anchor.getBoundingClientRect();
    spotlight.hidden = false;
    spotlight.style.left = `${rect.left - SPOTLIGHT_PADDING}px`;
    spotlight.style.top = `${rect.top - SPOTLIGHT_PADDING}px`;
    spotlight.style.width = `${rect.width + SPOTLIGHT_PADDING * 2}px`;
    spotlight.style.height = `${rect.height + SPOTLIGHT_PADDING * 2}px`;

    floatingPlace(spotlight, popover, { placement: step.placement ?? 'bottom', offset: 12 });
  };

  // The lit element moves as the page scrolls or the window changes: the
  // light and the card follow it, once a frame.
  private schedulePlace = (): void => {
    if (this.frame === null) {
      this.frame = window.requestAnimationFrame(this.place);
    }
  };

  private startFollowing(): void {
    window.addEventListener('scroll', this.schedulePlace, true);
    window.addEventListener('resize', this.schedulePlace);
    document.addEventListener('keydown', this.onKeyDown);
  }

  private stopFollowing(): void {
    window.removeEventListener('scroll', this.schedulePlace, true);
    window.removeEventListener('resize', this.schedulePlace);
    document.removeEventListener('keydown', this.onKeyDown);

    if (this.frame !== null) {
      window.cancelAnimationFrame(this.frame);
      this.frame = null;
    }
  }

  // Said once, kept for good: a finished or dismissed tour does not start on
  // its own again, and the page hears how it ended.
  private finish(status: TourStatus): void {
    this.stopFollowing();
    this.el.hidden = true;
    this.anchor = null;

    const id = String(this.options?.id ?? '');
    this.app.persistUiState(`ui.tour.${id}`, status);
    this.options.status = status;

    document.dispatchEvent(new CustomEvent('tour:end', { detail: { id, status } }));
  }

  private onPrevious = (): void => this.show(this.index - 1);

  private onNext = (): void => this.show(this.index + 1);

  private onDone = (): void => this.finish('completed');

  private onClose = (): void => this.finish('dismissed');

  private onKeyDown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
      this.finish('dismissed');
    } else if (event.key === 'ArrowRight' && this.index < this.steps.length - 1) {
      this.show(this.index + 1);
    } else if (event.key === 'ArrowLeft' && this.index > 0) {
      this.show(this.index - 1);
    }
  };

  // Any element can start it again: `data-tour-start="<id>"`.
  private onStartClick = (event: MouseEvent): void => {
    const trigger = (event.target as Element | null)?.closest?.('[data-tour-start]') as HTMLElement | null;

    if (trigger && trigger.dataset.tourStart === String(this.options?.id)) {
      event.preventDefault();
      this.start();
    }
  };
}
