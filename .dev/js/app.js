import MoloniSettings from './settings/settings'

$(document).ready(() => {
    console.log('Moloni module loaded');

    window.moloniSettings = new MoloniSettings();
});
