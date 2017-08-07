const $ = require('jquery');

/**
 * Utility script to enable password visibility toggle.
 */

function clickHandler(event) {
  event.preventDefault();

  const { target } = event;
  target.classList.toggle('-hide');
}

function init() {
  $(document).ready(() => {
    const passwords = document.getElementsByClassName('password-visibility__toggle');
    if (! passwords) return;

    for (const input of passwords) {
      if (input) input.addEventListener('click', clickHandler);
    }
  });
}

export default { init };
