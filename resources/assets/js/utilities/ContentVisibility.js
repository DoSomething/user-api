const $ = require('jquery');

/**
 * Utility script to enable content visibility toggling
 */

function clickHandlerToggleContent(event) {
    const target = event.target;
    const content = document.getElementById('voter-reg-cta');

    if(target.value === 'unregistered') {
        content.classList.remove('hidden')
    } else {
        content.classList.add('hidden')
    }

}

const init = () => {
    $(document).ready(() => {
        const vrStatusInputs = document.getElementsByClassName('voter-reg-status')
        vrStatusInputs.forEach(inputField => {
            inputField.addEventListener('click', clickHandlerToggleContent)
        })
      });
}

export default { init };