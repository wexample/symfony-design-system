import PageResponsiveDisplay from '../../../../src/Resources/js/class/PageResponsiveDisplay';

export default class extends PageResponsiveDisplay {
  async onResponsiveEnter() {
    console.log('index l init');
  }

  async onResponsiveExit() {
    console.log('index l exit');
  }
}
