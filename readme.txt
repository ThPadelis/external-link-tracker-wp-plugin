=== External Link Tracker ===
Contributors: ptheodosiou
Tags: links, external links, analytics, outbound, tracking, click tracking
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 5.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track outbound link clicks in real time. Record clicks via client-side JavaScript and analyze them with Link and Domain views.

== Description ==

External Link Tracker records when visitors click links that leave your site. Clicks are sent from the browser and stored in your WordPress database. In the admin you can view reports by link URL or by domain, filter by date range, and see which pages each link was clicked from.

= Features =

* Tracks left click, Ctrl/Cmd+click, and middle-click on external links
* Link report: URL, anchor text, source page, click count
* Domain report: aggregated clicks per domain
* Date range filters and pagination
* No external services; data stays on your site

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install via WordPress admin (Plugins → Add New).
2. Activate the plugin via the Plugins screen.
3. Use the "External Link Tracker" menu in the admin to view reports.

== Frequently Asked Questions ==

= Which links are tracked? =

Only links that point to a different host (external/outbound). Links to the same site, mailto, tel, and javascript are ignored.

= Is data sent to an external server? =

No. Clicks are sent to your own site via the WordPress REST API and stored in your database.

= What data is stored? =

For each outbound link click, the plugin stores in your database: the link URL, anchor text, the page URL where the click happened, the post ID (if any), the link’s domain, and the click time. Data is stored only on your server. Consider mentioning this in your site’s privacy policy if you use the plugin.

== Plugin directory icons ==

* icon-128x128.png
* icon-256x256.png

== Screenshots ==

1. Reports screen – Link view
2. Reports screen – Domain view
3. Empty state
4. Admin menu placement
5. Date filters
6. Sorting

== Credits ==

The admin interface uses the following libraries (built from source, GPL-compatible):

* Vue.js (MIT) – https://vuejs.org/
* Feather Icons (MIT) – https://feathericons.com/

= Source code for minified admin assets =

The admin UI is built from human-readable source in the `admin/spa/` directory (Vue 3, Vite). The minified files in `admin/dist/` are produced by running `npm ci` and `npm run build` from `admin/spa/`. Source files (`.vue`, `.js`, `package.json`, `vite.config.js`) are included in the plugin package so the build is reproducible and the minified output can be verified against source.

== Changelog ==

= 1.1.0 =
* Admin reports UI (Vue SPA) with Link and Domain views, date filters, pagination

= 1.0.0 =
* Initial release: frontend click tracking, REST API, database storage
