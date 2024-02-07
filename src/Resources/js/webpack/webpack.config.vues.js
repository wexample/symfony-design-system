const tools = require('./webpack.tools');

tools.logTitle('Vues app level');

tools.forEachFrontPath((bundle, location) => {
  tools.addAssetsJsWrapped(
    'app',
    location,
    '',
    'vue',
    'vue'
  );
});

tools.logTitle('Vues core');

tools.addAssetsCss(
  tools.designSystemPackageRootDir + 'front/css/',
  'vue/',
  'scss'
);

// We have to define manually which css is for vue components.
tools.addAssetsCss(
  tools.designSystemPackageRootDir + 'front/css/',
  'forms_themes/vue/',
  'scss'
);
