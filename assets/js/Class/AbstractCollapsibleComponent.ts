import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentNodeProviderInterface } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';

// What folds what it holds under a toggle: open while `is-open`. An agent
// pointing at something folded inside has it unfolded first.
export default abstract class AbstractCollapsibleComponent extends Component {
  private toggleEl?: HTMLElement;

  protected abstract getToggleSelector(): string;

  protected async activateListeners(): Promise<void> {
    this.toggleEl = this.el.querySelector(this.getToggleSelector()) as HTMLElement;
    this.toggleEl?.addEventListener('click', this.onToggleClick);
  }

  protected async deactivateListeners(): Promise<void> {
    this.toggleEl?.removeEventListener('click', this.onToggleClick);
  }

  private onToggleClick = () => {
    this.setOpen(!this.isOpen());
  };

  protected isOpen(): boolean {
    return this.el.classList.contains('is-open');
  }

  protected setOpen(open: boolean): void {
    this.el.classList.toggle('is-open', open);
  }

  public async revealAgentChild(child: AgentNodeProviderInterface): Promise<void> {
    this.setOpen(true);
  }
}
