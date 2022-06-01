import Paginator from "../paginator/paginator";

export default class MoloniDocuments {
    constructor() {}

    startObservers() {
        new Paginator();
    }
}
