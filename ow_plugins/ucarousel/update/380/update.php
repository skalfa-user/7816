<?php

require_once dirname(dirname(dirname(__FILE__))) . DS . 'classes' . DS . 'groups_bridge.php';

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'ucarousel');

UCAROUSEL_CLASS_GroupsBridge::getInstance()->addWidget('UCAROUSEL_CMP_GroupUsersWidget');

try
{
    $widgetService = Updater::getWidgetService();
    $widget = $widgetService->addWidget('UCAROUSEL_CMP_UsersWidget', false);
    $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
}
catch ( Exception $e ) {}