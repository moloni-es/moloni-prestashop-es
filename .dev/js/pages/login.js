export default class MoloniLogin {
    constructor() {}

    startObservers() {
        this.loginButton = $('#login_form_connect');
    }

    enableLogin() {
        this.loginButton.removeAttr('disabled');
    }

    disableLogin() {
        this.loginButton.attr('disabled', true);
    }
}
