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

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.observer = new ResizeObserver(() => this.fold());
    this.observer.observe(this.el);
    this.fold();
  }

  protected async deactivateListeners(): Promise<void> {
    this.observer?.disconnect();

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
