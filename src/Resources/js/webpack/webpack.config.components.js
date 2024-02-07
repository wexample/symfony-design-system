const tools = require('./webpack.tools');

// Project level

tools.logTitle('JS App level components');

tools.forEachJsExtAndLocations((srcExt, bundle, location) => {
  tools.addAssetsJsWrapped(
    bundle,
    location,
    'components/',
    srcExt,
    'components'
  );
});

tools.logTitle('JS App level components (forms)');

// Project level
tools.forEachJsExtAndLocations((srcExt, bundle, location) => {
  tools.addAssetsJsWrapped(
    'app',
    location,
    'forms/',
    srcExt,
    'components'
  );
});

