const fs = require('fs');
const path = require('path');
const {execSync} = require('child_process');
const webpack = require('webpack');
const Encore = require('@symfony/webpack-encore');
const FosRouting = require('fos-router/webpack/FosRouting');

const DEFAULT_OUTPUT_PATH = 'public/build/';
const DEFAULT_PUBLIC_PATH = '/build';
const DEFAULT_MANIFEST_PATH = path.resolve(process.cwd(), 'assets', 'encore.manifest.json');
const WRAPPER_ROOT = path.resolve(process.cwd(), 'var', 'tmp', 'encore-manifest', 'wrappers');
const WRAPPER_TEMPLATE = (classPath, className) => `import ClassDefinition from '${classPath}';
appRegistry.bundles.add('${className}', ClassDefinition);
`;

let wrappersPrepared = false;

const COLORS = {
  blue: '34',
  cyan: '36',
  gray: '90',
  green: '32',
  magenta: '35',
  yellow: '33',
  red: '31',
};

function color(text, colorCode) {
  return `\x1b[${colorCode || COLORS.gray}m${text}\x1b[0m`;
}

function logTitle(title, colorCode = COLORS.cyan) {
  console.log('');
  console.log(color(`# ${title.toUpperCase()}`, colorCode));
}

function logPath(label, value, colorCode = COLORS.gray) {
  console.log(`${color(label, colorCode)} ${color(value, COLORS.yellow)}`);
}

function logEntry(action, entry) {
  console.log(
    `${color('•', COLORS.green)} ${color(action, COLORS.blue)} ${color(entry.output, COLORS.yellow)}`
  );
  console.log(
    `    ${color('from', COLORS.gray)} ${color(entry.source, COLORS.gray)}`
  );
}

function configureEncoreBase(options = {}) {
  const env = options.env || process.env.NODE_ENV || 'dev';

  if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(env);
  }

  const isProd = Encore.isProduction();

  if (options.dumpFosRoutes !== false) {
    execSync(options.fosCommand || 'php bin/console fos:js-routing:dump', {
      stdio: 'inherit',
    });
  }

  Encore
    .setOutputPath(options.outputPath || DEFAULT_OUTPUT_PATH)
    .setPublicPath(options.publicPath || DEFAULT_PUBLIC_PATH)
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(options.sourceMaps ?? !isProd)
    .enableVersioning(options.versioning ?? isProd)
    .enableVueLoader(() => {}, {runtimeCompilerBuild: true})
    .addPlugin(new webpack.DefinePlugin({
      __VUE_OPTIONS_API__: true,
      __VUE_PROD_DEVTOOLS__: false,
    }))
    .addPlugin(new FosRouting())
    .enableSassLoader((loaderOptions) => {
      loaderOptions.sassOptions = {quietDeps: true};
    })
    .enableTypeScriptLoader((tsOptions) => {
      tsOptions.onlyCompileBundledFiles = true;
      const compilerOptions = options.tsCompilerOptions || {};

      if (!isProd && options.enableTsSourceMaps !== false) {
        compilerOptions.sourceMap = true;
      }

      tsOptions.compilerOptions = compilerOptions;
    })
    .enableIntegrityHashes(options.integrity ?? isProd);

  Encore.configureWatchOptions((watchOptions) => {
    watchOptions.aggregateTimeout = watchOptions.aggregateTimeout ?? 200;
    watchOptions.poll = watchOptions.poll ?? false;
    watchOptions.ignored = watchOptions.ignored ?? /node_modules/;
  });

  if (typeof options.configureEncore === 'function') {
    options.configureEncore(Encore);
  }

  logTitle(`Encore base configured (${isProd ? 'prod' : 'dev'})`);

  return Encore;
}

function applyManifestEntries(options = {}) {
  const manifestPath = path.resolve(
    process.cwd(),
    options.manifestPath || DEFAULT_MANIFEST_PATH
  );
  const manifest = options.manifest || loadManifest(manifestPath);
  const encore = options.encore || Encore;
  const seenEntries = new Set();

  logTitle(`Manifest ${path.relative(process.cwd(), manifestPath)}`);
  logPath('  Version', `${manifest.version || '?'}`);
  logPath('  Fronts ', `${manifest.frontCount ?? manifest.fronts?.length ?? 0}`);
  logFronts(manifest.fronts || []);

  addAliasesFromManifest(manifest, encore);
  registerCssEntries(manifest, encore, seenEntries);
  registerJsEntries(manifest, encore, seenEntries, options);

  return manifest;
}

