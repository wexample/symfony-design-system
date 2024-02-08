import AppService from '../class/AppService';
import ComponentsService from './ComponentsService';

export default class AdaptiveService extends AppService {
  public static dependencies: typeof AppService[] = [ComponentsService];
  public static serviceName: string = 'adaptive';
}
