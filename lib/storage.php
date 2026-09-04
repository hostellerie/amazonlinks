<?php

/* Private JSON storage helpers for AmazonLinks. */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

function AMAZONLINKS_dataDir()
{
    global $_CONF;

    $base = isset($_CONF['path_data']) ? rtrim($_CONF['path_data'], "/\\") : '';
    if ($base === '') {
        return '';
    }

    return dirname($base) . DIRECTORY_SEPARATOR
        . basename($base) . '-amazonlinks' . DIRECTORY_SEPARATOR;
}

function AMAZONLINKS_rulesFile()
{
    return AMAZONLINKS_dataDir() . 'rules.json';
}

function AMAZONLINKS_legacyConfigFile()
{
    global $_CONF;

    return isset($_CONF['path_data'])
        ? rtrim($_CONF['path_data'], "/\\") . DIRECTORY_SEPARATOR . 'amazonlinks_config.php'
        : '';
}

function AMAZONLINKS_installedVersion()
{
    global $_TABLES;

    if (empty($_TABLES['plugins']) || !function_exists('DB_getItem')) {
        return '';
    }

    $version = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = 'amazonlinks'");
    return is_string($version) ? trim($version) : '';
}

function AMAZONLINKS_hasCurrentConfig()
{
    if (!class_exists('config')) {
        return false;
    }

    $c = config::get_instance();
    if (!method_exists($c, 'group_exists')) {
        return false;
    }

    return $c->group_exists('amazonlinks');
}

function AMAZONLINKS_isLegacyRuntime()
{
    $legacyFile = AMAZONLINKS_legacyConfigFile();
    if ($legacyFile === '' || !file_exists($legacyFile)) {
        return false;
    }

    $version = AMAZONLINKS_installedVersion();
    if ($version !== '') {
        return version_compare($version, '1.1.0', '<');
    }

    /* Fallback for unusual bootstrap contexts where the plugin row is not readable. */
    return !AMAZONLINKS_hasCurrentConfig();
}

function AMAZONLINKS_legacyState()
{
    static $state = null;

    if ($state !== null) {
        return $state;
    }

    $state = array('config' => array(), 'rules' => array());

    if (!AMAZONLINKS_isLegacyRuntime()) {
        return $state;
    }

    $legacyFile = AMAZONLINKS_legacyConfigFile();
    $AMAZONLINKS_CONF = array();
    include $legacyFile;

    if (!is_array($AMAZONLINKS_CONF)) {
        return $state;
    }

    $title = isset($AMAZONLINKS_CONF['title'])
        ? trim((string) $AMAZONLINKS_CONF['title'])
        : 'Recommended Resources';
    $tag = isset($AMAZONLINKS_CONF['tag'])
        ? trim((string) $AMAZONLINKS_CONF['tag'])
        : '';
    $maxLinks = isset($AMAZONLINKS_CONF['max_links'])
        ? (int) $AMAZONLINKS_CONF['max_links']
        : 5;

    if ($maxLinks < 1) {
        $maxLinks = 1;
    } elseif ($maxLinks > 20) {
        $maxLinks = 20;
    }

    $keywords = isset($AMAZONLINKS_CONF['keywords']) && is_array($AMAZONLINKS_CONF['keywords'])
        ? $AMAZONLINKS_CONF['keywords']
        : array();

    $rules = array();
    $priority = count($keywords);
    $marketplace = 'www.amazon.com';
    $marketplaceDetected = false;
    $allowedMarketplaces = array(
        'www.amazon.com',
        'www.amazon.fr',
        'www.amazon.de',
        'www.amazon.it',
        'www.amazon.es',
        'www.amazon.co.uk',
        'www.amazon.ca',
        'www.amazon.com.au',
        'www.amazon.co.jp',
        'www.amazon.in'
    );

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

        if (!AMAZONLINKS_isSafeHttpUrl($url)) {
            continue;
        }

        if (!$marketplaceDetected) {
            $parts = @parse_url($url);
            $host = is_array($parts) && !empty($parts['host'])
                ? strtolower((string) $parts['host'])
                : '';
            if (in_array($host, $allowedMarketplaces, true)) {
                $marketplace = $host;
                $marketplaceDetected = true;
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

    $state['config'] = array(
        'enabled'        => 1,
        'title'          => $title,
        'marketplace'    => $marketplace,
        'affiliate_tag'  => $tag,
        'max_links'      => $maxLinks,
        'display_mode'   => 'template',
        'button_color'   => 'blue',
        'new_window'     => 1,
        'load_css'       => 1,
        'autotag_css'    => 1,
        'legacy_runtime' => 1
    );
    $state['rules'] = AMAZONLINKS_normalizeRules($rules);

    return $state;
}

function AMAZONLINKS_ensureDataDir()
{
    $dir = AMAZONLINKS_dataDir();

    if ($dir === '') {
        return false;
    }

    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return false;
        }
    }

    if (!is_writable($dir)) {
        return false;
    }

    $index = $dir . 'index.html';
    if (!file_exists($index)) {
        @file_put_contents($index, '');
        @chmod($index, 0640);
    }

    return true;
}

