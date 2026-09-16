A collection is read once when it appears, and then the world moves on. This is
how it hears about it — and why there is no such thing here as reloading a piece
of a page.

## The short answer

Do not ask the server for a fragment of HTML. Put the collection in a Vue
component that reads it from the API, and tell that component what to listen to.
It asks the API again and redraws itself. Nothing on the page reloads, and the
server stays the only one deciding what the collection holds — which was the
right half of the fragment idea, kept.

The whole of it is one method on a collection component:

```js
// front/pages/app/process/vue/run-table.vue
export default {
  extends: AbstractEntityTable,

  methods: {
    getEntityClass() {
      return ProcessRun;
    },

    getEntitiesFetchParams() {
      return { query: { process: this.processId } };
    },

    getLiveSource() {
      return { entityName: 'process', id: this.processId };
    },
  },
};
```

That is the whole of the runs table: it reads `api/process-run/list?process=…`,
it listens to the process, and it redraws when anything is published there.

## The three ways in

They live on `AbstractEntityCollectionVueMixin`, so any collection has them —
table, bar list, chat. What keeps a collection true and what it looks like are
two separate decisions: the same three ways serve
`collection/bar-list/abstract-entity-bar-list.vue`, one line per row, exactly as
they serve the table. They are not alternatives to weigh once and for all: they
are what a collection's situation happens to offer, and a collection may declare
none, one or several.

**An app event** — `getCollectionRefreshEvents()` returns event names, and the
collection redraws when one is triggered. For a change this page caused itself.
Whoever rings knows nothing of the collection and the collection knows nothing of
who rang; they share a name and that is all. Costs nothing, needs no server.

**A live source** — `getLiveSource()` returns `{entityName, id, event?}`, and the
collection subscribes to that entity's topic. Omit `event` to redraw on anything
published there. This is the one to reach for whenever the server has a topic.

**Polling** — `getPollingIntervalMs()` returns a number of milliseconds. For
where there is no topic, or where a round trip now and then is cheaper than a
subscription held open. It stops while the tab is in the background: a page
nobody is looking at is a page nobody needs read to them.

All four states are on the demo page, *Data › Keeping up*, with buttons to ring
each one by hand — the last section there is a bar list rather than a table,
running on the same mixin.

## The part that is easy to get wrong

**A collection listens to what it is the collection *of*, never to its own rows.**

A process for its runs. A session for its messages. Not each run, not each
message.

The reason is the case you were stuck on: a row that does not exist yet has no
topic, and the row that appears is exactly the one the reader is waiting for. A
page subscribing to the runs it was rendered with can never hear about the run
the button just asked for — which is why that button had to reload the page.
Subscribe to the process instead and the new run arrives like any other change.

So the server has to publish a child on its parent's topic. That is declared on
the entity, once:

```php
class ProcessRun extends AbstractEntity implements LivePublishedWithParentInterface
{
    public function getLiveParents(): array
    {
        return array_filter([$this->getProcess()]);
    }
}
```

`LiveEntityPublishListener` then publishes each change on the entity's own topic
**and** on each parent's, with the same action and the same payload — only the
address is added to. The parent must itself be `#[LiveEntity]`, otherwise the
topic would be an address nothing listens on.

## What a button does now

Nothing, as far as the collection is concerned. It posts, the server writes the
row, the row is published on the parent's topic, the collection hears it and
redraws. The button does not need to know the table exists, and the table does
not need to know a button was pressed.

That is the whole of your case 1, and it is what makes it the same thing as your
case 2.

## What was added for this

- `getLiveSource()` and `getPollingIntervalMs()` on
  `AbstractEntityCollectionVueMixin`, beside the `getCollectionRefreshEvents()`
  that was already there. The chat's live wiring was lifted out of
  `abstract-entity-chat.vue` and now lives there, so the chat is a consumer of
  the common brick rather than its only owner. `getLiveThread()` still works and
  is what the chat calls its source.
- `LivePublishedWithParentInterface` in symfony-live, and the parent topics in
  `LiveEntityPublishListener`.
- `api/process-run/list`, `ProcessRun` as a JS entity and repository, and
  `run-table.vue` in app-board.

## What was refused, and why

**Server-rendered fragments.** Naming a piece of a page, answering it alone, and
swapping it in place would be a second rendering path beside the one that exists,
with its own rules about what gets re-initialised — components, listeners, vue
apps — and every page would then have two ways of being drawn. A collection that
reads its own data has one way, and it is the way tables, trees and chats already
work here.

**`window.location.reload()`, obviously**, including the restrained version that
only fires on a terminal state. It was a reasonable thing to write when nothing
better existed; there is now something better.
