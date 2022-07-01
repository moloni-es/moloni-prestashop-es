import DisableAllButtons from "../helpers/disableAllButtons";

export default class Filters {
    constructor(action) {
        this.action = action;

        this.startObservers();
    }

    startObservers() {
        this.$filtersSearchButton = $('#filters_search');
        this.$filtersInputElements = $('[name^=filters]');

        this.$filtersSearchButton.on('click', this.doSearch.bind(this));

        this.$filtersInputElements
            .on('keyup', this.enableSearchButton.bind(this))
            .on('change', this.enableSearchButton.bind(this));
    }

    enableSearchButton() {
        this.$filtersInputElements
            .off('change')
            .off('keyup');

        this.$filtersSearchButton.removeAttr('disabled');
    }

    doSearch() {
        let data = this.$filtersInputElements.serialize();

        DisableAllButtons();

        window.location.href = this.action + "&" + data;
    }
}
