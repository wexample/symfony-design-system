import Component from '@wexample/symfony-loader/js/Class/Component';

/**
 * A theme chosen in the menu: each of its axes set on the page — the stylesheets
 * of every switchable value are already there, only the classes of the body
 * change — and kept for the next pages. One theme ticked at a time.
 */
export default class extends Component {
  protected async activateListeners(): Promise<void> {
    await super.activateListeners();
    this.el.addEventListener('button-menu:toggle', this.onToggle);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('button-menu:toggle', this.onToggle);
    await super.deactivateListeners();
  }

  private onToggle = async (event: Event): Promise<void> => {
    const name = (event as CustomEvent).detail?.value as string | null;
    const themes = JSON.parse(this.el.dataset.themes ?? '{}') as Record<string, Record<string, string>>;
    const axes = name ? themes[name] : undefined;

    if (!name || !axes) {
      return;
    }

    // A choice of one: the one picked ticked, the others not — picking the
    // ticked one again keeps it.
    this.el.querySelectorAll<HTMLElement>('.button-menu--toggle').forEach((item) => {
      const checked = item.dataset.value === name;
      item.setAttribute('aria-checked', checked ? 'true' : 'false');
      item.classList.toggle('is-checked', checked);
    });

    const label = this.el.querySelector('.button-menu--label');
    if (label) {
      label.textContent = name.charAt(0).toUpperCase() + name.slice(1);
    }

    for (const [usage, value] of Object.entries(axes)) {
      await (this.app.layout as any).setUsage(usage, value, true);
      this.app.persistUiState(`ui.${usage}`, value);
    }
  };
}
