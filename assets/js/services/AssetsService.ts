import AssetsCollectionInterface from '../interfaces/AssetsCollectionInterface';
import AppService from '../class/AppService';
import AssetInterface from '../interfaces/AssetInterface';
import RenderNode from '../class/RenderNode';
import { Attribute, AttributeValue, TagName } from '../helpers/DomHelper';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import MixinsAppService from '../class/MixinsAppService';
import AssetUsage from '../class/AssetUsage';
import ColorScheme from '../class/AssetUsage/ColorScheme';
import DefaultAssetUsage from '../class/AssetUsage/Default';
import Margins from '../class/AssetUsage/Margins';
import Fonts from '../class/AssetUsage/Fonts';
import ResponsiveAssetUsage from '../class/AssetUsage/Responsive';
import Animations from "../class/AssetUsage/Animations";

type RenderNodeAssetsType = {
  assetsUpdate?: Function;
};

export class AssetsServiceType {
  public static CSS: string = 'css';

  public static JS: string = 'js';
}

export default class AssetsService extends AppService {
  public usages: { [key: string]: AssetUsage } = {};

  public assetsRegistry: any = {css: {}, js: {}};

  public jsAssetsPending: { [key: string]: AssetInterface } = {};

  public static serviceName: string = 'assets';

  constructor(props) {
    super(props);

    [
      Animations,
      ColorScheme,
      DefaultAssetUsage,
      Margins,
      ResponsiveAssetUsage,
      Fonts
    ].forEach(
      (definition: any) => {
        let usage = new definition(this.app);

        this.usages[usage.usageName] = usage;
      }
    );
  }

  registerMethods(object: any) {
    return {
      renderNode: {
        async assetsUpdate(usage: string) {
          await this.app.services.assets.loadValidAssetsForRenderNode(
            this,
            usage
          );
        },

        async setUsage(
          usageName: string,
          usageValue: string,
          updateAssets: boolean
        ) {
          RenderNode.prototype.setUsage.apply(
            this,
            [
              usageName,
              usageValue,
              updateAssets,
            ]);

          this.assetsUpdate(usageName);
        },
      } as RenderNodeAssetsType,
    };
  }

  registerHooks() {
    return {
      app: {
        hookInit() {
          // Wait for all render node tree to be properly set.
          this.app.ready(async () => {
            // Mark all initially rendered assets in layout as loaded.
            await this.app.layout.forEachTreeRenderNode(
              async (renderNode: RenderNode) =>
                this.assetsInCollection(renderNode.renderData.assets).forEach(
                  (asset: AssetInterface) => {
                    if (asset.initialLayout) {
                      // Fetch the server-side rendered tag.
                      asset.el = document.getElementById(asset.id);
                      this.setAssetLoaded(asset);
                    }
                  }
                )
            );
          });
        },

        async hookPrepareRenderData(renderData: RenderDataInterface) {
          // Replace assets list by reference objects if exists.
          renderData.assets = this.registerAssetsInCollection(
            renderData.assets
          );
        },
      },

      renderNode: {
        async hookBeforeCreate(
          definitionName: string,
          renderData: RenderDataInterface
        ) {
          await this.loadValidAssetsInCollection(
            renderData.assets,
            AssetUsage.USAGE_DEFAULT
          );
        },

        async hookMounted(renderNode: RenderNode, registry: any) {
          // Wait for responsive to be loaded before assets.
          // The current responsive should be detected to allow
          // selecting proper responsive assets.
          if (registry.responsive !== MixinsAppService.LOAD_STATUS_COMPLETE) {
            return MixinsAppService.LOAD_STATUS_WAIT;
          }

          for (let usage in this.usages) {
            await renderNode.setUsage(
              usage,
              renderNode.usages[usage],
              true
            );
          }
        },
      },
    };
  }

  appendAsset(asset: AssetInterface, assetReplaced?: AssetInterface): Promise<AssetInterface> {
    return new Promise(async (resolve) => {
      // Avoid currently and already loaded.
      if (!asset.active) {
        // Active said that asset should be loaded,
        // even loading is not complete or queue is terminated.
        asset.active = true;

        // Storing resolver allow javascript to be,
        // marked as loaded asynchronously.
        asset.resolver = resolve;

        if (asset.type === 'js') {
          // Browsers does not load twice the JS file content.
          if (!asset.rendered) {
            this.jsAssetsPending[asset.id] = asset;
            asset.el = this.addScript(
              asset.path,
              assetReplaced && assetReplaced.el);

            // Javascript file will run resolve.
            return;
          }
        } else {
          if (!asset.loaded) {
            asset.el = this.addStyle(
              asset.path,
              assetReplaced && assetReplaced.el);
          }
        }
      }

      resolve(asset);
    }).then((asset: AssetInterface) => {
      this.setAssetLoaded(asset);

      return asset;
    });
  }

