import Paginator from "../paginator/paginator";
import Filters from "../filters/filters";
import MakeRequest from "../helpers/makeRequest";

export default class MoloniProducts {
    constructor() {
    }

    startObservers({thisRoute, ImportStockRoute, ImportProductRoute}) {
        this.ImportStockRoute = ImportStockRoute;
        this.ImportProductRoute = ImportProductRoute;

        new Paginator();
        new Filters(thisRoute);

        $('.product__row').each((index, rowHtml) => {
            // this.loadRowObservers($(rowHtml));
        });
    }

    loadRowObservers(row) {
        if (!row) {
            return;
        }

        let moloniId = row.attr('data-moloni-id');
        let actionsBtn = row.find('.dropdown-toggle');

        row.find('.import--product').on('click', () => {
            this.doAction(this.ExportProductRoute, moloniId, actionsBtn, row);
        });

        row.find('.import--stock').on('click', () => {
            this.doAction(this.ImportStockRoute, moloniId, actionsBtn, row);
        });
    }

    async doAction(route, productId, actionsBtn, currentrow) {
        actionsBtn.trigger('click');
        currentrow.addClass('product__row--disabled');

        let resp = await MakeRequest(route, {product_id: productId});
        let className = '';

        resp = JSON.parse(resp);
        currentrow.removeClass('product__row--disabled');

        if (resp.valid === 1) {
            let newRow = $(resp.productRow);

            currentrow.replaceWith(newRow);

            newRow.addClass('product__row--new');
            setTimeout(() => {
                newRow.removeClass('product__row--new');
            }, 2000);

            this.loadRowObservers(newRow);
        } else {
            alert(resp.message || 'Request error');

            currentrow.addClass('product__row--error');
            setTimeout(() => {
                currentrow.removeClass('product__row--error');
            }, 2000);

            if (resp.result) {
                console.log(resp.result);
            }
        }
    }
}
