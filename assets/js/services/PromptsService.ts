import AppService from '../class/AppService';
import LocaleService from './LocaleService';
import { format as StringFormat } from '../helpers/StringHelper';

export default class PromptService extends AppService {
  public static dependencies: typeof AppService[] = [LocaleService];
  protected service: PromptService;
  public static serviceName: string = 'prompt';

  systemError(
    message,
    args: {} = {},
    debugData: any = null,
    fatal: boolean = false
  ) {
    message = StringFormat(message, args);

    if (fatal) {
      throw new Error(message);
    } else {
      console.error(message);
    }

    if (debugData) {
      console.warn(debugData);
    }
  }
}
