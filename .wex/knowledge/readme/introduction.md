## Installation

### PHP dependencies

Add to `composer.json` and run `composer install`:

```json
"wexample/symfony-design-system": "*",
"wexample/symfony-loader": "*",
"wexample/symfony-routing": "*"
```

Register bundles in `config/bundles.php`:

```php
Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle::class => ['all' => true],
Wexample\SymfonyLoader\WexampleSymfonyLoaderBundle::class => ['all' => true],
Wexample\SymfonyRouting\WexampleSymfonyRoutingBundle::class => ['all' => true],
Wexample\SymfonyTranslations\WexampleSymfonyTranslationsBundle::class => ['all' => true],
```

### Loader configuration

Create `config/packages/wexample_symfony_loader.yaml`:

```yaml
wexample_symfony_loader:
    front_paths:
        front: '%kernel.project_dir%/front/'
        WexampleSymfonyDesignSystemBundle: '%kernel.project_dir%/front/'
    default_color_scheme: light  # or dark
```

Both `front` and `WexampleSymfonyDesignSystemBundle` must point to the app's `front/` directory so that app templates override DS bundle defaults and translations are scanned correctly.

### Routing

Create `config/routes/symfony_routing.yaml`:

```yaml
symfony_routing:
    resource: '@WexampleSymfonyRoutingBundle/Resources/config/routes.yaml'
```

Create `config/routes/symfony_loader.yaml`:

```yaml
symfony_loader:
    resource: '@WexampleSymfonyLoaderBundle/Resources/config/routes.yaml'
```

### Services

In `config/services.yaml`, declare the app home route parameter used by the `ds_home_url()` Twig function:

```yaml
parameters:
    wexample_ds_app_home_route: 'your_home_route_name'
```

### JS dependencies

In `package.json`, add (in addition to the standard encore/webpack stack):

```json
"@wexample/js-api": "*",
"@wexample/js-app": "*",
"@wexample/js-helpers": "*",
"@wexample/symfony-api": "file:vendor/wexample/symfony-api/assets",
"@wexample/symfony-design-system": "file:vendor/wexample/symfony-design-system/assets",
"@wexample/symfony-loader": "file:vendor/wexample/symfony-loader/assets",
"@emoji-mart/data": "^1.2.1",
"@fortawesome/free-brands-svg-icons": "*",
"@fortawesome/free-solid-svg-icons": "*",
"ajv": "*",
"emoji-mart": "*",
"fos-router": "file:vendor/friendsofsymfony/jsrouting-bundle/Resources",
"glob": "*",
"prismjs": "*",
"vue": "^3",
"vue-loader": "*",
"webpack-virtual-modules": "*"
```

### Webpack config

In `webpack.config.mjs`, use `buildEncoreConfig` from the loader. If the Node container does not have PHP available, bypass the PHP build hooks:

```js
import { buildEncoreConfig } from './vendor/wexample/symfony-loader/src/Resources/js/webpack/encore.manifest.js';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const FosRouting = require('./vendor/friendsofsymfony/jsrouting-bundle/Resources/webpack/FosRouting');

const config = buildEncoreConfig({
    clearCache: false,
    generateEncoreManifest: false,
    dumpFosRoutes: false,
});

// Replace FosRouting plugin to skip PHP compilation hooks
const fosIdx = config.plugins.findIndex(p => p.constructor?.name === 'FosRouting');
if (fosIdx !== -1) {
    config.plugins.splice(fosIdx, 1, new FosRouting({}, false));
}

export default config;
```

Pre-generate the PHP artifacts from the PHP container before running webpack (see watch script below).

### tsconfig.json

```json
{
    "ts-node": { "ignore": ["/node_modules/(?!@wexample/)"] },
    "compilerOptions": {
        "target": "ES6",
        "module": "ESNext",
        "moduleResolution": "bundler",
        "baseUrl": ".",
        "paths": {
            "@front/*": ["./front/*"],
            "@WexampleSymfonyDesignSystemBundle/*": ["./front/*"],
            "@wexample/symfony-api/*": ["./vendor/wexample/symfony-api/assets/*"],
            "@wexample/symfony-design-system/*": ["./vendor/wexample/symfony-design-system/assets/*"],
            "@wexample/symfony-loader/*": ["./vendor/wexample/symfony-loader/assets/*"]
        }
    }
}
```

