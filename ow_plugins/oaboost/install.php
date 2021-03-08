<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.oaboost
 * @since 1.5
 */
$dbPrefix = OW_DB_PREFIX;

$queryList = array(
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}oacompress_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `fileList` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash_index` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
);

foreach ( $queryList as $query )
{
    \OW::getDbo()->query($query);
}

$configArray = array(
    "compress_css" => 1,
    "compress_js" => 1,
    "compress_content" => 0,
    "encode_image_size" => 3072,
    "enable_sql_cache" => "sql",
    "table_optimize_ts" => 0,
    "table_optimize_period" => 3600 * 24 * 7,
    "sql_cache_exclude_table" => "base_user_online,base_config",
    "sql_backend_hits" => 0,
    "sql_backend_misses" => 0,
    "memcached_attrs" => "{}",
    "plugin_init" => 0,
    "mongo_attrs" => "{}",
    "mark_all_expired" => 0,
    "sql_cache_page_for_guests" => 0,
    "clear_plugin_cache" => 0,
    "cache_db_queries" => 0,
    "cache_storage" => "",
    "compress_html" => 0
);

$config = \OW::getConfig();

foreach ( $configArray as $configName => $configVal )
{
    if ( !$config->configExists("oacompress", $configName) )
    {
        $config->addConfig("oacompress", $configName, $configVal);
    }
}

\OW::getPluginManager()->addPluginSettingsRouteName("oacompress", "oacompress.admin");
\OW::getLanguage()->importPluginLangs(\OW::getPluginManager()->getPlugin("oacompress")->getRootDir() . "langs.zip",
    "oacompress");

$staticPath = OW_DIR_STATIC_PLUGIN . \OW::getPluginManager()->getPlugin("oacompress")->getModuleName();
$pluginFilesPath = \OW::getPluginManager()->getPlugin("oacompress")->getPluginFilesDir();
$userfilesDir = \OW::getPluginManager()->getPlugin("oacompress")->getUserFilesDir();

\OW::getStorage()->mkdir($staticPath);
\OW::getStorage()->mkdir($userfilesDir);

$dirsTomake = array($staticPath, $pluginFilesPath, $userfilesDir);

foreach ( $dirsTomake as $dir )
{
    if ( !file_exists($dir) )
    {
        mkdir($dir);
    }

    chmod($dir, 0777);
}