import CreateSlug from "../helpers/createSlug";
import ValidateRegistrationForm from "../helpers/validateRegistrationForm";

export default class MoloniRegistration {
    constructor() {
        this.registrationIdPrefix = 'MoloniRegistration_';
    }

    startObservers() {
        // Holders
        this.$businessTypeNameHolder = $('#registration_form_businessTypeName_row');
        this.$passwordError = $('#registration_form_password_error');

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
        this.$password.on('keyup', this.onPasswordChange.bind(this));
        this.$passwordConfirmation.on('keyup', this.onPasswordChange.bind(this));
        this.$companyName.on('keyup', this.onCompanyNameChange.bind(this));

        // Verify form
        this.$email.on('change', this.verifyForm.bind(this));
        this.$businessType.on('change', this.verifyForm.bind(this));
        this.$companyName.on('change', this.verifyForm.bind(this));
        this.$vat.on('change', this.verifyForm.bind(this));
        this.$country.on('change', this.verifyForm.bind(this));
        this.$slug.on('change', this.verifyForm.bind(this));
        this.$password.on('change', this.verifyForm.bind(this));
        this.$passwordConfirmation.on('keyup', this.verifyForm.bind(this));
        this.$serviceTerms.on('change', this.verifyForm.bind(this));

        this.onBusinessChange();
        this.onPasswordChange();
        this.verifyForm();
    }

    onCompanyNameChange() {
        if (this.$companyName.val() !== '') {
            this.$slug.val(CreateSlug(event.target.value));
        }
    }

    onPasswordChange() {
        let password = this.$password.val();
        let confirmPassword = this.$passwordConfirmation.val();

        if (password !== '' && confirmPassword !== '') {
            if (password === confirmPassword) {
                this.$passwordError.slideUp(200);
            } else {
                this.$passwordError.slideDown(200);
            }
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
            email: this.$email.val(),
            businessType: this.$businessType.val(),
            companyName: this.$companyName.val(),
            vat: this.$vat.val(),
            country: this.$country.val(),
            slug: this.$slug.val(),
            password: this.$password.val(),
            passwordConfirmation: this.$passwordConfirmation.val(),
            serviceTerms: this.$serviceTerms.is(":checked"),
        });


        if (valid) {
            this.$registerButton.removeAttr('disabled');
        } else {
            this.$registerButton.attr('disabled', true);
        }
    }
}
