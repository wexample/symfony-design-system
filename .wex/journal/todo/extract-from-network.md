# Design system: component gaps found in network

Opened: 2026-09-24
Updated: 2026-10-12
Author: agent:archeology

## État au 2026-10-09, pour l'agent design system (agent:main)

Le propriétaire confie la suite de cette todo à l'agent design system. Esprit de la revue : **comprendre l'intention de départ, ne pas sur-migrer** ce que network faisait ; chaque étape se discute avec le propriétaire avant d'être codée.

- **Fait** : étape 4 (`form/tel-input`, `form/checkbox-input`, avec les types de symfony-forms, commit 501f105d), étape 5 (`form/otp-input`, `done/code-input.md`). À côté du périmètre : `dropzone`, `image-crop`.
- **Fait le 2026-10-09 (agent design system)** : 1. `file-input` glisser-déposer + aperçu d'image ; 2. `number-input` coller « 1,5 » (`numberParse`) ; 3. compteur de caractères sur tout champ à `maxlength` (`Field`) ; 6. `period-nav` (année / mois, hash, bornes, événement `change` avec `from`/`to`), démo sur la page Lists. Le jumeau Vue de `file-input` n'a pas le glisser-déposer.
- **Reste** :
  - 7. Champ et liste éditables en ligne.
  - 8. Messages d'entité avec boutons d'action (#58).
  - 9. Variante d'actions de `document-embed` (ouvrir, télécharger, régénérer, envoyer).
  - 10. Galerie d'icônes dans la démo.
  - ~~11. Catalogue `fr` des composants.~~ Fait le 2026-10-12 : les catalogues existaient (23 langues), les 20 clés `fr` manquantes de `common/system.trans.yml` ajoutées. Les autres langues passent par les scripts de traduction.
  - 12. Vérifier les issues listées (#50, #115, #205, #118).

## Liés, ailleurs

- ~~**Formateur de prix JS**~~ fait (2026-10-12) : `numberFormatMoney`, testé contre les sorties PHP ; suffixe HT / TTC côté Twig ; calcul en direct reporté à l'interface de la compta. Ancienne note : identique au PHP (virgule en `fr`, devise, format compact), calcul prix / TVA en direct sur les formulaires, suffixe HT / TTC : `symfony-money/.wex/journal/todo/extract-from-network-sweep.md`, étapes 2 et 3 (le `NumberHelper.ts` du design system n'a que `numberFormat`).
- **Boutons en pied de modale** : symfony-forms le fait désormais (`in_footer` + `form_footer(form)` dans `page_actions`) ; la todo media (bouton « Choisir » en pied de la modale du sélecteur) peut s'en servir : `symfony-media/.wex/journal/todo/extract-from-network.md`.
- **Registre** : réparé le 2026-10-09 (`demo-image-crop` déclaré).
- **Smoke test** : `MOJOE/local/design-system/tests/Scenario/explore.yaml` parcourt les menus en visiteur, membre et admin et signale les 500, les clés de traduction brutes et le JSON illisible (`php -d memory_limit=-1 vendor/bin/phpunit --filter explore tests/Scenario` dans le conteneur). Le 2026-10-09, il a signalé `article.list` (démo composition) et `section.check.items` (démo interactions) : corrigé (listes lues élément par élément).
- Le point d'ensemble de l'extraction : `NETWORK/local/network/.wex/knowledge/readme/archeology/status.md.j2`.


## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It was written by the 2026-09 network archaeology pass. Read it, then discuss it with the owner: every design choice and recommendation below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2` (entry point, order between packages), then `sources.md.j2` (where the legacy code lives: archive repo, branch checkouts, GitLab issues) and the domain page linked below.
> - Pending owner decisions affecting this work are listed in `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/recap.md.j2`, section "Décisions qui t'attendent". Where this todo assumes an answer, treat it as an open question.
> - Safety: `NETWORK/local/network` runs on **production data** (real bookkeeping, real invoices in `var/`, a prod dump in `.wex/mysql/dumps/`) — read its code only, never run anything against it. Anonymize any fixture taken from network (bank exports, FEC, mails contain real names/accounts). Never copy secrets found in its history (Stripe keys, tokens, passwords, private keys).

## Goal

network's components are mostly covered by this package and its demo. This todo lists the generic gaps; paths and rationale are in `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/already-extracted-check.md.j2`, section "Design system (+ demo)". Port the **behaviour** into the current component conventions: run `wex ai::design/rules --formatter javascript-code`. Do not port the Vue options-API `extends` chains or anything Materialize.

## Steps (each = component + demo section + test)

1. `form/file-input`: drag & drop zone (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/js/components/file-drag-zone.ts`, `css/components/file-drag-zone.scss`) and an image-preview variant (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/js/components/form-picture.ts`, theme block `picture_row` in `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/templates/forms_themes/form.html.twig`), refreshed after an ajax submit.
2. `form/number-input`: pasting "1,5" gives 1.5 (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/js/components/float-type.ts`).
3. Character counter on text/textarea (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/js/components/character-counter.ts`, Materialize: rewrite).
4. `form/checkbox-input` and `form/tel-input` (they do not exist yet; the symfony-forms todo adds the PHP types).
5. `form/code-input` (OTP): paste, auto-advance, n digits (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/components/double-factor-code-char.ts`). Used by symfony-user 2FA.
6. Period navigation for collections: year/month prev/next/today, state in the URL hash, invalid values ignored (#74 NaN crash) (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/js/vue/list-yearly.vue`, `list-monthly.vue`, `list-navigation.vue`). Also used by symfony-charts.
7. Inline-editable field + editable list (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/vue/entity/partials/field-editable.vue`, `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/vue/entities-list-editable.vue`); the server contract is in the symfony-api todo (`_newLine`/`_deleted`).
8. Entity messages with optional action buttons (#58) (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/vue/entity/partials/entity-messages.vue`).
9. `document-embed` actions variant: open, download, regenerate, upload (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/templates/components/document-viewer.html.twig`, `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/components/document-viewer.ts`).
10. Demo: icon gallery page under foundations (`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/pages/demo/icons.*`; symfony-template has `icon_list`); review `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/pages/demo/tabs/layouts/widgets` for dashboard widgets.
11. French catalogues for the components (the package ships none). Sources: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/src/Wex/BaseBundle/Resources/translations/messages.fr.yml` and the UI strings in `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/trees/develop-131-fos-user/front/**/*.fr.yml`.
12. Check against issues: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/gitlab/issues/050.md` (modal overflow, loading spinner, confirm before losing a filled form), `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/gitlab/issues/115.md` (tabs API bugs), `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/gitlab/issues/205.md` (global spinner), `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/gitlab/issues/118.md` (img + object-fit).

## Do not

- Charts go to symfony-charts, the Stripe front to symfony-payment-ds (the provider itself is symfony-remote-payment-stripe; there is no symfony-stripe), money formatting to symfony-money.
