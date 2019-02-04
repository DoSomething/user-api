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
    // Tracks each individual inline error.
    Analytics.analyze('Form', 'Inline Validation Error', args);
    puck.trackEvent(`northstar_failed_inline_validation_${args}`);
  });

  Validation.Events.subscribe('Validation:Suggestion', (topic, args) => {
    // Tracks email fix suggestion.
    Analytics.analyze('Form', 'Suggestion', args);
  });

  Validation.Events.subscribe('Validation:SuggestionUsed', (topic, args) => {
    // Tracks when email fix suggestion is used.
    Analytics.analyze('Form', 'Suggestion Used', args);
  });

  Validation.Events.subscribe('Validation:Submitted', (topic, args) => {
    // Tracks when an inline validation error free submission is made.
    Analytics.analyze('Form', 'Submitted', args);
    puck.trackEvent('northstar_submitted_register');
  });

  Validation.Events.subscribe('Validation:SubmitError', (topic, args) => {
    // Tracks when a submission is prevented due to inline validation errors.
    Analytics.analyze('Form', 'Validation Error on submit', args);
    puck.trackEvent('northstar_failed_submission_register');
  });

  // Attach any custom events.
  $(document).ready(() => {
    // Track an auto focused form field (which will already be focused upon page load).
    const focusedElement = $('input:focus');
    if (focusedElement.length) {
      const inputName = focusedElement.attr('name');
      Analytics.analyze('Form', 'Focused', inputName);
      puck.trackEvent(`northstar_focused_field_${inputName}`);
    }

    // Tracks when user focuses on form field.
    $('input').on('focus', (element) => {
      const elementName = element.target.name;
      Analytics.analyze('Form', 'Focused', elementName);
      puck.trackEvent(`northstar_focused_field_${elementName}`);
    })

    $('#profile-login-form').on('submit', () => {
      // Tracks login form submissions.
      Analytics.analyze('Form', 'Submitted', 'profile-login-form')
      puck.trackEvent('northstar_submitted_login');
    });

    $('#profile-edit-form').on('submit', () => {
      // Tracks profile edit form submissions.
      Analytics.analyze('Form', 'Submitted', 'profile-edit-form')
      puck.trackEvent('northstar_submitted_edit_profile');
    });

    $('.facebook-login').on('click', () => {
      // Tracks clicking on the Login With Facebook button.
      Analytics.analyze('Form', 'Clicked', 'facebook-login');
      puck.trackEvent('northstar_clicked_login_facebook')
    });

    // Check for and track validation errors returned from the backend after form submission.
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
