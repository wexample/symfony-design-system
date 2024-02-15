import AppChild from './AppChild';
import AssetsInterface from '../interfaces/AssetInterface';
import RenderNode from './RenderNode';

export default abstract class AssetUsage extends AppChild {
  public static USAGE_ANIMATION: string = 'animation';

  public static USAGE_COLOR_SCHEME: string = 'color_scheme';

  public static USAGE_DEFAULT: string = 'default';

  public static USAGE_RESPONSIVE: string = 'responsive';

  public static USAGE_SHAPE: string = 'shape';

  public static USAGES: string[] = [
    // The order is the same as backend order.
    // @see AssetsService.php.
    AssetUsage.USAGE_DEFAULT,
    AssetUsage.USAGE_COLOR_SCHEME,
    AssetUsage.USAGE_RESPONSIVE,
    AssetUsage.USAGE_SHAPE,
    AssetUsage.USAGE_ANIMATION,
  ];

  public abstract usageName: string;

  public assetShouldBeLoaded(
    asset: AssetsInterface,
    renderNode: RenderNode
  ): boolean {
    return asset.usage === this.usageName;
  }
}
