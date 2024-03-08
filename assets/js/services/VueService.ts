import AppService from '../class/AppService';

export default class VueService extends AppService {
  public static serviceName: string = 'vue';

  constructor(app: App) {
    super(app);

    this.elTemplates = document.getElementById('vue-templates');
  }

}
