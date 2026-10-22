## The two faces of a package that ships assets

A PHP package of this stack that carries front-end code — this one, `symfony-loader`, `symfony-api`, `symfony-content` — is reached from two sides at once, and an application must wire both:

- **composer**, as `vendor/wexample/<package>`;
- **npm**, as `@wexample/<package>`, whose source is the same package's `assets/` directory, declared in the app's `package.json`.

The composer side is linked into the live source by the `composer::service/install_local` wex hook. The npm side is *not* handled by the node hook: `node::service/install_local` links the javascript suite (`/var/www/javascript-dev/wexample/*`, the `js-*` packages) and nothing else. What lands under `node_modules/@wexample/<package>` is therefore whatever yarn did with the `package.json` line — and **yarn 1 copies a `file:` dependency where it links a `link:` one**.

Declare them as `link:`, never `file:`:

```json
"@wexample/symfony-loader": "link:vendor/wexample/symfony-loader/assets"
```

## Why a copy is worse than it looks

A copy under `node_modules` is not read by the build. `symfony-loader` generates the webpack aliases and the tsconfig `paths` from one list (its `EncoreManifestBuilder` and `TsconfigPathsSynchronizer` services), and both send `@wexample/<package>/…` to `vendor/…/assets/` — the live tree. So an app builds from the source for months while the copy sits frozen at its last `yarn install`, and nobody notices.

It is read in exactly one case. When a file mapped by `paths` **does not exist** — a file moved, renamed or deleted in the package — TypeScript does not fail: it falls back to plain node resolution, finds the copy, and from there follows the copy's own relative imports into the whole frozen package. The type-check then reports errors in files nobody edited, against signatures the live code changed months ago. The error names `node_modules/@wexample/<package>/…` and looks like a broken install; it is a stale copy standing in for a missing file.

With `link:` the fallback lands on the same live tree the aliases point to, and a missing file says so.

It has happened. When components were moved from `components/modal.ts` to `components/modal/modal.ts`, two pages of `symfony-loader-testing` kept importing the old path and built without a word for days: the copy still had the file. The day the copies became links, the build named the two lines — which is the behaviour to want, and the reason the switch is not optional.

## What still has to happen

`link:` only creates the symlink when yarn installs. After switching a `package.json` from `file:` to `link:`, run `yarn install` in the app's node container: it rewrites the `yarn.lock` entries and replaces the copied directories with links. The `node::service/refresh_lock` wex hook does exactly this.

The `js-*` packages keep the hook rather than `link:` on purpose: their manifests say `"*"` because they are published, and an app without the local suite must still be able to install them from the registry. The four assets packages have no such life outside their PHP package, which is why `link:` fits them and nothing is lost.
