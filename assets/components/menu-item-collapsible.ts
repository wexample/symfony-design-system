import AbstractCollapsibleComponent from '../js/Class/AbstractCollapsibleComponent';

export default class extends AbstractCollapsibleComponent {
  protected getToggleSelector(): string {
    return '.menu-item-collapsible--toggle';
  }
}
