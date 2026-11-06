# Un interrupteur et un nombre dans `dynamic-form`

Opened: 2026-11-06
Updated: 2026-11-06
Status: livré
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

## Livré (2026-11-06)

- Norme 2.5.0 : `switch_input`, `checkbox_input` (`checked`, `value` posté quand coché) et `number_input` (`min`, `max`, `step` nombre ou `any`, `stepper`), dans `form.schema.json`. Les pointeurs des composants `switch-input`, `checkbox-input` et `number-input` visaient déjà ces fichiers.
- `FormDefinitionBuilder` : kinds par préfixe de bloc ; `IntegerType` rattaché à `number`, pas de 1 par défaut ; bornes et pas lus dans les `attr`.
- `dynamic-form` : brouillon booléen pour les bascules (`formDefinitionChecked` lit `true`, `1`, `on`…), posté comme une checkbox dessinée (la valeur si coché, rien sinon) ; nombre avec ses bornes et ses boutons. Démo : `form/from-server` (Pages, Public).

## Côté Syrtis

- Les nombres de `SlotConfigsForm` sont déjà des `NumberInputType` : dessinés en nombre sans rien changer.
- Le booléen y est un choix à trois états (`—` = la valeur par défaut du node, Oui, Non). Un interrupteur n'a que deux états : passer à `SwitchInputType` perd « par défaut ». À Syrtis de trancher ; si l'état « par défaut » doit rester, le choix actuel est le bon contrôle.

## « Plus tard », livré (2026-11-06)

L'expression est une extension de champ (`extensions.expression`, ticket `field-extensions.md`) : sur n'importe quel champ à une valeur, un bouton bascule vers l'éditeur du langage donné et retour. Syrtis la pose sur ses configs `allow_expression` (`SlotConfigsForm::EXPRESSION_PATTERN`) et ne dessine plus de texte libre à la place d'un nombre ni d'un éditeur à la place d'un choix.
