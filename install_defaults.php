<?php

/* AmazonLinks Plugin 1.1.0 - configuration defaults */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

global $_AMAZONLINKS_DEFAULT;

$_AMAZONLINKS_DEFAULT = array(
    'enabled'       => 1,
    'title'         => 'Recommended resources on Amazon',
    'marketplace'   => 'www.amazon.com',
    'affiliate_tag' => '',
    'max_links'     => 5,
    'display_mode'  => 'automatic',
    'button_color'  => 'theme',
    'new_window'    => 1,
    'load_css'      => 1,
    'autotag_css'   => 1
);

function AMAZONLINKS_migrateLegacyConfig($c)
{
    global $_CONF;

    if (!isset($_CONF['path_data'])) {
        return true;
    }

    $legacyFile = rtrim($_CONF['path_data'], "/\\")
        . DIRECTORY_SEPARATOR . 'amazonlinks_config.php';

    if (!file_exists($legacyFile)) {
        return true;
    }

    /*
     * AmazonLinks 1.0 loaded this administrator-managed PHP file on article
     * requests. During the 1.0 -> 1.1 upgrade it is loaded once, only to
     * migrate the trusted legacy array to Geeklog Configuration + private JSON.
     */
    $AMAZONLINKS_CONF = array();
    include $legacyFile;

    if (!is_array($AMAZONLINKS_CONF)) {
        COM_errorLog('AmazonLinks: legacy configuration migration failed: invalid configuration array.');
        return false;
    }

    if (isset($AMAZONLINKS_CONF['tag'])) {
        $c->set('affiliate_tag', trim((string) $AMAZONLINKS_CONF['tag']), 'amazonlinks');
    }

    if (isset($AMAZONLINKS_CONF['title'])) {
        $legacyTitle = trim((string) $AMAZONLINKS_CONF['title']);
        if ($legacyTitle !== '') {
            $c->set('title', $legacyTitle, 'amazonlinks');
        }
    }

    if (isset($AMAZONLINKS_CONF['max_links'])) {
        $maxLinks = (int) $AMAZONLINKS_CONF['max_links'];
        if ($maxLinks < 1) {
            $maxLinks = 1;
        } elseif ($maxLinks > 20) {
            $maxLinks = 20;
        }
        $c->set('max_links', $maxLinks, 'amazonlinks');
    }

    /*
     * Once the site has explicitly completed its 1.0 -> 1.1 upgrade, use the
     * native automatic renderer. Legacy template mode is reserved for the
     * read-only shared-files compatibility path before that site's upgrade.
     */
    $c->set('display_mode', 'automatic', 'amazonlinks');

    $keywords = isset($AMAZONLINKS_CONF['keywords']) && is_array($AMAZONLINKS_CONF['keywords'])
        ? $AMAZONLINKS_CONF['keywords']
        : array();

    $rules = array();
    $priority = count($keywords);
    $detectedMarketplace = '';

    foreach ($keywords as $keyword => $data) {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            continue;
        }

        $label = $keyword;
        $url = '';

        if (is_array($data)) {
            if (isset($data['label']) && trim((string) $data['label']) !== '') {
                $label = trim((string) $data['label']);
            }
            if (isset($data['url'])) {
                $url = trim((string) $data['url']);
            }
        } else {
            $url = trim((string) $data);
        }

        $parts = @parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            continue;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            continue;
        }

        if ($detectedMarketplace === '') {
            $host = strtolower((string) $parts['host']);
            $allowedMarketplaces = array(
                'www.amazon.com', 'www.amazon.fr', 'www.amazon.de',
                'www.amazon.it', 'www.amazon.es', 'www.amazon.co.uk',
                'www.amazon.ca', 'www.amazon.com.au', 'www.amazon.co.jp',
                'www.amazon.in'
            );
            if (in_array($host, $allowedMarketplaces, true)) {
                $detectedMarketplace = $host;
            }
        }

        $rules[] = array(
            'keyword'  => $keyword,
            'label'    => $label,
            'type'     => 'url',
            'target'   => $url,
            'match'    => 'substring',
            'topic'    => '',
            'priority' => $priority,
            'enabled'  => true
        );

        $priority--;
    }

    if ($detectedMarketplace !== '') {
        $c->set('marketplace', $detectedMarketplace, 'amazonlinks');
    }

    $GLOBALS['_AMAZONLINKS_MIGRATING_LEGACY'] = true;
    $saved = function_exists('AMAZONLINKS_saveRules') && AMAZONLINKS_saveRules($rules);
    unset($GLOBALS['_AMAZONLINKS_MIGRATING_LEGACY']);

    if (!$saved) {
        COM_errorLog('AmazonLinks: legacy configuration migration failed while saving contextual rules. Legacy data was preserved.');
        return false;
    }

    $backup = $legacyFile . '.migrated-1.0.0.bak';
    if (file_exists($backup)) {
        $backup .= '-' . date('Ymd-His');
    }

    if (!@rename($legacyFile, $backup)) {
        COM_errorLog(
            'AmazonLinks: legacy configuration migrated successfully, but the original file could not be renamed: '
            . $legacyFile
        );
    } else {
        @chmod($backup, 0640);
    }

    COM_errorLog(
        'AmazonLinks: migrated legacy 1.0 configuration (' . count($rules)
        . ' contextual rule(s)) to 1.1 storage.'
    );

    return true;
}

