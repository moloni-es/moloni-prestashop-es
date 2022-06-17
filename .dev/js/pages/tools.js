import ImportProducts from '../tools/imports/importProducts';
import ImportStocks from '../tools/imports/importStocks';
import ExportProducts from '../tools/exports/exportProducts';
import ExportStocks from '../tools/exports/exportStocks';

export default class MoloniTools {
    constructor() {}

    startObservers() {
        $('#import_products_button').on('click', this.importProducts);
        $('#import_stocks_button').on('click', this.importStocks);
        $('#export_products_button').on('click', this.exportProducts);
        $('#export_stocks_button').on('click', this.exportStocks);
    }

    importProducts() {
        let action = $(this).attr('data-href');

        ImportProducts({ action });
    }

    importStocks() {
        let action = $(this).attr('data-href');

        ImportStocks({ action });
    }

    exportProducts() {
        let action = $(this).attr('data-href');

        ExportProducts({ action });
    }

    exportStocks() {
        let action = $(this).attr('data-href');

        ExportStocks({ action });
    }
}
