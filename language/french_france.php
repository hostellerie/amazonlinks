<?php

/* Fichier de langue français pour AmazonLinks 1.1.0 */

$LANG_AMAZONLINKS = array(
    'plugin_name' => 'Liens Amazon',
    'admin_title' => 'Liens Amazon',
    'admin_intro' => 'Gérez ici les règles de liens Amazon contextuels. Les paramètres généraux sont enregistrés dans la configuration native de Geeklog.',
    'open_configuration' => 'Ouvrir la configuration générale',
    'rules_title' => 'Règles contextuelles',
    'rules_help' => 'Utilisez une recherche Amazon ou une URL HTTP/HTTPS complète. Un identifiant de sujet limite la règle à ce sujet Geeklog ; laissez vide ou utilisez "all" pour tous les sujets.',
    'data_directory' => 'Dossier de données privé',
    'legacy_warning' => 'Un ancien fichier de configuration PHP AmazonLinks 1.0 a été détecté. La version 1.1 ne l’exécute pas. Recréez les règles utiles ci-dessous, puis archivez ou supprimez manuellement l’ancien fichier.',
    'invalid_token' => 'La requête n’a pas pu être validée. Rechargez la page et recommencez.',
    'saved' => 'Les règles de liens Amazon ont été enregistrées.',
    'save_failed' => 'Impossible d’enregistrer les règles. Vérifiez les droits du dossier de données et le journal d’erreurs Geeklog.',
    'save_rules' => 'Enregistrer les règles',
    'enabled' => 'Actif',
    'keyword' => 'Mot-clé / expression',
    'label' => 'Libellé du lien',
    'type' => 'Type de cible',
    'target' => 'Recherche / URL',
    'match' => 'Correspondance',
    'topic' => 'ID du sujet',
    'priority' => 'Priorité',
    'search' => 'Recherche Amazon',
    'url' => 'URL directe',
    'word' => 'Mot entier',
    'phrase' => 'Expression',
    'substring' => 'Sous-chaîne',
    'default_block_title' => 'Ressources recommandées sur Amazon',
    'affiliate_disclosure' => 'Liens affiliés : le site peut percevoir une commission sans coût supplémentaire pour vous.',
    'template_title' => 'Intégration au thème',
    'template_help' => 'Le mode automatique ne nécessite aucune modification du thème. Le mode template permet d’ajouter <code>{amazonlinks}</code> dans les templates d’article souhaités, généralement <code>article/storytext.thtml</code> et <code>article/featuredstorytext.thtml</code>.',
    'autotag_title' => 'Autotags',
    'autotag_description' => '[amazonlinks:recherche] crée un lien de recherche Amazon. Ajoutez |libellé pour personnaliser le texte, par exemple [amazonlinks:geeklog|Cliquez ici]. Utilisez [amazonlinks:asin:ASIN|libellé] pour un produit précis.',
    'documentation_title' => 'Documentation du plugin',
    'documentation_quickstart_title' => 'Mise en route',
    'documentation_quickstart' => 'Configurez votre boutique Amazon et votre tag d’affiliation, puis créez au moins une règle dont le mot-clé est présent dans le titre, l’introduction ou le corps d’un article.',
    'documentation_display_title' => 'Modes d’affichage',
    'documentation_display_auto' => 'Automatique : le bloc est ajouté uniquement sur la page complète de l’article, sans modification du thème.',
    'documentation_display_template' => 'Variable de template : ajoutez {amazonlinks} dans le template d’article à l’endroit exact où le bloc doit apparaître.',
    'documentation_display_disabled' => 'Désactivé : aucun bloc contextuel n’est affiché ; les autotags restent disponibles.',
    'documentation_color_title' => 'Couleur des boutons',
    'documentation_color' => 'Choisissez Couleur du thème pour conserver le rendu naturel du template, ou sélectionnez une couleur prédéfinie : bleu, vert, orange, rouge, sombre ou gris.',
    'documentation_rules_title' => 'Règles',
    'documentation_rules' => 'Chaque règle associe un mot-clé ou une expression à une recherche Amazon ou à une URL directe. Les priorités les plus élevées sont traitées en premier. Vous pouvez limiter une règle à un sujet Geeklog.',
    'documentation_tag_title' => 'Tag d’affiliation',
    'documentation_tag' => 'Le tag configuré dans AmazonLinks est toujours utilisé. Si une URL directe contient déjà un paramètre tag, celui-ci est supprimé et remplacé par votre tag configuré.',
    'documentation_autotags_title' => 'Autotags',
    'documentation_storage_title' => 'Stockage des données',
    'documentation_storage' => 'Les paramètres généraux utilisent la Configuration API de Geeklog. Les règles sont enregistrées en JSON dans un dossier privé dérivé de path_data ; aucun fichier PHP modifiable n’est exécuté.'
);

$LANG_configsections['amazonlinks'] = array(
    'label' => 'Liens Amazon',
    'title' => 'Configuration de Liens Amazon'
);

$LANG_confignames['amazonlinks'] = array(
    'enabled' => 'Activer Liens Amazon ?',
    'title' => 'Titre du bloc',
    'marketplace' => 'Boutique Amazon',
    'affiliate_tag' => 'Tag d’affiliation',
    'max_links' => 'Nombre maximal de liens contextuels',
    'display_mode' => 'Mode d’affichage',
    'button_color' => 'Couleur des boutons',
    'new_window' => 'Ouvrir les liens dans une nouvelle fenêtre ?',
    'load_css' => 'Charger la feuille de style du plugin ?',
    'autotag_css' => 'Appliquer le style bouton aux autotags ?'
);

$LANG_configsubgroups['amazonlinks'] = array(
    'sg_main' => 'Paramètres principaux'
);

$LANG_tab['amazonlinks'] = array(
    'tab_main' => 'Principal'
);

$LANG_fs['amazonlinks'] = array(
    'fs_main' => 'Paramètres généraux'
);

$LANG_configselects['amazonlinks'] = array(
    0 => array('Oui' => 1, 'Non' => 0),
    1 => array('Oui' => 1, 'Non' => 0),
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
        'Automatique après l’article' => 'automatic',
        'Variable de template {amazonlinks}' => 'template',
        'Désactivé' => 'disabled'
    ),
    4 => array(
        'Couleur du thème' => 'theme',
        'Bleu' => 'blue',
        'Vert' => 'green',
        'Orange' => 'orange',
        'Rouge' => 'red',
        'Sombre' => 'dark',
        'Gris' => 'gray'
    )
);

$PLG_amazonlinks_MESSAGE3001 = 'Mise à jour du plugin non prise en charge.';
$PLG_amazonlinks_MESSAGE3002 = 'Cette version de Geeklog n’est pas prise en charge.';
