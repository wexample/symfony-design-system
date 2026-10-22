# Le remplissage d'une rangée de zones prend le plancher des zones

Opened: 2026-10-06
Updated: 2026-10-06
Author: agent:addon:ai/editor

## Constat

Page Convert de doc-manager : la page (`zone--measure`), une colonne `zone--narrow`
(24rem), puis le remplissage `zone--filler`. En rétrécissant la fenêtre, le remplissage
garde 320 px pendant que la page est écrasée à son plancher et que la rangée déborde.
Mesuré dans le navigateur (rangée `zone zone--split` dans `.layout--body`, `w` 1016,
`scrollWidth` 1032) :

| zone | largeur | flex | min-width |
|---|---|---|---|
| `zone--panel zone--measure` | 320 | `0 1 960px` | 320px |
| `zone--panel zone--narrow convert--aside` | 384 | `0 0 auto` | 0 |
| `zone--panel zone--filler` | 320 | `1 1 0px` | **320px** |

## Cause

`assets/css/shapes/_zone.scss`, la règle des rangées à plancher :

```scss
.zone--split:where(.zone--split--floor, .layout--body > *):where(:has(> .zone ~ .zone:not(.zone--filler)))
  > :where(.zone:not(.zone--narrow, .zone--fit-content)) {
  min-width: var(--zone-floor, var(--zone-body-min, 20rem));
}
```

Le `:has()` écarte bien le remplissage pour décider si la rangée a deux zones, mais le
sélecteur des enfants ne l'écarte pas : `zone--filler` reçoit le plancher (20rem), alors
que sa propre règle (`flex: 1 1 0; min-width: 0`) le veut capable de partir à zéro.

## À faire

- Ajouter `.zone--filler` au `:not(...)` des enfants de cette règle.
- Attendu : en rétrécissant, le remplissage part à zéro d'abord, puis les zones
  rétrécissent jusqu'à leur plancher, puis la rangée défile.

## Hors de ce ticket (à discuter avec l'opérateur)

Une page voudrait que sa zone garde la mesure sans jamais rétrécir (remplissage à zéro,
puis défilement direct), et la question du responsive d'une colonne `zone--narrow`.
