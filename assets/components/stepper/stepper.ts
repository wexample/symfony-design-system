import Component from '@wexample/symfony-loader/js/Class/Component';

/**
 * Folds the steps until they hold on their line: 0 names every step, 1 names
 * the current one only, the others keeping their number and saying their name
 * under the pointer. Measured on the stepper itself, not on the window, as the
 * page's tab bar does.
 */
export default class extends Component {
  private static readonly STEPS = ['0', '1'];

  private observer?: ResizeObserver;
  private width = -1;
  private frame = 0;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    // Its width alone, a frame later, out of the observer's own pass.
    this.observer = new ResizeObserver(([entry]) => {
      if (entry.contentRect.width === this.width) return;
      this.width = entry.contentRect.width;
      cancelAnimationFrame(this.frame);
      this.frame = requestAnimationFrame(() => this.fold());
    });
    this.observer.observe(this.el);
    this.fold();
  }

  protected async deactivateListeners(): Promise<void> {
    this.observer?.disconnect();
    cancelAnimationFrame(this.frame);

    await super.deactivateListeners();
  }

  private fold(): void {
    for (const step of (this.constructor as any).STEPS as string[]) {
      this.el.dataset.fold = step;

      if (this.el.scrollWidth <= this.el.clientWidth + 1) {
        break;
      }
    }

    this.title(this.el.dataset.fold !== '0');
  }

  // A step that lost its name says it under the pointer; given back unfolded.
  private title(folded: boolean): void {
    this.el.querySelectorAll<HTMLElement>('.stepper--item:not(.is-current) .stepper--step').forEach((el) => {
      if (folded && !el.title) {
        el.title = el.querySelector('.stepper--label')?.textContent?.trim() ?? '';
        el.dataset.foldTitle = '';
      } else if (!folded && el.dataset.foldTitle !== undefined) {
        el.removeAttribute('title');
        delete el.dataset.foldTitle;
      }
    });
  }
}
