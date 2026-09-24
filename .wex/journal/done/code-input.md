# Composant `otp-input` : saisie d'un code en cases séparées

Opened: 2026-09-24
Updated: 2026-09-24
Author: agent:main (symfony-user)
Done: 2026-09-24

## Pourquoi

`symfony-user` va demander un code à 6 chiffres reçu par mail (2FA), puis un code TOTP. Ce
widget n'a rien de propre aux users (PIN, vérification SMS, code d'invitation) : il appartient
au design system, à côté des autres `assets/components/form/*-input`. Aucun équivalent
n'existe aujourd'hui dans `symfony-design-system` ni dans `symfony-forms`.

## Comportement attendu

- N cases d'un caractère (6 par défaut, configurable).
- Saisie d'un caractère → focus sur la case suivante ; `Backspace` vide la case et recule ;
  flèches gauche/droite naviguent ; clic sélectionne le contenu de la case.
- Coller un code dans n'importe quelle case le répartit sur les cases suivantes.
- Soumission automatique du formulaire quand toutes les cases sont remplies (désactivable).
- Jeu de caractères : chiffres par défaut, alphanumérique en option (codes de secours TOTP).
- Mobile : `inputmode="numeric"` et `autocomplete="one-time-code"`, pour que l'OS propose le
  code reçu.
- Accessibilité : un libellé pour l'ensemble, un `aria-label` par case (« chiffre 3 sur 6 »).

## Point de design à trancher

Le serveur doit recevoir **une seule valeur** (`code=123456`), pas six champs : un input
porteur (caché ou visuellement masqué) tenu à jour par les cases. C'est ce qui rend le
composant utilisable par n'importe quel form type sans logique serveur de recomposition.

## Source legacy (lecture seule)

`NETWORK/archeo/trees/develop-131-fos-user/front/components/double-factor-code-char.ts`
(116 lignes) : un composant par case, index en `data-code-char-index`, soumission via
`app.form.submitWithEvent`. S'en inspirer pour le comportement, pas pour le code : ancien
système de composants, une instance par case, pas de valeur unique.

## Livrables

- `assets/components/form/code-input/` sur le modèle de `password-input` (twig, ts, scss,
  et variante vue si les voisins l'ont).
- Déclaration dans le registre des éléments, section dans une page de
  `symfony-design-system-demo`.
- Si un form type Symfony est nécessaire, voir avec les voisins s'il va ici ou dans
  `symfony-forms` ; `symfony-user` l'utilisera tel quel.

À discuter avec le propriétaire avant de coder, comme toute todo.

## Fait

- Nommé `otp-input` : `code-input` est déjà le champ CodeMirror de `symfony-coding`.
- `assets/components/form/otp-input/` (twig, ts, scss, vue, vue.twig) et
  `js/Helper/OtpInputHelper.ts`. Un seul vrai `<input>` (`autocomplete="one-time-code"`,
  `inputmode`, `pattern`) posé transparent sur les cases, qui ne font que dessiner. Il n'y a
  donc pas d'input porteur à synchroniser, et le collage, l'autofill mobile, Backspace et le
  libellé restent natifs. Aucun `aria-label` par case : les cases sont `aria-hidden` et le
  libellé du champ suffit.
- Options Twig : `length` (6), `alphanumeric` (false, met en majuscules), `auto_submit` (true,
  `requestSubmit()` au caractère qui complète, jamais pendant l'assistance). Côté Vue :
  `length`, `alphanumeric`, `autoSubmit`, `v-model` et l'événement `complete`.
- `symfony-forms` : `OtpInputType` (parent `TextType`, options `length`, `alphanumeric`,
  `auto_submit`) et le bloc `otp_input_widget` du thème. `symfony-user` l'utilise tel quel :
  `->add('code', OtpInputType::class)`.
- Registre (`elements/form/otp-input.yml`), démo dans la page Inputs.
