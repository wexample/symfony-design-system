import AppService from '../class/AppService';
import Routing from 'fos-router';

export default class RoutingService extends AppService {
  public static serviceName: string = 'routing';

  path(route: string, params: any = {}): string {
    // Routes are generated and imported using webpack and runtime.js file.
    return Routing.generate(route, params);
  }
}
