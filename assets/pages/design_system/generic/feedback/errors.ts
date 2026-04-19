import Page from '@wexample/symfony-loader/js/Class/Page';
import ConnectionStatusService from '@wexample/symfony-loader/js/Services/ConnectionStatusService';
import { reconnectBackoffAttempt } from '@wexample/js-helpers/Helper/Reconnect';

export default class extends Page {
  pageReady() {
    const client = (this.app as any).getClient();
    const connectionStatusService = this.app.getService(ConnectionStatusService) as ConnectionStatusService;
    const INTERNET_PROBE_SOURCE = 'internet-probe';
    const INTERNET_PROBE_URL = 'https://jsonplaceholder.typicode.com/todos/1';
    const ERROR_TRIGGER_SELECTOR = '.error-trigger-button';
    const FRONTEND_THROW_SELECTOR = '.frontend-throw-button';
    const INTERNET_PROBE_SELECTOR = '.internet-probe-button';
    let reconnectProbeRunning = false;

    const startReconnectProbe = () => {
      if (reconnectProbeRunning) {
        return;
      }

      reconnectProbeRunning = true;
      void reconnectBackoffAttempt(
        async () => {
          await client.getAbsolute({
            url: INTERNET_PROBE_URL,
            options: {
              retry: 0,
            },
          }).text();
        },
        {
          initialDelayMs: 1000,
          maxDelayMs: 15000,
          factor: 2,
          jitterRatio: 0.25,
          maxAttempts: Number.POSITIVE_INFINITY,
        }
      ).then(() => {
        reconnectProbeRunning = false;
        connectionStatusService.markSourceReconnected(INTERNET_PROBE_SOURCE);
      }).catch(() => {
        reconnectProbeRunning = false;
      });
    };

    const attachClick = (selector: string, onClick: () => void | Promise<void>) => {
      const button = this.el?.querySelector(selector) as HTMLElement | null;
      if (!button) {
        return;
      }

      button.addEventListener('click', () => {
        void Promise.resolve(onClick());
      });
    };

    attachClick(ERROR_TRIGGER_SELECTOR, async () => {
      await client.get({
        path: 'test/fatal-error',
      });
    });

    attachClick(FRONTEND_THROW_SELECTOR, () => {
      throw new Error('Frontend demo exception triggered from errors page.');
    });

    attachClick(INTERNET_PROBE_SELECTOR, async () => {
      try {
        await client.getAbsolute({
          url: INTERNET_PROBE_URL,
          options: {
            retry: {
              limit: 3,
              methods: ['get'],
              statusCodes: [],
            },
          },
        }).text();
        connectionStatusService.markSourceReconnected(INTERNET_PROBE_SOURCE);
      } catch (error) {
        connectionStatusService.markSourceDisconnected(INTERNET_PROBE_SOURCE, {
          reason: 'probe-request-failed',
        });
        startReconnectProbe();
      }
    });
  }
}
