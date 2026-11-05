# Un champ à sa propre largeur parmi les colonnes (`form--fit`)

Opened: 2026-11-05
Updated: 2026-11-05
Author: agent:main (Syrtis)

## Pourquoi

Les colonnes d'un formulaire (`form--columns`, `form--columns-2`) donnent à chaque champ la même largeur. Un champ court — une couleur, une icône, un nombre — y prend autant de place qu'un titre, alors qu'il se lit en un coup d'œil à côté de lui : `[C][Titre                 ]`.

Premier cas : `SlotForm` de Syrtis, la couleur du slot devant son titre, dans l'onglet Configuration de l'éditeur de slot (`dynamic-form`). Aujourd'hui en deux colonnes égales.

## À faire

- Une classe de ligne `form--fit` (`row_attr`), comme `form--span` : le champ prend la largeur de son contenu, et le suivant le reste de la ligne. Sans doute une ligne en `grid-template-columns: auto 1fr` pour la paire, ou un `display: flex` local, à trancher avec `_layout.scss`.
- `FormDefinitionBuilder` la transmet (`fit: true`), `dynamic-form` la pose sur la ligne, comme `span`.
- `color-input` en ligne : sa pastille seule suffit à cette largeur, son hex sous le pointeur ou au focus — à voir avec son composant.
