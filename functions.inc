<?php

/* AmazonLinks Plugin 1.1.0 */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

global $_CONF, $_AMAZONLINKS_CONF;

$amazonlinksPluginPath = $_CONF['path'] . 'plugins/amazonlinks/';

require_once $amazonlinksPluginPath . 'lib/storage.php';

$langfile = $amazonlinksPluginPath . 'language/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $amazonlinksPluginPath . 'language/english.php';
}

if (!isset($_AMAZONLINKS_CONF) || !is_array($_AMAZONLINKS_CONF)) {
    $_AMAZONLINKS_CONF = AMAZONLINKS_loadConfig();
}

function AMAZONLINKS_loadConfig()
{
    $defaults = array(
        'enabled'       => 1,
        'title'         => 'Recommended resources',
        'marketplace'   => 'www.amazon.com',
        'affiliate_tag' => '',
        'max_links'     => 5,
        'display_mode'  => 'automatic',
        'button_color'  => 'theme',
        'new_window'    => 1,
        'load_css'      => 1,
        'autotag_css'   => 1
    );

    if (!class_exists('config')) {
        return $defaults;
    }

    $c = config::get_instance();

    if (method_exists($c, 'group_exists') && !$c->group_exists('amazonlinks')) {
        return $defaults;
    }

    $stored = $c->get_config('amazonlinks');

    if (!is_array($stored) || empty($stored)) {
        return $defaults;
    }

    return array_merge($defaults, $stored);
}

function AMAZONLINKS_isFullStoryDisplay($displayType)
{
    $displayType = trim((string) $displayType);

    if ($displayType !== '') {
        return $displayType === 'n';
    }

    $script = '';
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $script = basename((string) $_SERVER['SCRIPT_NAME']);
    } elseif (!empty($_SERVER['PHP_SELF'])) {
        $script = basename((string) $_SERVER['PHP_SELF']);
    }

    return $script === 'article.php';
}

