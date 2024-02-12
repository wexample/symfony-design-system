import Page from './Page';

import AppService from './AppService';
import AssetsService from '../services/AssetsService';
import ColorSchemeService from '../services/ColorSchemeService';
import EventsService from '../services/EventsService';
import LayoutsService from '../services/LayoutsService';
import MixinsService from '../services/MixinsService';
import PagesService from '../services/PagesService';
import ResponsiveService from '../services/ResponsiveService';
import RoutingService from '../services/RoutingService';

import { unique as arrayUnique } from '../helpers/ArrayHelper';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import LayoutInitial from './LayoutInitial';
import LayoutInterface from '../interfaces/RenderData/LayoutInterface';
import AsyncConstructor from './AsyncConstructor';
import { toCamel } from '../helpers/StringHelper';
import ServicesRegistryInterface from '../interfaces/ServicesRegistryInterface';

export default class extends AsyncConstructor {
  public bundles: any;
  public hasCoreLoaded: boolean = false;
  public layout: LayoutInitial = null;
  public mixins: typeof AppService[] = [];
  public lib: object = {};
  public services: ServicesRegistryInterface = {};

  constructor(
    readyCallback?: any | Function,
    globalName: string = 'app'
  ) {
    super();

    window[globalName] = this;

    // Allow callback as object definition.
    if (typeof readyCallback === 'object') {
      Object.assign(this, readyCallback);
      // Allow object.readyCallback property.
      readyCallback = readyCallback.readyCallback || readyCallback;
    }

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

      this.addLibraries(this.lib);

      // The main functionalities are ready,
      // but first data has not been loaded.
      this.hasCoreLoaded = true;

      // Every core properties has been set,
      // block any try to add extra property.
      this.seal();

      await this.loadLayoutRenderData(this.layout.renderData);

      // Display page content.
      this.layout.el.classList.remove('layout-loading');

      // Activate every new render node.
      await this.layout.setNewTreeRenderNodeReady();

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
    await this.services.mixins.invokeUntilComplete(
      'hookLoadLayoutRenderData',
      'app',
      [renderData]
    );

    // Pass through the whole tree to find unmounted nodes.
    await this.layout.mountTree();
  }

  buildServiceName(serviceName: string): string {
    return toCamel(serviceName.slice(0, -'Service'.length));
  }

  getClassPage() {
    return Page;
  }

  getServices(): typeof AppService[] {
    return [
      AssetsService,
      ColorSchemeService,
      EventsService,
      LayoutsService,
      MixinsService,
      PagesService,
      RoutingService,
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

  addLib(name: string, object: any) {
    this.lib[name] = object;
  }

  addLibraries(libraries) {
    // Initialize preexisting libs.
    Object.entries(libraries).forEach((data) => {
      this.addLib(data[0], data[1]);
    });
  }
}
