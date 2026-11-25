# Saying what an action did

What came of an action — saved, sent, refused — is said in a toast, once, on the page the user lands on. Never in the address: a `?success=true` or `?done=sealed` read by the template says it again on every reload, in every shared link and in the history, and leaves the page to parse its own URL.

## After a redirect: a flash

The controller adds a Symfony flash before it redirects; the `toast-stack` of the dashboard layout reads the flashes of the four types it knows (`success`, `error`, `warning`, `info`) on the next page, translates them and shows each as a toast. Read once, then gone.

```php
$this->addFlash('success', [
    'type' => 'success',
    'message' => 'WexampleSymfonySignatureDsBundle.pages.signature.envelope::done.sealed',
]);

return $this->redirectToRoute(SignatureRoute::ENVELOPE, ['id' => $id]);
```

- The message is a translation key, written with its full domain: the toast stack is drawn by the layout, where a page's `@page::` does not resolve.
- `action` (`{ label, href }`, the label a key too) adds a button to the toast.
- A controller saying several outcomes keeps a small `notify(type, message)` beside its actions rather than repeating the array.

Nothing to add to the template: any layout extending `layouts/dashboard` already has the stack.

## Without leaving the page: the toast service

A script that acted in place — a request answered in the background, an agent's tool — calls the `toast` service of the loader:

```ts
this.app.services.toast.show({ type: 'success', message: this.trans('@component::saved') });
```

## When a message belongs in the page

A message in the page (`message_success`, `message_warning`…) is for a state the page is in for as long as it lasts — an envelope already sealed, an account not confirmed —, read from the data, not from how the user got there.

## Which one: toast, message, banner

Four ways to tell the reader something, each for one kind of thing — the wrong one is noise:

| What it says | Where | How long | Use |
|---|---|---|---|
| What came of an action — saved, sent, refused | a **toast**, over the page | a few seconds, once | flash, or the `toast` service |
| A state of the page or of one of its parts — a record sealed, a step that failed, a value over its limit, a form's warning | a **message** in the content, beside what it is about | as long as the state lasts | `message_info`, `message_warning`, `message_error`… — the vue `message` |
| What a field refused | under the **field** | until corrected | the form's errors |
| A state of the **application** the reader must not miss, whatever the page — offline, saves waiting, a maintenance coming, the environment not being production | a **banner**, across the layout | until it is no longer true | the `banner` service |

## The banner: the application's state, never a page's

A banner is a band across the layout, out of the page's content: it says something about the whole application, true on every page — the connection lost (ConnectionStatusNoticeService), writes not yet saved (PendingWritesService), a service about to be interrupted, an environment that is not production. It is raised by a service (`this.app.services.banner.show({ type, message })`, `dismiss()`) into the layout's place for it, and goes when the state ends.

It is **not** a message: never in a page's content, a block, a form, a list's row or a widget, never for what a page or a step says of itself. A step that failed, a figure over its cap, a field's advice, a preview notice on one page — all are messages (`message_warning(…)`, `<message type="warning">`), set beside what they are about. A banner in the content reads as an alarm the whole application raises: used for a page's detail, it drowns the ones that matter.
