import Field from '../../js/Class/Field';
import type { AssistanceWriteOptions } from '@wexample/js-api/Helper/Assistance';

export default class extends Field {
  /**
   * A file field is the one that cannot be written into: a browser refuses a
   * value it was not handed a real file for, and rightly so. What an agent
   * gives is therefore a File — fetched, generated, read from somewhere — and
   * never a name, because a name with nothing behind it would submit nothing.
   */
  protected async writeValueAssisted(
    value: unknown,
    options: AssistanceWriteOptions
  ): Promise<void> {
    const control = this.assistedControl;

    if (!(control instanceof HTMLInputElement) || control.type !== 'file') {
      return;
    }

    const files = this.toFileList(value);

    if (!files) {
      return;
    }

    control.files = files;
    this.notifyChanged(control);
  }

  private toFileList(value: unknown): FileList | null {
    if (value instanceof FileList) {
      return value;
    }

    const files = value instanceof File
      ? [value]
      : (Array.isArray(value) && value.every((entry) => entry instanceof File) ? value : null);

    if (!files) {
      return null;
    }

    const transfer = new DataTransfer();
    files.forEach((file) => transfer.items.add(file));

    return transfer.files;
  }
}
