> Etat actuel, il reste du code a migrer de /home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/backup/symfony-design-system
> Il vaudrait mieux commencer tout de suite part migrer les ancien test unitaire, il n'y en a pas beaucoup, et reforcer le noyau avec d'autres
> Ensuite migrer ce qui a vocation a etre encore dans ce bundl, quelques extension, subtilités de configuration etc.
> On devrait aussi migrer le reste des templates de base (panel / modal etc) le tout testé.
> Les parties Demo / Test pourraient alle dans un bundle à par symfony-design-system-demo
> Le reste des assets doivent aller dans d'autres bundle symfony-ds-theme-admin / symfony-ds-theme-tailwind / symfony-ds-theme-black, etc..

- Trouver un moyen de feire des "bases" qui ne soit pas en dur (modal / panel etc..)
- Valider l'approche de assets.html.twig: des sections de header identifiées avec des commentaires pour les manipuler ensuite en JS
- Faire plusieurs passes générales du code pour s'assurer de la qualité / cohérence de l'ensemble
- Rédiger des tests
- Passer certaine méthode dans des helpers 
- Faire une pass sur web render node pour s'assurer de la cohérence du package / portabilité vers js