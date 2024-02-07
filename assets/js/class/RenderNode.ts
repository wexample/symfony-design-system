import AppChild from './AppChild';

export default abstract class RenderNode extends AppChild {
  public el: HTMLElement;

  protected async mounted(): Promise<void> {

  }
}
