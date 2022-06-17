const Encore = require('@symfony/webpack-encore');
const Path = require('path')

const ModuleDir = Path.resolve(__dirname, '..')
const ModuleDevDir = Path.resolve(__dirname);

const DevAppCSS = Path.resolve(__dirname, 'css/app.scss');
const DevAppJS = Path.resolve(__dirname, 'js/app.js');

const DeployDir = Path.resolve(__dirname, '../views');
const DeployAppCSS = Path.resolve(__dirname, '../views/css/');
const DeployAppJS = Path.resolve(__dirname, '../views/js/');

console.log(`Adding ${DevAppCSS}`);

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'production');
}

Encore
  .setOutputPath(DeployDir)
  .setPublicPath('/views')
  .addEntry('js/app', DevAppJS)
  .addStyleEntry('css/app', DevAppCSS)
  .enableBuildNotifications()
  .enableSassLoader()
  .enablePostCssLoader()
  .disableSingleRuntimeChunk()

module.exports = Encore.getWebpackConfig();
