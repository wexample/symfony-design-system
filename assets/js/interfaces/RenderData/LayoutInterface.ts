import RenderDataInterface from './RenderDataInterface';
import PageInterface from './PageInterface';
import ComponentInterface from './ComponentInterface';

export default interface LayoutInterface extends RenderDataInterface {
  components: ComponentInterface[];
  env: string;
  page: PageInterface;
  vueTemplates?: string[];
}
