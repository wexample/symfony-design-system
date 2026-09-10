The design system carries real time on the browser side. A component that shows something
the server can change — a chat thread, a table, a counter — is expected to keep itself
current rather than wait for the next click, and what it takes to do that lives here rather
than in each app.

This is worth stating because it is not what a design system usually holds. The decision
was made deliberately: being live is not a family of components, it is a capacity that cuts
across them, and cutting a package per capacity puts a seam through every component that
uses it. So the seam is not cut, and `symfony-design-system` requires
`wexample/symfony-live` — an app that takes the design system gets the server half with it.

## What sits where

- **`symfony-loader`** — `LiveUpdatesService`: opens a connection, names it by a topic,
  attaches it to the render node that asked for it, closes it with that node, and
  reconnects with backoff. `MercureLiveUpdatesDriver` (js-api) is what it opens.
- **Here** — `AbstractEntityLiveUpdatesVueMixin`, which subscribes a component to the
  topic of the entity it shows and dispatches incoming payloads to
  `getLiveUpdateHandlers()`; and `AbstractLiveUpdateStatusVueMixin`, which turns the
  registry's connection counts into something a widget can display.
- **`symfony-live`** — the server half: the Mercure hub configuration, the publisher, and
  the subscriber token. It owns the topic grammar and the payload envelope, and it never
  depends on the browser half.

## The two halves have to agree

A topic is `entity/<kebab-entity-name>/<action>/<identifier>`. The server builds it with
`LiveTopicHelper::entity()`, the browser rebuilds the same string segment by segment
through `LiveUpdatesService.topic()`. A change to the grammar is a change on both sides.

An update payload is `{"event": "...", "data": ...}`. A subscriber reads the event name to
decide what to do rather than guess from the shape of the data.

## Nothing happens without a hub

`LiveUpdatesService` has no driver until something gives it one, and it throws rather than
pretend when asked to connect without it. An app that declares no hub therefore pays for
the dependency in autoload and in nothing else: its components render, and they simply do
not update by themselves.
