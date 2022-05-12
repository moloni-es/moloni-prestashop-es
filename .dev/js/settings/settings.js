import {LoadAddress} from '../enums/LoadAddress';
import {DocumentStatus} from '../enums/DocumentStatus';

export default class MoloniSettings {
    constructor() {
        this.settingIdPrefix = 'settings_form_';
    }

    startObservers() {
        // Holders
        this.$loadAddressHolder = $('#settings_form_loadAddress_row');
        this.$customLoadAddressHolder = $('#settings_form_custom_loadAddress_row');
        this.$sendDocumentByEmailHolder = $('#settings_form_sendDocumentByEmail_row');

        // Fields
        this.$shippingInfo = $('#' + this.settingIdPrefix + 'shippingInformation');
        this.$loadAddress = $('#' + this.settingIdPrefix + 'loadAddress');
        this.$documentStatus = $('#' + this.settingIdPrefix + 'documentStatus');

        // Actions
        this.$documentStatus
            .on('change', this.onDocumentStatusChange.bind(this))
            .trigger('change');
        this.$shippingInfo
            .on('change', this.onShippingInformationChange.bind(this))
            .trigger('change');
        this.$loadAddress
            .on('change', this.onAddressChange.bind(this))
            .trigger('change');
    }

    onDocumentStatusChange(event) {
        switch (parseInt(event.target.value)) {
            case DocumentStatus.DRAFT:
                this.$sendDocumentByEmailHolder.slideUp(200);
                break;
            case DocumentStatus.CLOSED:
                this.$sendDocumentByEmailHolder.slideDown(200);
                break;
        }
    }

    onShippingInformationChange(event) {
        if (parseInt(event.target.value) > 0) {
            this.$loadAddressHolder.slideDown(200);
        } else {
            this.$loadAddressHolder.slideUp(200);
            this.$loadAddress
                .val(LoadAddress.SHOP)
                .trigger('change');
        }
    }

    onAddressChange(event) {
        if (parseInt(event.target.value) === LoadAddress.CUSTOM) {
            this.$customLoadAddressHolder.slideDown(200);
        } else {
            this.$customLoadAddressHolder.slideUp(200);
        }
    }
}
