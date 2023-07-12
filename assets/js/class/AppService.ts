import App from './App';
import AppChild from './AppChild';

export default abstract class AppService extends AppChild {
  public app: App;
  public services: any = {};
  public serviceName: string;
  public static dependencies: typeof AppService[] = [];

  constructor(app: App) {
    super(app);

    this.services = this.app.services;
  }

  registerHooks(): { app?: {}; page?: {} } {
    return {};
  }

  registerMethods(object: any, group: string) {
    return {};
  }
}
