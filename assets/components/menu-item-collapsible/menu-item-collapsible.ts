import AgentNavigation from '@wexample/symfony-loader/js/Class/AgentNavigation';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import AbstractCollapsibleComponent from '../../js/Class/AbstractCollapsibleComponent';
import { rippleAgentPress, rippleOnPress } from '../../js/Helper/RippleHelper';

export default class extends AbstractCollapsibleComponent {
  private stopRipples: (() => void)[] = [];

  protected getToggleSelector(): string {
    return '.menu-item-collapsible--toggle';
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.stopRipples = [rippleOnPress(this.linkEl), rippleOnPress(this.toggleButtonEl)];
  }

  protected async deactivateListeners(): Promise<void> {
    this.stopRipples.forEach((stop) => stop());
    await super.deactivateListeners();
  }

  private get toggleButtonEl(): HTMLElement | null {
    return this.el.querySelector(this.getToggleSelector());
  }

  private get linkEl(): HTMLAnchorElement | null {
    return this.el.querySelector('.menu-item-collapsible--link');
  }

  public getAgentLabel(): string | undefined {
    return this.linkEl?.querySelector('.menu--item--label')?.textContent?.trim() || undefined;
  }

  public getAgentState(): Record<string, any> {
    return { expanded: this.isOpen() };
  }

  public getAgentActions(): AgentActionsImplementation {
    return {
      expand: async () => {
        await rippleAgentPress(this.app, this.toggleButtonEl);
        this.setOpen(true);
        return { expanded: true };
      },
      collapse: async () => {
        await rippleAgentPress(this.app, this.toggleButtonEl);
        this.setOpen(false);
        return { expanded: false };
      },
      open: async () => {
        await rippleAgentPress(this.app, this.linkEl);

        return new AgentNavigation(this.linkEl.href, { opened: this.getAgentLabel() });
      },
    };
  }

  // The frame goes round the header, the entry itself, not what it folds.
  public getAgentFrameElement(): HTMLElement | null {
    return this.el.querySelector('.menu-item-collapsible--header');
  }
}
