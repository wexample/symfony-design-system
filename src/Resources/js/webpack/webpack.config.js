const Encore = require('@symfony/webpack-encore');
const isProd = Encore.isProduction();
const webpack = require('webpack');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or sub-directory deploy
  //.setManifestKeyPrefix('build/')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  //.splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!isProd)
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(isProd)

  // Load VueJs.
  .enableVueLoader(() => {}, { runtimeCompilerBuild: true })
  .addPlugin(
    new webpack.DefinePlugin({
      // Drop Options API from bundle
      __VUE_OPTIONS_API__: true,
      __VUE_PROD_DEVTOOLS__: false,
    })
  )

  // enables Sass/SCSS support
  .enableSassLoader()

  // uncomment if you use TypeScript
  .enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  .enableIntegrityHashes(isProd);

// uncomment if you use API Platform Admin (composer req api-admin)
//.enableReactPreset()
//.addEntry('admin', './assets/js/admin.js')

require('./webpack.config.common');
require('./webpack.config.pages');
require('./webpack.config.components');
require('./webpack.config.vues');

module.exports = Encore;
