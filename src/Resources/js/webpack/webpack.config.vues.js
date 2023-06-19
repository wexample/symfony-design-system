const tools = require('./webpack.tools');

tools.logTitle('Vues local');

tools.buildAssetsLocationsList('js').forEach((location) => {
  tools.addAssetsJsWrapped(
    location,
    '',
    'vue',
    'vue'
  );
});

tools.logTitle('Vues global');

tools.addAssetsCss(
  __dirname + '/../../css/',
  'vue/',
  'scss'
);

// We have to define manually which css is for vue components.
tools.addAssetsCss(
  __dirname + '/../../css/',
  'forms_themes/vue/',
  'scss'
);
