const $ = require('jquery');

/**
 * Utility script to enable toggling visibility of pre registration for voting 
 * based on birthdate on '/profile/about' form
 */

function togglePreregistrationPrompt() {
    const birthdate = document.getElementById('birthdate').value;
    const preRegisteredContent = document.getElementById('voter-reg-cta-pre-registration');

    const calculateAge = (date) => {
        const now = new Date();
        return now.getFullYear() - date.getFullYear();
    }

    if(birthdate.length === 10 && new Date(birthdate)) {
        const newBirthdate = new Date(birthdate);
        const age = calculateAge(newBirthdate);
        console.log('this is the age', age)
        if(16 <= age && age < 18) {
            preRegisteredContent.classList.remove('hidden');
        }
    }
    else {
        preRegisteredContent.classList.add('hidden');
    }
}

/**
 * Utility script to enable toggling visibility of voter registration 
 * call to actions on '/profile/about' form
 */

function clickHandlerToggleContent(event) {
    const confirmedContent = document.getElementById('voter-reg-cta-confirmed');
    const uncertainContent = document.getElementById('voter-reg-cta-uncertain');
    const unregisteredContent = document.getElementById('voter-reg-cta-unregistered');
    const preRegisteredContent = document.getElementById('voter-reg-cta-pre-registration');
    const value = event.target.value

    if(value === 'unregistered' && window.getComputedStyle(preRegisteredContent).display === 'none') {
        unregisteredContent.classList.remove('hidden')
        uncertainContent.classList.add('hidden')
        confirmedContent.classList.add('hidden')
    } else if(value === 'uncertain') {
        uncertainContent.classList.remove('hidden')
        unregisteredContent.classList.add('hidden')
        confirmedContent.classList.add('hidden')
    } else if(value === 'confirmed') {
        confirmedContent.classList.remove('hidden')
        unregisteredContent.classList.add('hidden')
        uncertainContent.classList.add('hidden')
    } else {
        unregisteredContent.classList.add('hidden');
        uncertainContent.classList.add('hidden');
        confirmedContent.classList.add('hidden')
    }

}

const init = () => {
    $(document).ready(() => {
        const voterRegStatusInputs = document.getElementsByClassName('voter-reg-status')
        voterRegStatusInputs.forEach(inputField => {
            inputField.addEventListener('click', clickHandlerToggleContent)
        })

        const birthdateInput = document.getElementById('birthdate');
        birthdateInput.addEventListener('input', togglePreregistrationPrompt);
      });
}

export default { init };