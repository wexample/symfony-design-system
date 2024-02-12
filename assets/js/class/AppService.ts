import App from './App';
import AppChild from './AppChild';

export default abstract class AppService extends AppChild {
  public app: App;
  public serviceName: string;
  public static dependencies: typeof AppService[] = [];

  registerHooks(): { app?: {}; page?: {} } {
    return {};
  }

  registerMethods(object: any, group: string) {
    return {};
  }
}
