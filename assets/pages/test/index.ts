import AggregationTest from './class/AggregationTest';
import AdaptiveRenderingTest from './class/AdaptiveRenderingTest';
import AppTest from './class/AppTest';
import HelperTest from './class/HelperTest';
import IconTest from './class/IconTest';
import ModalInModalTest from './class/ModalInModalTest';
import NoJsTest from './class/NoJsTest';
import OverlayTest from './class/OverlayTest';
import ResponsiveTest from './class/ResponsiveTest';
import TestTest from './class/TestTest';
import TranslationTest from './class/TranslationTest';
import UsageTest from './class/UsageTest';
import VariablesTest from './class/VariablesTest';
import TestManagerPage from '../../js/class/TestManagerPage';


export default class extends TestManagerPage {
  async pageReady() {
    await this.runTests({
      AggregationTest,
      AdaptiveRenderingTest,
      AppTest,
      IconTest,
      HelperTest,
      ModalInModalTest,
      NoJsTest,
      OverlayTest,
      ResponsiveTest,
      TestTest,
      TranslationTest,
      UsageTest,
      VariablesTest,
    });

    // Run test without aggregation.
    if (!this.app.layout.vars.enableAggregation) {
      document.location.replace(
        `${document.location.origin}${document.location.pathname}?test-aggregation=1`
      );
    }
  }
}
