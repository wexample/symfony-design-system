import PageResponsiveDisplay from '../../../js/class/PageResponsiveDisplay';

export default class extends PageResponsiveDisplay {
  async onResponsiveEnter() {
    if (this.page.vars.responsiveSizesCounters) {
      this.page.vars.responsiveSizesCounters.xl++;
    }
  }

  async onResponsiveExit() {
    if (this.page.vars.responsiveSizesCounters) {
      this.page.vars.responsiveSizesCounters.xl--;
    }
  }
}
