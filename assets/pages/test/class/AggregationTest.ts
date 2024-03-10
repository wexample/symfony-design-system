import UnitTest from '../../../js/class/UnitTest';

export default class AggregationTest extends UnitTest {
  public getTestMethods() {
    return [
      this.testDefault,
    ];
  }

  private testDefault(path: string, testString: string) {
    let enableAggregation = this.app.layout.vars.enableAggregation;

    this.assertEquals(typeof(enableAggregation), 'boolean')
  }
}