function logFronts(fronts) {
  if (!fronts.length) {
    return;
  }

  logTitle('Front paths', COLORS.yellow);
  fronts.forEach((front) => {
    const label = `${front.bundle} (${front.key})`;
    const rel = front.paths?.relative || front.paths?.absolute || '';
    logPath(`  ${label}`, rel);
  });
}

function loadManifest(filePath = DEFAULT_MANIFEST_PATH) {
  if (!fs.existsSync(filePath)) {
    throw new Error(
      `[encore-manifest] Missing manifest at ${filePath}. ` +
      'Run "php bin/console design-system:generate-encore-manifest" first.'
    );
  }

  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function addAliasesFromManifest(manifest, encore) {
  const aliases = manifest.aliases || {};
  const resolvedAliases = {};

  Object.entries(aliases).forEach(([alias, relPath]) => {
    resolvedAliases[alias] = path.resolve(process.cwd(), relPath);
  });

  const aliasesCount = Object.keys(resolvedAliases).length;

  if (aliasesCount) {
    logTitle(`Aliases (${aliasesCount})`, COLORS.blue);
    encore.addAliases(resolvedAliases);
    Object.entries(resolvedAliases).forEach(([alias, target]) => {
      logPath(`  ${alias}`, target);
    });
  }
}

function registerCssEntries(manifest, encore, seenEntries) {
  const cssEntries = manifest.entries?.css || [];

  if (cssEntries.length) {
    logTitle(`CSS entries (${cssEntries.length})`, COLORS.magenta);
  }

  cssEntries.forEach((entry) => {
    if (seenEntries.has(entry.output)) {
      return;
    }

    encore.addStyleEntry(
      entry.output,
      resolveSourcePath(entry.source)
    );
    seenEntries.add(entry.output);
    logEntry('style', entry);
  });
}

function registerJsEntries(manifest, encore, seenEntries, options) {
  const jsEntries = manifest.entries?.js || {};

  const categories = Object.keys(jsEntries);
  if (categories.length) {
    logTitle('JS entries', COLORS.magenta);
  }

  Object.values(jsEntries).forEach((categoryEntries) => {
    (categoryEntries || []).forEach((entry) => {
      if (seenEntries.has(entry.output)) {
        return;
      }

      let entrySource = resolveSourcePath(entry.source);

      if (entry.wrapper) {
        entrySource = buildWrapper(entry, entrySource, options);
      }

      encore.addEntry(entry.output, entrySource);
      seenEntries.add(entry.output);
      logEntry(entry.wrapper ? `entry+wrapper (${entry.wrapper.type})` : 'entry', entry);
    });
  });
}

function resolveSourcePath(source) {
  const absolutePath = path.resolve(process.cwd(), source);

  if (!fs.existsSync(absolutePath)) {
    throw new Error(`[encore-manifest] Missing asset source ${absolutePath}`);
  }

  return absolutePath;
}

function buildWrapper(entry, absoluteSource, options = {}) {
  prepareWrapperRoot();

  const relativeDir = sanitizeRelativeDir(entry.relative);
  const targetDir = path.join(options.wrapperRoot || WRAPPER_ROOT, relativeDir);
  fs.mkdirSync(targetDir, {recursive: true});

  const sourceName = entry.relative && entry.relative.length
    ? path.basename(entry.relative)
    : entry.output;
  const fileName = `${toKebab(sourceName)}.js`;
  const wrapperPath = path.join(targetDir, fileName);
  const importPath = toPosix(absoluteSource);

  fs.writeFileSync(
    wrapperPath,
    WRAPPER_TEMPLATE(importPath, entry.wrapper.className)
  );

  logPath('    wrapper', wrapperPath);

  return wrapperPath;
}

function sanitizeRelativeDir(relativePath = '') {
  if (!relativePath) {
    return '';
  }

  const dir = path.dirname(relativePath);
  return dir === '.' ? '' : dir;
}

function prepareWrapperRoot() {
  if (wrappersPrepared) {
    return;
  }

  fs.rmSync(WRAPPER_ROOT, {recursive: true, force: true});
  wrappersPrepared = true;
}

function toKebab(value) {
  if (!value) {
    return 'index';
  }

  return value
    .replace(/\.[^.]+$/, '')
    .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
    .replace(/[\s_]+/g, '-')
    .replace(/-+/g, '-')
    .toLowerCase();
}

function toPosix(filePath) {
  return filePath.split(path.sep).join('/');
}

module.exports = {
  DEFAULT_MANIFEST_PATH,
  configureEncoreBase,
  applyManifestEntries,
  loadManifest,
};
