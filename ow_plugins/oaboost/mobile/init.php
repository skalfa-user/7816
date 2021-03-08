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

$eventHandler = new oacompress\classes\EventHandler();
$eventHandler->mobileInit();
