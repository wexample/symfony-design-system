import Page from'@wexample/symfony-design-system/js/class/Page';
import Events from'@wexample/symfony-design-system/js/helpers/Events';

export default class extends Page {
  async pageReady() {
    document
      .querySelectorAll('.demo-button-switch-color-scheme')
      .forEach((el) => {
        el.addEventListener(Events.CLICK, async () => {
          await this.app.layout.colorSchemeSet(
            el.getAttribute('data-color-scheme'),
            true
          );
        });
      });
  }
}
