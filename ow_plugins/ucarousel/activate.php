<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

require_once dirname(__FILE__) . DS . 'classes' . DS . 'groups_bridge.php';

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('UCAROUSEL_CMP_UsersWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_TOP, 0);

$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);

UCAROUSEL_CLASS_GroupsBridge::getInstance()->addWidget('UCAROUSEL_CMP_GroupUsersWidget');