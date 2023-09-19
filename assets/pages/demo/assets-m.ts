import PageResponsiveDisplay from'@wexample/symfony-design-system/js/class/PageResponsiveDisplay';

export default class extends PageResponsiveDisplay {
  async onResponsiveEnter() {
    console.log('index m init');
  }

  async onResponsiveExit() {
    console.log('index m exit');
  }
}