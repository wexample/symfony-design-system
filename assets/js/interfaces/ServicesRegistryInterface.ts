import LayoutsService from '../services/LayoutsService';
import MixinsService from '../services/MixinsService';

export default interface ServicesRegistryInterface {
  layouts?: LayoutsService;
  mixins?: MixinsService;
}
