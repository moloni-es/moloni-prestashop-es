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
        let element = $(this);
        let form = element.closest('form');

        let page = parseInt(element.val());
        let value = parseInt(element.attr('value'));
        let psmax = parseInt(element.attr('psmax'));

        if (page === value) {
            return;
        }

        element.attr('disabled', true);

        if (page > psmax) {
            page = psmax;
        }

        if (page < 0) {
            page = 1;
        }

        element.val(page);
    }
}
