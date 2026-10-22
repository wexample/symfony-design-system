import Component from '@wexample/symfony-loader/js/Class/Component';

/**
 * Folds the bar, step by step, until it holds on its line: 0 shows everything,
 * 1 keeps the icons of all but the current tab and of the actions, 2 gathers
 * the tabs in a menu. Measured on the bar itself, not on the window, so a bar
 * in a narrow region folds as one in a narrow screen does.
 */
export default class extends Component {
  private static readonly STEPS = ['0', '1', '2'];

  private observer?: ResizeObserver;
  private width = -1;
  private frame = 0;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    // Its width alone: a step changes the bar's height, which must not ask
    // for another step. And a frame later, out of the observer's own pass.
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

  // What lost its words says them under the pointer; given back unfolded.
  private title(folded: boolean): void {
    this.el.querySelectorAll<HTMLElement>('.page-tabs--back a, .page-tabs--items .tabs--item, .page-tabs--actions .button').forEach((el) => {
      if (folded && !el.title) {
        el.title = el.textContent?.trim() ?? '';
        el.dataset.foldTitle = '';
      } else if (!folded && el.dataset.foldTitle !== undefined) {
        el.removeAttribute('title');
        delete el.dataset.foldTitle;
      }
    });
  }
}
