<?php

/* AmazonLinks Plugin 1.1.0 - rule administration */

require_once dirname(__FILE__) . '/../../../lib-common.php';
require_once dirname(__FILE__) . '/../../auth.inc.php';

if (!SEC_hasRights('amazonlinks.admin')) {
    COM_accessLog('User tried to access AmazonLinks administration without permission.');
    $content = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $MESSAGE[30])));
    exit;
}

global $_CONF, $LANG_AMAZONLINKS;

$message = '';
$rules = AMAZONLINKS_loadRules();

if (isset($_POST['save_rules'])) {
    if (!SEC_checkToken()) {
        $message = COM_showMessageText(
            $LANG_AMAZONLINKS['invalid_token'],
            $LANG_AMAZONLINKS['admin_title']
        );
    } else {
        $posted = isset($_POST['rules']) && is_array($_POST['rules'])
            ? $_POST['rules']
            : array();

        $candidateRules = array();

        foreach ($posted as $row) {
            if (!is_array($row)) {
                continue;
            }

            $candidateRules[] = array(
                'keyword'  => isset($row['keyword']) ? $row['keyword'] : '',
                'label'    => isset($row['label']) ? $row['label'] : '',
                'type'     => isset($row['type']) ? $row['type'] : 'search',
                'target'   => isset($row['target']) ? $row['target'] : '',
                'match'    => isset($row['match']) ? $row['match'] : 'phrase',
                'topic'    => isset($row['topic']) ? $row['topic'] : '',
                'priority' => isset($row['priority']) ? $row['priority'] : 0,
                'enabled'  => !empty($row['enabled'])
            );
        }

        $rules = AMAZONLINKS_normalizeRules($candidateRules);

        if (AMAZONLINKS_saveRules($rules)) {
            $message = COM_showMessageText(
                $LANG_AMAZONLINKS['saved'],
                $LANG_AMAZONLINKS['admin_title']
            );
        } else {
            $message = COM_showMessageText(
                $LANG_AMAZONLINKS['save_failed'],
                $LANG_AMAZONLINKS['admin_title']
            );
        }
    }
}

for ($i = 0; $i < 5; $i++) {
    $rules[] = array(
        'keyword' => '',
        'label' => '',
        'type' => 'search',
        'target' => '',
        'match' => 'phrase',
        'topic' => '',
        'priority' => 0,
        'enabled' => true
    );
}

$token = SEC_createToken();
$configUrl = $_CONF['site_admin_url'] . '/configuration.php';
$dataDir = AMAZONLINKS_dataDir();
$legacyFile = AMAZONLINKS_legacyConfigFile();

$content = COM_startBlock(
    $LANG_AMAZONLINKS['admin_title'],
    '',
    COM_getBlockTemplate('_admin_block', 'header')
);

$content .= $message;
$content .= '<style>'
    . '.amazonlinks-rules-wrap{width:100%;max-width:100%;overflow-x:auto}'
    . '.amazonlinks-rules{width:100%;max-width:100%;table-layout:fixed;border-collapse:collapse}'
    . '.amazonlinks-rules th,.amazonlinks-rules td{vertical-align:top;overflow-wrap:anywhere;padding:.35rem}'
    . '.amazonlinks-rules input[type=text],.amazonlinks-rules input[type=number],.amazonlinks-rules select{width:100%;max-width:100%;min-width:0;box-sizing:border-box}'
    . '.amazonlinks-rules th:nth-child(1),.amazonlinks-rules td:nth-child(1){width:5%}'
    . '.amazonlinks-rules th:nth-child(2),.amazonlinks-rules td:nth-child(2){width:14%}'
    . '.amazonlinks-rules th:nth-child(3),.amazonlinks-rules td:nth-child(3){width:14%}'
    . '.amazonlinks-rules th:nth-child(4),.amazonlinks-rules td:nth-child(4){width:11%}'
    . '.amazonlinks-rules th:nth-child(5),.amazonlinks-rules td:nth-child(5){width:24%}'
    . '.amazonlinks-rules th:nth-child(6),.amazonlinks-rules td:nth-child(6){width:12%}'
    . '.amazonlinks-rules th:nth-child(7),.amazonlinks-rules td:nth-child(7){width:10%}'
    . '.amazonlinks-rules th:nth-child(8),.amazonlinks-rules td:nth-child(8){width:10%}'
    . '@media(max-width:900px){.amazonlinks-rules{min-width:760px;table-layout:auto}}'
    . '</style>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['admin_intro'], ENT_QUOTES, 'UTF-8') . '</p>';

if ($legacyFile !== '' && file_exists($legacyFile)) {
    $content .= '<div class="alert">'
        . htmlspecialchars($LANG_AMAZONLINKS['legacy_warning'], ENT_QUOTES, 'UTF-8')
        . '<br><code>' . htmlspecialchars($legacyFile, ENT_QUOTES, 'UTF-8') . '</code></div>';
}

$content .= '<p><strong>' . htmlspecialchars($LANG_AMAZONLINKS['data_directory'], ENT_QUOTES, 'UTF-8')
    . ':</strong> <code>' . htmlspecialchars($dataDir, ENT_QUOTES, 'UTF-8') . '</code></p>';

$content .= '<form method="post" action="'
    . htmlspecialchars($configUrl, ENT_QUOTES, 'UTF-8') . '">'
    . '<input type="hidden" name="conf_group" value="amazonlinks">'
    . '<button type="submit">'
    . htmlspecialchars($LANG_AMAZONLINKS['open_configuration'], ENT_QUOTES, 'UTF-8')
    . '</button></form>';

