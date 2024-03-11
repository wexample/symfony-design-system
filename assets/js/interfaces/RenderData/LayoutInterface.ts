import RenderDataInterface from './RenderDataInterface';
import PageInterface from './PageInterface';

export default interface LayoutInterface extends RenderDataInterface {
  templates: string;
  page: PageInterface;
}
