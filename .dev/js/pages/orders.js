import Paginator from '../paginator/paginator';
import Filters from '../filters/filters';

export default class MoloniOrders {
    constructor() {}

    startObservers(thisAction) {
        new Paginator();
        new Filters(thisAction);
    }
}
