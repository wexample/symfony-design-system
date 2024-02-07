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

// Local components css are built in common config.

// Core level

tools.logTitle('JS Core level components');

tools.jsFilesExtensions.forEach((srcExt) => {
  tools.addAssetsJsWrapped(
    tools.designSystemPackageRootDir + 'src/Resources/js/',
    'components/',
    srcExt,
    'components'
  );
});
