## Refonte moderne de la config Encore

- Distinguer clairement la découverte des assets de la config Webpack : créer un script (Node ou commande Symfony) qui scanne les fronts, applique les conventions (alias, catégories, wrappers) et sérialise un manifest JSON versionné pour éviter d’exécuter du PHP à chaque build.
- Consommer ce manifest depuis `webpack.config.ts` en composant des configs ciblées via l’API Encore/webpack moderne (watch options, `addEntry` en boucle, éventuel `webpack-merge`) et offrir des hooks pour que chaque projet puisse étendre/surcharger la config.
- Remplacer les wrappers temporairement écrits sur disque par une factory TS (virtual modules ou DynamicEntryPlugin) afin de générer des modules ES propres tout en conservant l’enregistrement automatique des classes.
- Configurer précisément les loaders (SCSS, Vue, TS) et tirer parti des fonctionnalités actuelles de Webpack 5 (cache persistant, split chunks contrôlés) au lieu de presets implicites.
- Documenter la convention des fronts et synchroniser `tsconfig.json` (paths) avec le manifest pour que les IDE et TypeScript résolvent les alias de la même façon que Webpack.
