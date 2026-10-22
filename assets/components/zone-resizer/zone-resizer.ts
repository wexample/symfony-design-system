import Component from '@wexample/symfony-loader/js/Class/Component';
import { attachZoneResize } from '../../js/Helper/ZoneResizeHelper';

export default class extends Component {
  private detach?: () => void;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.detach = attachZoneResize(this.el, this.app);
  }

  protected async deactivateListeners(): Promise<void> {
    this.detach?.();

    await super.deactivateListeners();
  }
}
