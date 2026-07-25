import Page from '@wexample/symfony-loader/js/Class/Page';

export default class extends Page {
  pageReady() {
    const dispatchLoading = (eventName: string, sourceEl: HTMLElement) => {
      sourceEl.dispatchEvent(
        new CustomEvent(eventName, {
          bubbles: true,
          detail: { source: { el: sourceEl } },
        })
      );
    };

    this.el?.querySelector('.loading-demo-start')?.addEventListener('click', (e) => {
      e.preventDefault();
      dispatchLoading('loading:start', e.currentTarget as HTMLElement);
    });

    this.el?.querySelector('.loading-demo-end')?.addEventListener('click', (e) => {
      e.preventDefault();
      dispatchLoading('loading:end', e.currentTarget as HTMLElement);
    });

    this.el?.querySelector('.loading-demo-simulate')?.addEventListener('click', (e) => {
      e.preventDefault();
      const el = e.currentTarget as HTMLElement;
      dispatchLoading('loading:start', el);
      setTimeout(() => dispatchLoading('loading:end', el), 1500);
    });
  }
}
