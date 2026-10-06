# Rendu markdown : résoudre les images, et dessiner celles qui manquent

Opened: 2026-10-06
Updated: 2026-10-06
Author: agent:addon:ai/editor

## Contexte

`markdown_ds` (`DesignSystemMarkdownService`) rend les images avec leur `src` tel
qu'écrit. Un document lu dans une page (doc-explorer du board) a des images relatives à
son dossier : le navigateur les lit contre l'adresse de la page, et chacune fait un 404 —
qu'elle existe ou non.

## Provisoire en place

doc-explorer (bundle HH) post-traite le html rendu avec un filtre Twig
`document_images` (`src/Twig/DocumentImagesExtension.php`) : une image trouvée dans l'app
pointe vers la route du board `app_file_raw`, une image manquante devient
`<span class="text-empty">Image not found: …</span>`, sans requête. Regex sur le html,
faute de point d'accroche dans le rendu.

## Demandé

1. Un point d'accroche au rendu des images : une option de `toHtml` / `markdown_ds`, une
   fonction `resolveImage(string $src): ?string` — l'URL à utiliser, ou null quand
   l'image est introuvable.
2. Un dessin DS pour une image introuvable (son `alt`, le chemin cherché, un ton
   discret), à la place de l'`<img>`, sans aucune requête.

Une fois en place, doc-explorer passe son résolveur et le filtre disparaît.
