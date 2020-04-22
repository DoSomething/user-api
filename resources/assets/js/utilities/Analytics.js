const $ = require('jquery');
const queryString = require('query-string');
const Validation = require('dosomething-validation');
const {
  flattenDeep,
  isNil,
  isObjectLike,
  mapValues,
  omitBy,
  snakeCase,
  startCase,
} = require('lodash');

// App name prefix used for analytics event naming.
const APP_PREFIX = 'northstar';

/**
 * Get the query-string value at the given key.
 *
 * @param  {String}   key
 * @param  {URL|Location}   url
 * @return {String|Undefined}
 */
export function query(key, url = window.location) {
  // Ensure we have a URL object from the location.
  const search = queryString.parse(url.search);

  return search[key];
}

/**
 * Stringify all properties on an object whose value is object with properties.
 *
 * @param  {Object} data
 * @return {Object}
 */
export function stringifyNestedObjects(data) {
  return mapValues(data, value => {
    if (isObjectLike(value)) {
      return JSON.stringify(value);
    }

    return value;
  });
}

/**
 * Return a boolean indicating whether the provided argument is a string.
 *
 * @param  {Mixed}  string
 * @return {Boolean}
 */
export function isEmptyString(string) {
  return string === '';
}

/**
 * Remove items from object with null, undefined, or empty string values.
 *
 * @param  {Object} data
 * @return {Object}
 */
export function withoutValueless(data) {
  return omitBy(omitBy(data, isNil), isEmptyString);
}

/**
 * Get additional context data.
 *
 * @return {Object}
 */
export function getAdditionalContext() {
  return {
    utmSource: query('utm_source'),
    utmMedium: query('utm_medium'),
    utmCampaign: query('utm_campaign'),
  };
}

/**
 * Get the category for an event based on current pathname.
 *
 * @return {String}
 */
const getCategoryFromPath = () => {
  const pathToCategoryMap = {
    '/register': 'authentication',
    '/profile/about': 'onboarding',
    '/profile/subscriptions': 'onboarding',
    // Temporary:
    '/register-beta': 'authentication',
  };

  return pathToCategoryMap[window.location.pathname] || 'authentication';
}

/**
 * Parse an id value to discern the form type (with a sensible default).
 * The id should contain at least two hyphen-separated values -
 * e.g. an id of 'profile-register-form' would yield the 'register' value.
 *
 * @param {String} [id='']
 * @return {String}
 */
const getFormTypeFromId = (id = '') => id.split('-')[1] || 'form';

/**
 * Parse analytics event name parameters into a snake cased string.
 *
 * @param  {String}      verb
 * @param  {String}      noun
 * @param  {String|Null} [adjective=null]
 * @return {void}
 */
const formatEventName = (verb, noun, adjective = null) => {
  let eventName = `${APP_PREFIX}_${snakeCase(verb)}_${snakeCase(noun)}`;
  // Append adjective if defined.
  eventName += adjective ? `_${snakeCase(adjective)}` : '';

  return eventName;
};

/**
 * Send event to analyze with Google Analytics.
 *
 * @param  {String} category
 * @param  {String} action
 * @param  {String} label
 * @param  {Object} data
 * @return {void}
 */
export function analyzeWithGoogle(name, category, action, label, data) {
  if (!name || !category || !action || !label) {
    console.error('Some expected data is missing!');
    return;
  }

  const flattenedData = stringifyNestedObjects(data);

  if (window.NORTHSTAR_ID) {
    flattenedData.userId = window.NORTHSTAR_ID;
  }

  const analyticsEvent = {
    event: name,
    eventAction: startCase(action),
    eventCategory: startCase(category),
    eventLabel: startCase(label),
    ...flattenedData,
  }

  // Push event action to Google Tag Manager's data layer.
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(analyticsEvent);
}

/**
 * Send event to analyze with Snowplow.
 *
 * @param  {String} name
 * @param  {String} category
 * @param  {String} action
 * @param  {String} label
 * @param  {Object} data
 * @return {void}
 */
export function analyzeWithSnowplow(name, category, action, label, data) {
  if (!window.snowplow) {
    return;
  }

  window.snowplow('trackStructEvent', category, action, label, name, null, [
    {
      schema: `${window.ENV.PHOENIX_URL}/snowplow_schema.json`,
      data: {
        payload: JSON.stringify(data),
      },
    },
  ]);
}

