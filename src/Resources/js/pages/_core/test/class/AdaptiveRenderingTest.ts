import ModalComponent from '../../../../components/modal';
import LayoutInterface from '../../../../interfaces/RenderData/LayoutInterface';
import { sleep } from '../../../../helpers/Time';
import { toScreamingSnake } from '../../../../helpers/StringHelper';
import AbstractResponsiteTest from "./AbstractResponsiteTest";

export default class AdaptiveRenderingTest extends AbstractResponsiteTest {
  public getTestMethods() {
    return [
      this.testNonAdaptivePage,
      this.testAdaptivePage,
      this.testAdaptiveErrorMissingView,
    ];
  }

  async testNonAdaptivePage() {
    await this.fetchTestPageAdaptiveHtml(
      'VIEW',
      this.app.services.routing.path('_core_test_view')
    );
  }

  async testAdaptivePage() {
    // Load in html.
    await this.fetchTestPageAdaptiveHtml('ADAPTIVE');

    await this.fetchTestPageAdaptiveAjax().then(async () => {
      let pageFocused = this.app.layout.pageFocused;
      let modal = pageFocused.parentRenderNode as ModalComponent;

      this.assertEquals(
        pageFocused.name,
        `pages/_core/test/adaptive`,
        'The focused page is the modal content page'
      );

      this.assertEquals(
        pageFocused.parentRenderNode.name,
        `components/modal`,
        'The focused page is a child of modal component'
      );

      this.assertEquals(
        modal.parentRenderNode.name,
        this.app.layout.name,
        'The parent of modal is the initial layout'
      );

      this.assertEquals(
        modal.callerPage.name,
        'pages/_core/test/index',
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

      this.assertTestComponentIntegrity(elComponent, 'test-component', '-2');

      this.assertTestComponentAssets(elComponent, 'test-component');

      this.assertTestComponentAssets(elComponent, 'test-component', '-2');

      // Test twice to ensure stability.
      await this.assertVueUpdateSupportedByComponent();
      await this.assertVueUpdateSupportedByComponent();

      this.assertTestVueIntegrity();
      this.assertTestVueIntegrity('2');
      this.assertTestVueIntegrity('3');

      // Close modal.
      await modal.close();

      this.assertEquals(
        this.app.layout.pageFocused,
        this.app.layout.page,
        'The focus has been thrown back to the main page'
      );
    });
  }

  assertTestComponentAssets(
    el: HTMLElement,
    prefix: string = '',
    suffix: string = ''
  ) {
    this.assertEquals(
      getComputedStyle(
        this.app.layout.pageFocused.el.querySelector(
          `.test-component-test-css${suffix}`
        )
      ).backgroundColor,
      'rgb(0, 128, 0)',
      'The adaptive CSS has applied green on component'
    );

    this.assertEquals(
      getComputedStyle(
        this.app.layout.pageFocused.el.querySelector(
          `.test-component-test-js${suffix}`
        )
      ).backgroundColor,
      'rgb(0, 128, 0)',
      'The adaptive JS has applied green on component'
    );
  }

  assertTestComponentIntegrity = (
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

  assertTestVueIntegrity(suffix: string = '') {
    this.assertTestComponentIntegrity(
      this.app.layout.pageFocused.el.querySelector(
        '.adaptive-page-test-vue'
      ) as HTMLElement,
      'test-vue',
      suffix ? `-${suffix}` : ''
    );
  }

  async assertVueUpdateSupportedByComponent() {
    // Event changes vue content.
    this.app.services.events.trigger('test-vue-event', {
      hidePartOfDomContainingComponent: true,
    });

    // Need to wait for dom to break up.
    await sleep();

    let testComponent = this.app.layout.pageFocused
      .findChildRenderNodeByName('components/vue')
      .findChildRenderNodeByName('components/test-component');

    this.assertFalse(
      testComponent.isMounted,
      'The vue dom has been hidden, then component is unmounted'
    );

    this.assertTrue(
      testComponent.el === undefined,
      'The vue dom has been hidden, then component el is empty'
    );

    this.app.services.events.trigger('test-vue-event', {
      hidePartOfDomContainingComponent: false,
    });

    // Wait for remounting dom.
    await sleep();

    this.assertTrue(
      testComponent.isMounted,
      'The vue dom has been hidden, then component is mounted back'
    );

    this.assertFalse(
      testComponent.el === undefined,
      'The vue dom has been hidden, then component el is not empty'
    );
  }

  async testAdaptiveErrorMissingView() {
    await this.app.services.adaptive
      .get(this.app.services.routing.path('_core_test_error-missing-view'))
      .then(async () => {
        let pageFocused = this.app.layout.pageFocused;

        this.assertTrue(
          pageFocused.el
            .querySelector('.modal-body')
            .innerHTML.indexOf('Unable to find template') !== -1,
          'Error message has been displayed into modal'
        );

        this.assertTrue(pageFocused.vars.hasError, 'Page has error');

        // Close modal.
        let modal = pageFocused.parentRenderNode as ModalComponent;

        await modal.close();
      });
  }

  private fetchTestPageAdaptiveAjax() {
    // Load in json.
    return this.fetchAdaptiveAjaxPage()
      .then((renderData: LayoutInterface) => {
        this.assertTrue(
          !renderData.assets.css.length,
          `Layout data contains any CSS assets`
        );

        this.assertTrue(
          !renderData.assets.js.length,
          `Layout data contains any JS assets`
        );

        this.assertTrue(!!renderData.page, 'The response contains page data');

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

      let found = elHtml
        .querySelector('#layout-data')
        .innerHTML.match(/layoutRenderData = ([.\S\s\n]*);(\s*)/);

      this.assertTrue(!!found, `Layout data found`);

      let layoutData = JSON.parse(found[1]);

      this.assertTrue(!!layoutData, `Layout data is valid JSON`);

      this.assertTrue(
        !!layoutData.assets.css.length,
        `Layout data contains CSS assets`
      );

      this.assertTrue(
        !!layoutData.assets.js.length,
        `Layout data contains JS assets`
      );
    });
  }
}
