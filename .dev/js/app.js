import Settings from './pages/settings'
import Orders from './pages/orders'
import Documents from './pages/documents'
import Tools from './pages/tools'
import Logs from './pages/logs'
import Login from './pages/login'
import PrestashopProducts from "./pages/prestashopProducts";
import MoloniProducts from "./pages/moloniProducts";

$(document).ready(() => {
    window.moloni = {};

    window.moloni.Settings = new Settings();
    window.moloni.Orders = new Orders();
    window.moloni.Documents = new Documents();
    window.moloni.Tools = new Tools();
    window.moloni.Logs = new Logs();
    window.moloni.Login = new Login();
    window.moloni.PrestashopProducts = new PrestashopProducts();
    window.moloni.MoloniProducts = new MoloniProducts();
});