/**
 * Dispatch analytics event to specified service, or all services by default.
 *
 * @param  {String}      category
 * @param  {String}      name
 * @param  {Object|Null} [data]
 * @param  {String|Null} [service]
 * @return {void}
 */
const sendToServices = (name, category, action, label, data, service) => {
  switch (service) {
    case 'google':
      analyzeWithGoogle(name, category, action, label, data);
      break;

    case 'snowplow':
      analyzeWithSnowplow(name, category, action, label, data);
      break;

    default:
      analyzeWithGoogle(name, category, action, label, data);
      analyzeWithSnowplow(name, category, action, label, data);
  }
};

/**
 * Track an analytics event with a specified service.
 * (Defaults to tracking with all services.)
 *
 * @param  {Object} options
 * @param  {Object} options.metadata
 * @param  {Object} options.context
 * @param  {String} options.service
 * @return {void}
 */
export function trackAnalyticsEvent({ metadata, context = {}, service }) {
  if (!metadata) {
    console.error('The metadata object is missing!');
    return;
  }

  const { adjective, category, target, noun, verb } = metadata;
  const label = metadata.label || noun;

  const name = formatEventName(verb, noun, adjective);

  const data = withoutValueless({
    ...context,
    ...getAdditionalContext(),
  });

  const action = snakeCase(`${target}_${verb}`);

  sendToServices(name, category, action, label, data, service);
}

// Helper method to track field focus analytics events.
function trackInputFocus(inputName) {
  if (!inputName) {
    return;
  }

  trackAnalyticsEvent({
    metadata: {
      adjective: inputName,
      category: 'focused_field',
      label: inputName,
      noun: 'field',
      target: 'field',
      verb: 'focused',
    },
  });
}

