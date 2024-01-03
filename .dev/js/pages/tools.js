import ImportProducts from '../tools/imports/importProducts';
import ImportStocks from '../tools/imports/importStocks';
import ExportProducts from '../tools/exports/exportProducts';
import ExportStocks from '../tools/exports/exportStocks';

export default class Tools {
    constructor() {
    }

    startObservers({
        importProductsAction,
        importStocksAction,
        exportProductsAction,
        exportStocksAction
    }) {
        $('#import_products_button').on('click', ImportProducts.bind(this,importProductsAction));
        $('#import_stocks_button').on('click', ImportStocks.bind(this,importStocksAction));
        $('#export_products_button').on('click', ExportProducts.bind(this,exportProductsAction));
        $('#export_stocks_button').on('click', ExportStocks.bind(this,exportStocksAction));
    }
}
