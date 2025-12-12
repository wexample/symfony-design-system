const fs = require('fs');
const path = require('path');
const {
  loadManifest,
  DEFAULT_MANIFEST_PATH,
} = require('./encore.manifest');

const DEFAULT_TSCONFIG_PATH = path.resolve(process.cwd(), 'tsconfig.json');

function syncTsconfigPaths(options = {}) {
  const manifestPath = path.resolve(process.cwd(), options.manifestPath || DEFAULT_MANIFEST_PATH);
  const tsconfigPath = path.resolve(process.cwd(), options.tsconfigPath || DEFAULT_TSCONFIG_PATH);

  const manifest = options.manifest || loadManifest(manifestPath);
  const tsconfig = readTsconfig(tsconfigPath);

  const compilerOptions = tsconfig.compilerOptions = tsconfig.compilerOptions || {};
  compilerOptions.baseUrl = compilerOptions.baseUrl || '.';
  const paths = compilerOptions.paths = compilerOptions.paths || {};

  Object.entries(manifest.aliases || {}).forEach(([alias, relativePath]) => {
    const aliasKey = ensureAliasKey(alias);
    paths[aliasKey] = [ensureGlobPath(relativePath)];
  });

  compilerOptions.paths = sortObjectKeys(paths);
  writeJson(tsconfigPath, tsconfig);

  return tsconfig;
}

function readTsconfig(tsconfigPath) {
  if (!fs.existsSync(tsconfigPath)) {
    throw new Error(`[tsconfig.paths] Unable to find tsconfig at ${tsconfigPath}`);
  }

  return JSON.parse(fs.readFileSync(tsconfigPath, 'utf-8'));
}

function writeJson(targetPath, data) {
  fs.writeFileSync(targetPath, JSON.stringify(data, null, 2) + '\n');
}

function ensureAliasKey(alias) {
  return alias.endsWith('/*') ? alias : `${alias}/*`;
}

function ensureGlobPath(relativePath) {
  const normalized = relativePath.endsWith('/') ? relativePath : `${relativePath}/`;

  return normalized.endsWith('/*') ? normalized : `${normalized}*`;
}

function sortObjectKeys(object) {
  return Object.keys(object)
    .sort()
    .reduce((acc, key) => {
      acc[key] = object[key];
      return acc;
    }, {});
}

function parseArgs(argv = process.argv.slice(2)) {
  return argv.reduce((acc, arg) => {
    if (arg.startsWith('--manifest=')) {
      acc.manifestPath = arg.split('=')[1];
    } else if (arg.startsWith('--tsconfig=')) {
      acc.tsconfigPath = arg.split('=')[1];
    }

    return acc;
  }, {});
}

if (require.main === module) {
  const options = parseArgs();

  try {
    syncTsconfigPaths(options);
    console.log('[tsconfig.paths] compilerOptions.paths synchronized with Encore manifest.');
  } catch (error) {
    console.error('[tsconfig.paths] Error while updating tsconfig:', error.message);
    process.exitCode = 1;
  }
}

module.exports = {
  DEFAULT_TSCONFIG_PATH,
  syncTsconfigPaths,
};