function init() {
  if (typeof window.snowplow === 'function') {
    // If available, set User ID for Snowplow analytics.
    if (window.NORTHSTAR_ID) {
      window.snowplow('setUserId', window.NORTHSTAR_ID);
    }

    // Track page view to Snowplow analytics.
    window.snowplow('trackPageView', null, [
      {
        schema: `${window.ENV.PHOENIX_URL}/snowplow_schema.json`,
        data: {
          payload: JSON.stringify(withoutValueless(getAdditionalContext())),
        },
      },
    ]);
  }

  // Validation Events for the Register form.
  Validation.Events.subscribe('Validation:InlineError', (topic, args) => {
    // Tracks each individual inline error.
    trackAnalyticsEvent({
      metadata: {
        adjective: `field_${args}`,
        category: getCategoryFromPath(),
        label: args,
        noun: 'error',
        target: 'error',
        verb: 'triggered',
      },
    });
  });

  Validation.Events.subscribe('Validation:Suggestion', (topic, args) => {
    // Tracks email fix suggestion.
    trackAnalyticsEvent({
      context: {
        suggestion: args,
      },
      metadata: {
        adjective: 'field_email',
        category: getCategoryFromPath(),
        label: 'email_suggestion',
        noun: 'suggestion',
        target: 'suggestion',
        verb: 'triggered',
      },
    });
  });

  Validation.Events.subscribe('Validation:SuggestionUsed', (topic, args) => {
    // Tracks when email fix suggestion is used.
    trackAnalyticsEvent({
      metadata: {
        adjective: 'field_email',
        category: getCategoryFromPath(),
        label: 'email_suggestion',
        noun: 'suggestion',
        target: 'suggestion',
        verb: 'used',
      },
    });
  });

  Validation.Events.subscribe('Validation:Submitted', (topic, args) => {
    // Tracks when an inline validation error free submission is made.
    trackAnalyticsEvent({
      metadata: {
        category: getCategoryFromPath(),
        noun: getFormTypeFromId(args),
        target: 'form',
        verb: 'submitted',
      },
    });
  });

  Validation.Events.subscribe('Validation:SubmitError', (topic, args) => {
    const formType = getFormTypeFromId(args);

    // Tracks when a submission is prevented due to inline validation errors.
    trackAnalyticsEvent({
      metadata: {
        adjective: `submit_${formType}`,
        category: getCategoryFromPath(),
        label: `submit_${formType}`,
        noun: 'error',
        target: 'error',
        verb: 'triggered',
      },
    });
  });

  // Custom tracking events.
  $(document).ready(() => {
    // Tracks an auto focused form field (which will already be focused upon page load).
    const focusedElement = $('input:focus');
    if (focusedElement.length) {
      const inputName = focusedElement.attr('name');
      trackInputFocus(inputName);
    }

    // Specifically tracks the #profile subscriptions & about forms to ensure submission is
    // tracked irrespective of the validation package registering the submission sans required, populated input fields.
    // @TODO replace this @HACK with the updated doSomething/validation package to register
    // these submissions to begin with.
    $('#profile-subscriptions-form, #profile-about-form').on('submit', function() {
      // The following copies logic used by the dosomething-validation package,
      // which determines weather there are fields to be validated.
      var $form = $(this);

      var $validationFields = $form.find("[data-validate]");

      $validationFields = $validationFields.map(function() {
        var $this = $(this);
        if(typeof $this.attr("data-validate-required") !== "undefined" || $this.val() !== "") {
          return $this;
        }
      });

      // If there are fields to be validated, we defer to the dosomething-validation package
      // which will publish a submission event which we've subscribed to, to track analytics.
      if ($validationFields.length) {
        return;
      }

      // Otherwise, track when an inline validation error free submission is made.
      trackAnalyticsEvent({
        metadata: {
          category: getCategoryFromPath(),
          noun: getFormTypeFromId($form.attr('id')),
          target: 'form',
          verb: 'submitted',
        },
      });
    });

    // Tracks when user focuses on form field.
    $('input').on('focus', (element) => {
      const inputName = element.target.name;
      trackInputFocus(inputName);
    })

    $('#profile-login-form').on('submit', () => {
      // Tracks login form submissions.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'login',
          target: 'form',
          verb: 'submitted',
        },
      });
    });

    $('#profile-edit-form').on('submit', () => {
      // Tracks profile edit form submissions.
      trackAnalyticsEvent({
        metadata: {
          category: 'account_edit',
          noun: 'edit_profile',
          target: 'form',
          verb: 'submitted',
        },
      });
    });

    $('#forgot-password-form').on('submit', () => {
      // Tracks forgot password email form submissions.
      trackAnalyticsEvent({
        metadata: {
          category: 'account_edit',
          noun: 'forgot_password',
          target: 'form',
          verb: 'submitted',
        },
      });
    });

    $('#password-reset-form').on('submit', () => {
      // Tracks password reset form submissions.
      trackAnalyticsEvent({
        metadata: {
          category: 'account_edit',
          noun: 'reset_password',
          target: 'form',
          verb: 'submitted',
        },
      });
    });

    $('.facebook-login').on('click', () => {
      // Tracks clicking on the Login With Facebook button.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'login_facebook',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('.google-login').on('click', () => {
      // Tracks clicking on the Login With Google button.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'login_google',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('.login-link').on('click', (element) => {
      // @HACK: Allow overriding the default 'button' target via a data-target attribute.
      // (Ideally this should be standard and consistant based on the element type (e.g. a tag vs button),
      // however, we're accounting for legacy 'button' events tracked on <a> tags.)
      // (See DoSomething Slack: http://bit.ly/32kcSWE).
      const target = element.target.dataset.target || 'button';

      // Tracks clicking on any of the 'Log in' buttons and links.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'login',
          target,
          verb: 'clicked',
        },
      });
    });

    $('.register-link').on('click', () => {
      // Tracks clicking on any of the 'Register' or 'Create account' buttons and links.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'register',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('.forgot-password-link').on('click', () => {
      // Tracks clicking on the 'Forgot Password' link.
      trackAnalyticsEvent({
        metadata: {
          category: 'account_edit',
          noun: 'forgot_password',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('.form-skip').on('click', () => {
      // Tracks clicking on the 'Skip' button in onboarding forms.
      trackAnalyticsEvent({
        metadata: {
          category: 'onboarding',
          noun: 'skip',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('#voter-reg-link').on('click', () => {
      // Tracks clicking on the Voter Registration Link in the Onboarding flow.
      trackAnalyticsEvent({
        metadata: {
          adjective: 'register_to_vote',
          category: 'onboarding',
          noun: 'link_action',
          target: 'link',
          verb: 'clicked',
        },
      });
    });

    // Check for and track validation errors returned from the backend after form submission.
    const $validationErrors = $('.validation-error');
    if ($validationErrors && $validationErrors.length) {
      const errors = window.ERRORS || {};
      const invalidFields = Object.keys(errors);

      const validationMessages = flattenDeep(Object.values(errors));

      trackAnalyticsEvent({
        context: {
          invalidFields,
          validationMessages,
        },
        metadata: {
          category: getCategoryFromPath(),
          label: 'validation_error',
          noun: 'validation',
          target: 'validation',
          verb: 'failed',
        },
      });
    }
  });
}

export default { init };
