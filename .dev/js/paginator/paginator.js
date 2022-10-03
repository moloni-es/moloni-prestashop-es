import DisableAllButtons from "../helpers/disableAllButtons";

export default class Paginator {
    constructor() {
        this.startObservers();
    }

    startObservers() {
        let paginator = $('input[name="moloni_paginator"]');

        if (paginator.length) {
            paginator.on('focusout', this.onLoseFocus);
        }
    }

    onLoseFocus() {
        let pageNumberInput = $(this);

        let page = parseInt(pageNumberInput.val());
        let value = parseInt(pageNumberInput.attr('value'));
        let url = pageNumberInput.attr('psurl');
        let psmax = parseInt(pageNumberInput.attr('psmax'));

        if (page === value) {
            return;
        }

        DisableAllButtons();
        pageNumberInput.attr('disabled', true);

        if (page > psmax) {
            page = psmax;
        }

        if (page <= 0) {
            page = 1;
        }

        pageNumberInput.val(page);

        url = url + '&page=' + page;

        window.location.href = url;
    }
}
