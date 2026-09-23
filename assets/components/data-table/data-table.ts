import Component from '@wexample/symfony-loader/js/Class/Component';

// What the server table needs of the browser once its rows can be ticked: the
// box in the header ticks them all, the count says how many, and an action
// cannot be pressed with nothing to act on. A table without the option has
// none of these, and this does nothing.
export default class extends Component {
  private formEl?: HTMLFormElement | null;
  private selectAllEl?: HTMLInputElement | null;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.formEl = this.el.querySelector('form.table--bulk');
    this.selectAllEl = this.el.querySelector('.table--select-all');

    this.el.addEventListener('change', this.onChange);
    this.formEl?.addEventListener('submit', this.onSubmit);

    this.update();
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.removeEventListener('change', this.onChange);
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

    if (!selectEl || !this.formEl) {
      return;
    }

    const option = selectEl.selectedOptions[0];

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
    this.formEl.querySelectorAll<HTMLButtonElement>('.table--bulk-action, .table--bulk-apply').forEach((button) => {
      button.disabled = count === 0
        || (button.classList.contains('table--bulk-apply') && (!selectEl || selectEl.value === ''));
    });
  }
}
