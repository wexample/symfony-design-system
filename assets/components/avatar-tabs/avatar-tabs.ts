import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import type { AgentCallContext } from '@wexample/symfony-loader/js/Interfaces/AgentToolInterface';
import { agentTabFind, agentTabName, agentTabOpen, agentTabRead } from '../../js/Helper/AgentTabsHelper';

// The faces of the conversations, a tab each: nothing to do in the browser
// for the reader — each is a link —, what an agent reads and does of them
// (`avatar-tabs.ai.yml`, a bar of tabs).
export default class extends Component {
  private agentTabs(): HTMLElement[] {
    return [...this.el.querySelectorAll<HTMLElement>('.avatar-tabs--tab')];
  }

  public getAgentState(): Record<string, any> {
    const current = this.el.querySelector<HTMLElement>('.avatar-tabs--tab.is-active');

    return { current: current ? agentTabName(current) : null };
  }

  public getAgentActions(): AgentActionsImplementation {
    const read = (el: HTMLElement) => agentTabRead(el, el.classList.contains('is-active'));

    return {
      tabs: () => this.agentTabs().map(read),
      open: ({ tab }) => {
        const el = agentTabFind(this.agentTabs(), tab);

        return agentTabOpen(this.app, el, read(el));
      },
      point: ({ tab }, context?: AgentCallContext) => {
        this.app.services.agentTools?.pointer.point([agentTabFind(this.agentTabs(), tab)], context);

        return { pointed: 1 };
      },
    };
  }
}
