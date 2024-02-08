import AssetsService from '../services/AssetsService';
import AdaptiveService from '../services/AdaptiveService';
import LayoutsService from '../services/LayoutsService';
import MixinsService from '../services/MixinsService';
import PagesService from '../services/PagesService';
import PromptService from '../services/PromptsService';
import ComponentsService from '../services/ComponentsService';
import DebugService from '../services/DebugService';
import LocaleService from '../services/LocaleService';

export default interface ServicesRegistryInterface {
  adaptive?: AdaptiveService;
  assets?: AssetsService;
  components?: ComponentsService;
  debug?: DebugService;
  layouts?: LayoutsService;
  locale?: LocaleService;
  mixins?: MixinsService;
  pages?: PagesService;
  prompt?: PromptService;
}
