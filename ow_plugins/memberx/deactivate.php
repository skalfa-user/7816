<?php


BOL_ComponentAdminService::getInstance()->deleteWidget('MEMBERX_CMP_QuickSearchWidget');
BOL_MobileWidgetService::getInstance()->deleteWidget('MEMBERX_MCMP_QuickSearchWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('MEMBERX_CMP_LatestActivityWidget');

try {
    OW::getNavigation()->deleteMenuItem('memberx', 'members');
    OW::getNavigation()->deleteMenuItem('memberx', 'featured_users');
}
catch ( Exception $e ) { }

try {
    OW::getNavigation()->deleteMenuItem('memberx', 'mobile_menu_item_search');
    OW::getNavigation()->deleteMenuItem('memberx', 'mobile_menu_item_members');
    OW::getNavigation()->deleteMenuItem('memberx', 'mobile_menu_item_featured_users');
    
}
catch ( Exception $e ) { }

try {
    /* @var $menu BOL_MenuItem */
    $menu = BOL_NavigationService::getInstance()->findMenuItem('base', 'users_main_menu_item');

    if ( !empty($menu) )
    {
        $menu->type = BOL_NavigationService::MENU_TYPE_MAIN;
        BOL_NavigationService::getInstance()->saveMenuItem($menu);
    }
}
catch ( Exception $e ) { }