function plugin_initconfig_amazonlinks()
{
    global $_AMAZONLINKS_DEFAULT;

    $c = config::get_instance();

    if (!$c->group_exists('amazonlinks')) {
        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, 'amazonlinks', 0);
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'amazonlinks', 0);
        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, 'amazonlinks', 0);

        $c->add('enabled', $_AMAZONLINKS_DEFAULT['enabled'], 'select',
            0, 0, 0, 10, true, 'amazonlinks', 0);
        $c->add('title', $_AMAZONLINKS_DEFAULT['title'], 'text',
            0, 0, 0, 20, true, 'amazonlinks', 0);
        $c->add('marketplace', $_AMAZONLINKS_DEFAULT['marketplace'], 'select',
            0, 0, 2, 30, true, 'amazonlinks', 0);
        $c->add('affiliate_tag', $_AMAZONLINKS_DEFAULT['affiliate_tag'], 'text',
            0, 0, 0, 40, true, 'amazonlinks', 0);
        $c->add('max_links', $_AMAZONLINKS_DEFAULT['max_links'], 'text',
            0, 0, 0, 50, true, 'amazonlinks', 0);
        $c->add('display_mode', $_AMAZONLINKS_DEFAULT['display_mode'], 'select',
            0, 0, 3, 60, true, 'amazonlinks', 0);
        $c->add('button_color', $_AMAZONLINKS_DEFAULT['button_color'], 'select',
            0, 0, 4, 70, true, 'amazonlinks', 0);
        $c->add('new_window', $_AMAZONLINKS_DEFAULT['new_window'], 'select',
            0, 0, 1, 80, true, 'amazonlinks', 0);
        $c->add('load_css', $_AMAZONLINKS_DEFAULT['load_css'], 'select',
            0, 0, 1, 90, true, 'amazonlinks', 0);
        $c->add('autotag_css', $_AMAZONLINKS_DEFAULT['autotag_css'], 'select',
            0, 0, 1, 100, true, 'amazonlinks', 0);
    }

    /*
     * Retry-safe migration: if a previous upgrade attempt created the 1.1
     * configuration group but failed before completing rule conversion, the
     * legacy file remains and the next explicit upgrade retries the migration.
     */
    if (function_exists('AMAZONLINKS_isLegacyRuntime') && AMAZONLINKS_isLegacyRuntime()) {
        if (!AMAZONLINKS_migrateLegacyConfig($c)) {
            return false;
        }
    }

    return true;
}
