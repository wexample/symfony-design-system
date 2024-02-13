import Layout from './Layout';

export default class extends Layout {
  public id: string = 'layout-initial';
  public elStylesContainer: HTMLElement;
  public elScriptsContainer: HTMLElement;

  attachCoreHtmlElements() {
    this.el = document.getElementById('layout');
    this.elStylesContainer = document.getElementById('layout-styles-container');
    this.elScriptsContainer = document.getElementById('layout-scripts-container');
  }

  getElWidth(forceCache: boolean = false): number {
    // Responsiveness is relative to real window size.
    return window.innerWidth;
  }

  getElHeight(forceCache: boolean = false): number {
    return window.innerHeight;
  }
}
