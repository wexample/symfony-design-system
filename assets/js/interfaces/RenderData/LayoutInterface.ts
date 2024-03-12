import RenderDataInterface from './RenderDataInterface';
import PageInterface from './PageInterface';

export default interface LayoutInterface extends RenderDataInterface {
  body?: null | string;
  templates: string;
  page: PageInterface;
}
