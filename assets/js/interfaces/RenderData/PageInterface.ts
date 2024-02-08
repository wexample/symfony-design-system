import RenderDataInterface from './RenderDataInterface';

export default interface PageInterface extends RenderDataInterface {
  body: string;
  isInitialPage: boolean;
}
