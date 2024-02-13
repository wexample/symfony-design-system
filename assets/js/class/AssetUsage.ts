import AppChild from './AppChild';
import AssetsInterface from '../interfaces/AssetInterface';
import RenderNode from './RenderNode';

export default abstract class AssetUsage extends AppChild {
  public static USAGE_ANIMATION: string = 'animation';

  public static USAGE_COLOR_SCHEME: string = 'color-scheme';

  public static USAGE_DEFAULT: string = 'default';

  public static USAGE_RESPONSIVE: string = 'responsive';

  public static USAGE_SHAPE: string = 'shape';

  public static USAGES: string[] = [
    AssetUsage.USAGE_ANIMATION,
    AssetUsage.USAGE_COLOR_SCHEME,
    AssetUsage.USAGE_DEFAULT,
    AssetUsage.USAGE_RESPONSIVE,
    AssetUsage.USAGE_SHAPE,
  ];

  public abstract usageName: string;

  public assetShouldBeLoaded(
    asset: AssetsInterface,
    renderNode: RenderNode
  ): boolean {
    return asset.usage === this.usageName;
  }
}
