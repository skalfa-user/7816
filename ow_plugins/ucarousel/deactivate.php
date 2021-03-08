<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

require_once dirname(__FILE__) . DS . 'classes' . DS . 'groups_bridge.php';

BOL_ComponentAdminService::getInstance()->deleteWidget('UCAROUSEL_CMP_UsersWidget');
UCAROUSEL_CLASS_GroupsBridge::getInstance()->removeWidget('UCAROUSEL_CMP_GroupUsersWidget');