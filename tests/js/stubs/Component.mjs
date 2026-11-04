// The loader's Component, which a field's script extends: nothing of the
// page here, the actions are what is read.
export default class Component {
  constructor(el = null, app = null) {
    this.el = el;
    this.app = app;
  }
}