$content .= '<h2>' . htmlspecialchars($LANG_AMAZONLINKS['rules_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['rules_help'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<form method="post" action="">';
$content .= '<div class="amazonlinks-rules-wrap"><table class="admin-list amazonlinks-rules">';
$content .= '<thead><tr>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['enabled'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['keyword'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['label'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['type'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['target'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['match'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['topic'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '<th>' . htmlspecialchars($LANG_AMAZONLINKS['priority'], ENT_QUOTES, 'UTF-8') . '</th>'
    . '</tr></thead><tbody>';

foreach ($rules as $index => $rule) {
    $content .= '<tr>';
    $content .= '<td><input type="checkbox" name="rules[' . $index . '][enabled]" value="1"'
        . (!empty($rule['enabled']) ? ' checked' : '') . '></td>';

    foreach (array('keyword', 'label') as $field) {
        $content .= '<td><input type="text" name="rules[' . $index . '][' . $field . ']" value="'
            . htmlspecialchars($rule[$field], ENT_QUOTES, 'UTF-8') . '"></td>';
    }

    $content .= '<td><select name="rules[' . $index . '][type]">'
        . '<option value="search"' . ($rule['type'] === 'search' ? ' selected' : '') . '>'
        . htmlspecialchars($LANG_AMAZONLINKS['search'], ENT_QUOTES, 'UTF-8') . '</option>'
        . '<option value="url"' . ($rule['type'] === 'url' ? ' selected' : '') . '>'
        . htmlspecialchars($LANG_AMAZONLINKS['url'], ENT_QUOTES, 'UTF-8') . '</option>'
        . '</select></td>';

    $content .= '<td><input type="text" name="rules[' . $index . '][target]" value="'
        . htmlspecialchars($rule['target'], ENT_QUOTES, 'UTF-8') . '"></td>';

    $content .= '<td><select name="rules[' . $index . '][match]">';
    foreach (array('word', 'phrase', 'substring') as $mode) {
        $content .= '<option value="' . $mode . '"'
            . ($rule['match'] === $mode ? ' selected' : '') . '>'
            . htmlspecialchars($LANG_AMAZONLINKS[$mode], ENT_QUOTES, 'UTF-8')
            . '</option>';
    }
    $content .= '</select></td>';

    $content .= '<td><input type="text" name="rules[' . $index . '][topic]" value="'
        . htmlspecialchars($rule['topic'], ENT_QUOTES, 'UTF-8') . '" placeholder="all"></td>';

    $content .= '<td><input type="number" name="rules[' . $index . '][priority]" value="'
        . (int) $rule['priority'] . '" min="-1000" max="1000"></td>';

    $content .= '</tr>';
}

$content .= '</tbody></table></div>';
$content .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
    . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
$content .= '<p><button type="submit" name="save_rules" value="1">'
    . htmlspecialchars($LANG_AMAZONLINKS['save_rules'], ENT_QUOTES, 'UTF-8')
    . '</button></p></form>';

$content .= '<h2>' . htmlspecialchars($LANG_AMAZONLINKS['template_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p>' . $LANG_AMAZONLINKS['template_help'] . '</p>';

$content .= '<h2>' . htmlspecialchars($LANG_AMAZONLINKS['autotag_title'], ENT_QUOTES, 'UTF-8') . '</h2>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['autotag_description'], ENT_QUOTES, 'UTF-8') . '</p>';
$content .= '<p><code>[amazonlinks:libreoffice]</code><br>'
    . '<code>[amazonlinks:geeklog|Cliquez ici]</code><br>'
    . '<code>[amazonlinks:rocket stove|Voir les livres sur les rocket stoves]</code><br>'
    . '<code>[amazonlinks:asin:B0XXXXXXXX]</code><br>'
    . '<code>[amazonlinks:asin:B0XXXXXXXX|Voir ce produit sur Amazon]</code></p>';

$content .= '<details class="amazonlinks-documentation" style="margin-top:2em">';
$content .= '<summary style="cursor:pointer;font-weight:700">'
    . htmlspecialchars($LANG_AMAZONLINKS['documentation_title'], ENT_QUOTES, 'UTF-8')
    . '</summary>';
$content .= '<div style="margin-top:1em">';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_quickstart_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_quickstart'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_display_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<ul>'
    . '<li>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_display_auto'], ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_display_template'], ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_display_disabled'], ENT_QUOTES, 'UTF-8') . '</li>'
    . '</ul>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_rules_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_rules'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_color_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_color'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_tag_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_tag'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_autotags_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['autotag_description'], ENT_QUOTES, 'UTF-8') . '</p>';
$content .= '<p><code>[amazonlinks:libreoffice]</code><br>'
    . '<code>[amazonlinks:libreoffice|Voir les livres sur LibreOffice]</code><br>'
    . '<code>[amazonlinks:asin:B01FIX87WG]</code><br>'
    . '<code>[amazonlinks:asin:B01FIX87WG|Voir ce produit sur Amazon]</code></p>';

$content .= '<h3>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_storage_title'], ENT_QUOTES, 'UTF-8') . '</h3>';
$content .= '<p>' . htmlspecialchars($LANG_AMAZONLINKS['documentation_storage'], ENT_QUOTES, 'UTF-8') . '</p>';

$content .= '</div></details>';

$content .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

COM_output(COM_createHTMLDocument($content, array(
    'pagetitle' => $LANG_AMAZONLINKS['admin_title']
)));
