import Component from '@wexample/symfony-loader/js/Class/Component';
import AssetUsage from '@wexample/symfony-loader/js/Class/AssetUsage';
import type AgentToolInterface from '@wexample/symfony-loader/js/Interfaces/AgentToolInterface';

type ColorScheme = 'light' | 'dark';

export default class extends Component {
  private checked: boolean = false;

  private onToggle = async () => {
    await this.setScheme(this.checked ? 'dark' : 'light');
  };

  // The switch and the agent go the same way: the box follows, the layout
  // takes the scheme, the choice outlives the page.
  private async setScheme(scheme: ColorScheme): Promise<void> {
    this.checked = scheme === 'light';
    this.el.setAttribute('data-checked', String(this.checked));
    await (this.app.layout as any).setUsage(AssetUsage.USAGE_COLOR_SCHEME, scheme, true);
    this.app.persistUiState('ui.color_scheme', scheme);
  }

  private currentScheme(): ColorScheme {
    return document.body.classList.contains('usage-color-scheme-dark') ? 'dark' : 'light';
  }

  protected getAgentTools(): AgentToolInterface[] {
    return [
      {
        name: 'set_color_scheme',
        description: 'Show the interface in light or in dark colours. The choice is kept for the next visits.',
        inputSchema: {
          type: 'object',
          properties: {
            scheme: { type: 'string', enum: ['light', 'dark'] },
          },
          required: ['scheme'],
        },
        annotations: { title: 'Colour scheme', idempotentHint: true },
        execute: async ({ scheme }) => {
          if (scheme !== 'light' && scheme !== 'dark') {
            throw new Error(`Unknown colour scheme "${scheme}": light or dark.`);
          }

          await this.setScheme(scheme);

          return { scheme: this.currentScheme() };
        },
      },
    ];
  }

  protected async activateListeners(): Promise<void> {
    this.checked = this.currentScheme() === 'light';
    this.el.setAttribute('data-checked', String(this.checked));
    this.el.addEventListener('click', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('click', this.onToggle);
  }
}
