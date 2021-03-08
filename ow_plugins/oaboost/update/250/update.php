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
$queryList = array(
    "DROP TABLE `" . OW_DB_PREFIX . "oacompress_cache_item`",
    "DROP TABLE `" . OW_DB_PREFIX . "oacompress_cache_tag_item`"
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
    "compress_html" => 0
);

foreach ( $configsToAdd as $name => $val )
{
    if ( !$configService->configExists($pluginKey, $name) )
    {
        $configService->addConfig($pluginKey, $name, $val);
    }
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . "langs.zip", $pluginKey);
