<?php

 if (memberx_api_init_isAndroidRequest() && OW::getPluginManager()->isPluginActive('skandroid')){
    $service = SKANDROID_ABOL_Service::getInstance();
    OW::getRouter()->removeRoute('skandroid.user_get_search_questions');
    $service->addRoute('skandroid.user_get_search_questions', 'user/get-search-questions', 'MEMBERX_ACTRL_AndroidUser', 'getSearchQuestions'); 

    OW::getRouter()->removeRoute('skandroid.join_question_list');
    $service->addRoute('skandroid.join_question_list', 'sign-up/questions', 'MEMBERX_ACTRL_AndroidSignUp', 'questionList');
    OW::getRequestHandler()->addCatchAllRequestsExclude("skandroid.not_authenticated", "MEMBERX_ACTRL_AndroidSignUp", "questionList");

    OW::getRouter()->removeRoute('skandroid.join_user');
    $service->addRoute('skandroid.join_user', 'sign-up/save', 'MEMBERX_ACTRL_AndroidSignUp', 'save');
    OW::getRequestHandler()->addCatchAllRequestsExclude("skandroid.not_authenticated", "MEMBERX_ACTRL_AndroidSignUp", "save");
    
    OW::getRouter()->removeRoute('skandroid.speedmatches_get_list');
    $service->addRoute('skandroid.speedmatches_get_list', 'speedmatches/list', 'MEMBERX_ACTRL_AndroidSpeedmatches', 'getList');
    
    OW::getRouter()->removeRoute('skandroid.fbconnect_questions');
    OW::getRouter()->removeRoute('skandroid.fbconnect_save');
    $service->addRoute('skandroid.fbconnect_questions', 'fbconnect/questions', 'MEMBERX_ACTRL_AndroidFacebookSignup', 'getFacebookLoginQuestion');
    $service->addRoute('skandroid.fbconnect_save', 'fbconnect/save', 'MEMBERX_ACTRL_AndroidFacebookSignup', 'saveFacebookLogin');
    OW::getRequestHandler()->addCatchAllRequestsExclude("skandroid.not_authenticated", "MEMBERX_ACTRL_AndroidFacebookSignup", "getFacebookLoginQuestion");
    OW::getRequestHandler()->addCatchAllRequestsExclude("skandroid.not_authenticated", "MEMBERX_ACTRL_AndroidFacebookSignup", "saveFacebookLogin");
    OW::getRequestHandler()->addCatchAllRequestsExclude("skandroid.not_authenticated", "MEMBERX_ACTRL_AndroidBase", "siteInfo");
    
    if (!OW::getPluginManager()->isPluginActive('meusugar')){
        OW::getRouter()->removeRoute('skandroid.get_info');
        $service->addRoute('skandroid.get_info', 'site/get-info', 'MEMBERX_ACTRL_AndroidBase', 'siteInfo');
        
    }
    
 }

MEMBERX_CLASS_EventHandler::getInstance()->apiInit();


function memberx_api_init_isAndroidRequest()
{
    return in_array("android", explode("/", UTIL_Url::getRealRequestUri(OW_URL_HOME, $_SERVER['REQUEST_URI'])));
}