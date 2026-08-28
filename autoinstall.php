<?php

/* AmazonLinks Plugin 1.1.0 */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

function plugin_autoinstall_amazonlinks($pi_name)
{
    $piName = 'amazonlinks';
    $displayName = 'Amazon Links';
    $adminGroup = $displayName . ' Admin';

    return array(
        'info' => array(
            'pi_name'         => $piName,
            'pi_display_name' => $displayName,
            'pi_version'      => '1.1.0',
            'pi_gl_version'   => '2.1.1',
            'pi_homepage'     => 'https://github.com/Geeklog-Plugins/amazonlinks'
        ),
        'groups' => array(
            $adminGroup => 'Users in this group can administer the ' . $displayName . ' plugin'
        ),
        'features' => array(
            $piName . '.admin' => 'Full access to the Amazon Links plugin',
            'config.' . $piName . '.tab_main' => 'Access to Amazon Links configuration'
        ),
        'mappings' => array(
            $piName . '.admin' => array($adminGroup),
            'config.' . $piName . '.tab_main' => array($adminGroup)
        ),
        'tables' => array()
    );
}

function plugin_load_configuration_amazonlinks($pi_name)
{
    global $_CONF;

    $defaults = $_CONF['path'] . 'plugins/' . $pi_name . '/install_defaults.php';
    if (!file_exists($defaults)) {
        return false;
    }

    require_once $defaults;

    return function_exists('plugin_initconfig_' . $pi_name)
        && call_user_func('plugin_initconfig_' . $pi_name);
}

function AMAZONLINKS_installConfigPermission()
{
    global $_TABLES;

    $featureName = 'config.amazonlinks.tab_main';
    $featureId = (int) DB_getItem(
        $_TABLES['features'],
        'ft_id',
        "ft_name = '" . DB_escapeString($featureName) . "'"
    );

    if ($featureId < 1) {
        DB_save(
            $_TABLES['features'],
            'ft_name,ft_descr',
            "'" . DB_escapeString($featureName) . "','Access to Amazon Links configuration'"
        );
        $featureId = (int) DB_getItem(
            $_TABLES['features'],
            'ft_id',
            "ft_name = '" . DB_escapeString($featureName) . "'"
        );
    }

    $groupId = (int) DB_getItem(
        $_TABLES['groups'],
        'grp_id',
        "grp_name = 'Amazon Links Admin'"
    );

    if ($featureId < 1 || $groupId < 1) {
        COM_errorLog('AmazonLinks: could not resolve configuration permission or admin group.');
        return false;
    }

    if (DB_count(
        $_TABLES['access'],
        array('acc_ft_id', 'acc_grp_id'),
        array($featureId, $groupId)
    ) == 0) {
        DB_save($_TABLES['access'], 'acc_ft_id,acc_grp_id', $featureId . ',' . $groupId);
    }

    return true;
}

function plugin_postinstall_amazonlinks($pi_name)
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/amazonlinks/lib/storage.php';

    if (!AMAZONLINKS_ensureDataDir()) {
        COM_errorLog('AmazonLinks: unable to create the private data directory.');
        return false;
    }

    if (!file_exists(AMAZONLINKS_rulesFile())) {
        $defaultRules = $_CONF['path'] . 'plugins/amazonlinks/defaults/rules.json';
        if (file_exists($defaultRules)) {
            $json = file_get_contents($defaultRules);
            if ($json === false || !AMAZONLINKS_writeJsonAtomically(AMAZONLINKS_rulesFile(), $json)) {
                COM_errorLog('AmazonLinks: unable to install the default rules file.');
                return false;
            }
        }
    }

    return AMAZONLINKS_installConfigPermission();
}

function plugin_compatible_with_this_version_amazonlinks($pi_name)
{
    return function_exists('COM_newTemplate') && class_exists('config');
}
