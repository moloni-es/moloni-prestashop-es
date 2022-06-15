import MoloniSettings from './pages/settings'
import MoloniOrders from './pages/orders'
import MoloniDocuments from './pages/documents'
import MoloniTools from './pages/tools'
import MoloniLogs from './pages/logs'
import MoloniLogin from './pages/login'
import MoloniRegistration from './pages/registration'

$(document).ready(() => {
    window.moloniSettings = new MoloniSettings();
    window.moloniOrders = new MoloniOrders();
    window.moloniDocuments = new MoloniDocuments();
    window.moloniTools = new MoloniTools();
    window.moloniLogs = new MoloniLogs();
    window.moloniLogin = new MoloniLogin();
    window.moloniRegistration = new MoloniRegistration();
});
