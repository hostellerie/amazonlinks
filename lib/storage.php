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
