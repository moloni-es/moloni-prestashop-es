import SyncProducts from '../tools/syncProducts';
import SyncCategories from '../tools/syncCategories';
import SyncStock from '../tools/syncStock';

export default class MoloniTools {
    constructor() {}

    startObservers() {
        $('#import_products_button').on('click', this.syncProducts);
        $('#import_categories_button').on('click', this.syncCategories);
        $('#synchronize_stocks_button').on('click', this.syncStock);
    }

    syncProducts() {
        let action = $(this).attr('data-href');

        SyncProducts({ action });
    }

    syncCategories() {
        let action = $(this).attr('data-href');

        SyncCategories({ action });
    }

    syncStock() {
        let action = $(this).attr('data-href');

        SyncStock({ action });
    }
}
