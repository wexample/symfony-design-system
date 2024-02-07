const tools = require('./webpack.tools');

tools.logTitle('JS : Pages');

tools.forEachJsExtAndLocations((srcExt, bundle, location) => {
  tools.addAssetsJsWrapped(
    location,
    'pages/',
    srcExt,
    'pages',
    (srcFile) => {
      // If first letter is a capital, this is an included class.
      return !tools.fileIsAClass(srcFile.file) && srcFile;
    }
  );
});
