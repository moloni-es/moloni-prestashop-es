import MoloniSettings from './settings/settings'
import MoloniOrders from './orders/orders'
import MoloniDocuments from './documents/documents'

$(document).ready(() => {
    console.log('Moloni module loaded');

    window.moloniSettings = new MoloniSettings();
    window.moloniOrders = new MoloniOrders();
    window.moloniDocuments = new MoloniDocuments();
});
