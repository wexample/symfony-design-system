import ModalComponent from '../../../components/modal';

import { toScreamingSnake } from '../../../js/helpers/StringHelper';
import AbstractTest from "./AbstractTest";
import LayoutInterface from "../../../js/interfaces/RenderData/LayoutInterface";

export default class AdaptiveRenderingTest extends AbstractTest {
  public getTestMethods() {
    return [
      this.testNonAdaptivePage,
      this.testAdaptivePage,
    ];
  }

  async testNonAdaptivePage() {
    await this.fetchTestPageAdaptiveHtml(
      'VIEW',
      this.app.services.routing.path('_design_system_test_view')
    );
  }

  async testAdaptivePage() {
    // Load in html.
    await this.fetchTestPageAdaptiveHtml('ADAPTIVE');

    await this.fetchTestPageAdaptiveAjax().then(async () => {
      let pageFocused = this.app.layout.pageFocused;
      let modal = pageFocused.parentRenderNode as ModalComponent;

      this.assertEquals(
        pageFocused.templateAbstractPath,
        `@wexample/symfony-design-system::pages/test/adaptive`,
        'The focused page is the modal content page'
      );

      this.assertEquals(
        modal.templateAbstractPath,
        `@wexample/symfony-design-system::components/modal`,
        'The focused page is a child of modal component'
      );

      this.assertEquals(
        modal.parentRenderNode.templateAbstractPath,
        this.app.layout.templateAbstractPath,
        'The parent of modal is the initial layout'
      );

      this.assertEquals(
        modal.callerPage.templateAbstractPath,
        this.app.layout.page.templateAbstractPath,
        'The caller page of modal is the initial layout page'
      );

      this.assertEquals(
        pageFocused.el.querySelector('.modal-header h2').innerHTML,
        'ADAPTIVE_PAGE_TITLE',
        'The modal page title has been translated'
      );

      this.assertEquals(
        pageFocused.vars.pageLevelTestVar,
        'value',
        'The modal page has vars'
      );

      this.assertEquals(
        this.app.layout.vars.layoutLevelTestVar,
        'value',
        'The layout has a new var'
      );

      this.assertTrue(
        pageFocused.components[0].options.testOption,
        'The component option has been loaded'
      );

      this.assertEquals(
        getComputedStyle(
          pageFocused.el.querySelector(`.adaptive-page-test-css`)
        ).backgroundColor,
        'rgb(0, 128, 0)',
        'The adaptive CSS has applied green'
      );

      this.assertEquals(
        getComputedStyle(pageFocused.el.querySelector(`.adaptive-page-test-js`))
          .backgroundColor,
        'rgb(0, 128, 0)',
        'The adaptive JS has applied green'
      );

      let elComponent = pageFocused.el.querySelector(
        '.adaptive-page-test-component'
      ) as HTMLElement;

      this.assertTestComponentIntegrity(elComponent, 'test-component');
    });
  }

  private assertTestComponentIntegrity = (
    el: HTMLElement,
    prefix: string = '',
    suffix: string = ''
  ) => {
    this.assertEquals(
      this.app.layout.pageFocused.el.querySelector(
        `.${prefix}-string-translated-server${suffix}`
      ).innerHTML,
      `SERVER_SIDE_${toScreamingSnake(prefix)}_TRANSLATION${suffix}`,
      `Test server side translation`
    );

    this.assertEquals(
      this.app.layout.pageFocused.el.querySelector(
        `.${prefix}-string-translated-client${suffix}`
      ).innerHTML,
      `CLIENT_SIDE_${toScreamingSnake(prefix)}_TRANSLATION${suffix}`,
      `Test client side translation`
    );
  };

  private fetchTestPageAdaptiveAjax() {
    // Load in json.
    return this.fetchAdaptiveAjaxPage()
      .then((renderData: LayoutInterface) => {
        this.assertTrue(
          !!renderData.id,
          `There is an id in the response object`
        );

        this.assertTrue(
          !renderData.assets,
          `Layout data contains any assets`
        );

        this.assertTrue(
          !!renderData.page,
          'The response contains page data'
        );

        this.assertFalse(
          renderData.page.isInitialPage,
          'Page is not set as initial'
        );

        this.assertTrue(
          !!renderData.templates,
          'The response contains template html'
        );

        return renderData;
      });
  }

  protected createElDocument(html: string) {
    let elHtml = document.createElement('html');
    elHtml.innerHTML = html;

    return elHtml;
  }

  private fetchTestPageAdaptiveHtml(testString: string, path: string = undefined) {
    // Use normal fetch to fake a non ajax get request.
    return this.fetchAdaptiveHtmlPage(path).then((html) => {
      let elHtml = this.createElDocument(html);

      this.assertTrue(
        !!elHtml.querySelector('body'),
        `${path} : Fetched page content is a standard html document `
      );

      this.assertEquals(
        elHtml.querySelectorAll('.page').length,
        1,
        `${path} : Page element exists and is unique`
      );

      this.assertEquals(
        elHtml.querySelector('.page .test-string').innerHTML,
        testString,
        `Test string equals "${testString}"`
      );
    });
  }
}
