import Component from '../class/Component';
import { hashParamGet as locationHashParamGet } from '../helpers/LocationHelper';
import { hashParamSet as locationHashParamSet } from '../helpers/LocationHelper';
import { parseUrl as locationParseUrl } from '../helpers/LocationHelper';
import ComponentInterface from '../interfaces/RenderData/ComponentInterface';
import RequestOptionsInterface from '../interfaces/RequestOptions/RequestOptionsInterface';
import { Attribute } from '../helpers/DomHelper';

export default class extends Component {
  protected elTabCurrent?: HTMLElement;
  protected elContentCurrent?: HTMLElement;
  protected group: string;

  mergeRenderData(renderData: ComponentInterface) {
    super.mergeRenderData(renderData);

    this.group = this.options.group;

    this.el.querySelectorAll('a.tab-internal').forEach((elTab) => {
      if (this.isCurrentPageTab(elTab)) {
        elTab.addEventListener('click', this.clickInternal.bind(this));
      }
      // Disable all tabs first.
      elTab.classList.remove('active');
    });

    // React to history changes.
    window.addEventListener('hashchange', this.onHistoryChange.bind(this));

    // Search into hash query string
    let opened = locationHashParamGet('tab-' + this.group);

    // There is no tab specified in url hash.
    if (!opened) {
      let elActive = this.el.querySelectorAll('.active');

      // There is no active tabs;
      if (!elActive.length) {
        let elTabs = this.el.querySelectorAll('.tab');

        elTabs.forEach((elTab) => {
          if (!opened) {
            // There is an internal tab pointing into the current route (or to /).
            if (
              elTab.classList.contains('tab-internal') &&
              this.isCurrentPageTab(elTab)
            ) {
              opened = elTab.getAttribute('data-tab');
            }
          }
        });
      }
    }

    opened && this.enable(opened, true);
  }

  isCurrentPageTab(elTab) {
    let path = locationParseUrl(elTab.getAttribute(Attribute.HREF)).pathname;
    return path === '/' || path === window.location.pathname;
  }

  onHistoryChange() {
    let opened = locationHashParamGet('tab-' + this.group);
    opened && this.enable(opened, true);
  }

  clickInternal(e) {
    e.preventDefault();
    this.enable(e.target.getAttribute('data-tab'));
  }

  enable(tabName, ignoreHistory = false) {
    let id = 'tab-' + this.group + '-' + tabName;
    let elTab = document.getElementById(id);
    let elContent = document.getElementById(id + '-content');

    locationHashParamSet('tab-' + this.group, tabName, ignoreHistory);

    // Clear previous.
    if (this.elTabCurrent) {
      this.elTabCurrent.classList.remove('active');
    }

    // Support missing tabs.
    if (!elTab) {
      return;
    }

    this.elTabCurrent = elTab;
    this.elTabCurrent.classList.add('active');

    // Having a content is optional.
    if (!elContent) {
      return;
    }

    // Clear previous.
    if (this.elContentCurrent) {
      this.elContentCurrent.classList.remove('tab-content-active');
    }

    this.elContentCurrent = elContent;
    this.elContentCurrent.classList.add('tab-content-active');
  }
}
