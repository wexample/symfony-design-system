import Field from '../../../js/Class/Field';
import type { AssistanceWriteOptions } from '@wexample/js-api/Helper/Assistance';

// A file field the file can be dropped on: the zone lights while it is held
// over it, the files taken become the control's, their names are said in the
// zone, the images shown under it.
export default class extends Field {
  private controlEl: HTMLInputElement | null = null;
  private previewUrls: string[] = [];
  private dragDepth = 0;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.controlEl = this.el.querySelector('.file-input--control');
    this.controlEl?.addEventListener('change', this.onChange);
    this.controlEl?.form?.addEventListener('reset', this.onReset);
    this.zone?.addEventListener('click', this.onZoneClick);
    this.zone?.addEventListener('dragenter', this.onDragEnter);
    this.zone?.addEventListener('dragover', this.onDragOver);
    this.zone?.addEventListener('dragleave', this.onDragLeave);
    this.zone?.addEventListener('drop', this.onDrop);
    this.describe();
  }

  protected async deactivateListeners(): Promise<void> {
    this.controlEl?.removeEventListener('change', this.onChange);
    this.controlEl?.form?.removeEventListener('reset', this.onReset);
    this.zone?.removeEventListener('click', this.onZoneClick);
    this.zone?.removeEventListener('dragenter', this.onDragEnter);
    this.zone?.removeEventListener('dragover', this.onDragOver);
    this.zone?.removeEventListener('dragleave', this.onDragLeave);
    this.zone?.removeEventListener('drop', this.onDrop);
    this.previewsClear();

    await super.deactivateListeners();
  }

  private get zone(): HTMLElement | null {
    return this.el.querySelector('.file-input--zone');
  }

  // Anywhere on the zone opens the browser's picker, the button included —
  // but not the control itself, which opens it on its own.
  private onZoneClick = (event: MouseEvent): void => {
    if (event.target !== this.controlEl) {
      this.controlEl?.click();
    }
  };

  private onDragEnter = (event: DragEvent): void => {
    event.preventDefault();
    this.dragDepth++;
    this.zone?.classList.add('is-dragover');
  };

  private onDragOver = (event: DragEvent): void => {
    event.preventDefault();
  };

  private onDragLeave = (): void => {
    this.dragDepth = Math.max(0, this.dragDepth - 1);

    if (!this.dragDepth) {
      this.zone?.classList.remove('is-dragover');
    }
  };

  // What is dropped becomes the control's — one file only, unless the field
  // takes several —, as if it had been picked.
  private onDrop = (event: DragEvent): void => {
    event.preventDefault();
    this.dragDepth = 0;
    this.zone?.classList.remove('is-dragover');

    const dropped = Array.from(event.dataTransfer?.files ?? []);

    if (!dropped.length || !this.controlEl) {
      return;
    }

    const files = this.toFileList(this.controlEl.multiple ? dropped : dropped.slice(0, 1));

    if (files) {
      this.controlEl.files = files;
      this.notifyChanged(this.controlEl);
    }
  };

  private onChange = (): void => {
    this.describe();
  };

  // Emptied with its form — sent, then reset —, after the browser did it.
  private onReset = (): void => {
    window.setTimeout(() => this.describe());
  };

  // The names of the files taken in the zone — or the hint while there are
  // none —, and the images among them under it.
  private describe(): void {
    const files = Array.from(this.controlEl?.files ?? []);
    const names = this.el.querySelector('.file-input--names');

    if (names) {
      names.textContent = files.length > 1
        ? (this.el.dataset.count ?? '').replace('%count%', String(files.length))
        : (files[0]?.name ?? this.el.dataset.hint ?? '');
      names.setAttribute('title', files.map((file) => file.name).join(', '));
    }

    this.el.classList.toggle('is-filled', files.length > 0);
    this.previewsClear();

    const previews = this.el.querySelector<HTMLElement>('.file-input--previews');
    const images = files.filter((file) => file.type.startsWith('image/'));

    if (!previews) {
      return;
    }

    images.forEach((file) => {
      const url = URL.createObjectURL(file);
      const image = document.createElement('img');

      this.previewUrls.push(url);
      image.src = url;
      image.alt = file.name;
      previews.appendChild(image);
    });
    previews.hidden = images.length === 0;
  }

  private previewsClear(): void {
    this.previewUrls.forEach((url) => URL.revokeObjectURL(url));
    this.previewUrls = [];
    this.el.querySelector('.file-input--previews')?.replaceChildren();
  }

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
