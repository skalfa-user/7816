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

$dbPref = OW_DB_PREFIX;

$queryList = array(
    "ALTER TABLE `{$dbPref}oacompress_cache_tag_item` DROP `type`",
    "ALTER TABLE  `{$dbPref}oacompress_cache_tag_item` ADD  `status` TINYINT NOT NULL DEFAULT '1';"
    
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

$configService = UPDATER::getConfigService();
$pluginKey = "oacompress";
$configsToAdd = array(
    "sql_cache_page_for_guests" => 1,
    "clear_plugin_cache" => 1,
    "cache_db_queries" => 1,
    "cache_storage" => ""
);

foreach ( $configsToAdd as $name => $val )
{
    if ( !$configService->configExists($pluginKey, $name) )
    {
        $configService->addConfig($pluginKey, $name, $val);
    }
}

$configService->saveConfig($pluginKey, "encode_image_size", 0);

$configVal = $configService->getValue($pluginKey, "enable_sql_cache");
$dbCache = 1;

if ( $configVal == "no" )
{
    $configVal = "sql";
    $dbCache = 0;
}

$configService->saveConfig($pluginKey, "cache_storage", $configVal);
$configService->saveConfig($pluginKey, "cache_db_queries", $dbCache);
$configService->saveConfig($pluginKey, "mark_all_expired", 1);

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . "langs.zip", $pluginKey);
