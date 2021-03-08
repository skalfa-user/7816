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

use \OW as OW;

$staticPath = OW_DIR_STATIC_PLUGIN . \OW::getPluginManager()->getPlugin('oacompress')->getModuleName();
$pluginFilesPath = \OW::getPluginManager()->getPlugin('oacompress')->getPluginFilesDir();

if ( OW::getStorage()->fileExists($staticPath) )
{
    OW::getStorage()->removeDir($staticPath);
}

if ( OW::getStorage()->fileExists($pluginFilesPath) )
{
    OW::getStorage()->removeDir($pluginFilesPath);
}

