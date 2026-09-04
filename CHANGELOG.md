# Changelog

## 1.1.0

- Increased CSS specificity and added targeted `!important` rules so theme hover styles cannot override AmazonLinks button colors.

- Fixed button hover/focus states so theme link colors cannot override the configured button text color.

- Added configurable button color presets: theme, blue, green, orange, red, dark and gray.

- Existing `tag` parameters in direct URLs are now removed case-insensitively before the configured affiliate tag is added.

- Added collapsed built-in documentation to the bottom of the AmazonLinks administration page.

- Replaced executable `amazonlinks_config.php` runtime configuration with Geeklog Configuration API and JSON rule storage.
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
- Contextual matching now checks both article introduction and body text.
- Contextual matching now checks the article title in addition to the introduction and body text.
- Prevented duplicate automatic/template output by making the modes mutually exclusive.
- Simplified configuration initialization so it only creates settings on first install.
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
