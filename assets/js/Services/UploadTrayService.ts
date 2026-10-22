import AppService from '@wexample/symfony-loader/js/Class/AppService';
import EventsService from '@wexample/symfony-loader/js/Services/EventsService';
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';
import UploadService, { UploadJob, UploadServiceEvents } from '@wexample/symfony-loader/js/Services/UploadService';
import { bytesFormatBytes } from '@wexample/js-helpers/Helper/Bytes';
import { showInTarget, TARGET_DOCK } from '../Helper/TargetHelper';

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.upload.';

// The files being sent, in a window docked at the foot of the screen: one row
// each — its name, its size, how far it got, a way to give it up —, kept while
// the page is used. Opened by the first file queued, folded to its title or
// closed at will; closing it does not stop what is still being sent, the next
// file opens it again.
export default class UploadTrayService extends AppService {
  public static serviceName: string = 'uploadTray';
  public static dependencies: typeof AppService[] = [EventsService, LocaleService, UploadService];

  private dock: any = null;
  private opening: Promise<void> | null = null;
  private listEl: HTMLElement | null = null;
  private rows: Map<string, HTMLElement> = new Map();
  private jobs: Map<string, UploadJob> = new Map();

  registerHooks() {
    return {
      app: {
        hookInit: () => {
          const events = this.app.getServiceOrFail(EventsService) as EventsService;

          events.listen(UploadServiceEvents.QUEUED, this.onQueued);
          events.listen(UploadServiceEvents.START, this.onUpdate);
          events.listen(UploadServiceEvents.PROGRESS, this.onUpdate);
          events.listen(UploadServiceEvents.SUCCESS, this.onUpdate);
          events.listen(UploadServiceEvents.ERROR, this.onUpdate);
        },
      },
    };
  }

  private trans(key: string, args: object = {}): string {
    return (this.app.getServiceOrFail(LocaleService) as LocaleService).trans(`${T}${key}`, args);
  }

  private onQueued = async (event: CustomEvent) => {
    const job: UploadJob | undefined = event.detail?.job;

    if (!job) {
      return;
    }

    this.jobs.set(job.id, job);
    await this.ensureDock();
    this.listEl?.appendChild(this.buildRow(job));
    this.update(job);
  };

  private onUpdate = (event: CustomEvent) => {
    const job: UploadJob | undefined = event.detail?.job;

    if (job && this.jobs.has(job.id)) {
      this.update(job);
    }
  };

  // The window, opened once and opened again once closed.
  private ensureDock(): Promise<void> {
    if (this.dock?.el?.isConnected) {
      this.dock.setCollapsed?.(false);

      return Promise.resolve();
    }

    if (!this.opening) {
      this.rows.clear();
      this.listEl = document.createElement('ul');
      this.listEl.className = 'upload-tray';

      this.opening = showInTarget(this.app, TARGET_DOCK, {
        title: this.trans('title'),
        body: this.listEl,
      }).then((dock) => {
        this.dock = dock;
        this.opening = null;
      });
    }

    return this.opening;
  }

  // The design system's progress bar — its label the file's name, its value
  // how far it got, as progress-bar.html.twig draws them: a change to either
  // is a change to both —, the way to give it up beside it, what came of it
  // under it.
  private buildRow(job: UploadJob): HTMLElement {
    const row = document.createElement('li');
    row.className = 'upload-tray--row';
    row.innerHTML = '<div class="upload-tray--head">'
      + '<div class="progress progress--info" role="progressbar" aria-valuemin="0" aria-valuemax="100">'
      + '<div class="progress--head"><span class="progress--label"></span><span class="progress--value"></span></div>'
      + '<div class="progress--track"><div class="progress--bar"></div></div>'
      + '</div>'
      + '<button type="button" class="action-icon upload-tray--cancel"></button>'
      + '</div>'
      + '<span class="upload-tray--status"></span>';

    const label = row.querySelector('.progress--label') as HTMLElement;
    label.textContent = job.file.name;
    label.title = job.file.name;
    (row.querySelector('.progress') as HTMLElement).setAttribute('aria-label', job.file.name);

    const cancel = row.querySelector('.upload-tray--cancel') as HTMLButtonElement;
    cancel.innerHTML = (this.app.getServiceOrFail('icon') as any).icon('ph:bold/x');
    cancel.setAttribute('aria-label', this.trans('cancel'));
    cancel.setAttribute('data-tooltip', this.trans('cancel'));
    cancel.addEventListener('click', () => (this.app.getServiceOrFail(UploadService) as UploadService).cancel(job));

    this.rows.set(job.id, row);

    return row;
  }

  private update(job: UploadJob): void {
    const row = this.rows.get(job.id);

    if (!row) {
      return;
    }

    const cancelled = job.status === 'error' && job.error?.cancelled;
    const progress = row.querySelector('.progress') as HTMLElement;
    const type = job.status === 'success' ? 'success' : (job.status === 'error' ? 'error' : 'info');
    // Done is all of it, whatever the last progress event said.
    const percent = job.status === 'success' ? 100 : job.progress;

    progress.className = `progress progress--${type}`;
    progress.style.setProperty('--progress-value', String(percent / 100));
    progress.setAttribute('aria-valuenow', String(percent));
    (row.querySelector('.progress--value') as HTMLElement).textContent = `${percent}% · ${bytesFormatBytes(job.file.size, 1)}`;

    (row.querySelector('.upload-tray--status') as HTMLElement).textContent = cancelled
      ? this.trans('cancelled')
      : job.status === 'error'
        ? (job.error?.message || this.trans('failed'))
        : this.trans(`status.${job.status}`);

    // Nothing left to give up once it is over: the button goes.
    if (job.status === 'success' || job.status === 'error') {
      row.querySelector('.upload-tray--cancel')?.remove();
    }
    row.dataset.status = cancelled ? 'cancelled' : job.status;

    this.dock?.el?.querySelector('.header-title')?.replaceChildren(this.trans('progress', {
      '%done%': [...this.jobs.values()].filter((each) => each.status === 'success').length,
      '%count%': this.jobs.size,
    }));
  }
}