The `ts-node.ignore` pattern allows ts-node to process `@wexample` TypeScript packages during webpack config loading.

> **Note:** `tsconfig.json` is partially overwritten by `php bin/console loader:generate-encore-manifest`. Only the `ts-node` block and manually added path aliases survive. Run the command first, then add custom paths.

### Watch script (split PHP/Node containers)

If PHP and Node run in separate containers, create a host-side orchestration script (e.g. `.wex/bash/watch.sh`):

```bash
docker exec -i "$CONTAINER_FRANKENPHP" php bin/console cache:clear --no-warmup
docker exec -i "$CONTAINER_FRANKENPHP" php bin/console loader:generate-encore-manifest
docker exec -i "$CONTAINER_FRANKENPHP" php bin/console fos:js-routing:dump --target=var/cache/fosRoutes.json --format=json
docker exec -it "$CONTAINER_NODE" /bin/sh -c "cd $APP_DIR && yarn watch"
```

The `package.json` watch script is the direct encore command; the shell script is the host-level orchestration.

### Private layout

Create `front/layouts/private/layout.html.twig` extending the DS dashboard layout:

```twig
{%- extends '@WexampleSymfonyDesignSystemBundle/layouts/dashboard/layout.html.twig' -%}

{%- block layout_config -%}
    {{- render_pass.layoutRenderNode.setDefaultView(_self) -}}
    {{ parent() }}
{%- endblock -%}
```

Add the sidebar menu and page structure blocks as needed. See the DS bundle's `layouts/design_system/layout.html.twig` for the menu pattern using `menu_get_routes_from_controller_namespace`.

Create `front/layouts/private/layout.ts` to instantiate the JS app:

```ts
import App from '@wexample/symfony-loader/js/Class/App';
import AppService from '@wexample/symfony-loader/js/Class/AppService';
import VueService from '@wexample/symfony-loader/js/Services/VueService';
import IconService from '@wexample/symfony-loader/js/Services/IconService';
import ToastService from '@wexample/symfony-loader/js/Services/ToastService';
import ConfirmService from '@wexample/symfony-loader/js/Services/ConfirmService';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import ModalService from '@wexample/symfony-loader/js/Services/ModalService';

class AppCustom extends App {
    getServices(): (typeof AppService | [typeof AppService, any[]])[] {
        return [
            ...super.getServices(),
            VueService, IconService, ToastService,
            ConfirmService, KeyboardService, ModalService,
        ];
    }
}

new AppCustom();
```

Create `front/layouts/private/layout.en.yml` with at minimum:

```yaml
layout:
  logo:
    alt: Your app name
```

### Color palette

Create `front/css/partials/_palette.scss` to override DS bundle defaults:

```scss
$colorPrimary: #your-color;
$colorSecondary: #your-color;
$colorAccent: #your-color;
$colorBlack: #000000;
```

The DS bundle provides `assets/css/partials/_palette.scss` with `!default` values that this file overrides.

### App design system controller

Create `src/Controller/DesignSystem/AppController.php` to expose the `/_design_system/app/` route:

```php
#[Route(name: 'app_design_system_app_', path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/app/')]
final class AppController extends AbstractAppController
{
    #[Route(name: 'index', path: '')]
    public function index(): Response
    {
        return $this->renderPage('index');
    }
}
```

Template at `front/design_system/app/index.html.twig` (path mirrors the controller namespace, stripping `App\Controller\`).

### Known issues / TODOs

- **Translations overwrite bug**: `WexampleSymfonyTranslationsExtension` used to overwrite `translations_paths` instead of merging, erasing paths set by `WexampleSymfonyLoaderExtension`. Fixed by merging in the translations extension. Verify this is resolved in your version.
- TODO: Document how to add additional app-specific DS pages beyond `app/index`.
- TODO: Document the `has_simple_routes` / `has_template_routes` service tags and when `#[TemplateBasedRoutes]` is sufficient vs explicit route declarations.
