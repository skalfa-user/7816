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
    "sql_cache_exclude_table" => "base_user_online,base_config",
    "sql_backend_hits" => 0,
    "sql_backend_misses" => 0,
    "memcached_attrs" => "{}",
    "plugin_init" => 0,
    "mongo_attrs" => "{}",
    "mark_all_expired" => 0
);

foreach ( $configsToAdd as $name => $val )
{
    if ( !$configService->configExists("oacompress", $name) )
    {
        $configService->addConfig("oacompress", $name, $val);
    }
}

$queryList = array(
    "DROP TABLE IF EXISTS `{$dbPrefix}\OACOMPRESS_cache_tag`",
    "DROP TABLE IF EXISTS `{$dbPrefix}\OACOMPRESS_cache_item`",
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}\OACOMPRESS_cache_item` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `itemKey` varchar(60) NOT NULL,
      `data` mediumtext NOT NULL,
      `expireTs` int(10) unsigned NOT NULL,
      `instantLoad` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `expireTs` (`expireTs`),
      KEY `itemKey` (`itemKey`(40))
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "DROP TABLE IF EXISTS `{$dbPrefix}\OACOMPRESS_cache_tag_item`",
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}\OACOMPRESS_cache_tag_item` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `tag` varchar(60) NOT NULL,
      `itemKey` varchar(60) NOT NULL,
      `expireTs` int(10) unsigned NOT NULL DEFAULT '0',
      `type` varchar(5) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `tagItemKey` (`tag`(40),`itemKey`(40)),
      KEY `expireTs` (`expireTs`),
      KEY `itemKey` (`itemKey`(40)),
      KEY `type` (`type`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "UPDATE `{$dbPrefix}base_config` SET `value` = 4096 WHERE `key` = 'oacompress' AND `name` = 'encode_image_size'",
    "UPDATE `{$dbPrefix}base_config` SET `value` = 'sql' WHERE `key` = 'oacompress' AND `name` = 'enable_sql_cache'"
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
