# Changelog

## 1.1.0

- Increased CSS specificity and added targeted `!important` rules so theme hover styles cannot override AmazonLinks button colors.

- Fixed button hover/focus states so theme link colors cannot override the configured button text color.

- Added configurable button color presets: theme, blue, green, orange, red, dark and gray.

- Existing `tag` parameters in direct URLs are now removed case-insensitively before the configured affiliate tag is added.

- Added collapsed built-in documentation to the bottom of the AmazonLinks administration page.

- Replaced executable `amazonlinks_config.php` runtime configuration with Geeklog Configuration API and JSON rule storage after the site completes its explicit 1.1 upgrade.
- Added shared-files multisite upgrade safety: sites still persisted as 1.0 continue to read their own legacy `amazonlinks_config.php` through a read-only compatibility path after 1.1 files are deployed.
- The legacy compatibility path preserves the historical affiliate tag, title, maximum links, Amazon URLs, rule order, substring matching and `{amazonlinks}` template mode until that site is upgraded.
- Normal frontend requests never migrate, rename or rewrite the legacy configuration.
- Rule administration is prevented from creating the new JSON rule state prematurely while a site is still in the 1.0 persisted state.
- Made the 1.0 -> 1.1 migration retry-safe: if an upgrade attempt creates the new configuration but rule conversion fails, the legacy file remains available and the next explicit upgrade retries the migration.
- The installed plugin version is updated to 1.1.0 only after required configuration, permissions, storage and rule migration steps succeed.
- Added a private data directory derived from `path_data`.
- Added an administrator rule editor protected by `amazonlinks.admin`.
- Added CSRF protection and atomic rule writes.
- Added URL scheme validation and safer affiliate-tag handling.
- Added marketplace configuration.
- Added priority, match mode, topic restriction and enabled state for rules.
- Added `[amazonlinks:...]` autotags, including ASIN links.
- Renamed the autotag from `[amazon:...]` to `[amazonlinks:...]` to avoid collisions with existing or legacy Amazon autotags.
- Fixed autotag parsing so multi-word searches and custom labels are read from the original `tagstr` instead of being truncated by Geeklog's space-based `parm1` / `parm2` parsing.
- Added explicit `|` separator support for custom autotag labels, e.g. `[amazonlinks:geeklog|Cliquez ici]` and `[amazonlinks:rocket stove|Voir les livres sur les rocket stoves]`.
- Added the `autotag_css` configuration option. When disabled, autotags render as normal unclassed links instead of AmazonLinks buttons, while contextual block styling remains unchanged.
- Updated French and English autotag help and administrator examples to document the `|` separator and the `amazonlinks` tag name.
- Added automatic story display mode using Geeklog's native template-variable hook.
- Added optional `{amazonlinks}` template mode and disabled mode.
- Fixed automatic mode so it no longer relies on the unused item-display path in article rendering.
- Automatic blocks are appended only on full article views, not topic/index summaries.
- Automatic matching now prefers the final visible `story_text_no_br` content, with a fallback to title/introduction/body variables. This improves compatibility with Eclipse and other themes that render the final article body through `story_text_no_br`.
- Contextual matching checks the article title together with the visible article text when available.
- Added a Geeklog 2.1.1 fallback that detects full article pages via `article.php` when `story_display_type` is unavailable; Geeklog 2.2.2 continues to use the native `n` / `y` display type.
- Contextual blocks now use an explicit localized default title such as `Ressources recommandées sur Amazon` and display an affiliate disclosure below the links when an affiliate tag is configured.
- Legacy generic default titles are upgraded at render time without overwriting administrator-defined custom titles.
- Prevented duplicate automatic/template output by making the modes mutually exclusive.
- Simplified configuration initialization so it only creates missing settings and can safely retry an incomplete legacy migration.
- Moved missing-setting migration logic into `plugin_upgrade_amazonlinks()`.
- Made runtime configuration loading fail-safe during Geeklog configuration bootstrap.
- Restricted the template hook to article templates before any AmazonLinks processing.
- Moved frontend presentation to a dedicated stylesheet.
- Added English and French localization.
- Added upgrade logic from 1.0.0.
- Kept compatibility target at Geeklog 2.1.1+ and PHP 5.6+.
- No telemetry, Composer dependencies or plugin-specific database tables.

## 1.0.0

- Initial release.
