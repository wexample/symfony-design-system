import AgentNavigation from '@wexample/symfony-loader/js/Class/AgentNavigation';
import Component from '@wexample/symfony-loader/js/Class/Component';
import type { AgentActionsImplementation } from '@wexample/symfony-loader/js/Interfaces/AgentNodeInterface';
import type { AgentCallContext } from '@wexample/symfony-loader/js/Interfaces/AgentToolInterface';
import { rippleAgentPress } from '../../js/Helper/RippleHelper';
import { confirmAskFor } from '../../js/Helper/ConfirmHelper';
import { rowLinkClick } from '../../js/Helper/RowLinkHelper';

// What the server table needs of the browser once its rows can be ticked: the
// box in the header ticks them all, the count says how many, and an action
// cannot be pressed with nothing to act on. A table without the option has
// none of these. A row whose only action is a link is pressed as that link.

// The server's table: the rows it holds are those of the page, and an order
// or a filter asked for is a page asked again. To an agent (`data-table.ai.yml`,
// shared with the vue twin) the rows read the same; sorting leaves the page,
// and what the page does not offer — a filter to clear — says so.
export default class extends Component {
  private formEl?: HTMLFormElement | null;
  private selectAllEl?: HTMLInputElement | null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.formEl = this.el.querySelector('form.table--bar');
    this.selectAllEl = this.el.querySelector('.table--select-all');

    this.el.addEventListener('change', this.onChange);
    this.el.addEventListener('click', rowLinkClick);
    this.formEl?.addEventListener('submit', this.onSubmit);

    this.update();
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('change', this.onChange);
    this.el.removeEventListener('click', rowLinkClick);
    this.formEl?.removeEventListener('submit', this.onSubmit);

