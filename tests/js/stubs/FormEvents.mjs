export const FORM_FIELD_REGISTER = 'form-field:register';
export const FORM_FIELD_UNREGISTER = 'form-field:unregister';
export const FORM_FIELD_COLLECT = 'form-field:collect';
export const FORM_SUCCESS = 'form:success';
export const formSuccessEvent = (formName) => `${FORM_SUCCESS}:${formName}`;
