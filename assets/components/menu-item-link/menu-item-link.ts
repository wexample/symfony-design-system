import AgentNavigation from '@wexample/symfony-loader/js/Class/AgentNavigation';
import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import { rippleAgentPress, rippleOnPress } from '../../js/Helper/RippleHelper';

// An entry of a menu. It does nothing a link does not already do but answer
// a press with a wave; the rest is for an agent, which reads it, follows it,
// or points it out (`menu-item-link.ai.yml`).
export default class extends Component {
  private stopRipple: () => void = () => {};

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.stopRipple = rippleOnPress(this.linkEl);
  }

  protected async deactivateListeners(): Promise<void> {
    this.stopRipple();
    await super.deactivateListeners();
  }

  private get linkEl(): HTMLAnchorElement | null {
    return this.el.querySelector('a[href]');
  }

  public getAgentLabel(): string | undefined {
    return this.el.querySelector('.menu--item--label')?.textContent?.trim() || undefined;
  }

  public getAgentState(): Record<string, any> {
    return {
      href: this.linkEl?.getAttribute('href') ?? null,
      current: this.linkEl?.classList.contains('active') ?? false,
    };
  }

  public getAgentActions(): AgentActionsImplementation {
    return {
      // Pressed where the reader sees it, then followed.
      open: async () => {
        await rippleAgentPress(this.app, this.linkEl);

        return new AgentNavigation(this.linkEl.href, { opened: this.getAgentLabel() });
      },
    };
  }

  // The frame goes round the link, the part of the entry the reader sees.
  public getAgentFrameElement(): HTMLElement | null {
    return this.linkEl;
  }
}
