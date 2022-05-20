import {LoadAddress} from '../enums/LoadAddress';
import {DocumentStatus} from '../enums/DocumentStatus';
import {DocumentType} from '../enums/DocumentType';
import {Boolean} from '../enums/Boolean';

export default class MoloniSettings {
    constructor() {
        this.settingIdPrefix = 'settings_form_';
    }

    startObservers() {
        // Holders
        this.$loadAddressHolder = $('#' + this.settingIdPrefix + 'loadAddress_row');
        this.$customLoadAddressHolder = $('#' + this.settingIdPrefix + 'custom_loadAddress_row');
        this.$sendByEmailHolder = $('#' + this.settingIdPrefix + 'sendByEmail_row');
        this.$billOfLadingHolder = $('#' + this.settingIdPrefix + 'billOfLading_row');

        // Fields
        this.$shippingInfo = $('#' + this.settingIdPrefix + 'shippingInformation');
        this.$loadAddress = $('#' + this.settingIdPrefix + 'loadAddress');
        this.$documentStatus = $('#' + this.settingIdPrefix + 'documentStatus');
        this.$documentType = $('#' + this.settingIdPrefix + 'documentType');
        this.$sendByEmail = $('#' + this.settingIdPrefix + 'sendByEmail');
        this.$billOfLading = $('#' + this.settingIdPrefix + 'billOfLading');

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
        this.$documentType
            .on('change', this.onDocumentTypeChange.bind(this))
            .trigger('change');
    }

    onDocumentTypeChange(event) {
        if (event.target.value === DocumentType.INVOICE_AND_RECEIPT) {
            this.$documentStatus
                .val(DocumentStatus.CLOSED)
                .attr('disabled', true)
                .trigger('change');
        } else {
            this.$documentStatus
                .removeAttr('disabled');
        }
    }

    onDocumentStatusChange(event) {
        switch (parseInt(event.target.value)) {
            case DocumentStatus.DRAFT:
                this.$sendByEmailHolder.slideUp(200);
                this.$billOfLadingHolder.slideUp(200);
                this.$sendByEmail.val(Boolean.NO);
                this.$billOfLading.val(Boolean.NO);

                break;
            case DocumentStatus.CLOSED:
                this.$sendByEmailHolder.slideDown(200);
                this.$billOfLadingHolder.slideDown(200);

                break;
        }
    }

    onShippingInformationChange(event) {
        if (parseInt(event.target.value) > 0) {
            this.$loadAddressHolder.slideDown(200);
        } else {
            this.$loadAddressHolder.slideUp(200);
            this.$loadAddress
                .val(LoadAddress.MOLONI)
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
