const $ = require('jquery');
const { flattenDeep } = require('lodash');
const Analytics = require('@dosomething/analytics');
const Validation = require('dosomething-validation');
const { Engine } = require('@dosomething/puck-client');

// App name prefix used for analytics event naming.
const APP_PREFIX = 'northstar';

// Helper method to track field focus analytics events.
function trackInputFocus(puck, inputName) {
  if (!inputName) {
    return;
  }

  Analytics.analyze('Form', 'Focused', inputName);
  if (puck) {
    puck.trackEvent(`northstar_focused_field_${inputName}`);
  }
}

// Formats event naming and tracks event to Puck and GA.
function trackEvent(puck, event) {
  const { verb, noun, adjective, data } = event;

  let eventName = `${APP_PREFIX}_${verb}_${noun}`;
  if (adjective) {
    eventName += `_${adjective}`;
  }

  const category = `${APP_PREFIX}_${noun}`;
  const label = window.location.pathname;

  Analytics.analyze(category, eventName, label);
  if (puck) {
    puck.trackEvent(eventName, data);
  }
}

function init() {
  Analytics.init();

  const puck = new Engine({
    source: 'northstar',
    puckUrl: window.ENV.PUCK_URL,
    getUser: () => window.NORTHSTAR_ID,
  });

  // Validation Events for the Register form.
  Validation.Events.subscribe('Validation:InlineError', (topic, args) => {
    // Tracks each individual inline error.
    Analytics.analyze('Form', 'Inline Validation Error', args);
    puck.trackEvent(`northstar_triggered_error_field_${args}`);
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
    puck.trackEvent('northstar_triggered_error_submit_register');
  });

  // Custom tracking events.
  $(document).ready(() => {
    // Tracks an auto focused form field (which will already be focused upon page load).
    const focusedElement = $('input:focus');
    if (focusedElement.length) {
      const inputName = focusedElement.attr('name');
      trackInputFocus(puck, inputName);
    }

    // Tracks when user focuses on form field.
    $('input').on('focus', (element) => {
      const inputName = element.target.name;
      trackInputFocus(puck, inputName);
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

    $('#forgot-password-form').on('submit', () => {
      // Tracks forgot password email form submissions.
      Analytics.analyze('Form', 'Submitted', 'forgot-password-form');
      puck.trackEvent('northstar_submitted_forgot_password');
    })

    $('#password-reset-form').on('submit', () => {
      // Tracks password reset form submissions.
      Analytics.analyze('Form', 'Submitted', 'password-reset-form');
      puck.trackEvent('northstar_submitted_reset_password');
    })

    $('.facebook-login').on('click', () => {
      // Tracks clicking on the Login With Facebook button.
      Analytics.analyze('Form', 'Clicked', 'facebook-login');
      puck.trackEvent('northstar_clicked_login_facebook')
    });

    $('.login-link').on('click', () => {
      // Tracks clicking on any of the 'Log in' buttons and links.
      Analytics.analyze('Form', 'Clicked', 'login-link');
      puck.trackEvent('northstar_clicked_login');
    })

    $('.register-link').on('click', () => {
      // Tracks clicking on any of the 'Register' or 'Create account' buttons and links.
      Analytics.analyze('Form', 'Clicked', 'register-link');
      puck.trackEvent('northstar_clicked_register');
    })

    $('.forgot-password-link').on('click', () => {
      // Tracks clicking on the 'Forgot Password' link.
      Analytics.analyze('Form', 'Clicked', 'forgot-password-link');
      puck.trackEvent('northstar_clicked_forgot_password');
    })

    // Check for and track validation errors returned from the backend after form submission.
    const $validationErrors = $('.validation-error');
    if ($validationErrors && $validationErrors.length) {
      const errors = window.ERRORS || {};
      const invalidFields = Object.keys(errors);

      const validationMessages = flattenDeep(Object.values(errors));

      puck.trackEvent('northstar_failed_validation', {
        invalidFields,
        validationMessages,
      });

      const formId = $('form').attr('id');
      Analytics.analyze('Form', 'Validation Error', formId);
    }
  });
}

export default { init };
