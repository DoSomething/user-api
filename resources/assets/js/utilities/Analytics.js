const $ = require('jquery');
const queryString = require('query-string');
const Validation = require('dosomething-validation');
const { Engine } = require('@dosomething/puck-client');
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

// Variable that stores the instance of PuckClient.
let puckClient = null;

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
 * Return an instantiated Puck Client (Engine).
 *
 * @return {Object}
 */
const puckClientInit = () => (
  new Engine({
    source: 'northstar',
    puckUrl: window.ENV.PUCK_URL,
    getUser: () => window.NORTHSTAR_ID,
  })
);

/**
 * Send event to analyze with Puck.
 *
 * @param  {String} name
 * @param  {Object} data
 * @return {void}
 */
export function analyzeWithPuck(name, data) {
  if (!puckClient) {
    puckClient = puckClientInit();
  }

  puckClient.trackEvent(name, data);
}

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
    case 'ga':
      analyzeWithGoogle(name, category, action, label, data);
      break;

    case 'puck':
      analyzeWithPuck(name, data);
      break;

    default:
      analyzeWithGoogle(name, category, action, label, data);
      analyzeWithPuck(name, data);
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
  if (!puckClient) {
    puckClient = puckClientInit();
  }

  // Validation Events for the Register form.
  Validation.Events.subscribe('Validation:InlineError', (topic, args) => {
    // Tracks each individual inline error.
    trackAnalyticsEvent({
      metadata: {
        adjective: `field_${args}`,
        category: 'authentication',
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
        category: 'authentication',
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
        category: 'authentication',
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
      context: {
        suggestion: args,
      },
      metadata: {
        category: 'authentication',
        noun: 'register',
        target: 'form',
        verb: 'submitted',
      },
    });
  });

  Validation.Events.subscribe('Validation:SubmitError', (topic, args) => {
    // Tracks when a submission is prevented due to inline validation errors.
    trackAnalyticsEvent({
      metadata: {
        adjective: 'submit_register',
        category: 'authentication',
        label: 'submit_register',
        noun: 'error',
        target: 'error',
        verb: 'triggered',
      },
    });
  });

  $(document).ready(() => {
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

    // Custom tracking events:

    // Tracks an auto focused form field (which will already be focused upon page load).
    const focusedElement = $('input:focus');
    if (focusedElement.length) {
      const inputName = focusedElement.attr('name');
      trackInputFocus(inputName);
    }

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
    })

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
    })

    $('.facebook-login').on('click', () => {
      // Tracks clicking on the Login With Facebook button.
      trackAnalyticsEvent({
        metadata: {
          category: 'login_facebook',
          label: 'authentication',
          noun: 'login_facebook',
          target: 'button',
          verb: 'clicked',
        },
      });
    });

    $('.login-link').on('click', () => {
      // Tracks clicking on any of the 'Log in' buttons and links.
      trackAnalyticsEvent({
        metadata: {
          category: 'authentication',
          noun: 'login',
          target: 'button',
          verb: 'clicked',
        },
      });
    })

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
    })

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
    })

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
          category: 'authentication',
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
