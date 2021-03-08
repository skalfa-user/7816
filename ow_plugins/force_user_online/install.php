<?php

/**
 * Copyright (c) 2017, Pryadkin Sergey
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


$dbPrefix = OW_DB_PREFIX;
$pluginKey = 'force';
OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, 'force_admin_settings');


 $path = OW::getPluginManager()->getPlugin($pluginKey)->getRootDir() . 'langs.zip';
 OW::getLanguage()->importPluginLangs($path, $pluginKey);
 
 $sql = array();

 $sql[] = "CREATE TABLE `{$dbPrefix}force_task` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `amount_of_users` INT UNSIGNED NOT NULL,
          `total_amount` int(10) unsigned NOT NULL,
          `status` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
          `command` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
          PRIMARY KEY (`id`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
 
 $sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}force_fake_online_users` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";


$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}force_actions` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `amount` int(10) unsigned NOT NULL,
          `hours` int(10) unsigned NOT NULL,
          `minutes` int(10) unsigned NOT NULL,
          `action` varchar(50) NOT NULL,
          `triggered` tinyint(2) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
 


foreach ( $sql as $q )
{
    try 
    {
        OW::getDbo()->query( $q );
    } 
    catch (Exception $ex) 
    {
        $logger = OW::getLogger();
        $logger->addEntry($ex->getMessage());
        $logger->writeLog();
    }
}