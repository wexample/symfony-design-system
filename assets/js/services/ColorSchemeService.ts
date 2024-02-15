import AppService from '../class/AppService';
import AssetUsage from "../class/AssetUsage";

export default class ColorSchemeService extends AppService {
  public static serviceName: string = 'colorScheme';

  registerMethods(object: any, group: string) {
    return {
      renderNode: {
        async colorSchemeSet(name: string, updateAssets: boolean) {
          let classList = document.body.classList;

          classList.forEach((className) => {
            if (className.startsWith('color-scheme-')) {
              classList.remove(className);
            }
          });

          this.app.layout.activeColorScheme = name;

          classList.add(`color-scheme-${this.app.layout.activeColorScheme}`);

          if (updateAssets) {
            await this.app.layout.assetsUpdate(AssetUsage.USAGE_COLOR_SCHEME);
          }
        }
      }
    }
  }
}
