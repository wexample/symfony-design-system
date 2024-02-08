
import ComponentInterface from './ComponentInterface';
import RequestOptionsInterface from '../RequestOptions/RequestOptionsInterface';

export default interface RenderDataInterface {

  components: ComponentInterface[];
  id: string;
  name: string;
  requestOptions?: RequestOptionsInterface;
  translations: {};
  vars: any;
}
