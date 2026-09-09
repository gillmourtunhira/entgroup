# The ENT Group WordPress theme

Custom Timber/Twig theme for The ENT Group homepage and About page demo.

## Requirements

- WordPress 6.6+
- PHP 8.2+
- Composer 2
- Node.js 20+
- Secure Custom Fields installed as a WordPress plugin

## Setup

```bash
composer install
npm install
npm run build
```

Activate **The ENT Group**, create and assign a primary menu, then set static pages for Home and About. Insert the **ENT Homepage** and **ENT About Page** patterns into those pages. Contact and appointment values are managed under **Site details** in WordPress.

## Blocks

Page sections are native, server-rendered SCF blocks. Each block lives in `blocks/<slug>/` and contains `block.json` plus a small `render.php` bridge. Fields are defined inline under `acf.fields`; frontend markup lives in `views/blocks/<slug>.twig`. Block-specific Sass and Alpine modules live beside the block as `style.scss` and `view.js`.

To add a block:

1. Copy an existing directory under `blocks/` and give it a permanent `entgroup/<slug>` name.
2. Define its fields in `block.json` and keep field names stable after content is entered.
3. Add the matching Twig file under `views/blocks/`.
4. Add block-specific styles in `style.scss`.
5. When interaction is needed, export an Alpine registration function from `view.js`.
6. Run `npm run build`.

The theme discovers block metadata and styles automatically. `src/js/app.js` discovers block Alpine modules and registers them before `Alpine.start()`. Do not create matching field groups manually in the SCF admin.

For Vite HMR, add this to local `wp-config.php` and run `npm run dev`:

```php
define('ENTGROUP_VITE_DEV_SERVER', 'http://localhost:5173');
```

Do not define the development server in staging or production. Those environments use the generated Vite manifest in `dist/.vite/manifest.json`.

## Content status

Photography, logo, addresses, biographies, credentials and claims are placeholders until approved by The ENT Group.
