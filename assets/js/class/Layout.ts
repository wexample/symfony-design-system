import Page from './Page';
import RenderNode from './RenderNode';

export default abstract class extends RenderNode {
  public page: Page;
  public pageFocused?: Page;
}
