<?php

/* AmazonLinks Plugin 1.1.0 - configuration defaults */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

global $_AMAZONLINKS_DEFAULT;

$_AMAZONLINKS_DEFAULT = array(
    'enabled'       => 1,
    'title'         => 'Recommended resources',
    'marketplace'   => 'www.amazon.com',
    'affiliate_tag' => '',
    'max_links'     => 5,
    'display_mode'  => 'automatic',
    'button_color'  => 'theme',
    'new_window'    => 1,
    'load_css'      => 1
);

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
    }

    return true;
}
