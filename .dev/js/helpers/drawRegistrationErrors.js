const DrawRegistrationErrors = (errors, prefix) => {
    errors = errors || {};
    prefix = prefix || '';

    Object.keys(errors).forEach(field => {
        let input = $('#' + prefix + field);
        let errorsElement = input.closest('[class^=moloni-registration]').find('.invalid-feedback');
        let errorsHTML = '';

        input.addClass('is-invalid');

        errors[field].forEach(error => {
            errorsHTML += '<li>' + error + '</li>';
        });

        errorsHTML = '<ul class="m-0">' + errorsHTML + '</ul>'

        errorsElement.html(errorsHTML).slideDown(200);
    });
}

export default DrawRegistrationErrors;