    await super.deactivateListeners();
  }

  private getRowBoxes(): HTMLInputElement[] {
    return Array.from(this.el.querySelectorAll<HTMLInputElement>('.table--select-row'));
  }

  private getSelectEl(): HTMLSelectElement | null {
    return this.el.querySelector<HTMLSelectElement>('.table--bulk-select');
  }

  private onChange = (event: Event): void => {
    const target = event.target as HTMLElement;

    if (target === this.selectAllEl) {
      const checked = this.selectAllEl.checked;
      this.getRowBoxes().forEach((box) => {
        box.checked = checked;
      });
    }

    this.update();
  };

  // A select says which action; the form learns where to post and with which
  // token only then, each action having its own.
  private onSubmit = (event: SubmitEvent): void => {
    const selectEl = this.getSelectEl();
    const option = selectEl?.selectedOptions[0];

    // An action on every row posts every row: the boxes are ticked on the way.
    if (event.submitter?.classList.contains('table--bulk-action--all') || option?.dataset.all !== undefined) {
      this.getRowBoxes().forEach((box) => {
        box.checked = true;
      });
    }

    if (!selectEl || !this.formEl) {
      return;
    }

    if (!option || option.value === '') {
      event.preventDefault();
      return;
    }

    this.formEl.action = option.dataset.href || '#';

    const tokenEl = this.formEl.querySelector<HTMLInputElement>('input[name="_token"]');
    if (tokenEl) {
      tokenEl.value = option.dataset.token || '';
    }
  };

  private update(): void {
    const boxes = this.getRowBoxes();
    const count = boxes.filter((box) => box.checked).length;

    if (this.selectAllEl) {
      this.selectAllEl.checked = boxes.length > 0 && count === boxes.length;
      this.selectAllEl.indeterminate = count > 0 && count < boxes.length;
    }

    if (!this.formEl) {
      return;
    }

    const countEl = this.formEl.querySelector<HTMLElement>('.table--bulk-count');
    if (countEl?.dataset.label) {
      countEl.textContent = countEl.dataset.label.replace('%count%', String(count));
    }

    const selectEl = this.getSelectEl();
    const option = selectEl?.selectedOptions[0];
    const optionAll = option !== undefined && option.value !== '' && option.dataset.all !== undefined;

    this.formEl.querySelectorAll<HTMLButtonElement>('.table--bulk-action, .table--bulk-apply').forEach((button) => {
      if (button.classList.contains('table--bulk-action--all')) {
        button.disabled = false;
        return;
      }

      if (button.classList.contains('table--bulk-apply')) {
        button.disabled = !selectEl || selectEl.value === '' || (count === 0 && !optionAll);
        return;
      }

      button.disabled = count === 0;
    });
  }

  public getAgentLabel(): string | undefined {
    return this.el.querySelector('caption')?.textContent?.trim() || undefined;
  }

  public getAgentState(): Record<string, any> {
    const sorted = this.headers().find((th) => ['ascending', 'descending'].includes(th.getAttribute('aria-sort') ?? ''));

    return {
      rowsShown: this.rows().length,
      columns: this.headers().map((th) => ({
        key: th.dataset.columnKey,
        label: this.headerLabel(th),
        sortable: Boolean(th.querySelector('.table--sort')),
      })),
      sort: sorted
        ? { column: sorted.dataset.columnKey, direction: sorted.getAttribute('aria-sort') === 'ascending' ? 'asc' : 'desc' }
        : null,
    };
  }

  public getAgentActions(): AgentActionsImplementation {
    return {
      rows: () => this.rows().map((tr) => ({
        row: tr.dataset.rowKey,
        ...Object.fromEntries(Array.from(tr.querySelectorAll<HTMLElement>('td[data-column-key]'))
          .map((td) => [td.dataset.columnKey, td.textContent?.trim() ?? ''])),
      })),
      // The next order is a page: followed as the header's link is, which
      // cycles as a click does. A direction asked for is reached by following
      // it as many times as it takes, each a page — one at a time here.
      sort: async ({ column, direction }) => {
        const th = this.findColumnHeader(column);
        const link = th.querySelector<HTMLAnchorElement>('a.table--sort');

        if (!link) {
          throw new Error(`Column "${column}" cannot be sorted.`);
        }

        const current = th.getAttribute('aria-sort') === 'ascending' ? 'asc' : (th.getAttribute('aria-sort') === 'descending' ? 'desc' : null);

        if (direction && direction === current) {
          return { column: th.dataset.columnKey, direction };
        }

        await rippleAgentPress(this.app, link);

        return new AgentNavigation(link.href, { column: th.dataset.columnKey, direction: direction ?? 'next' });
      },
      reset_sort: () => {
        throw new Error('This table is ordered by its page: sort a column until it is back to its default order.');
      },
      point_row: (args: Record<string, any>, context?: AgentCallContext) => this.pointAt([this.findRow(args.row)], context),
      point_cell: (args: Record<string, any>, context?: AgentCallContext) => this.pointAt(
        [this.findRow(args.row).querySelector<HTMLElement>(`td[data-column-key="${CSS.escape(this.findColumnHeader(args.column).dataset.columnKey)}"]`)],
        context
      ),
      point_column: (args: Record<string, any>, context?: AgentCallContext) => this.pointAt(
        Array.from(this.el.querySelectorAll<HTMLElement>(`[data-column-key="${CSS.escape(this.findColumnHeader(args.column).dataset.columnKey)}"]`)),
        context
      ),
      clear_filters: () => {
        throw new Error('This table has no filters of its own: the page narrows its rows.');
      },
      row_actions: ({ row }: Record<string, any>) => this.readRowActions(this.findRow(row)).map(({ name, kind, el }) => ({
        action: name,
        ...(kind === 'post' ? { posts: true } : {}),
        ...((kind === 'post' ? el : el.closest('form'))?.dataset.confirmMessage || el.dataset.confirmMessage ? { confirms: true } : {}),
        ...(el.getAttribute('data-target') ? { opens: el.getAttribute('data-target') } : {}),
        ...(kind === 'link' && !el.getAttribute('data-target') ? { leaves: true } : {}),
      })),
      run_row_action: async ({ row, action }: Record<string, any>) => this.runRowAction(this.findRow(row), action),
    };
  }

  // What may be done to a row, read off its markup: the controls of its
  // action cells, named as the reader reads them, and the row's own link.
  private readRowActions(tr: HTMLElement): { name: string; kind: 'post' | 'link' | 'handler' | 'none'; el: HTMLElement }[] {
    const controls = Array.from(tr.querySelectorAll<HTMLElement>('.table--actions > *, .table--row-open'));

    return controls.map((el) => {
      const pressed = el.tagName === 'FORM' ? el.querySelector<HTMLElement>('button') : el;
      const name = pressed?.getAttribute('aria-label') || pressed?.getAttribute('title') || pressed?.textContent?.trim() || '';
      const kind = el.tagName === 'FORM' ? 'post' : (el.tagName === 'A' ? 'link' : (el.tagName === 'BUTTON' ? 'handler' : 'none'));

      return { name, kind, el };
    }).filter((entry) => entry.name);
  }

  private async runRowAction(tr: HTMLElement, name: unknown): Promise<any> {
    const wanted = String(name ?? '').toLowerCase();
    const found = this.readRowActions(tr).find((entry) => entry.name.toLowerCase() === wanted);

    if (!found || found.kind === 'none') {
      throw new Error(`No action "${name}" on this row.`);
    }

    const { kind, el } = found;
    const pressed = (kind === 'post' ? el.querySelector<HTMLElement>('button') : el) ?? el;
    const result = { row: tr.dataset.rowKey, action: found.name };

    await rippleAgentPress(this.app, pressed);

    if (kind === 'post') {
      const form = el as HTMLFormElement;

      if (!(await confirmAskFor(this.app, pressed.dataset.confirmMessage ? pressed : form))) {
        return { ...result, refused: true };
      }

      return new AgentNavigation(() => {
        form.dataset.confirmState = 'confirmed';
        form.requestSubmit(pressed instanceof HTMLButtonElement ? pressed : undefined);
        delete form.dataset.confirmState;
      }, result);
    }

    const target = el.getAttribute('data-target');

    if (kind === 'link' && !target) {
      return new AgentNavigation(() => pressed.click(), result);
    }

    pressed.click();

    return { ...result, ...(target ? { opened: target } : {}) };
  }

  private headers(): HTMLElement[] {
    return Array.from(this.el.querySelectorAll<HTMLElement>('thead th[data-column-key]'));
  }

  private headerLabel(th: HTMLElement): string {
    return (th.querySelector('.table--sort > span') ?? th).textContent?.trim() ?? '';
  }

  private rows(): HTMLElement[] {
    return Array.from(this.el.querySelectorAll<HTMLElement>('tbody tr[data-row-key]'));
  }

  private findColumnHeader(name: unknown): HTMLElement {
    const wanted = String(name ?? '').toLowerCase();
    const th = this.headers().find((candidate) => candidate.dataset.columnKey?.toLowerCase() === wanted
      || this.headerLabel(candidate).toLowerCase() === wanted);

    if (!th) {
      throw new Error(`No column "${name}" in the table.`);
    }

    return th;
  }

  private findRow(key: unknown): HTMLElement {
    const tr = this.el.querySelector<HTMLElement>(`tbody tr[data-row-key="${CSS.escape(String(key ?? ''))}"]`);

    if (!tr) {
      throw new Error(`No row "${key}" shown in the table.`);
    }

    return tr;
  }

  private pointAt(elements: (HTMLElement | null)[], context?: AgentCallContext): Record<string, any> {
    this.app.services.agentTools?.pointer.point(elements, context);

    return { pointed: elements.filter(Boolean).length };
  }
}
