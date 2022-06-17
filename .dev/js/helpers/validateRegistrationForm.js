import MakeRequest from "../tools/makeRequest";

const ValidateRegistrationForm = ({
    $emailElem,
    $businessTypeElem,
    $companyNameElem,
    $vatElem,
    $countryElem,
    $slugElem,
    $passwordElem,
    $passwordConfirmationElem,
    $serviceTermsElem,
    verifySlugAction,
    verifyVatAction,
}) => {
    const verifySlug = async () => {
        let resp = await MakeRequest(action, { page });
        return JSON.parse(resp);
    }

    const verifyVat = async () => {
        let resp = await MakeRequest(action, { page });
        return JSON.parse(resp);
    }

    let email = $emailElem.val();
    let businessType = $businessTypeElem.val();
    let companyName = $companyNameElem.val();
    let vat = $vatElem.val();
    let country = $countryElem.val();
    let slug = $slugElem.val();
    let password = $passwordElem.val();
    let passwordConfirmation = $passwordConfirmationElem.val();
    let serviceTerms = $serviceTermsElem.is(":checked");

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

        if ($passwordConfirmationElem === '' || $passwordConfirmationElem.length < 6) {
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
