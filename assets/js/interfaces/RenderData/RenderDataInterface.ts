import AssetsCollectionInterface from '../AssetsCollectionInterface';
import ComponentInterface from './ComponentInterface';
import RequestOptionsInterface from '../RequestOptions/RequestOptionsInterface';

export default interface RenderDataInterface {
  assets: AssetsCollectionInterface;
  components: ComponentInterface[];
  cssClassName: string;
  id: string;
  name: string;
  options: any;
  requestOptions?: RequestOptionsInterface;
  translations: {};
  vars: {};
  usages: {};
}
