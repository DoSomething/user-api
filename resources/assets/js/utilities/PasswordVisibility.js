const $ = require('jquery');

/**
 * Utility script to enable password visibility toggle.
 */

function clickHandler(event) {
  event.preventDefault();

  const { target } = event;
  target.classList.toggle('-hide');

  const siblings = target.parentNode.childNodes;
  const inputKey = Object.keys(siblings).filter(key => siblings[key].tagName === 'INPUT');
  const input = siblings[inputKey];

  const shouldHide = target.classList.contains('-hide');
  if (input) {
    shouldHide ? input.type = 'password' : input.type = 'text';
  }
}

function init() {
  $(document).ready(() => {
    const passwordToggles = document.getElementsByClassName('password-visibility__toggle');
    if (! passwordToggles) return;

    for (const toggle of passwordToggles) {
      if (toggle) toggle.addEventListener('click', clickHandler);
    }
  });
}

export default { init };
