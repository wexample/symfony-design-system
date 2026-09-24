import Component from '@wexample/symfony-loader/js/Class/Component';
import { buttonSetLoading } from '../../js/Helper/ButtonHelper';

export default class extends Component {
  async activateListeners(): Promise<void> {
    await super.activateListeners();

    const button = this.el as HTMLButtonElement;
    if (button.type === 'submit' && button.form) {
      button.form.addEventListener('submit', this.onSubmit);
    }
  }

  async deactivateListeners(): Promise<void> {
    (this.el as HTMLButtonElement).form?.removeEventListener('submit', this.onSubmit);

    await super.deactivateListeners();
  }

  // Decided once the event has gone all the way up: a submit that something
  // above takes over, to send it in the background, leaves the page where it
  // is, and a button waiting for a navigation that never comes would spin for
  // ever. Whoever took it over says when it is busy.
  private onSubmit = (event: SubmitEvent): void => {
    window.setTimeout(() => {
      if (!event.defaultPrevented) {
        this.setLoading(true);
      }
    });
  };

  setLoading(loading: boolean): void {
    buttonSetLoading(this.el as HTMLButtonElement, loading);
  }
}
