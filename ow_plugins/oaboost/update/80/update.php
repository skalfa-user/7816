<?php

/**
 * Copyright (c) 2011 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package oacompress
 * @since 1.0
 */
$configService = UPDATER::getConfigService();
$dbPrefix = \OW_DB_PREFIX;

$configsToAdd = array(
    "enable_sql_cache" => 1,
    "table_optimize_ts" => 0,
    "table_optimize_period" => 3600 * 24 * 7
);

foreach ( $configsToAdd as $name => $val )
{
    if ( !$configService->configExists("oacompress", $name) )
    {
        $configService->addConfig("oacompress", $name, $val);
    }
}

$queryList = array(
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}\OACOMPRESS_cache_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(70) NOT NULL,
  `data` text NOT NULL,
  `expireTs` int(10) unsigned NOT NULL,
  `instantLoad` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `expireTs` (`expireTs`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}\OACOMPRESS_cache_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}\OACOMPRESS_cache_tag_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itemId` (`itemId`,`tagId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "UPDATE `{$dbPrefix}base_language_prefix` SET `label` = '0xArt Speed Optimizer' WHERE `prefix` = 'oacompress'",
    "UPDATE `{$dbPrefix}\OACOMPRESS_item` SET `status` = 2"
);

foreach ( $queryList as $query )
{
    try
    {
        Updater::getDbo()->query($query);
    }
    catch ( Exception $ex )
    {
        Updater::getLogger()->addEntry($ex->getMessage(), "oacompress");
    }
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'oacompress');
