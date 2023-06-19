import UnitTest from '../../../../class/UnitTest';

export default class TranslationTest extends UnitTest {
  public getTestMethods() {
    return [this.testDefault];
  }

  public testDefault() {
    this.assertEquals(
      document.querySelector('#test-layout-translation').innerHTML,
      'TEST_LAYOUT_SERVER_TRANSLATION',
      'Initial layout server translation works'
    );

    this.assertEquals(
      this.app.layout.page.trans('@layout::string.client_side'),
      'CLIENT_SIDE_LAYOUT_TRANSLATION',
      'Layout translation is loaded in js'
    );

    this.assertEquals(
      this.app.layout.page.trans('@page::secondGroup.first'),
      'First',
      'A simple translation is loaded in js'
    );

    this.assertEquals(
      this.app.layout.page.trans('@page::firstGroup.third'),
      'Third',
      'A translations with the * wildcard is loaded in js'
    );
  }
}
