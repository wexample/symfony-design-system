<script>
import AbstractFormMixin from "@wexample/js-api/Vue/AbstractFormMixin";
import LocaleService from '@wexample/symfony-loader/js/Services/LocaleService';
import { formDefinitionAction, formDefinitionData, notifyFormDefinition } from '../../../js/Helper/FormDefinitionHelper';
import AdaptiveService from '@wexample/symfony-loader/js/Services/AdaptiveService';

// A form of the design system: the mixin's fields, errors and submit, and
// where it is sent —
// - through the api client by default, which prefixes its address with the
//   api's;
// - to a page's own route as it stands (`submitThroughApi: false`);
// - to a form of the server (`formDefinition`, as `form_definition()` hands one, the
//   json-schema norm's `form.json`): sent as the drawn form would be, to its
//   processor, which answers as the norm says (`form_submit_response.json`),
//   the fields' errors under their full names — the names the fields were
//   registered under — in the page's words.
// The switch and the answer's reading belong to the mixin and move there
// with js-api's next release.
export default {
  mixins: [AbstractFormMixin],

  data() {
    return {
      submitThroughApi: true,
      formDefinition: null,
      // The entity a form of the server is sent for, put where its address
      // holds the placeholder (`form_definition(form, { id_placeholder })`).
      formDefinitionId: null
    };
  },

  methods: {
    async requestApiSubmit(options) {
      if (this.formDefinition) {
        return this.app.getServiceOrFail(AdaptiveService).requestData(formDefinitionAction(this.formDefinition, this.formDefinitionId), {
          method: this.formDefinition.method ?? 'POST',
          body: formDefinitionData(this.formDefinition, options.payload ?? {})
        });
      }

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
    },

    // A form of the server refusing what was sent.
    responseHasValidationError(response) {
      if (response && typeof response === 'object' && response.ok === false && response.form?.errors) {
        return true;
      }

      return AbstractFormMixin.methods.responseHasValidationError.call(this, response);
    },

    applyApiValidationFromResponse(response) {
      const errors = response?.form?.errors;

      if (!errors) {
        AbstractFormMixin.methods.applyApiValidationFromResponse.call(this, response);

        return;
      }

      const locale = this.app.getServiceOrFail(LocaleService);
      const translate = (messages = []) => messages.map((message) => locale.trans(message, {}, response.translations ?? {}));

      this.setFormErrors({
        formErrors: translate(errors.form ?? []),
        fieldErrors: Object.fromEntries(Object.entries(errors.fields ?? {}).map(([name, messages]) => [name, translate(messages)]))
      });
    },

    // What a form of the server said once taken, told.
    onApiSubmitSuccess(response) {
      if (this.formDefinition) {
        notifyFormDefinition(this.app, response);
      }
    }
  }
};
</script>
