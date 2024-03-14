export default interface AssetInterface {
  // Defines that assets should be loaded
  // even the loading process is not finished.
  active: boolean;
  colorScheme?: string;
  context: string;
  domId: string;
  filesize: number;
  el: HTMLElement;
  id: string;
  initialLayout: string;
  media: string;
  loaded: boolean;
  name: string;
  path: string;
  preload: boolean;
  resolver: Function;
  responsive?: string;
  // Defines that asset has been fully loaded once,
  // so browser will not load it again
  // if we append it again to document.
  rendered: boolean;
  type: string;
  usage: string;
  usages: any;
  view: string;
}
