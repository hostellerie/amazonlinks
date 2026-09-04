<?php

/* English language file for AmazonLinks 1.1.0 */

$LANG_AMAZONLINKS = array(
    'plugin_name' => 'Amazon Links',
    'admin_title' => 'Amazon Links',
    'admin_intro' => 'Manage contextual Amazon link rules here. General settings are stored in Geeklog Configuration.',
    'open_configuration' => 'Open general configuration',
    'rules_title' => 'Contextual rules',
    'rules_help' => 'Use Amazon search terms or a complete HTTP/HTTPS URL. A topic value limits a rule to that Geeklog topic ID; leave it blank or use "all" for every topic.',
    'data_directory' => 'Private data directory',
    'legacy_warning' => 'This site is still using its AmazonLinks 1.0 configuration. The 1.1 files temporarily read it through a read-only compatibility path without modifying it. Run the plugin upgrade on this site when ready to migrate the tag, title, maximum links and contextual rules automatically. Do not recreate the rules manually.',
    'invalid_token' => 'The request could not be validated. Please reload the page and try again.',
    'saved' => 'Amazon link rules were saved.',
    'save_failed' => 'The rules could not be saved. Check the data-directory permissions and Geeklog error log.',
    'save_rules' => 'Save rules',
    'enabled' => 'Enabled',
    'keyword' => 'Keyword / phrase',
    'label' => 'Link label',
    'type' => 'Target type',
    'target' => 'Search query / URL',
    'match' => 'Match',
    'topic' => 'Topic ID',
    'priority' => 'Priority',
    'search' => 'Amazon search',
    'url' => 'Direct URL',
    'word' => 'Whole word',
    'phrase' => 'Phrase',
    'substring' => 'Substring',
    'default_block_title' => 'Recommended resources on Amazon',
    'affiliate_disclosure' => 'Affiliate links: the site may earn a commission at no additional cost to you.',
    'template_title' => 'Theme integration',
    'template_help' => 'Automatic mode requires no theme change. Template mode lets you add <code>{amazonlinks}</code> to the desired story templates, normally <code>article/storytext.thtml</code> and <code>article/featuredstorytext.thtml</code>.',
    'autotag_title' => 'Autotags',
    'autotag_description' => '[amazonlinks:query] creates an Amazon search link. Add |label to customize the text, for example [amazonlinks:geeklog|Click here]. Use [amazonlinks:asin:ASIN|label] for a specific product.',
    'documentation_title' => 'Plugin documentation',
    'documentation_quickstart_title' => 'Quick start',
    'documentation_quickstart' => 'Configure your Amazon marketplace and affiliate tag, then create at least one rule whose keyword appears in the article title, introduction or body.',
    'documentation_display_title' => 'Display modes',
    'documentation_display_auto' => 'Automatic: the block is appended only on the full article page, with no theme modification required.',
    'documentation_display_template' => 'Template variable: add {amazonlinks} to the article template exactly where the block should appear.',
    'documentation_display_disabled' => 'Disabled: contextual blocks are hidden; autotags remain available.',
    'documentation_color_title' => 'Button color',
    'documentation_color' => 'Choose Theme color to preserve the natural look of the active template, or select a predefined color: blue, green, orange, red, dark or gray.',
    'documentation_rules_title' => 'Rules',
    'documentation_rules' => 'Each rule maps a keyword or phrase to an Amazon search or direct URL. Higher priorities are evaluated first. A rule can optionally be limited to a Geeklog topic.',
    'documentation_tag_title' => 'Affiliate tag',
    'documentation_tag' => 'AmazonLinks always uses the configured affiliate tag. If a direct URL already contains a tag parameter, it is removed and replaced with the configured tag.',
    'documentation_autotags_title' => 'Autotags',
    'documentation_storage_title' => 'Data storage',
    'documentation_storage' => 'General settings use the Geeklog Configuration API and rules use private JSON storage. With shared plugin files, a site still persisted as 1.0 temporarily reads its legacy PHP configuration in memory until that site runs its explicit upgrade; normal requests never trigger the migration.'
);

$LANG_configsections['amazonlinks'] = array(
    'label' => 'Amazon Links',
    'title' => 'Amazon Links Configuration'
);

$LANG_confignames['amazonlinks'] = array(
    'enabled' => 'Enable Amazon Links?',
    'title' => 'Block title',
    'marketplace' => 'Amazon marketplace',
    'affiliate_tag' => 'Affiliate tag',
    'max_links' => 'Maximum contextual links',
    'display_mode' => 'Display mode',
    'button_color' => 'Button color',
    'new_window' => 'Open links in a new window?',
    'load_css' => 'Load the plugin stylesheet?',
    'autotag_css' => 'Apply button styling to autotags?'
);

$LANG_configsubgroups['amazonlinks'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_tab['amazonlinks'] = array(
    'tab_main' => 'Main'
);

$LANG_fs['amazonlinks'] = array(
    'fs_main' => 'General settings'
);

$LANG_configselects['amazonlinks'] = array(
    0 => array('Yes' => 1, 'No' => 0),
    1 => array('Yes' => 1, 'No' => 0),
    2 => array(
        'Amazon.com' => 'www.amazon.com',
        'Amazon.fr' => 'www.amazon.fr',
        'Amazon.de' => 'www.amazon.de',
        'Amazon.it' => 'www.amazon.it',
        'Amazon.es' => 'www.amazon.es',
        'Amazon.co.uk' => 'www.amazon.co.uk',
        'Amazon.ca' => 'www.amazon.ca',
        'Amazon.com.au' => 'www.amazon.com.au',
        'Amazon.co.jp' => 'www.amazon.co.jp',
        'Amazon.in' => 'www.amazon.in'
    ),
    3 => array(
        'Automatic after story' => 'automatic',
        'Template variable {amazonlinks}' => 'template',
        'Disabled' => 'disabled'
    ),
    4 => array(
        'Theme color' => 'theme',
        'Blue' => 'blue',
        'Green' => 'green',
        'Orange' => 'orange',
        'Red' => 'red',
        'Dark' => 'dark',
        'Gray' => 'gray'
    )
);

$PLG_amazonlinks_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_amazonlinks_MESSAGE3002 = 'This Geeklog version is not supported.';
