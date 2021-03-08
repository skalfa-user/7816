<?php




OW::getRouter()->removeRoute('users-search');

OW::getRouter()->addRoute(
        new OW_Route('users-search', '/user-search/', 'MEMBERX_MCTRL_Search', 'form')
);

OW::getRouter()->removeRoute('users-search-result');
OW::getRouter()->addRoute(
    new OW_Route('users-search-result', '/users/search-result/:orderType/', 'MEMBERX_MCTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);

OW::getRouter()->addRoute(
    new OW_Route('memberx.members', '/members/:orderType/', 'MEMBERX_MCTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);

OW::getRouter()->addRoute(
    new OW_Route('memberx.map', '/users/search/map/', 'MEMBERX_MCTRL_Search', 'map')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.load_list_action', '/memberx/ajax/load-list', 'MEMBERX_CTRL_Ajax', 'loadList')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.quick_search', '/memberx/quick-search', 'MEMBERX_MCTRL_Search', 'quickSearch')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.quick_search_action', '/memberx/ajax/quick-search', 'MEMBERX_CTRL_Ajax', 'quickSearch')
);

if (class_exists('WINKS_CTRL_Winks')){
    OW::getRouter()->addRoute(new OW_Route('winks.rsp', 'winks/rsp', 'WINKS_CTRL_Winks', 'ajaxRsp'));
}

OW::getRouter()->addRoute(new OW_Route('memberx.featured_users', 'profile/featured/', 'MEMBERX_MCTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')))) ;


if (OW::getPluginManager()->isPluginActive('matchmaking')){
            OW::getRouter()->removeRoute('matchmaking_members_page');
            OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page', 'profile/matches/', 'MEMBERX_MCTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'match_compatibility')))) ;
}

MEMBERX_MCLASS_EventHandler::getInstance()->init();
MEMBERX_CLASS_EventHandler::getInstance()->genericInit();
