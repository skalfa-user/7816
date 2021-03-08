<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /install.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites
 * @since 1.5.3
 */

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsfavorites_favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `favoriteId` int(11) NOT NULL,
  `addTimestamp` int(11) NOT NULL,
  `viewed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`favoriteId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$config = OW::getConfig();

if ( !$config->configExists('ocsfavorites', 'can_view') )
{
    $config->addConfig('ocsfavorites', 'can_view', 1, 'Users can view who added them to favorites');
}

$authorization = OW::getAuthorization();
$groupName = 'ocsfavorites';
$authorization->addGroup($groupName, false);
$authorization->addAction($groupName, 'add_to_favorites', false);
$authorization->addAction($groupName, 'view_users', false);

OW::getPluginManager()->addPluginSettingsRouteName('ocsfavorites', 'ocsfavorites.admin');

$path = OW::getPluginManager()->getPlugin('ocsfavorites')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ocsfavorites');