import RenderDataInterface from './RenderDataInterface';

export default interface ComponentInterface extends RenderDataInterface {
  cssClassName: string;
  id: string;
  initMode: string;
  options: any;
  renderRequestId: string;
}
