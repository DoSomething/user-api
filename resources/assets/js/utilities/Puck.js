import { Engine } from '@dosomething/puck-client';
import { flattenDeep } from 'lodash';
import $ from 'jquery';

function init() {
  const puck = new Engine({
    source: 'northstar',
    puckUrl: window.ENV.PUCK_URL,
    getUser: () => window.NORTHSTAR_ID,
  });

  $(document).ready(() => {
    $('.facebook-login').on('click', () => (
      puck.trackEvent('northstar_clicked_login_facebook')
    ));

    $('#register-submit').on('click', () => (
      puck.trackEvent('northstar_submitted_register')
    ));

    $('#login-submit').on('click', () => (
      puck.trackEvent('northstar_submitted_login')
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
    }
  });
}

export default { init };
