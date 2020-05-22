const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to action on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const unRegisteredContent = document.getElementById('voter-reg-cta-unregistered');
    const unCertainContent = document.getElementById('voter-reg-cta-uncertain');
    const value = event.target.value

    if(value === 'unregistered') {
        unRegisteredContent.classList.remove('hidden')
        if(!unCertainContent.classList.contains('hidden')) {
            unCertainContent.classList.add('hidden')
        }
    } else if(value === 'uncertain') {
        unCertainContent.classList.remove('hidden')
        if(!unRegisteredContent.classList.contains('hidden')) {
            unRegisteredContent.classList.add('hidden')
        }
    } else {
        unRegisteredContent.classList.add('hidden');
        unCertainContent.classList.add('hidden');
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