const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to action on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const content = document.getElementById('voter-reg-cta');

    if(event.target.value === 'unregistered') {
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