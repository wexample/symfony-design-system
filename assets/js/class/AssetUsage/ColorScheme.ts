import AssetUsage from '../AssetUsage';
import AssetsInterface from '../../interfaces/AssetInterface';
import RenderNode from '../RenderNode';

export default class extends AssetUsage {
  public usageName: string = AssetUsage.USAGE_COLOR_SCHEME;

  assetShouldBeLoaded(
    asset: AssetsInterface,
    renderNode: RenderNode
  ): boolean {
    return !(
      asset.colorScheme !== null
      && asset.colorScheme !== renderNode.activeColorScheme
    );
  }
}
