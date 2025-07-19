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

export type RenderNodeAssetsType = {
  assetsUpdate?: Function;
};

export class AssetsServiceType {
  public static CSS: string = 'css';

  public static JS: string = 'js';

  public static ALL: [string, string] = [
    AssetsServiceType.CSS,
    AssetsServiceType.JS,
  ];
}

export default class AssetsService extends AppService {
  public usages: { [key: string]: AssetUsage } = {};

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
        const usage = new definition(this.app);

        this.usages[usage.usageName] = usage;
      }
    );
  }

  registerMethods(object: any) {
    return {
      renderNode: {
        async assetsUpdate(usage: string) {
          try {
            await this.app.services.assets.loadValidAssetsForRenderNode(
              this,
              usage
            );
          } catch (error) {
            throw error;
          }
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
            try {
              // Mark all initially rendered assets in layout as loaded.
              await this.app.layout.forEachTreeRenderNode(
                async (renderNode: RenderNode) => {
                  if (!renderNode.renderData || !renderNode.renderData.assets) {
                    return;
                  }
                  
                  this.assetsInCollection(renderNode.renderData.assets).forEach(
                    (asset: AssetInterface) => {
                      if (asset.initialLayout) {
                        // Fetch the server-side rendered tag.
                        const el = document.getElementById(asset.domId);
                        if (el) {
                          asset.el = el;
                          this.setAssetLoaded(asset);
                        } else {
                        }
                      }
                    }
                  );
                }
              );
            } catch (error) {
            }
          });
        },

        async hookPrepareRenderData(renderData: RenderDataInterface) {
          // Ajax layouts does not have assets.
          if (renderData.assets) {
            // Replace assets list by reference objects if exists.
            renderData.assets = this.registerAssetsInCollection(
              renderData.assets
            );
          }
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

          for (const usage in this.usages) {
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

  appendAsset(asset: AssetInterface, assetReplaced?: AssetInterface | null): Promise<AssetInterface> {
    return new Promise(async (resolve, reject) => {
      // Avoid currently and already loaded.
      if (!asset.active) {
        // Active said that asset should be loaded,
        // even loading is not complete or queue is terminated.
        asset.active = true;

        // Storing resolver allow javascript to be,
        // marked as loaded asynchronously.
        asset.resolver = resolve as (value: AssetInterface) => void;

        if (asset.type === 'js') {
          // Browsers does not load twice the JS file content.
          // We need to check if it's already rendered.
          if (!asset.rendered) {
            this.jsAssetsPending[asset.view] = asset;
            this.addScript(asset, assetReplaced);
            return;
          }
        } else {
          if (!asset.loaded) {
            this.addStyle(asset, assetReplaced);
          }
        }
      }

      // Resolve immediately if already loaded or active
      resolve(asset);
    }).then((asset: unknown) => {
      const typedAsset = asset as AssetInterface;
      this.setAssetLoaded(typedAsset);
      return typedAsset;
    });
  }

  assetsInCollection(
    assetsCollection: AssetsCollectionInterface | null
  ): AssetInterface[] {
    if (!assetsCollection) {
      return [];
    }
    
    const entries = Object.entries(assetsCollection);
    const output: AssetInterface[] = [];

    for (const data of entries) {
      for (const asset of data[1]) {
        output.push(asset);
      }
    }

    return output;
  }

  async appendAssets(
    assetsCollection: AssetsCollectionInterface,
    replacedCollection: AssetsCollectionInterface | null = null
  ) {
    return new Promise(async (resolveAll, rejectAll) => {
      // Is empty.
      if (!this.assetsInCollection(assetsCollection).length) {
        this.removeAssets(replacedCollection);
        resolveAll(assetsCollection);
        return;
      }

      let count = 0;
      let hasError = false;
      
      // Set a timeout to prevent hanging promises
      const timeoutId = setTimeout(() => {
        if (count > 0) {
          rejectAll(new Error(`Timeout loading assets after 10 seconds with ${count} assets still loading`));
        }
      }, 10000); // 10 second timeout

      // Load all assets.
      Object.entries(assetsCollection).forEach(([type, assets]) => {
        assets.forEach((asset, index) => {
          count++;
          
          // Use type assertion to handle null/undefined cases
          const replacementAsset = replacedCollection && replacedCollection[type] ? 
            replacedCollection[type][index] as AssetInterface : undefined;
          this.appendAsset(asset, replacementAsset)
            .then(() => {
              count--;
              
              if (count === 0 && !hasError) {
                clearTimeout(timeoutId);
                resolveAll(assetsCollection);
              }
            })
            .catch((error: Error) => {
              count--;
              hasError = true;
              
              if (count === 0) {
                clearTimeout(timeoutId);
                rejectAll(error);
              }
            });
        });
      });
    });
  }

  registerAssetsInCollection(
    assetsCollection: AssetsCollectionInterface
  ): AssetsCollectionInterface {
    const outputCollection = AssetsService.createEmptyAssetsCollection();

    this.assetsInCollection(assetsCollection).forEach((asset) =>
      outputCollection[asset.type].push(this.registerAsset(asset))
    );

    return outputCollection;
  }

  registerAsset(asset: AssetInterface): AssetInterface {
    const registry = this.app.registry.assetsRegistry;

    // Each asset has a unique reference object shared between all render node.
    if (registry && registry[asset.type] && !registry[asset.type][asset.view]) {
      registry[asset.type][asset.view] = asset;
    }
    return registry[asset.type][asset.view];
  }

  removeAssets(assetsCollection: AssetsCollectionInterface | null) {
    if (!assetsCollection) {
      return;
    }
    
    this.assetsInCollection(assetsCollection).forEach((asset) => {
      this.removeAsset(asset);
    });
  }

  removeAsset(asset: AssetInterface) {
    asset.active = false;
    asset.loaded = false;

    if (asset.el) {
      // Do some cleanup, only useful for source readability.
      if (asset.initialLayout) {
        const elPreload = document.getElementById(`${asset.view}-preload`);
        if (elPreload) {
          elPreload.remove();
        }
      }

      // Remove from document.
      asset.el.remove();
      // Use undefined instead of null to avoid TypeScript errors
      asset.el = undefined as unknown as HTMLElement;
    }
  }

  setAssetLoaded(asset: AssetInterface) {
    asset.loaded = true;
    asset.rendered = true;
  }

  jsPendingLoaded(view: string) {
    const asset = this.jsAssetsPending[view];
    if (!asset) {
      return;
    }
    
    // Resolve the asset if it has a resolver
    if (asset.resolver && typeof asset.resolver === 'function') {
      asset.resolver(asset);
    }

    delete this.jsAssetsPending[view];
  }

  addScript(asset: AssetInterface, assetReplacement?: AssetInterface | null): Promise<HTMLElement> {
    return new Promise((resolve, reject) => {
      const el = document.createElement(TagName.SCRIPT);
      
      // Add event listeners to track script loading
      const self = this; // Store reference to the service instance
      el.onload = function() {
        // If the script doesn't call jsPendingLoaded itself, we'll resolve it here
        setTimeout(function() {
          if (self.jsAssetsPending[asset.view]) {
            self.jsPendingLoaded(asset.view);
          }
        }, 1000); // Increased timeout to give more time for script execution
        resolve(el);
      };
      
      el.onerror = function() {
        reject(new Error(`Failed to load script: ${asset.path}`));
      };
      
      el.setAttribute(Attribute.SRC, `/${asset.path}`);
      asset.el = el;
      
      this.addAssetEl(asset, assetReplacement);

      return el;
    });
  }

  addStyle(asset: AssetInterface, assetReplacement?: AssetInterface | null): Promise<HTMLElement> {
    return new Promise((resolve, reject) => {
      const el = this.createStyleLinkElement();
      
      // Add event listeners to track stylesheet loading
      el.onload = function() {
        resolve(el);
      };
      
      el.onerror = function() {
        reject(new Error(`Failed to load stylesheet: ${asset.path}`));
      };
      
      el.setAttribute(Attribute.HREF, `/${asset.path}`);
      asset.el = el;
      
      this.addAssetEl(asset, assetReplacement);

      return el;
    });
  }

  addAssetEl(asset: AssetInterface, assetReplacement?: AssetInterface | null) {
    // Make sure asset.el is defined
    if (!asset.el) {
      throw new Error(`Asset element is not defined for ${asset.view}`);
    }

    const usageMarkerKey = `ASSET_${asset.usage}`;
    const elUsageMarker = Array.from(document.head.childNodes)
      .find(node => node.nodeType === 8 && node.nodeValue === `END_${usageMarkerKey}`);

    // Ensure we have a valid parent element
    const elParent = elUsageMarker?.parentNode || (this.app.layout.el?.ownerDocument?.head || document.head);
    
    if (!elParent) {
      throw new Error(`Could not find parent element for asset ${asset.view}`);
    }

    // If we have a replacement, handle it
    const elReplacement = assetReplacement?.el;

    if (elReplacement) {
      if (elParent && elReplacement && !elParent.contains(elReplacement)) {
        this.app.services.prompt.systemError(
          'The replacement node is not in the expected location in head marker :marker, ignoring',
          {
            ':marker': usageMarkerKey
          }, undefined, true);
      }

      if (elReplacement && elReplacement.parentNode) {
        elReplacement.parentNode.replaceChild(asset.el, elReplacement);
      } else if (elParent && asset.el) {
        elParent.appendChild(asset.el);
      }
      return;
    }
    
    // If no replacement, just append to parent
    if (elParent && asset.el) {
      elParent.appendChild(asset.el);
    }
  }

  createStyleLinkElement() {
    const el = document.createElement(TagName.LINK);
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
    if (!collection) {
      return;
    }
    
    const usageManager = this.getAssetUsage(usage);
    
    if (!usageManager) {
      return;
    }
      
    let hasChange = false;

    this.assetsInCollection(collection).forEach((asset: AssetInterface) => {
      if (asset.usage !== usage) {
        return;
      }

      let type = asset.type;
      if (usageManager.assetShouldBeLoaded(asset, renderNode)) {
        if (!asset.active) {
          hasChange = true;
          type = asset.type;
          this.appendAsset(asset);
        }
      } else {
        if (asset.active) {
          hasChange = true;
          this.removeAsset(asset);
        }
      }
    });

    if (hasChange) {
      await this.appendAssets(collection);
    }
  }

  public async loadValidAssetsForRenderNode(
    renderNode: RenderNode,
    usage: string
  ) {
    if (!renderNode || !renderNode.renderData || !renderNode.renderData.assets) {
      return;
    }
    
    return await this.loadValidAssetsInCollection(
      renderNode.renderData.assets,
      usage,
      renderNode
    );
  }
}
