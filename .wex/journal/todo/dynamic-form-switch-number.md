# Un interrupteur et un nombre dans `dynamic-form`

Opened: 2026-11-06
Updated: 2026-11-06
Author: agent:main (Syrtis)

## Pourquoi

La norme json-schema des formulaires n'a ni kind booléen ni kind nombre : `SwitchInputType` et `NumberInputType` arrivent dans `dynamic-form` en `text`. Un booléen s'y écrit `false`, un nombre sans contrôle ni flèches.

Premier cas : l'onglet Configuration de l'éditeur de slot Syrtis (`SlotConfigsForm`, un champ par config du node — `streaming`, `aggregate`, `timeout`, `max_output_tokens`…). En attendant, Syrtis y dessine un booléen comme un choix Oui / Non (`1` / `0`) et un nombre comme un texte.

## À faire

- Kind `switch` (ou `checkbox`) : la valeur `true` / `false`, postée comme le thème Twig la poste ; le libellé à côté.
- Kind `number` : `min`, `max`, `step` repris des `attr` du champ.
- `FormDefinitionBuilder` les reconnaît par leur préfixe de bloc ; `dynamic-form` les dessine avec les composants existants (`switch`, `form--input` de type number).

## Plus tard, même formulaire

Une config qui peut être une valeur choisie **ou** une expression (un modèle du catalogue, ou `{{ … }}` qui le nomme) : un select qui accepte aussi un texte libre — ou un bouton qui bascule le champ en éditeur de template. Aujourd'hui, Syrtis montre le select tant que la valeur en est une, l'éditeur Jinja quand elle est déjà une expression ; passer d'une valeur à une expression n'est pas possible depuis le select.
