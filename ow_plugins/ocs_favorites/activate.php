<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /activate.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites
 * @since 1.5.3
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('OCSFAVORITES_CMP_MyFavoritesWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);

$widget = $widgetService->addWidget('OCSFAVORITES_CMP_IndexWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);

OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'ocsfavorites.list', 'ocsfavorites', 'favorites', OW_Navigation::VISIBLE_FOR_MEMBER);