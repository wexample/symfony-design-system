The actions a row of a table offers — the `actions` cell, in `data_table()` as in the `data-table` vue — are handed over by the server, ready made: `{ href, icon, label, method, token, target, target_options, confirm, rel }`. This page is about how they are written and placed; the cell itself is in *Tables*.

## Written once: TableRowActionHelper

`Wexample\SymfonyDesignSystem\Helper\TableRowActionHelper` writes the three every list has, so that every table draws them with the same icon, the same name, in the same place:

| Call | Icon | What it is |
|---|---|---|
| `open($href, $target = null)` | eye | where the row is read or worked on at length — its page, its editor, or a panel showing it when it has no page |
| `edit($href)` | pencil | a quick edit, always in a panel |
| `delete($href, $token, $title, $message)` | trash | posted with its token, asked first: `$message` says what is lost |
| `deleteIn($href, $target = 'panel')` | trash | a deletion that asks more than a yes — what replaces the row where it is used — in a panel |

What a row offers besides is a `link($href, $icon, $label, $target)` or a `post($href, $token, $icon, $label, $confirm)`, with no `rel`.

```php
$actions[$id] = [TableRowActionHelper::open($this->generateUrl('project_pipeline_editor', $params))];
$actions[$id][] = TableRowActionHelper::delete($deleteUrl, $token, 'Delete '.$pipeline->getTitle(), 'Its runs leave the project.');
```

## The place of each: `rel`

`rel` says what an action is to its row: `open`, `edit`, `delete`. The table sets the actions that carry one in that order — open, edit, the row's own actions, delete last —, whatever order they came in; the ones without keep theirs among themselves. The eye and the pencil thus stand in the same column from one row to the next, however many actions follow. The twig table and the vue one order them alike (`TableRowActionHelper.ts`).

The actions cell takes the width of its icons. Left to itself it sets them at its start: a column of actions centred moves with their number, and a row with three icons no longer lines up with one holding two. A column given `align: 'center'` is centred all the same — it is the column's to say, and only right when every row holds the same actions.

## The title leads where the eye leads: `link`

A column given `link: 'open'` makes each of its cells a link to where the row's `open` action leads, opened the same way — a page, or a panel. A list is read in order, the first the row offers winning: `link: ['open', 'edit']` leads to the eye, or to the pencil on a row that has no eye. Nothing is declared twice: the cell reads the action, so the title and the eye cannot part. A row offering none of them shows the value as text.

The link reads as the value it is, in the text colour, and shows itself a link under the pointer only (`table--link`): a page of titles does not turn into a page of blue. In twig, the value is escaped and never translated.

A row made a link whole is `rowLink` (`row_link`), another thing: the row pressed anywhere, a caret column of its own. `link` keeps the rest of the row as it is — text selected, a status read — and is the one to reach for when the row already has an eye.
