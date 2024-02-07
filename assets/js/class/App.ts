import AppService from './AppService';
import AsyncConstructor from './AsyncConstructor';

export default class extends AsyncConstructor {

  constructor(
    readyCallback?: any | Function,
    globalName: string = 'app'
  ) {
    super();

    window[globalName] = this;
  }

  getServices(): typeof AppService[] {
    return [

    ];
  }
}
