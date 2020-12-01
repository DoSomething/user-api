const $ = require('jquery');

/**
 * Utility script to enable routing back to the last page,
 * as long as it's within the same top-level domain.
 */

function init(element = 'back') {
  $(document).ready(() => {
    const backLink = document.getElementById(element);
    if (!backLink) return;

    backLink.addEventListener('click', event => {
      event.preventDefault();

      analyze('Redirect', 'Clicked', 'Back To DoSomething Site');
      window.history.back();
    });
  });
}

export default { init };
