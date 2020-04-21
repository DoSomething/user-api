const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration content
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
        const voterRegStatusInputs = document.getElementsByClassName('voter-reg-status')
        voterRegStatusInputs.forEach(inputField => {
            inputField.addEventListener('click', clickHandlerToggleContent)
        })
      });
}

export default { init };