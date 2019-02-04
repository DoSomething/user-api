const $ = require('jquery');
const { flattenDeep } = require('lodash');
const Analytics = require('@dosomething/analytics');
const Validation = require('dosomething-validation');
const { Engine } = require('@dosomething/puck-client');

function init() {
  Analytics.init();

  const puck = new Engine({
    source: 'northstar',
    puckUrl: window.ENV.PUCK_URL,
    getUser: () => window.NORTHSTAR_ID,
  });

  // Validation Events
  Validation.Events.subscribe('Validation:InlineError', (topic, args) => {
    Analytics.analyze('Form', 'Inline Validation Error', args);
    puck.trackEvent(`northstar_failed_inline_validation_${args}`);
  });

  Validation.Events.subscribe('Validation:Suggestion', (topic, args) => {
    Analytics.analyze('Form', 'Suggestion', args);
  });

  Validation.Events.subscribe('Validation:SuggestionUsed', (topic, args) => {
    Analytics.analyze('Form', 'Suggestion Used', args);
  });

  Validation.Events.subscribe('Validation:Submitted', (topic, args) => {
    Analytics.analyze('Form', 'Submitted', args);
    puck.trackEvent('northstar_submitted_register');
  });

  Validation.Events.subscribe('Validation:SubmitError', (topic, args) => {
    Analytics.analyze('Form', 'Validation Error on submit', args);
    puck.trackEvent('northstar_failed_submission_register');
  });

  // Attach any custom events.
  $(document).ready(() => {
    $('#profile-login-form').on('submit', () => {
      Analytics.analyze('Form', 'Submitted', 'profile-login-form')
      puck.trackEvent('northstar_submitted_login');
    });

    $('#profile-edit-form').on('submit', () => {
      Analytics.analyze('Form', 'Submitted', 'profile-edit-form')
      puck.trackEvent('northstar_submitted_edit_profile');
    });

    $('.facebook-login').on('click', () => (
      Analytics.analyze('Form', 'Clicked', 'facebook-login');
      puck.trackEvent('northstar_clicked_login_facebook')
    ));

    const $validationErrors = $('.validation-error');
    if ($validationErrors && $validationErrors.length) {
      const errors = window.ERRORS || {};
      const invalidFields = Object.keys(errors);

      const validationMessages = flattenDeep(Object.values(errors));

      puck.trackEvent('has validation errors', {
        invalidFields,
        validationMessages,
      });

      const formId = $('form').attr('id');
      Analytics.analyze('Form', 'Validation Error', formId);
    }
  });
}

export default { init };
