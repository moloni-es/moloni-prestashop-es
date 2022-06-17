import CreateSlug from "../helpers/createSlug";
import ValidateRegistrationForm from "../helpers/validateRegistrationForm";

export default class MoloniRegistration {
    constructor() {
        this.registrationIdPrefix = 'MoloniRegistration_';
    }

    startObservers({verifySlugAction, verifyVatAction}) {
        // Actions
        this.verifySlugAction = verifySlugAction;
        this.verifyVatAction = verifyVatAction;

        // Holders
        this.$businessTypeNameHolder = $('#registration_form_businessTypeName_row');

        // Fields
        this.$email = $('#' + this.registrationIdPrefix + 'email');
        this.$businessType = $('#' + this.registrationIdPrefix + 'businessType');
        this.$companyName = $('#' + this.registrationIdPrefix + 'companyName');
        this.$vat = $('#' + this.registrationIdPrefix + 'vat');
        this.$country = $('#' + this.registrationIdPrefix + 'country');
        this.$slug = $('#' + this.registrationIdPrefix + 'slug');
        this.$password = $('#' + this.registrationIdPrefix + 'password_first');
        this.$passwordConfirmation = $('#' + this.registrationIdPrefix + 'password_second');
        this.$serviceTerms = $('#' + this.registrationIdPrefix + 'serviceTerms');
        this.$registerButton = $('#' + this.registrationIdPrefix + 'register');

        // Actions
        this.$businessType.on('change', this.onBusinessChange.bind(this));
        this.$companyName.on('keyup', this.onCompanyNameChange.bind(this));

        // Verify form
        this.$email
            .add(this.$businessType)
            .add(this.$companyName)
            .add(this.$vat)
            .add(this.$country)
            .add(this.$slug)
            .add(this.$serviceTerms)
            .on('change', this.verifyForm.bind(this));
        this.$password
            .add(this.$passwordConfirmation)
            .on('keyup', this.verifyForm.bind(this));

        this.onBusinessChange();
        this.verifyForm();
    }

    onCompanyNameChange() {
        if (this.$companyName.val() !== '') {
            this.$slug.val(CreateSlug(event.target.value));
            this.$slug.trigger('change');
        }
    }

    onBusinessChange() {
        if (this.$businessType.val() == 'custom') {
            this.$businessTypeNameHolder.fadeIn(200);
        } else {
            this.$businessTypeNameHolder.fadeOut(200);
        }
    }

    verifyForm() {
        let valid = ValidateRegistrationForm({
            $emailElem: this.$email,
            $businessTypeElem: this.$businessType,
            $companyNameElem: this.$companyName,
            $vatElem: this.$vat,
            $countryElem: this.$country,
            $slugElem: this.$slug,
            $passwordElem: this.$password,
            $passwordConfirmationElem: this.$passwordConfirmation,
            $serviceTermsElem: this.$serviceTerms,
            verifySlugAction: this.verifyVatAction,
            verifyVatAction: this.verifyVatAction,
        });

        if (valid) {
            this.$registerButton.removeAttr('disabled');
        } else {
            this.$registerButton.attr('disabled', true);
        }
    }
}
