<?php

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('MEMBERX_CMP_QuickSearchWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

$widget = $widgetService->addWidget('MEMBERX_CMP_LatestActivityWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_TOP);


$widget = BOL_MobileWidgetService::getInstance()->addWidget('MEMBERX_MCMP_QuickSearchWidget', false);
$placeWidget = BOL_MobileWidgetService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
BOL_MobileWidgetService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN );

try {
    OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'memberx.members', 'memberx', 'members', OW_Navigation::VISIBLE_FOR_ALL);
    OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'memberx.featured_users', 'memberx', 'featured_users', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) { }

try {
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'memberx.members', 'memberx', 'mobile_menu_item_members', OW_Navigation::VISIBLE_FOR_ALL);
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'memberx.featured_users', 'memberx', 'mobile_menu_item_featured_users', OW_Navigation::VISIBLE_FOR_ALL);
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'users-search', 'memberx', 'mobile_menu_item_search', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) { 
    //print_r($e->getMessage());
    //exit();
}

try {
    /* @var $menu BOL_MenuItem */
    $menu = BOL_NavigationService::getInstance()->findMenuItem('base', 'users_main_menu_item');

    if ( !empty($menu) )
    {
        $menu->type = BOL_NavigationService::MENU_TYPE_HIDDEN;
        BOL_NavigationService::getInstance()->saveMenuItem($menu);
    }
}
catch ( Exception $e ) { }


$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "memberx_search_id` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `searchId` int(10) NOT NULL DEFAULT 0,
  `md5` VARCHAR(64) NOT NULL DEFAULT '',
  `data` TEXT NOT NULL DEFAULT '',
  `creationTime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "memberx_search_result` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `searchId` int(10) NOT NULL DEFAULT 0,
  `md5` VARCHAR(64) NOT NULL DEFAULT '',
  `data` TEXT NOT NULL DEFAULT '',
  `itemCount` int(10) NOT NULL DEFAULT 0,
  `dtoList` TEXT NOT NULL DEFAULT '',
  `creationTime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);