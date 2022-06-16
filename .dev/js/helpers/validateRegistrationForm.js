const ValidateRegistrationForm = ({
    email,
    businessType,
    companyName,
    vat,
    country,
    slug,
    password,
    passwordConfirmation,
    serviceTerms,
}) => {
    try {
        if (email === '') {
            throw false;
        }

        if (businessType === '') {
            throw false;
        }

        if (companyName === '') {
            throw false;
        }

        if (vat === '') {
            throw false;
        }

        if (country === '') {
            throw false;
        }

        if (slug === '') {
            throw false;
        }

        if (password === '' || password.length < 6) {
            throw false;
        }

        if (passwordConfirmation === '' || passwordConfirmation.length < 6) {
            throw false;
        }

        if (passwordConfirmation !== password) {
            throw false;
        }

        if (!serviceTerms) {
            throw false;
        }
    } catch (err) {
        return err;
    }

    return true;
}

export default ValidateRegistrationForm;