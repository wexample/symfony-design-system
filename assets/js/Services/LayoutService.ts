import AppService from '@wexample/symfony-loader/js/Class/AppService';

/**
 * Tells the stylesheet what it cannot measure: the width of the body's
 * scrollport, scrollbar excluded, as `--layout-scrollport` on the scroller.
 * The header and footer of a page wider than the window take that width and
 * no more, so they never push the page sideways by a scrollbar.
 */
export default class LayoutService extends AppService {
  public static serviceName: string = 'layout';

  private observer?: ResizeObserver;

  registerHooks() {
    return {
      app: {
        hookInit: () => {
          this.observer = new ResizeObserver((entries) => {
            for (const entry of entries) {
              (entry.target as HTMLElement).style.setProperty('--layout-scrollport', `${entry.contentRect.width}px`);
            }
          });

          document.querySelectorAll<HTMLElement>('.layout--body--scrollable').forEach((el) => this.observer?.observe(el));
        },
      },
    };
  }
}
