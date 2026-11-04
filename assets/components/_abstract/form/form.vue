<script>
import AbstractFormMixin from "@wexample/js-api/Vue/AbstractFormMixin";

// A form of the design system: the mixin's fields, errors and submit, and
// where it is sent — through the api client by default, which prefixes its
// address with the api's; a form answered by a page's own route
// (`submitThroughApi: false`) is sent to it as it stands.
// The switch belongs to the mixin and moves there with js-api's next release.
export default {
  mixins: [AbstractFormMixin],

  data() {
    return {
      submitThroughApi: true
    };
  },

  methods: {
    async requestApiSubmit(options) {
      if (this.submitThroughApi) {
        return AbstractFormMixin.methods.requestApiSubmit.call(this, options);
      }

      const { endpoint, method = 'POST', payload = {} } = options;
      const resolvedMethod = String(method || 'POST').toUpperCase();
      const init = { method: resolvedMethod, headers: { 'Content-Type': 'application/json' } };

      if (resolvedMethod !== 'GET' && payload != null) {
        init.body = JSON.stringify(payload);
      }

      const response = await fetch(endpoint, init);

      return response.json();
    }
  }
};
</script>