function AMAZONLINKS_marketplaces()
{
    return array(
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
}

function AMAZONLINKS_getMarketplace()
{
    global $_AMAZONLINKS_CONF;

    $marketplace = isset($_AMAZONLINKS_CONF['marketplace'])
        ? strtolower(trim($_AMAZONLINKS_CONF['marketplace']))
        : 'www.amazon.com';

    if (!in_array($marketplace, AMAZONLINKS_marketplaces(), true)) {
        $marketplace = 'www.amazon.com';
    }

    return $marketplace;
}

function AMAZONLINKS_buildSearchUrl($query)
{
    $url = 'https://' . AMAZONLINKS_getMarketplace() . '/s?k=' . rawurlencode($query);
    return AMAZONLINKS_addAffiliateTag($url);
}

function AMAZONLINKS_buildAsinUrl($asin)
{
    $asin = preg_replace('/[^A-Za-z0-9]/', '', (string) $asin);
    if ($asin === '') {
        return '';
    }

    $url = 'https://' . AMAZONLINKS_getMarketplace() . '/dp/' . rawurlencode($asin);
    return AMAZONLINKS_addAffiliateTag($url);
}

function AMAZONLINKS_addAffiliateTag($url)
{
    global $_AMAZONLINKS_CONF;

    if (!AMAZONLINKS_isSafeHttpUrl($url)) {
        return '';
    }

    $tag = isset($_AMAZONLINKS_CONF['affiliate_tag'])
        ? trim((string) $_AMAZONLINKS_CONF['affiliate_tag'])
        : '';

    if ($tag === '') {
        return $url;
    }

    $parts = parse_url($url);
    if (!is_array($parts)) {
        return '';
    }

    $query = array();
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    foreach (array_keys($query) as $key) {
        if (strtolower((string) $key) === 'tag') {
            unset($query[$key]);
        }
    }

    $query['tag'] = $tag;

    $rebuilt = '';
    if (!empty($parts['scheme'])) {
        $rebuilt .= $parts['scheme'] . '://';
    }
    if (!empty($parts['user'])) {
        $rebuilt .= $parts['user'];
        if (!empty($parts['pass'])) {
            $rebuilt .= ':' . $parts['pass'];
        }
        $rebuilt .= '@';
    }
    if (!empty($parts['host'])) {
        $rebuilt .= $parts['host'];
    }
    if (!empty($parts['port'])) {
        $rebuilt .= ':' . $parts['port'];
    }
    if (!empty($parts['path'])) {
        $rebuilt .= $parts['path'];
    }

    $rebuilt .= '?' . http_build_query($query, '', '&');

    if (!empty($parts['fragment'])) {
        $rebuilt .= '#' . $parts['fragment'];
    }

    return AMAZONLINKS_isSafeHttpUrl($rebuilt) ? $rebuilt : '';
}

function AMAZONLINKS_ruleMatches($content, $keyword, $mode)
{
    $keyword = trim((string) $keyword);
    if ($keyword === '') {
        return false;
    }

    if ($mode === 'substring') {
        return stripos($content, $keyword) !== false;
    }

    if ($mode === 'word') {
        $pattern = '/(?<![\\pL\\pN_])' . preg_quote($keyword, '/') . '(?![\\pL\\pN_])/iu';
        return preg_match($pattern, $content) === 1;
    }

    $pattern = '/' . preg_quote($keyword, '/') . '/iu';
    return preg_match($pattern, $content) === 1;
}

function AMAZONLINKS_storyTopics($template)
{
    global $_TABLES;

    $topics = array();
    $sid = '';

    foreach (array('story_id', 'sid') as $var) {
        $value = $template->get_var($var);
        if (!empty($value)) {
            $sid = (string) $value;
            break;
        }
    }

    if ($sid === '' || empty($_TABLES['topic_assignments'])) {
        return $topics;
    }

    $result = DB_query(
        "SELECT tid FROM {$_TABLES['topic_assignments']} "
        . "WHERE type = 'article' AND id = '" . DB_escapeString($sid) . "'"
    );

    while ($row = DB_fetchArray($result)) {
        if (isset($row['tid'])) {
            $topics[] = (string) $row['tid'];
        }
    }

    return array_unique($topics);
}

function AMAZONLINKS_ruleTopicMatches($ruleTopic, $storyTopics)
{
    $ruleTopic = trim((string) $ruleTopic);

    if ($ruleTopic === '' || strtolower($ruleTopic) === 'all') {
        return true;
    }

    return in_array($ruleTopic, $storyTopics, true);
}

function AMAZONLINKS_ruleUrl($rule)
{
    if ($rule['type'] === 'search') {
        return AMAZONLINKS_buildSearchUrl($rule['target']);
    }

    if (!AMAZONLINKS_isSafeHttpUrl($rule['target'])) {
        return '';
    }

    return AMAZONLINKS_addAffiliateTag($rule['target']);
}

function AMAZONLINKS_renderLink($label, $url, $className)
{
    global $_AMAZONLINKS_CONF;

    if (!AMAZONLINKS_isSafeHttpUrl($url)) {
        return '';
    }

    $attrs = '';
    $className = trim((string) $className);
    if ($className !== '') {
        $attrs .= ' class="' . htmlspecialchars($className, ENT_QUOTES, 'UTF-8') . '"';
    }
    $attrs .= ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . ' rel="nofollow sponsored noopener"';

    if (!empty($_AMAZONLINKS_CONF['new_window'])) {
        $attrs .= ' target="_blank"';
    }

    return '<a' . $attrs . '>'
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        . '</a>';
}

function AMAZONLINKS_displayMode()
{
    global $_AMAZONLINKS_CONF;

    $mode = isset($_AMAZONLINKS_CONF['display_mode'])
        ? strtolower(trim((string) $_AMAZONLINKS_CONF['display_mode']))
        : 'automatic';

    if (!in_array($mode, array('automatic', 'template', 'disabled'), true)) {
        $mode = 'automatic';
    }

    return $mode;
}

function AMAZONLINKS_buttonColor()
{
    global $_AMAZONLINKS_CONF;

    $allowed = array('theme', 'blue', 'green', 'orange', 'red', 'dark', 'gray');
    $color = isset($_AMAZONLINKS_CONF['button_color'])
        ? strtolower(trim((string) $_AMAZONLINKS_CONF['button_color']))
        : 'theme';

    if (!in_array($color, $allowed, true)) {
        $color = 'theme';
    }

    return $color;
}

function AMAZONLINKS_buildContextualBlock($content, $storyTopics)
{
    global $_AMAZONLINKS_CONF;

    if (empty($_AMAZONLINKS_CONF['enabled'])) {
        return '';
    }

    $content = html_entity_decode(strip_tags((string) $content), ENT_QUOTES, 'UTF-8');
    if (trim($content) === '') {
        return '';
    }

    $rules = AMAZONLINKS_loadRules();
    if (empty($rules)) {
        return '';
    }

    $found = array();
    $seenUrls = array();

    foreach ($rules as $rule) {
        if (empty($rule['enabled'])) {
            continue;
        }
        if (!AMAZONLINKS_ruleTopicMatches($rule['topic'], $storyTopics)) {
            continue;
        }
        if (!AMAZONLINKS_ruleMatches($content, $rule['keyword'], $rule['match'])) {
            continue;
        }

        $url = AMAZONLINKS_ruleUrl($rule);
        if ($url === '' || isset($seenUrls[$url])) {
            continue;
        }

        $seenUrls[$url] = true;
        $found[] = array('label' => $rule['label'], 'url' => $url);
    }

    $max = isset($_AMAZONLINKS_CONF['max_links']) ? (int) $_AMAZONLINKS_CONF['max_links'] : 5;
    if ($max < 1) {
        $max = 1;
    } elseif ($max > 20) {
        $max = 20;
    }

    if (count($found) > $max) {
        $found = array_slice($found, 0, $max);
    }

    if (empty($found)) {
        return '';
    }

    $title = isset($_AMAZONLINKS_CONF['title'])
        ? trim((string) $_AMAZONLINKS_CONF['title'])
        : 'Recommended resources';

    $colorClass = ' amazonlinks-color-' . AMAZONLINKS_buttonColor();

    $html = '<aside class="amazonlinks-block' . $colorClass . '" aria-label="'
        . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">';

    if ($title !== '') {
        $html .= '<div class="amazonlinks-title">'
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
            . '</div>';
    }

    $html .= '<div class="amazonlinks-list">';

    foreach ($found as $item) {
        $html .= AMAZONLINKS_renderLink($item['label'], $item['url'], 'amazonlinks-link');
    }

    $html .= '</div></aside>';

    return $html;
}

function plugin_templateSetVars_amazonlinks($templatename, $template)
{
    $storyTemplates = array(
        'storytext',
        'featuredstorytext',
        'archivestorytext',
        'storybodytext',
        'featuredstorybodytext',
        'archivestorybodytext'
    );

    if (!in_array($templatename, $storyTemplates, true)) {
        return;
    }

    $mode = AMAZONLINKS_displayMode();

    if ($mode === 'disabled') {
        return;
    }

    $visibleText = (string) $template->get_var('story_text_no_br');
    $title = (string) $template->get_var('story_title');
    $intro = (string) $template->get_var('story_introtext_only');
    $body = (string) $template->get_var('story_bodytext_only');

    if ($intro === '') {
        $intro = (string) $template->get_var('story_introtext');
    }

    $content = trim($visibleText);
    if ($content === '') {
        $content = trim($title . ' ' . $intro . ' ' . $body);
    } elseif ($title !== '') {
        $content = trim($title . ' ' . $content);
    }

    if ($content === '') {
        return;
    }

    $html = AMAZONLINKS_buildContextualBlock($content, AMAZONLINKS_storyTopics($template));

    if ($mode === 'template') {
        if ($html !== '') {
            $template->set_var('amazonlinks', $html);
        }
        return;
    }

    $displayType = (string) $template->get_var('story_display_type');
    if ($displayType === '') {
        $displayType = (string) $template->get_var('display_type');
    }

    if (!AMAZONLINKS_isFullStoryDisplay($displayType) || $html === '') {
        return;
    }

    if ($visibleText !== '') {
        if (strpos($visibleText, 'class="amazonlinks-block"') === false) {
            $template->set_var('story_text_no_br', $visibleText . $html);
        }
        return;
    }

    $visibleText = (string) $template->get_var('story_introtext');
    if ($visibleText !== '' && strpos($visibleText, 'class="amazonlinks-block"') === false) {
        $template->set_var('story_introtext', $visibleText . $html);
    }
}

function plugin_getheadercode_amazonlinks()
{
    global $_CONF, $_AMAZONLINKS_CONF;

    if (empty($_AMAZONLINKS_CONF['enabled']) || empty($_AMAZONLINKS_CONF['load_css'])) {
        return '';
    }

    $href = $_CONF['site_url'] . '/amazonlinks/css/amazonlinks.css';

    return '<link rel="stylesheet" href="'
        . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
        . '">' . LB;
}

function plugin_getadminoption_amazonlinks()
{
    global $_CONF, $LANG_AMAZONLINKS;

    if (!SEC_hasRights('amazonlinks.admin')) {
        return array();
    }

    return array(
        $LANG_AMAZONLINKS['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/amazonlinks/index.php',
        ''
    );
}

function plugin_cclabel_amazonlinks()
{
    global $_CONF, $LANG_AMAZONLINKS;

    if (!SEC_hasRights('amazonlinks.admin')) {
        return false;
    }

    return array(
        $LANG_AMAZONLINKS['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/amazonlinks/index.php',
        ''
    );
}

function plugin_chkVersion_amazonlinks()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/amazonlinks/autoinstall.php';
    $params = plugin_autoinstall_amazonlinks('amazonlinks');

    return $params['info']['pi_version'];
}

function plugin_upgrade_amazonlinks()
{
    global $_CONF, $_TABLES;

    require_once $_CONF['path'] . 'plugins/amazonlinks/autoinstall.php';
    require_once $_CONF['path'] . 'plugins/amazonlinks/install_defaults.php';

    if (!plugin_compatible_with_this_version_amazonlinks('amazonlinks')) {
        return 3002;
    }

    if (!plugin_initconfig_amazonlinks()) {
        return false;
    }

    $c = config::get_instance();

    if (DB_count($_TABLES['conf_values'], array('name', 'group_name'), array('display_mode', 'amazonlinks')) == 0) {
        $c->add('display_mode', 'automatic', 'select', 0, 0, 3, 60, true, 'amazonlinks', 0);
    }

    if (DB_count($_TABLES['conf_values'], array('name', 'group_name'), array('button_color', 'amazonlinks')) == 0) {
        $c->add('button_color', 'theme', 'select', 0, 0, 4, 70, true, 'amazonlinks', 0);
    }

    if (DB_count($_TABLES['conf_values'], array('name', 'group_name'), array('autotag_css', 'amazonlinks')) == 0) {
        $c->add('autotag_css', 1, 'select', 0, 0, 1, 100, true, 'amazonlinks', 0);
    }

    if (!AMAZONLINKS_installConfigPermission()) {
        return false;
    }

    if (!AMAZONLINKS_ensureDataDir()) {
        return false;
    }

    if (!file_exists(AMAZONLINKS_rulesFile())) {
        $defaultRules = $_CONF['path'] . 'plugins/amazonlinks/defaults/rules.json';
        if (file_exists($defaultRules)) {
            $json = file_get_contents($defaultRules);
            if ($json === false || !AMAZONLINKS_writeJsonAtomically(AMAZONLINKS_rulesFile(), $json)) {
                return false;
            }
        }
    }

    DB_query(
        "UPDATE {$_TABLES['plugins']} "
        . "SET pi_version = '1.1.0', pi_gl_version = '2.1.1' "
        . "WHERE pi_name = 'amazonlinks'"
    );

    return true;
}

function plugin_autouninstall_amazonlinks()
{
    return array(
        'tables'     => array(),
        'groups'     => array('Amazon Links Admin'),
        'features'   => array('amazonlinks.admin', 'config.amazonlinks.tab_main'),
        'php_blocks' => array(),
        'vars'       => array()
    );
}

function AMAZONLINKS_parseAutotagPayload($autotag)
{
    $payload = '';

    if (isset($autotag['tagstr'])) {
        $tagstr = trim((string) $autotag['tagstr']);
        $prefix = '[amazonlinks:';
        if (stripos($tagstr, $prefix) === 0 && substr($tagstr, -1) === ']') {
            $payload = substr($tagstr, strlen($prefix), -1);
        }
    }

    if ($payload === '') {
        $parm1 = isset($autotag['parm1']) ? trim((string) $autotag['parm1']) : '';
        $parm2 = isset($autotag['parm2']) ? trim((string) $autotag['parm2']) : '';
        $payload = $parm1;
        if ($parm2 !== '') {
            $payload .= '|' . $parm2;
        }
    }

    $target = trim($payload);
    $label = '';

    if (strpos($payload, '|') !== false) {
        list($target, $label) = explode('|', $payload, 2);
        $target = trim($target);
        $label = trim($label);
    }

    return array($target, $label);
}

function plugin_autotags_amazonlinks($op, $content = '', $autotag = array())
{
    global $LANG_AMAZONLINKS, $_AMAZONLINKS_CONF;

    if ($op === 'tagname') {
        return 'amazonlinks';
    }
    if ($op === 'description') {
        return array('amazonlinks' => $LANG_AMAZONLINKS['autotag_description']);
    }
    if ($op === 'permission' || $op === 'nopermission') {
        return ($op === 'permission') ? array('amazonlinks') : array();
    }
    if ($op === 'closetag') {
        return array();
    }
    if ($op !== 'parse') {
        return $content;
    }

    list($target, $label) = AMAZONLINKS_parseAutotagPayload($autotag);

    if ($target === '') {
        return $content;
    }

    $url = '';

    if (stripos($target, 'asin:') === 0) {
        $asin = substr($target, 5);
        $url = AMAZONLINKS_buildAsinUrl($asin);
        if ($label === '') {
            $label = 'Amazon';
        }
    } else {
        $url = AMAZONLINKS_buildSearchUrl($target);
        if ($label === '') {
            $label = $target;
        }
    }

    if ($url === '') {
        return $content;
    }

    $className = !empty($_AMAZONLINKS_CONF['autotag_css']) ? 'amazonlinks-autotag' : '';
    $link = AMAZONLINKS_renderLink($label, $url, $className);
    return str_replace($autotag['tagstr'], $link, $content);
}
