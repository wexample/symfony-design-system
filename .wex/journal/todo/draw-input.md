# Un champ de tracé à la main (`draw-input`)

Opened: 2026-10-08
Updated: 2026-10-08
Author: agent:main

## Pourquoi

Décidé avec le propriétaire en construisant symfony-signature : la signature d'une personne est d'abord une image téléversée, recadrée (`image-crop`) et rangée dans sa médiathèque. La tracer à la main vient ensuite.

## Forme retenue

Un composant de formulaire du DS, pas un package : un canevas où l'on trace, qui produit une image (PNG, fond transparent), envoyée comme n'importe quel fichier puis rangée par media-ds comme un média. symfony-signature-ds l'offrira à côté du dépôt d'image.

Un `symfony-draw` ne se justifierait que si le besoin dépasse la signature : annotations sur un document, tableau blanc. À réévaluer à ce moment-là.

## À voir

- Pointeur, souris et doigt (Pointer Events), épaisseur qui suit la vitesse, effacer, recommencer.
- Rogner l'image au tracé avant l'envoi, sans repasser par `image-crop`.
- Son JSON schema (`component-schemas.md`).
