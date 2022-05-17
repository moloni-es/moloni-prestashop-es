const Encore = require('@symfony/webpack-encore');
const Path = require('path')

const ModuleDir = Path.resolve(__dirname, '..')
const ModuleDevDir = Path.resolve(__dirname);

const DevAppCSS = Path.resolve(__dirname, 'css/app.scss');
const DevAppJS = Path.resolve(__dirname, 'js/app.js');

const DeployDir = Path.resolve(__dirname, '../views/compiled');
const DeployAppCSS = Path.resolve(__dirname, '../views/css/');
const DeployAppJS = Path.resolve(__dirname, '../views/js/');

console.log(`Adding ${DevAppCSS}`);

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath(DeployDir)
  .setPublicPath('/views/compiled')
  .addEntry('js/app', DevAppJS)
  .addStyleEntry('css/app', DevAppCSS)
  .enableBuildNotifications()
  .enableSassLoader()
  .enablePostCssLoader()
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()

module.exports = Encore.getWebpackConfig();