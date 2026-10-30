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
