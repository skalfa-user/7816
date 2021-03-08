<?php
/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

OW::getRouter()->addRoute(
        new OW_Route('force_admin_settings', 'force/admin/settings', 'FORCE_CTRL_Admin', 'settings'));

OW::getRouter()->addRoute(
    new OW_Route('force_admin_settings_auto', 'force/admin/settings_auto', 'FORCE_CTRL_Admin', 'settingsAuto'));

OW::getRouter()->addRoute(
    new OW_Route('force_admin_settings_help', 'force/admin/settings_help', 'FORCE_CTRL_Admin', 'settingsHelp'));

FORCE_CLASS_EventHandler::getInstance()->init();




