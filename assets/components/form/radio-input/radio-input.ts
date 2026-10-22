import Field from '../../../js/Class/Field';
import type { AssistanceWriteOptions } from '@wexample/js-api/Helper/Assistance';

export default class extends Field {
  /**
   * A set of radios has no single control standing for the field: the value
   * names which one of them is taken, so the group is searched rather than the
   * first one written into.
   */
  protected async writeValueAssisted(
    value: unknown,
    options: AssistanceWriteOptions
  ): Promise<void> {
    const target = String(value ?? '');
    const option = this.el.querySelector<HTMLInputElement>(
      `input[type="radio"][value="${CSS.escape(target)}"]`
    );

    if (!option) {
      return;
    }

    option.checked = true;
    this.notifyChanged(option);
  }
}
