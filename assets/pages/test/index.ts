import TestManagerPage from '../../js/class/TestManagerPage';


export default class extends TestManagerPage {
  async pageReady() {
    await this.runTests({
    });
  }
}
