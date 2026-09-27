import './bootstrap';

import Alpine from 'alpinejs';

import intlTelInput from 'intl-tel-input';

import 'intl-tel-input/styles';

window.Alpine = Alpine;

Alpine.start();


document.addEventListener('DOMContentLoaded', () => {
    const phoneInput = document.querySelector('#telefono');

    if (!phoneInput) {
        return;
    }

    const iti = intlTelInput(phoneInput, {
        initialCountry: 'sv',
        preferredCountries: [
            'sv',
            'gt',
            'hn',
            'ni',
            'cr',
            'mx',
            'us'
        ],
        separateDialCode: true,
        strictMode: true,
        loadUtils: () => import('intl-tel-input/utils'),
    });

    phoneInput.closest('.iti')?.classList.add('w-full');

    window.telefonoIti = iti;

    const oldPhone = phoneInput.value;

    if (oldPhone.startsWith('+')) {
        iti.setNumber(oldPhone);
    }

    /*
     * Mantener Alpine sincronizado con intl-tel-input.
     */
    phoneInput.addEventListener('input', () => {
        const numero = iti.getNumber();

        if (window.Alpine) {
            const component = Alpine.$data(phoneInput.closest('[x-data]'));

            if (component) {
                component.telefono = numero || phoneInput.value;
            }
        }
    });

    /*
     * También actualizar cuando cambia el país.
     */
    phoneInput.addEventListener('countrychange', () => {
        const numero = iti.getNumber();

        if (window.Alpine) {
            const component = Alpine.$data(phoneInput.closest('[x-data]'));

            if (component) {
                component.telefono = numero || phoneInput.value;
            }
        }
    });

    const form = phoneInput.closest('form');

    if (!form) {
        return;
    }

    form.addEventListener('submit', async () => {
        await iti.promise;

        if (iti.isValidNumber()) {
            phoneInput.value = iti.getNumber();
        }
    });
});