import AssetsCollectionInterface from '../AssetsCollectionInterface';
import ComponentInterface from './ComponentInterface';
import RequestOptionsInterface from '../RequestOptions/RequestOptionsInterface';

export default interface RenderDataInterface {
  assets: AssetsCollectionInterface;
  components: ComponentInterface[];
  id: string;
  name: string;
  requestOptions?: RequestOptionsInterface;
  translations: {};
  vars: any;
}
