import AssetsCollectionInterface from '../interfaces/AssetsCollectionInterface';
import AppService from '../class/AppService';
import AssetInterface from '../interfaces/AssetInterface';
import RenderNode from '../class/RenderNode';
import { Attribute, AttributeValue, TagName } from '../helpers/DomHelper';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';

export class AssetsServiceType {
  public static CSS: string = 'css';

  public static JS: string = 'js';
}

export default class AssetsService extends AppService {
  public assetsRegistry: any = {css: {}, js: {}};

  public jsAssetsPending: { [key: string]: AssetInterface } = {};

  public static serviceName: string = 'assets';

  registerHooks() {
    return {
      app: {
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
          );
        },
      },
    };
  }


  appendAsset(asset: AssetInterface): Promise<AssetInterface> {
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
            asset.el = this.addScript(asset.path);

            // Javascript file will run resolve.
            return;
          }
        } else {
          if (!asset.loaded) {
            asset.el = this.addStyle(asset.path);
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

  async appendAssets(assetsCollection: AssetsCollectionInterface) {
    return new Promise(async (resolveAll) => {
      let assets = this.assetsInCollection(assetsCollection);

      if (!assets.length) {
        resolveAll(assets);
        return;
      }

      let count: number = 0;
      assets.forEach((asset: AssetInterface) => {
        count++;

        this.appendAsset(asset).then(() => {
          count--;

          if (count === 0) {
            resolveAll(assetsCollection);
          }
        });
      });
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


  setAssetLoaded(asset: AssetInterface) {
    asset.loaded = true;
    asset.rendered = true;
  }

  jsPendingLoaded(id) {
    let asset = this.jsAssetsPending[id];
    asset.resolver(asset);

    delete this.jsAssetsPending[id];
  }

  addScript(src: string) {
    let el = document.createElement(TagName.SCRIPT);
    el.setAttribute(Attribute.SRC, src);

    document.head.appendChild(el);
    return el;
  }

  addStyle(href: string) {
    let el = this.createStyleLinkElement();
    el.setAttribute(Attribute.HREF, href);

    document.head.appendChild(el);
    return el;
  }

  createStyleLinkElement() {
    let el = document.createElement(TagName.LINK);
    el.setAttribute(Attribute.REL, AttributeValue.STYLESHEET);
    return el;
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
    let toLoad = AssetsService.createEmptyAssetsCollection();
    let toUnload = AssetsService.createEmptyAssetsCollection();
    let hasChange = false;

    this.assetsInCollection(collection).forEach((asset: AssetInterface) => {
      let type = asset.type;

      if (!asset.active) {
        hasChange = true;
        toLoad[type].push(asset);
      }
    });

    if (hasChange) {
      // Load new assets.
      await this.appendAssets(toLoad);
    }
  }

}
