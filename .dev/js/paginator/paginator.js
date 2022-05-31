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

        let page = parseInt(element.val());
        let value = parseInt(element.attr('value'));
        let url = element.attr('psurl');
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

        let form = $('<form>');

        form.attr('action', url);
        form.attr('method', 'get');

        $('body').append(form);

        form.submit();
    }
}
