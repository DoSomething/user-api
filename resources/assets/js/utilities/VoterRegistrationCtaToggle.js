const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to action on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const unregisteredContent = document.getElementById('voter-reg-cta-unregistered');
    const uncertainContent = document.getElementById('voter-reg-cta-uncertain');
    const value = event.target.value

    if(value === 'unregistered') {
        unregisteredContent.classList.remove('hidden')
        if(!uncertainContent.classList.contains('hidden')) {
            uncertainContent.classList.add('hidden')
        }
    } else if(value === 'uncertain') {
        uncertainContent.classList.remove('hidden')
        if(!unregisteredContent.classList.contains('hidden')) {
            unregisteredContent.classList.add('hidden')
        }
    } else {
        unregisteredContent.classList.add('hidden');
        uncertainContent.classList.add('hidden');
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