  assetsInCollection(
    assetsCollection: AssetsCollectionInterface
  ): AssetInterface[] {
    let asset: AssetInterface;
    let data;
    let entries = Object.entries(assetsCollection);
    let output = [];

    for (data of entries) {
      for (asset of data[1]) {
        output.push(asset);
      }
    }

    return output;
  }

  async appendAssets(
    assetsCollection: AssetsCollectionInterface,
    replacedCollection: AssetsCollectionInterface
  ) {
    return new Promise(async (resolveAll) => {
      // Is empty.
      if (!this.assetsInCollection(assetsCollection).length) {
        resolveAll(assetsCollection);
        return;
      }

      let count: number = 0;
      Object.keys(assetsCollection).forEach((type) => {
        assetsCollection[type].forEach((asset: AssetInterface, index: number) => {
          count++;

          this.appendAsset(asset, replacedCollection[type][index]).then(() => {
            count--;

            if (count === 0) {
              resolveAll(assetsCollection);
            }
          });
        });
      });

      // Remove replaced and non replaced assets.
      this.removeAssets(replacedCollection);
    });
  }

  registerAssetsInCollection(
    assetsCollection: AssetsCollectionInterface
  ): AssetsCollectionInterface {
    let outputCollection = AssetsService.createEmptyAssetsCollection();

    this.assetsInCollection(assetsCollection).forEach((asset) =>
      outputCollection[asset.type].push(this.registerAsset(asset))
    );

    return outputCollection;
  }

  registerAsset(asset: AssetInterface): AssetInterface {
    // Each asset has a unique reference object shared between all render node.
    if (!this.assetsRegistry[asset.type][asset.id]) {
      this.assetsRegistry[asset.type][asset.id] = asset;
    }

    return this.assetsRegistry[asset.type][asset.id];
  }

  removeAssets(assetsCollection: AssetsCollectionInterface) {
    this.assetsInCollection(assetsCollection).forEach((asset) =>
      this.removeAsset(asset)
    );
  }

  removeAsset(asset: AssetInterface) {
    asset.active = false;
    asset.loaded = false;

    if (asset.el) {
      // Do some cleanup, only useful for source readability.
      if (asset.initialLayout) {
        const elPreload = document.getElementById(`${asset.id}-preload`);
        if (elPreload) {
          elPreload.remove();
        }
      }

      // Remove from document.
      asset.el.remove();
      asset.el = null;
    }
  }

  setAssetLoaded(asset: AssetInterface) {
    asset.loaded = true;
    asset.rendered = true;
  }

  jsPendingLoaded(id) {
    let asset = this.jsAssetsPending[id];
    asset.resolver(asset);

    delete this.jsAssetsPending[id];
  }

  addScript(src: string, elReplacement?: HTMLElement) {
    let el = document.createElement(TagName.SCRIPT);
    el.setAttribute(Attribute.SRC, src);

    this.addAssetEl(el, elReplacement);

    return el;
  }

  addStyle(href: string, elReplacement?: HTMLElement) {
    let el = this.createStyleLinkElement();
    el.setAttribute(Attribute.HREF, href);

    this.addAssetEl(el, elReplacement);

    return el;
  }

  addAssetEl(el: HTMLElement, elReplacement?: HTMLElement) {
    if (elReplacement) {
      elReplacement.parentNode.replaceChild(el, elReplacement);
    } else {
      this.app.layout.el.ownerDocument.head.appendChild(el);
    }
  }

  createStyleLinkElement() {
    let el = document.createElement(TagName.LINK);
    el.setAttribute(Attribute.REL, AttributeValue.STYLESHEET);
    return el;
  }

  getAssetUsage(usage: string): AssetUsage | undefined {
    return this.usages[usage]
  }

  public static createEmptyAssetsCollection(): AssetsCollectionInterface {
    return {
      css: [],
      js: [],
    };
  }

  public async loadValidAssetsInCollection(
    collection: AssetsCollectionInterface,
    usage: string,
    renderNode?: RenderNode
  ) {
    const toLoad = AssetsService.createEmptyAssetsCollection();
    const toUnload = AssetsService.createEmptyAssetsCollection();
    const usageManager = this.getAssetUsage(usage);
    let hasChange = false;

    this.assetsInCollection(collection).forEach((asset: AssetInterface) => {
      if (asset.usage !== usage) {
        return;
      }

      let type = asset.type;
      if (usageManager.assetShouldBeLoaded(asset, renderNode)) {
        if (!asset.active) {
          hasChange = true;
          toLoad[type].push(asset);
        }
      } else {
        if (asset.active) {
          hasChange = true;
          toUnload[type].push(asset);
        }
      }
    });

    if (hasChange) {
      // Load new assets.
      await this.appendAssets(toLoad, toUnload);
    }
  }

  public async loadValidAssetsForRenderNode(
    renderNode: RenderNode,
    usage: string
  ) {
    await this.loadValidAssetsInCollection(
      renderNode.renderData.assets,
      usage,
      renderNode
    );
  }
}
