const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to action on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const content = document.getElementById('voter-reg-cta');
    const voterRegWrapper = document.getElementById('voter-reg-wrapper');

    if(event.target.value === 'unregistered') {
        content.classList.remove('hidden')
        voterRegWrapper.classList.remove('form-item')
    } else {
        content.classList.add('hidden')
        voterRegWrapper.classList.add('form-item')
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