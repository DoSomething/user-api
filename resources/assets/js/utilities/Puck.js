import { Engine } from '@dosomething/puck-client';
import $ from 'jquery';

function init() {
  const puck = new Engine({
    source: 'northstar',
    puckUrl: window.ENV.PUCK_URL,
    getUser: () => window.NORTHSTAR_ID,
  });

  $(document).ready(() => {
    $('.facebook-login').on('click', () => (
      puck.trackEvent('clicked facebook auth')
    ));

    const $validationErrors = $('.validation-error');
    if ($validationErrors && $validationErrors.length) {
      const errors = window.ERRORS || {};
      const invalidFields = Object.keys(errors);
      const validationMessages = Object.values(errors);

      puck.trackEvent('has validation errors', {
        invalidFields,
        validationMessages,
      });
    }
  });
}

export default { init };