function AMAZONLINKS_writeJsonAtomically($file, $json)
{
    if (!AMAZONLINKS_ensureDataDir()) {
        return false;
    }

    $tmp = $file . '.tmp-' . uniqid('', true);
    $written = @file_put_contents($tmp, $json, LOCK_EX);

    if ($written === false) {
        @unlink($tmp);
        return false;
    }

    @chmod($tmp, 0640);

    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }

    @chmod($file, 0640);
    return true;
}

function AMAZONLINKS_loadRules()
{
    $file = AMAZONLINKS_rulesFile();

    if (!file_exists($file)) {
        if (AMAZONLINKS_isLegacyRuntime()) {
            $legacy = AMAZONLINKS_legacyState();
            return isset($legacy['rules']) && is_array($legacy['rules'])
                ? $legacy['rules']
                : array();
        }
        return array();
    }

    $json = @file_get_contents($file);
    if ($json === false || trim($json) === '') {
        return array();
    }

    $rules = json_decode($json, true);
    if (!is_array($rules)) {
        COM_errorLog('AmazonLinks: invalid JSON in ' . $file);
        return array();
    }

    return AMAZONLINKS_normalizeRules($rules);
}

function AMAZONLINKS_saveRules($rules)
{
    /*
     * Shared-files safety: a site persisted as 1.0 is read-only until the
     * explicit migration routine authorizes the write. This remains true even
     * after a failed upgrade has already created the 1.1 configuration group.
     */
    if (AMAZONLINKS_isLegacyRuntime() && empty($GLOBALS['_AMAZONLINKS_MIGRATING_LEGACY'])) {
        return false;
    }

    $rules = AMAZONLINKS_normalizeRules($rules);
    $json = json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return false;
    }

    return AMAZONLINKS_writeJsonAtomically(AMAZONLINKS_rulesFile(), $json . "\n");
}

function AMAZONLINKS_normalizeRules($rules)
{
    $clean = array();

    if (!is_array($rules)) {
        return $clean;
    }

    foreach ($rules as $rule) {
        if (!is_array($rule)) {
            continue;
        }

        $keyword = isset($rule['keyword']) ? trim((string) $rule['keyword']) : '';
        $label = isset($rule['label']) ? trim((string) $rule['label']) : '';
        $type = isset($rule['type']) ? strtolower(trim((string) $rule['type'])) : 'search';
        $target = isset($rule['target']) ? trim((string) $rule['target']) : '';
        $match = isset($rule['match']) ? strtolower(trim((string) $rule['match'])) : 'phrase';
        $topic = isset($rule['topic']) ? trim((string) $rule['topic']) : '';
        $priority = isset($rule['priority']) ? (int) $rule['priority'] : 0;
        $enabled = !isset($rule['enabled']) || !empty($rule['enabled']);

        if ($keyword === '') {
            continue;
        }

        if (!in_array($type, array('search', 'url'), true)) {
            $type = 'search';
        }

        if (!in_array($match, array('word', 'phrase', 'substring'), true)) {
            $match = 'phrase';
        }

        if ($label === '') {
            $label = $keyword;
        }

        if ($type === 'search' && $target === '') {
            $target = $keyword;
        }

        if ($type === 'url' && !AMAZONLINKS_isSafeHttpUrl($target)) {
            continue;
        }

        if ($priority > 1000) {
            $priority = 1000;
        } elseif ($priority < -1000) {
            $priority = -1000;
        }

        $clean[] = array(
            'keyword'  => $keyword,
            'label'    => $label,
            'type'     => $type,
            'target'   => $target,
            'match'    => $match,
            'topic'    => $topic,
            'priority' => $priority,
            'enabled'  => $enabled
        );
    }

    usort($clean, 'AMAZONLINKS_compareRulePriority');

    return $clean;
}

function AMAZONLINKS_compareRulePriority($a, $b)
{
    if ($a['priority'] == $b['priority']) {
        return 0;
    }

    return ($a['priority'] > $b['priority']) ? -1 : 1;
}

function AMAZONLINKS_isSafeHttpUrl($url)
{
    if (!is_string($url) || trim($url) === '') {
        return false;
    }

    $parts = @parse_url($url);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme']);
    return $scheme === 'http' || $scheme === 'https';
}

/*
 * When 1.1 files are shared with a site still persisted as 1.0, preload the
 * legacy configuration read-only before functions.inc falls back to 1.1 defaults.
 */
if ((!isset($GLOBALS['_AMAZONLINKS_CONF']) || !is_array($GLOBALS['_AMAZONLINKS_CONF']))
    && AMAZONLINKS_isLegacyRuntime()
) {
    $amazonlinksLegacyState = AMAZONLINKS_legacyState();
    if (!empty($amazonlinksLegacyState['config'])) {
        $GLOBALS['_AMAZONLINKS_CONF'] = $amazonlinksLegacyState['config'];
    }
}
