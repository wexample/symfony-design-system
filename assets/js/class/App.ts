import Page from './Page';

import AppService from './AppService';
import AssetsService from '../services/AssetsService';
import LayoutsService from '../services/LayoutsService';
import MixinsService from '../services/MixinsService';
import PagesService from '../services/PagesService';

import { unique as arrayUnique } from '../helpers/ArrayHelper';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import LayoutInitial from './LayoutInitial';
import LayoutInterface from '../interfaces/RenderData/LayoutInterface';
import AsyncConstructor from './AsyncConstructor';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default class extends AsyncConstructor {
  public bundles: any;
  public hasCoreLoaded: boolean = false;
  public layout: LayoutInitial = null;
  public services: ServicesRegistryInterface = {};

  constructor(
    readyCallback?: any | Function,
    globalName: string = 'app'
  ) {
    super();

    window[globalName] = this;

    let doc = window.document;

    let run = async () => {
      await this.loadAndInitServices(this.getServices());

      let registry: {
        bundles: any;
        layoutRenderData: LayoutInterface;
      } = window['appRegistry'];

      this.bundles = registry.bundles;
      // Save layout class definition to allow loading it as a normal render node definition.
      this.bundles.classes[registry.layoutRenderData.name] = LayoutInitial;

      this.layout = (await this.services.layouts.createRenderNode(
        registry.layoutRenderData.name,
        registry.layoutRenderData
      )) as LayoutInitial;

      // The main functionalities are ready,
      // but first data has not been loaded.
      this.hasCoreLoaded = true;

      // Every core properties has been set,
      // block any try to add extra property.
      this.seal();

      await this.loadLayoutRenderData(this.layout.renderData);

      // Display page content.
      this.layout.el.classList.remove('layout-loading');

      // Execute ready callbacks.
      await this.readyComplete();

      readyCallback && (await readyCallback());
    };

    let readyState = doc.readyState;

    // Document has been parsed.
    // Allows running after loaded event.
    if (['complete', 'loaded', 'interactive'].indexOf(readyState) !== -1) {
      this.async(run);
    } else {
      doc.addEventListener('DOMContentLoaded', run);
    }
  }

  async loadLayoutRenderData(renderData: RenderDataInterface): Promise<any> {
    // These elements can"t be mounted during regular mount pass.
    this.layout.attachCoreHtmlElements();

    await this.services.mixins.invokeUntilComplete(
      'hookLoadLayoutRenderData',
      'app',
      [renderData]
    );

    // Pass through the whole tree to find unmounted nodes.
    await this.layout.mountTree();
  }

  getClassPage() {
    return Page;
  }

  getServices(): typeof AppService[] {
    return [
      AssetsService,
      LayoutsService,
      MixinsService,
      PagesService,
    ];
  }

  loadServices(services: typeof AppService[]): AppService[] {
    services = this.getServicesAndDependencies(services);
    let instances = [];

    services.forEach((service: any) => {
      let name = service.serviceName

      if (!this.services[name]) {
        this.services[name] = new service(this);
        instances.push(this.services[name]);
      }
    });

    return instances;
  }

  async loadAndInitServices(
    ServicesDefinitions: typeof AppService[]
  ): Promise<any> {
    let services = this.loadServices(ServicesDefinitions);

    // Init mixins.
    return this.services.mixins.invokeUntilComplete(
      'hookInit',
      'app',
      [],
      undefined,
      services
    );
  }

  getServicesAndDependencies(
    services: typeof AppService[]
  ): typeof AppService[] {
    services.forEach((service: typeof AppService) => {
      if (service.dependencies) {
        services = [
          ...services,
          ...this.getServicesAndDependencies(service.dependencies),
        ];
      }
    });

    return arrayUnique(services) as typeof AppService[];
  }

  /**
   * @param classRegistryName
   * @param bundled
   */
  getBundleClassDefinition(
    classRegistryName: string,
    bundled: boolean = false
  ): any | null {
    let bundle = this.bundles.classes[classRegistryName];

    if (bundled) {
      return bundle ? bundle : null;
    }

    return bundle;
  }
}
