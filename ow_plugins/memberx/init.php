<?php


OW::getRouter()->removeRoute('users-search');

OW::getRouter()->addRoute(
    new OW_Route('users-search', '/users/search/', 'MEMBERX_CTRL_Search', 'form')
);
OW::getRouter()->removeRoute('users-search-result');
OW::getRouter()->addRoute(
    new OW_Route('users-search-result', '/users/search-result/:orderType/', 'MEMBERX_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);

OW::getRouter()->addRoute(
    new OW_Route('memberx.members', '/members/:orderType/', 'MEMBERX_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);

OW::getRouter()->addRoute(
    new OW_Route('memberx.details', '/users/search/details/', 'MEMBERX_CTRL_Search', 'details')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.map', '/users/search/location/', 'MEMBERX_CTRL_Search', 'map')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.follow', '/users/search/ajax/follow/', 'MEMBERX_CTRL_Ajax', 'follow')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.unfollow', '/users/search/ajax/unfollow/', 'MEMBERX_CTRL_Ajax', 'unfollow')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.addfriend', '/users/search/ajax/addfriend/', 'MEMBERX_CTRL_Ajax', 'addfriend')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.removefriend', '/users/search/ajax/removefriend/', 'MEMBERX_CTRL_Ajax', 'removefriend')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.block', '/users/search/ajax/block/', 'MEMBERX_CTRL_Ajax', 'block')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.unblock', '/users/search/ajax/unblock/', 'MEMBERX_CTRL_Ajax', 'unblock')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.invitetoevent', '/users/search/ajax/invite-to-event/', 'MEMBERX_CTRL_Ajax', 'invitetoevent')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.invitetogroups', '/users/search/ajax/invite-to-groups/', 'MEMBERX_CTRL_Ajax', 'invitetogroups')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.quick_search_action', '/memberx/ajax/quick-search', 'MEMBERX_CTRL_Ajax', 'quickSearch')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.load_list_action', '/memberx/ajax/load-list', 'MEMBERX_CTRL_Ajax', 'loadList')
);
OW::getRouter()->addRoute(
    new OW_Route('memberx.admin_quick_search_setting', '/admin/memberx/quick-search-settings', 'MEMBERX_CTRL_Admin', 'quickSearchSettings')
);
OW::getRouter()->addRoute(new OW_Route('memberx.admin_general_setting', '/admin/memberx/general-settings', 'MEMBERX_CTRL_Admin', 'generalSettings'));
OW::getRouter()->addRoute(new OW_Route('memberx.admin_profile_field_setting', '/admin/memberx/profile-filed-settings', 'MEMBERX_CTRL_Admin', 'profileFieldSettings'));

OW::getRouter()->addRoute(new OW_Route('memberx.featured_users', 'profile/featured/', 'MEMBERX_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')))) ;

if (OW::getPluginManager()->isPluginActive('matchmaking')){
            //OW::getRouter()->removeRoute('matchmaking_members_page_sorting');
            OW::getRouter()->removeRoute('matchmaking_members_page');
            OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page', 'profile/matches/', 'MEMBERX_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'match_compatibility')))) ;
            //OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page_sorting', 'profile/matches/:orderType', 'MEMBERX_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'match_compatibility')))) ;
}

MEMBERX_CLASS_EventHandler::getInstance()->init();
MEMBERX_CLASS_EventHandler::getInstance()->genericInit();
MEMBERX_CLASS_EventHandler::getInstance()->initDesktop();

function memberx_disable_fields_on_edit_profile_question(OW_Event $event)
{
    $params = $event->getParams();
    $data = $event->getData();

    if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question )
    {
        $dto = $params['questionDto'];

        if ( in_array( $dto->name, array('sex', 'match_sex', 'match_age') ) )
        {
            $data['disable_on_search'] = false;
            $event->setData($data);
        }
    }
}
OW::getEventManager()->bind('admin.disable_fields_on_edit_profile_question', 'memberx_disable_fields_on_edit_profile_question');


//OW::getRouter()->removeRoute('skandroid.user_get_search_questions');
//            OW::getRouter()->addRoute(new OW_Route('skandroid.user_get_search_questions', 'user/get-search-questions', 'MEMBERX_ACTRL_AndroidUser', 'getSearchQuestions'));

//$sexQuestion = BOL_QuestionService::getInstance()->findQuestionByName('sex');
//print_r($sexQuestion);