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

## Hero carousel and bookings

The hero's original fields are retained as slide one. Add further slides under
**Additional slides** in the SCF block settings. Navigation is manual; there is
automatic rotation only when the **Autoplay** checkbox is enabled (six seconds
per slide). Autoplay pauses on hover, keyboard focus, and hidden browser tabs;
it is disabled for reduced-motion preferences. A Play/Pause control is available.
In the editor all slides are shown, without Alpine.

Insert **ENT Booking Request** into the homepage (the starter patterns now
include it). Link appointment buttons to that page's `#booking` anchor. Existing
pages and explicitly configured booking links are not overwritten by code
updates.

Requests appear under **Bookings** for administrators. Records are private,
excluded from public queries and REST, and contain contact details, service,
preferred clinic, preferred date, an optional message (up to 2,000 characters),
contact consent time, and review status. Service and clinic choices are defined
once in `Bookings::services()` and `Bookings::clinics()` and validated on submission.
Older records display “Not provided” for these new fields. Staff must contact the patient;
submission never reserves a slot or sends a confirmation email. No notifications
are configured yet, so staff need to check this screen.

The handler validates the nonce, contact details, date and consent, uses a
honeypot, and limits repeated requests per email for five minutes. Exclude pages
containing the form from full-page caching so form nonces stay fresh.
Decide the operational retention period and staff access before public launch.

The booking registration currently resides in `src/php/Bookings.php`; switching
themes hides its admin interface but does not delete saved records. Move that
class and its hooks to a site plugin when deploying independently of this theme.

Run `php tests/booking-handler.php` for isolated handler checks.

## Icons and block styling

Font Awesome Free solid icons are bundled locally by Vite for both the frontend
and editor; no external kit or CDN is required. Service cards have an SCF **Icon**
selector, with automatic specialty icons for existing content. Testimonials have
an optional **Rating** field: leave it blank unless a patient supplied a rating.
Booking contact details use the phone and opening hours configured in theme options.
Block-specific styles remain in `blocks/<slug>/style.scss`.

## About-page blocks

- **Media and Content:** keep Companion content set to Image for the existing
  layout, or choose Purpose / value cards and add mission, vision or value entries.
  Each entry has a title, description and Font Awesome icon. Companion position
  controls the image/card panel side on desktop; mobile shows the copy first.
- **Doctors:** an optional section introduction and verified qualifications are
  available alongside the existing portrait, role, biography and profile link.
- **Call to Action:** uses a centered teal panel with an optional secondary link.
  Existing primary links and the default booking destination are preserved.

Run `php tests/about-blocks.php` for isolated template checks.

The **ENT About Page** starter pattern uses the reference-style variants:

- Media and Content → **Layout: About intro** for the split H1/image hero,
  two links and optional approved badge value/label. Use only one H1 per page.
- Media and Content → **Layout: Purpose** puts the heading left and body copy
  right, with three compact icon features below the body. It ignores image and
  companion positioning. Existing Standard layouts remain unchanged.
- Doctors → **Heading layout: Heading left / intro right**; use Specialty detail
  for the focus area and the existing link field for a consultation/profile link.
- CTA → **Layout: Full-width strip**, with Location as the action icon.

Pattern changes apply to new insertions only. Set these options on existing page
blocks to adopt the layouts; saved page content is not automatically replaced.

## Content status

Photography, logo, addresses, biographies, credentials and claims are placeholders until approved by The ENT Group.
