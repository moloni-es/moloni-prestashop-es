import MoloniSettings from './settings/settings'
import MoloniOrders from './orders/orders'
import MoloniDocuments from './documents/documents'
import MoloniTools from './tools/tools'
import MoloniLogs from './logs/logs'

$(document).ready(() => {
    console.log('Moloni module loaded');

    window.moloniSettings = new MoloniSettings();
    window.moloniOrders = new MoloniOrders();
    window.moloniDocuments = new MoloniDocuments();
    window.moloniTools = new MoloniTools();
    window.moloniLogs = new MoloniLogs();
});
