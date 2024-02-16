import Page from './Page';
import RenderNode from './RenderNode';
import { toKebab } from '../helpers/StringHelper';

export default abstract class extends RenderNode {
  public page: Page;

  async setUsage(
    usageName: string,
    usageValue: string,
    updateAssets: boolean
  ) {
    let classList = document.body.classList;
    let usageKebab = toKebab(usageName)

    classList.forEach((className: string) => {
      if (className.startsWith(`usage-${usageKebab}-${usageValue}`)) {
        classList.remove(className);
      }
    });

    this.app.layout.usages[usageName] = usageValue;

    classList.add(`${usageKebab}-${usageValue}`);

    if (updateAssets) {
      await this.assetsUpdate(
        usageName
      );
    }
  }
}
