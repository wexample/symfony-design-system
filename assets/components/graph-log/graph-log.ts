import Component from '@wexample/symfony-loader/js/Class/Component';
import { graphLogColumns, graphLogLayout, graphLogSvg } from '../../js/Helper/GraphLogHelper';

// Draws the lanes of a server-rendered graph log: the rows say their id and
// their parents, the helper lays the lanes out and draws each row, the same
// drawing the vue twin makes.
export default class extends Component {
  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    const rowEls = Array.from(this.el.querySelectorAll<HTMLElement>('.graph-log--row'));
    const rows = graphLogLayout(rowEls.map((el) => ({
      id: el.dataset.id ?? '',
      parents: (el.dataset.parents ?? '').split(' ').filter(Boolean),
    })));
    const columns = graphLogColumns(rows);

    rowEls.forEach((el, index) => {
      const graphEl = el.querySelector('.graph-log--graph');

      if (graphEl) {
        graphEl.innerHTML = graphLogSvg(rows[index], columns);
      }
    });
  }
}
