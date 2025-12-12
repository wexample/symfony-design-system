## Refonte moderne de la config Encore

- Distinguer clairement la découverte des assets de la config Webpack : créer un script (Node ou commande Symfony) qui scanne les fronts, applique les conventions (alias, catégories, wrappers) et sérialise un manifest JSON versionné pour éviter d’exécuter du PHP à chaque build.
- Consommer ce manifest depuis `webpack.config.ts` en composant des configs ciblées via l’API Encore/webpack moderne (watch options, `addEntry` en boucle, éventuel `webpack-merge`) et offrir des hooks pour que chaque projet puisse étendre/surcharger la config.
- Remplacer les wrappers temporairement écrits sur disque par une factory TS (virtual modules ou DynamicEntryPlugin) afin de générer des modules ES propres tout en conservant l’enregistrement automatique des classes.
- Configurer précisément les loaders (SCSS, Vue, TS) et tirer parti des fonctionnalités actuelles de Webpack 5 (cache persistant, split chunks contrôlés) au lieu de presets implicites.
- Documenter la convention des fronts et synchroniser `tsconfig.json` (paths) avec le manifest pour que les IDE et TypeScript résolvent les alias de la même façon que Webpack.

### Implémentation du manifest (étape 1)

- Nouvelle commande `design-system:generate-encore-manifest` (voir `src/Command/GenerateEncoreManifestCommand.php`) qui s’appuie sur `EncoreManifestBuilder` pour scanner `design_system_packages_front_paths`, générer un manifest versionné (`assets/encore.manifest.json` par défaut) et consigner alias/bundle, entrées CSS/JS et métadonnées de wrapper.
- Le manifest contient `{ version, generatedAt, projectDir, aliases, fronts[], entries }` : chaque front conserve sa clé (numérique ou alias), le bundle cible (`@front` ou `@VendorBundle`), les chemins absolu/relatif et les assets catégorisés (`css`, `js.main`, `js.pages`, `js.config`, `js.components`, `js.forms`, `js.vue`) avec nom de sortie (`@bundle/{css|js}/...`) et info de wrapper (`type`, `className`) prête pour une factory TS.
- L’option `--output` permet de changer le chemin et `--no-pretty` flush un JSON compact pour la CI; la commande crée les dossiers cibles et échoue explicitement si l’écriture du fichier ne fonctionne pas.

### Consommation du manifest côté Encore (étape 2)

- Nouveau module `src/Resources/js/webpack/encore.manifest.js` qui expose `configureEncoreBase()` (équivalent moderne du vieux `webpack.config.js` : runtime, options Vue/TS/Sass, intégrité, FOS routing…) et `applyManifestEntries()` pour lire `assets/encore.manifest.json`, appliquer les alias et enregistrer chaque entrée Encore.
- `applyManifestEntries()` résout les chemins absolus, ignore les doublons, recrée des wrappers JavaScript temporaires (via modules virtuels) pour les entrées `pages/config/components/forms/vue` et inscrit automatiquement la classe dans `appRegistry` comme le faisait `wrapper.js.tpl`.
- Le vendor continue d’exporter un `webpack.config.js` rétro-compatible basé sur ces helpers, mais les apps peuvent désormais piloter leur config directement en important les fonctions (cf. `webpack.config.ts` du projet responsite) pour chaîner leurs propres règles avant/ après `Encore.getWebpackConfig()`.

### Wrappers virtuels (étape 3)

- `encore.manifest.js` dépend maintenant de `webpack-virtual-modules`, injecte systématiquement les wrappers en mémoire (chemins `.encore/virtual/wrappers/**`) et ajoute automatiquement le plugin à Encore.
- Les logs indiquent toujours les wrappers générés (marqués `(virtual)`), ce qui permet de contrôler le chargement sans bruit disque.
- `webpack-virtual-modules` figure dans les peerDependencies du package ; chaque projet doit donc l’installer (devDependency) pour que le bundle puisse le charger.

### Configuration fine des loaders et optimisations (étape 4)

- `configureEncoreBase()` accepte des options `loaders` et `splitChunks` fusionnées avec des valeurs par défaut : Sass (additionalData + quietDeps), CSS modules/esModule, PostCSS (activation explicite), Vue loader (options runtime), TypeScript (transpileOnly, configFile, compilerOptions).
- Le helper active `splitEntryChunks` (chunks `all`, regroupements personnalisables) pour profiter au mieux de Webpack 5 ; le cache persistant sera branché via `Encore.configureCache()` dès qu’une version publique l’exposera.
- Via ces options, chaque projet peut ajuster précisément les loaders (ex. injecter des variables Sass, activer PostCSS, basculer TS en `transpileOnly`, configurer Vue) sans forker la config partagée.

### Alignement IDE / tsconfig (étape 5)

- Nouvelle commande `design-system:sync-tsconfig` (et service `TsconfigPathsSynchronizer`) qui lit le manifest (`aliases`) et synchronise automatiquement `compilerOptions.paths` (ajoute `/*` et normalise les chemins relatifs). Elle est déclenchée automatiquement à la fin de `design-system:generate-encore-manifest` (désactivable via `--no-sync-tsconfig`).
- Les entrées existantes hors manifest (ex. alias projet) sont conservées, tandis que celles provenant des bundles référencés sont mises à jour de façon déterministe et triées.
- Pour des besoins spécifiques, la commande accepte `--tsconfig`/`--manifest` et peut être reliée à des scripts npm/yarn si on veut la lancer hors cycle Symfony.
