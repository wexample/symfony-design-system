import Component from '@wexample/symfony-loader/js/Class/Component';
import UploadService, { UploadJob, UploadServiceEvents } from '@wexample/symfony-loader/js/Services/UploadService';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';

// Files dropped on the zone or picked with its button, handed to the upload
// service with the address the server signed and the size of its pieces.
// Each file sent says so on the zone — `upload:done`, bubbling, with the
// file's name as stored — so the page around it can read its list again.
export default class extends Component {
  private inputEl?: HTMLInputElement | null;
  private dragDepth = 0;

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.inputEl = this.el.querySelector('.dropzone--input');
    this.el.querySelector('.dropzone--pick')?.addEventListener('click', this.onPick);
    this.inputEl?.addEventListener('change', this.onChange);
    this.el.addEventListener('dragenter', this.onDragEnter);
    this.el.addEventListener('dragover', this.onDragOver);
    this.el.addEventListener('dragleave', this.onDragLeave);
    this.el.addEventListener('drop', this.onDrop);
    this.events().listen(UploadServiceEvents.SUCCESS, this.onSuccess);
  }

  protected async deactivateListeners(): Promise<void> {
    this.el.querySelector('.dropzone--pick')?.removeEventListener('click', this.onPick);
    this.inputEl?.removeEventListener('change', this.onChange);
    this.el.removeEventListener('dragenter', this.onDragEnter);
    this.el.removeEventListener('dragover', this.onDragOver);
    this.el.removeEventListener('dragleave', this.onDragLeave);
    this.el.removeEventListener('drop', this.onDrop);
    this.events().forget(UploadServiceEvents.SUCCESS, this.onSuccess);

    await super.deactivateListeners();
  }

  private events(): EventsService {
    return this.app.getServiceOrFail(EventsService) as EventsService;
  }

  private send(files: FileList | null | undefined): void {
    if (!files || !files.length) {
      return;
    }

    (this.app.getServiceOrFail(UploadService) as UploadService).enqueueFiles(
      Array.from(files),
      { path: this.options.url, chunkSize: this.options.chunkSize },
      { source: this.el, name: this.options.name ?? null }
    );
  }

  private onPick = () => {
    this.inputEl?.click();
  };

  private onChange = () => {
    this.send(this.inputEl?.files);

    // Picking the same file again is a new upload, not nothing.
    if (this.inputEl) {
      this.inputEl.value = '';
    }
  };

  private onDragEnter = (event: DragEvent) => {
    if (!event.dataTransfer?.types.includes('Files')) {
      return;
    }

    event.preventDefault();
    this.dragDepth++;
    this.el.classList.add('is-dragover');
  };

  private onDragOver = (event: DragEvent) => {
    if (event.dataTransfer?.types.includes('Files')) {
      event.preventDefault();
      event.dataTransfer.dropEffect = 'copy';
    }
  };

  // Entering a child of the zone leaves the zone itself: counted, so the zone
  // stays lit until the pointer is really out.
  private onDragLeave = () => {
    this.dragDepth = Math.max(0, this.dragDepth - 1);

    if (!this.dragDepth) {
      this.el.classList.remove('is-dragover');
    }
  };

  private onDrop = (event: DragEvent) => {
    event.preventDefault();
    this.dragDepth = 0;
    this.el.classList.remove('is-dragover');
    this.send(event.dataTransfer?.files);
  };

  private onSuccess = (event: CustomEvent) => {
    const job: UploadJob | undefined = event.detail?.job;

    if (job?.context?.source !== this.el) {
      return;
    }

    this.el.dispatchEvent(new CustomEvent('upload:done', {
      bubbles: true,
      detail: { name: job.response?.name ?? job.file.name, source: this.options.name ?? null },
    }));
  };
}
