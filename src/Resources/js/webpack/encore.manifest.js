const fs = require('fs');
const path = require('path');
const {execSync} = require('child_process');
const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');
const FosRouting = require('fos-router/webpack/FosRouting');
const VirtualModules = require('webpack-virtual-modules');

const DEFAULT_OUTPUT_PATH = 'public/build/';
const DEFAULT_PUBLIC_PATH = '/build';
const DEFAULT_MANIFEST_PATH = path.resolve(process.cwd(), 'assets', 'encore.manifest.json');
const DEFAULT_CACHE_PATH = path.resolve(process.cwd(), '.webpack', 'cache');
const WRAPPER_VIRTUAL_ROOT = path.resolve(process.cwd(), '.encore', 'virtual', 'wrappers');
const WRAPPER_TEMPLATE = (classPath, className) => `import ClassDefinition from '${classPath}';
appRegistry.bundles.add('${className}', ClassDefinition);
`;

let virtualModulesInstance = null;
let pendingVirtualModules = {};

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
  const loaderConfig = mergeDeep({
    sass: {
      additionalData: null,
      sassOptions: {
        quietDeps: true,
      },
    },
    css: {
      modules: false,
      esModule: true,
    },
    postcss: {
      enabled: true,
      postcssOptions: null,
    },
    vue: {
      runtimeCompilerBuild: true,
      loaderOptions: {},
    },
    ts: {
      onlyCompileBundledFiles: true,
      transpileOnly: false,
      configFile: undefined,
      compilerOptions: {},
    },
  }, options.loaders || {});

  const cacheConfig = mergeDeep({
    enabled: true,
    type: 'filesystem',
    directory: DEFAULT_CACHE_PATH,
  }, options.cache || {});

  const splitChunksConfig = mergeDeep({
    enabled: true,
    options: {
      chunks: 'all',
      automaticNameDelimiter: '/',
      minSize: 20000,
      cacheGroups: {},
    },
  }, options.splitChunks || {});

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
    .addPlugin(new webpack.DefinePlugin({
      __VUE_OPTIONS_API__: true,
      __VUE_PROD_DEVTOOLS__: false,
    }))
    .addPlugin(new FosRouting())
    .enableSassLoader((loaderOptions) => {
      const additionalData = loaderConfig.sass.additionalData;

      if (additionalData) {
        if (typeof additionalData === 'function') {
          loaderOptions.additionalData = additionalData(loaderOptions.additionalData || '');
        } else {
          loaderOptions.additionalData = `${additionalData}\n${loaderOptions.additionalData || ''}`.trim();
        }
      }

      loaderOptions.sassOptions = mergeDeep(
        loaderOptions.sassOptions || {},
        loaderConfig.sass.sassOptions || {}
      );
    })
    .enableTypeScriptLoader((tsOptions) => {
      tsOptions.onlyCompileBundledFiles = loaderConfig.ts.onlyCompileBundledFiles !== false;
      tsOptions.transpileOnly = loaderConfig.ts.transpileOnly === true;

      const compilerOptions = mergeDeep({}, loaderConfig.ts.compilerOptions || {});

      if (!isProd && options.enableTsSourceMaps !== false) {
        compilerOptions.sourceMap = true;
      }

      tsOptions.compilerOptions = compilerOptions;

      if (loaderConfig.ts.configFile) {
        tsOptions.configFile = loaderConfig.ts.configFile;
      }
    })
    .enableIntegrityHashes(options.integrity ?? isProd);

  Encore.configureCssLoader((cssOptions) => {
    cssOptions.esModule = loaderConfig.css.esModule !== false;
    cssOptions.modules = loaderConfig.css.modules || false;
  });

  if (loaderConfig.postcss.enabled !== false) {
    Encore.enablePostCssLoader((postCssOptions) => {
      if (loaderConfig.postcss.postcssOptions) {
        postCssOptions.postcssOptions = loaderConfig.postcss.postcssOptions;
      }
    });
  }

  Encore.enableVueLoader(
    (vueLoaderOptions) => {
      Object.assign(vueLoaderOptions, loaderConfig.vue.loaderOptions || {});
    },
    {
      runtimeCompilerBuild: loaderConfig.vue.runtimeCompilerBuild !== false,
    }
  );

  Encore.configureWatchOptions((watchOptions) => {
    watchOptions.aggregateTimeout = watchOptions.aggregateTimeout ?? 200;
    watchOptions.poll = watchOptions.poll ?? false;
    watchOptions.ignored = watchOptions.ignored ?? /node_modules/;
  });

  if (cacheConfig.enabled !== false) {
    Encore.configureCache((cacheOptions) => {
      cacheOptions.type = cacheConfig.type || 'filesystem';
      cacheOptions.cacheDirectory = cacheConfig.directory || DEFAULT_CACHE_PATH;
    });
  }

  if (splitChunksConfig.enabled !== false) {
    Encore.splitEntryChunks((splitOptions) => {
      Object.assign(splitOptions, splitChunksConfig.options || {});
    });
  }

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
  finalizeVirtualModules(encore);

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
        entrySource = buildWrapper(entry, entrySource, options, encore);
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

function buildWrapper(entry, absoluteSource, options = {}, encore = Encore) {
  const wrapperContent = WRAPPER_TEMPLATE(toPosix(absoluteSource), entry.wrapper.className);
  const modulePath = buildWrapperVirtualPath(entry, options);
  pendingVirtualModules[modulePath] = wrapperContent;
  logPath('    wrapper (virtual)', modulePath);

  return modulePath;
}

function buildWrapperVirtualPath(entry, options) {
  const relativeDir = sanitizeRelativeDir(entry.relative);
  const targetDir = path.join(options.virtualWrapperRoot || WRAPPER_VIRTUAL_ROOT, relativeDir);

  return path.join(targetDir, `${toKebab(buildWrapperBaseName(entry))}.js`);
}

function buildWrapperBaseName(entry) {
  if (entry.relative && entry.relative.length) {
    return path.basename(entry.relative);
  }

  return entry.output || 'index';
}

function finalizeVirtualModules(encore, options = {}) {
  const modules = pendingVirtualModules;
  pendingVirtualModules = {};

  if (!Object.keys(modules).length) {
    return;
  }

  virtualModulesInstance = new VirtualModules(modules);
  encore.addPlugin(virtualModulesInstance);
  logTitle('Virtual wrapper modules enabled', COLORS.green);
}

function sanitizeRelativeDir(relativePath = '') {
  if (!relativePath) {
    return '';
  }

  const dir = path.dirname(relativePath);
  return dir === '.' ? '' : dir;
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

function mergeDeep(target = {}, source = {}) {
  const initial = {...target};

  Object.entries(source || {}).forEach(([key, value]) => {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
      initial[key] = mergeDeep(
        typeof initial[key] === 'object' && initial[key] !== null
          ? initial[key]
          : {},
        value
      );
    } else {
      initial[key] = value;
    }
  });

  return initial;
}

module.exports = {
  DEFAULT_MANIFEST_PATH,
  configureEncoreBase,
  applyManifestEntries,
  loadManifest,
};
