import Component from '@wexample/symfony-loader/js/Class/Component';

// The dock of the overlay layer: what it opens takes room at the window's
// end, and the modal under the veil moves over to leave it — told through a
// variable the modal's stylesheet reads (`--overlay-aside`), set to the
// dock's width as it changes, dragged or closed.
export default class extends Component {
  private observer?: ResizeObserver;

  protected async activateListeners(): Promise<void> {
    this.observer = new ResizeObserver(() => this.tell());
    this.observer.observe(this.el);
    this.tell();
  }

  protected async deactivateListeners(): Promise<void> {
    this.observer?.disconnect();
    document.documentElement.style.removeProperty('--overlay-aside');
  }

  private tell(): void {
    document.documentElement.style.setProperty('--overlay-aside', `${this.el.offsetWidth}px`);
  }
}
