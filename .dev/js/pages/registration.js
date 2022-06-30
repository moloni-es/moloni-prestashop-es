import CreateSlug from "../helpers/createSlug";
import MakeRequest from "../helpers/makeRequest";
import DrawRegistrationErrors from "../helpers/drawRegistrationErrors";

export default class MoloniRegistration {
    constructor() {
        this.registrationIdPrefix = 'MoloniRegistration_';
    }

    startObservers({verifyFormAction}) {
        // Actions
        this.verifyFormAction = verifyFormAction;

        // Holders
        this.$businessTypeNameHolder = $('#registration_form_businessTypeName_row');

        // Form
        this.$form = $('[name=MoloniRegistration]');

        // Fields
        this.$businessType = $('#' + this.registrationIdPrefix + 'businessType');
        this.$companyName = $('#' + this.registrationIdPrefix + 'companyName');
        this.$slug = $('#' + this.registrationIdPrefix + 'slug');

        // Buttons
        this.$formButton = $('#' + this.registrationIdPrefix + 'register');
        this.$verifyButton = $('#verify_registration_form');

        // Actions
        this.$businessType.on('change', this.onBusinessChange.bind(this));
        this.$companyName.on('keyup', this.onCompanyNameChange.bind(this));
        this.$verifyButton.on('click', this.verifyForm.bind(this));

        this.onBusinessChange();
    }

    onCompanyNameChange(event) {
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

    async verifyForm() {
        $('.invalid-feedback').hide().html('');
        $('.is-invalid').removeClass('is-invalid');

        this.$verifyButton.attr('disabled', true);

        let response = await MakeRequest(this.verifyFormAction, this.$form.serialize());
        response = JSON.parse(response);

        if (response.valid) {
            this.$formButton.trigger('click');
        } else {
            DrawRegistrationErrors(response.errors, this.registrationIdPrefix);

            this.$verifyButton.removeAttr('disabled');
        }
    }
}
