const Encore = require('@symfony/webpack-encore');
const {
  configureEncoreBase,
  applyManifestEntries,
} = require('./encore.manifest');

configureEncoreBase();
applyManifestEntries();

module.exports = Encore;
