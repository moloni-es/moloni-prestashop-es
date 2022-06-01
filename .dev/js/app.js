import MoloniSettings from './pages/settings'
import MoloniOrders from './pages/orders'
import MoloniDocuments from './pages/documents'
import MoloniTools from './pages/tools'
import MoloniLogs from './pages/logs'

$(document).ready(() => {
    console.log('Moloni module loaded');

    window.moloniSettings = new MoloniSettings();
    window.moloniOrders = new MoloniOrders();
    window.moloniDocuments = new MoloniDocuments();
    window.moloniTools = new MoloniTools();
    window.moloniLogs = new MoloniLogs();
});
