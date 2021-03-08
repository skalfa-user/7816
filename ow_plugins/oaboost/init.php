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
 * @since 1.5
 */
include_once OW::getPluginManager()->getPlugin("oacompress")->getRootDir() . "vendor/autoload.php";

OW::getAutoloader()->addClass("OACOMPRESS_CTRL_Admin",
    OW::getPluginManager()->getPlugin("oacompress")->getCtrlDir() . "Admin.php");

// admin route add
\OW::getRouter()->addRoute(new \OW_Route("oacompress.admin", "admin/oacompress", "OACOMPRESS_CTRL_Admin", "index"));
\OW::getRouter()->addRoute(new \OW_Route("oacompress.admin_index", "admin/oacompress", "OACOMPRESS_CTRL_Admin", "index"));
\OW::getRouter()->addRoute(new \OW_Route("oacompress.admin_cache_control", "admin/oacompress/cache-control",
    "OACOMPRESS_CTRL_Admin", "cacheControl"));
\OW::getRouter()->addRoute(new \OW_Route("oacompress.admin_storages", "admin/oacompress/storages",
    "OACOMPRESS_CTRL_Admin", "storages"));


$eventHandler = new oacompress\classes\EventHandler();
$eventHandler->init();
