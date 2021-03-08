<?php


class MEMBERX_CLASS_EventHandler
{
    /**
     * @var MEMBERX_CLASS_EventHandler
     */
    private static $classInstance;

    const EVENT_COLLECT_USER_ACTIONS = 'memberx.collect_user_actions';

    /**
     * @return MEMBERX_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function addFollowAction( BASE_CLASS_EventCollector $e )
    {
        $params = $e->getParams();
        $userIdList = $params['userIdList'];
        $lang = OW::getLanguage();
        $viewerId = OW::getUser()->getId();

        $feedList = array();
        foreach ( $userIdList as $userId )
        {
            $feedList[] = array('feedType' => 'user', 'feedId' => $userId);
        }

        $followList = OW::getEventManager()->call('feed.is_follow_list', array('userId' => $viewerId, 'feedList' => $feedList));

        if ( $followList === null )
        {
            return;
        }

        $actions = array();
        foreach ( $userIdList as $userId )
        {
            if ( $userId == $viewerId )
            {
                continue;
            }

            $key = !empty($followList['user'][$userId]) && $followList['user'][$userId] ? 'unfollow' : 'follow';
            $id = 'action_' . $key . '_' . $userId;
            $actions[$userId] = array(
                'key' => $key,
                'label' => $lang->text('memberx', 'action_' . $key),
                'id' => $id,
                'href' => 'javascript://',
                'order' => 1,
                'linkClass' => 'ow_ic_ok userlist_action_' . $key,
                'attributes' => array('uid' => $userId)
            );
        }

        if ( count($actions) )
        {
            $script =
            '$(document).on("click", ".userlist_action_follow", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.follow')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_unfollow')).');
                            $link.removeClass("userlist_action_follow")
                                .addClass("userlist_action_unfollow");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_follow")
                                .addClass("ow_search_results_profile_details_unfollow");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });

            $(document).on("click", ".userlist_action_unfollow", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.unfollow')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_follow')).');
                            $link.removeClass("userlist_action_unfollow")
                                .addClass("userlist_action_follow");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_unfollow")
                                .addClass("ow_search_results_profile_details_follow");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });
            ';
            OW::getDocument()->addOnloadScript($script);
        }

        $e->add($actions);
    }

    public function addFriendAction( BASE_CLASS_EventCollector $e )
    {
        $params = $e->getParams();
        $userIdList = $params['userIdList'];
        $viewerId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthorized('friends', 'add_friend') )
        {
            return;
        }

        $lang = OW::getLanguage();

        $friendshipList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $viewerId, 'idList' => $userIdList));

        $actions = array();
        foreach ( $userIdList as $userId )
        {
            if ( $userId == $viewerId )
            {
                continue;
            }

            $key = in_array($userId, $friendshipList) ? 'removefriend' : 'addfriend';
            $id = 'action_' . $key . '_' . $userId;
            $actions[$userId] = array(
                'key' => $key,
                'label' => $lang->text('memberx', 'action_' . $key),
                'id' => $id,
                'href' => 'javascript://',
                'order' => 1,
                'linkClass' => 'ow_ic_heart userlist_action_' . $key ,
                'attributes' => array('uid' => $userId)
            );
        }

        if ( count($actions) )
        {
            $script =
            '$(document).on("click", ".userlist_action_addfriend", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.addfriend')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_removefriend')).');
                            $link.removeClass("userlist_action_addfriend")
                                .addClass("userlist_action_removefriend");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_addfriend")
                                .addClass("ow_search_results_profile_details_removefriend");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });

            $(document).on("click", ".userlist_action_removefriend", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.removefriend')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_addfriend')).');
                            $link.removeClass("userlist_action_removefriend")
                                .addClass("userlist_action_addfriend");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_removefriend")
                                .addClass("ow_search_results_profile_details_addfriend");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });
            ';
            OW::getDocument()->addOnloadScript($script);
        }

        $e->add($actions);
    }

    public function addBlockAction( BASE_CLASS_EventCollector $e )
    {
        $params = $e->getParams();
        $userIdList = $params['userIdList'];
        $viewerId = OW::getUser()->getId();
        $lang = OW::getLanguage();

        $blockList = BOL_UserService::getInstance()->findBlockedListByUserIdList($viewerId, $userIdList);

        $actions = array();
        foreach ( $userIdList as $userId )
        {
            if ( $userId == $viewerId )
            {
                continue;
            }

            $key = isset($blockList[$userId]) && $blockList[$userId] ? 'unblock' : 'block';
            $id = 'action_' . $key . '_' . $userId;
            $actions[$userId] = array(
                'key' => $key,
                'label' => $lang->text('memberx', 'action_' . $key),
                'id' => $id,
                'href' => 'javascript://',
                'order' => 1,
                'linkClass' => 'ow_ic_delete userlist_action_' . $key,
                'attributes' => array('uid' => $userId)
            );
        }

        if ( count($actions) )
        {
            $script =
            '$(document).on("click", ".userlist_action_block", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.block')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_unblock')).');
                            $link.removeClass("userlist_action_block")
                                .addClass("userlist_action_unblock");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_block")
                                .addClass("ow_search_results_profile_details_unblock");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });

            $(document).on("click", ".userlist_action_unblock", function() {
                var $link = $(this);
                var userId = $link.attr("uid");
                $.ajax({
                    url: '.json_encode(OW::getRouter()->urlForRoute('memberx.unblock')).',
                    type: "POST",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result ) {
                            OW.info(data.message);
                            $link.html('.json_encode($lang->text('memberx', 'action_block')).');
                            $link.removeClass("userlist_action_unblock")
                                .addClass("userlist_action_block");
                            $link.parent()
                                .removeClass("ow_search_results_profile_details_unblock")
                                .addClass("ow_search_results_profile_details_block");
                        }
                        else if ( data.error != "" ) {
                            OW.error(data.error);
                        }
                    }
                });
            });
            ';
            OW::getDocument()->addOnloadScript($script);
        }

        $e->add($actions);
    }

    public function collectUserListActions( $userIdList )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return null;
        }

        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_USER_ACTIONS, array('userIdList' => $userIdList));
        OW::getEventManager()-> trigger($event);

        return $event->getData();
    }
    
    public function getSearchResult( OW_Event $e )
    {
        $params = $e->getParams();
                
        $criteriaList = $params['criterias'];
        $limit = empty($params['limit']) ? array(0, BOL_SearchService::USER_LIST_SIZE) : $params['limit'];
 
        unset($criteriaList['sex']);
        if (isset($criteriaList['match_sex'])){
            $criteriaList['sex'] = $criteriaList['match_sex'];
            unset($criteriaList['match_sex']);
        }

        $criteriaList = MEMBERX_BOL_Service::getInstance()->updateQuickSearchData( $criteriaList );
        $criteriaList = MEMBERX_BOL_Service::getInstance()->updateSearchData( $criteriaList );
        
     
        $searchService = MEMBERX_BOL_Service::getInstance();
        
        $listId = $searchService->getSearchListId($criteriaList);
        if ($listId){
            $userIdList = $searchService->getUserIdList($listId, 0,  BOL_SearchService::USER_LIST_SIZE);
            
        }else{
            $extra = array();
            if (OW::getUser()->isAuthenticated()){
                
                //if (!isset($criteriaList['match_sex']) || empty($criteriaList['match_sex'])){
                //    $extra['where'] = " AND `user`.`accountType` != '" . OW::getUser()->getUserObject()->accountType . "' AND `user`.`id` !=" . OW::getUser()->getId() . " ";
                //}else{
                //    $extra['where'] = " AND `user`.`accountType` != '" . OW::getUser()->getUserObject()->accountType . "' AND `user`.`id` !=" . OW::getUser()->getId() . " ";
                //}
                //$extra['where'] = '';
                //if ($accountRestrict){
                //    $extra['where'] = " AND `user`.`accountType` != '" . OW::getUser()->getUserObject()->accountType . "' ";
                //}
                
                $extra['where'] = " AND `user`.`id` !=" . OW::getUser()->getId() . " ";

            }

            $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($criteriaList, $limit[0], $limit[1], false, $extra);
            if (count($userIdList) > 0){
                $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
            }
            
            if (!$listId){
                $listId = 0;
            }
            
            $searchService->saveSearchListId($listId, $criteriaList);
            
        }
       
        
        $e->setData($userIdList);
        
        return $userIdList;
        
        
        
    }
    
    public function getForAndroidSearchResult( OW_Event $e )
    {
        /*$params = $e->getParams();
                
        $criteriaList = $params['criterias'];
        $limit = empty($params['limit']) ? array(0, BOL_SearchService::USER_LIST_SIZE) : $params['limit'];
        
        $sex = !empty($criteriaList['sex']) ? $criteriaList['sex'] : null;
        $match_sex = !empty($criteriaList['match_sex']) ? $criteriaList['match_sex'] : null;
        
        unset($criteriaList['sex']);
        unset($criteriaList['match_sex']);
        
        if ( !empty($sex) )
        {
            $criteriaList['match_sex'] = $sex;
        }
        
        if ( !empty($match_sex) )
        {
            $criteriaList['sex'] = $match_sex;
        }
        
        $criteriaList = MEMBERX_BOL_Service::getInstance()->updateQuickSearchData( $criteriaList );
        $criteriaList = MEMBERX_BOL_Service::getInstance()->updateSearchData( $criteriaList );
        
        $extra = array();
        if (OW::getUser()->isAuthenticated()){
            if (!isset($criteriaList['match_sex']) || empty($criteriaList['match_sex'])){
                $extra['where'] = " AND `user`.`accountType` != '" . OW::getUser()->getUserObject()->accountType . "' AND `user`.`id` !=" . OW::getUser()->getId() . " ";
            }else{
                $extra['where'] = "AND `user`.`id` !=" . OW::getUser()->getId() . " ";
            }
        }
        
        $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($criteriaList, $limit[0], $limit[1], false, $extra);
        
        $e->setData($userIdList);
        
        return $userIdList;*/
        return $this->getSearchResult($e);
    }

    public function onInitQuestion(OW_Event $event) 
    {
        if (OW::getPluginManager()->isPluginActive('usearch')){
            return;
        }
        $params = $event->getParams();

        if ( $params['type'] != 'search' || !in_array($params['fieldName'], array('sex', 'birthdate')) )
        {
            return;
        }

        $lang = OW::getLanguage();
        $sessionData = OW::getSession()->get(MEMBERX_CLASS_QuickSearchForm::FORM_SESSEION_VAR);

        switch( $params['fieldName'] )
        {
            case 'sex':
                $field = new Selectbox('sex');
                $field->setLabel($lang->text('memberx', 'search_label_sex'));
                $field->setHasInvitation(false);
                if ( !empty($sessionData['sex']) )
                {
                    $field->setValue($sessionData['sex']);
                }
                break;

            case 'birthdate':
                $field = new MEMBERX_CLASS_AgeRangeField('birthdate');
                $field->setLabel($lang->text('memberx', 'age'));
                if ( !empty($sessionData['birthdate']['from']) && !empty($sessionData['birthdate']['to']) )
                {
                    $field->setValue($sessionData['birthdate']);
                }

                $configs = !empty($params['configs']) ? BOL_QuestionService::getInstance()->getQuestionConfig($params['configs'], 'year_range') : null;
                $max = !empty($configs['from']) ? date("Y") - (int) $configs['from'] : null;
                $min = !empty($configs['to']) ? date("Y") - (int) $configs['to'] : null;

                $field->setMaxAge($max);
                $field->setMinAge($min);

                $validator = new MEMBERX_CLASS_AgeRangeValidator($min, $max);
                $errorMsg = $lang->text('memberx', 'age_range_incorrect_values', array('min' => $min, 'max' => $max));
                $validator->setErrorMessage($errorMsg);
                $field->addValidator($validator);

                break;
        }

        if ( !empty($field) )
        {
            $event->setData($field);
        }
    }
    
    public function setSearchSql(BASE_CLASS_QueryBuilderEvent $event) 
    {
        
        /*if (OW::getPluginManager()->isPluginActive('usearch')){
            return;
        }
        
        $params = $event->getParams();

        if ( empty($params['question']) || !$params['question'] instanceof BOL_Question || empty($params['value'])
            || !in_array($params['question']->name, array('sex', 'match_sex')) )
        {
            return;
        }
        $value = is_array($params['value']) ? array_sum($params['value']) : (int) $params['value'];

        $prefix = !empty($params['prefix']) ? $params['prefix'] : 'q'.rand(100, 10000);
        $questionName = $params['question']->name == 'sex' ? 'match_sex' : 'sex';

        $innerJoin = " INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $prefix . "`
            ON ( `user`.`id` = `" . $prefix . "`.`userId` AND `" . $prefix . "`.`questionName` = '" .
            OW::getDbo()->escapeString($questionName) ."' AND `" . $prefix . "`.`intValue` & ".OW::getDbo()->escapeString($value)." ) ";

        $event->addJoin($innerJoin);*/
    } 
    
    public function afterPluginsInit(OW_Event $event) 
    {
        $router = OW::getRouter()->getRoute('googlelocation_user_map');

        if ( !empty($router) )
        {
            OW::getRouter()->removeRoute('googlelocation_user_map');
        } 
        
        $routesToDelete = array('users', 'base_user_lists');
        $router = OW::getRouter();

        foreach ( $routesToDelete as $route )
        {
            $routeObj = $router->getRoute($route);
            $routeObj->setDispatchAttrs(array(OW_RequestHandler::ATTRS_KEY_CTRL => 'MEMBERX_CTRL_Search', OW_RequestHandler::ATTRS_KEY_ACTION => 'form'));
            $router->removeRoute($route);
            $router->addRoute($routeObj);
        }
        
        if (!OW::getPluginManager()->isPluginActive('usearch')) {
            OW::getAutoloader()->addClass('USEARCH_CMP_QuickSearch', OW::getPluginManager()->getPlugin('memberx')->getCmpDir() . 'fake_usearch_quick_search.php');
            OW::getAutoloader()->addClass('USEARCH_CMP_QuickSearchIndex', OW::getPluginManager()->getPlugin('memberx')->getCmpDir() . 'fake_usearch_quick_search_index.php');
        }

        
        if (OW::getPluginManager()->isPluginActive('event')){
            OW::getAutoloader()->addClass('MEMBERX_BOL_EventDao', OW::getPluginManager()->getPlugin('memberx')->getBolDir() . 'fake_event_dao.php');
        }

        if (OW::getPluginManager()->isPluginActive('groups')){
            OW::getAutoloader()->addClass('MEMBERX_BOL_GroupDao', OW::getPluginManager()->getPlugin('memberx')->getBolDir() . 'fake_group_dao.php');
        }
    }
    
    public function onProcessProfileActionToolbar(BASE_CLASS_EventCollector $event){
 
 	
        if (!OW::getUser()->isAuthenticated()){
            return;
        }
        
 	$params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( OW::getUser()->getId() == $userId )
        {
            return;
        }
    	
    	if (OW::getPluginManager()->isPluginActive('event')){

    		$resultArray = array(
        		BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('memberx', 'invite_to_event'),
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => 'invite-to-event-' . $userId,
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'memberx.invite_to_event',
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'invite-to-event',
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => array('data-id' => $userId),
            	BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
        	);

            $event->add($resultArray);
            
            $script = 
            "$('a.invite-to-event').on('click', function(event){
                var id = this.getAttribute('data-id');
                if (id){
                    EventInviteWindow = OW.ajaxFloatBox('MEMBERX_CMP_EventSelector', [id], {
                        width: '480'
                    } );
                }
            });";
            
            OW::getDocument()->addOnloadScript($script);
    	}
    	
    }
    
    
    public function restrictAccountTypeView(OW_Event $event){
    
        if (!OW::getUser()->isAuthenticated()){
            return;
        }
        
        if (OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base')){
            return;
        }
        
        $accountRestrict = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT);
       
        if (!$accountRestrict){
            return;
        }
        
        $route = ow::getRouter()->getUsedRoute();
        if (!$route){
            return;
        }
        
        $routeName = $route->getRouteName();
        $accountTypeCheckRouteName = array('base_user_profile', 'base_about_profile');
        if (!in_array($routeName, $accountTypeCheckRouteName)){
            return;
        }

        $vars = $route->getDispatchAttrs();
        $username = $vars['vars']['username'];
        
        if ($username === OW::getUser()->getUserObject()->username){
            return;
        }

        $user = BOL_UserService::getInstance()->findByUsername($username);
        if (!$user){
            return;
        }
        
        if ($user->getAccountType() === OW::getUser()->getUserObject()->accountType){
            $url404 = OW_URL_HOME  .  'member-not-found/';
            OW::getApplication()->redirect($url404);
        }
        
    }
    
    public function init()
    {
        $em = OW::getEventManager();

        $em->bind(self::EVENT_COLLECT_USER_ACTIONS, array($this, 'addFollowAction'));
        if ( OW::getPluginManager()->isPluginActive('friends') )
        {
            $em->bind(self::EVENT_COLLECT_USER_ACTIONS, array($this, 'addFriendAction'));
        }
        $em->bind(self::EVENT_COLLECT_USER_ACTIONS, array($this, 'addBlockAction'));
    }
    
    public function initDesktop(){
    	OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onProcessProfileActionToolbar'));
    }
    
    public function apiInit()
    {
        $em = OW::getEventManager();
        $em->bind('memberx.get_user_id_list', array($this, 'getSearchResult'));
        $em->bind('memberx.get_user_id_list_for_android', array($this, 'getForAndroidSearchResult'));
        $em->bind('usearch.get_user_id_list', array($this, 'getSearchResult'));
        $em->bind('usearch.get_user_id_list_for_android', array($this, 'getForAndroidSearchResult'));

        
    }
    
    public function genericInit()
    {
        $em = OW::getEventManager();
        $em->bind('usearch.get_user_id_list', array($this, 'getSearchResult'));
        $em->bind('memberx.get_user_id_list', array($this, 'getSearchResult'));
        $em->bind('base.questions_field_init', array($this, 'onInitQuestion'));
        //$em->bind('base.question.search_sql', array($this, 'setSearchSql'));
        $em->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'afterPluginsInit'), 9999);
        $em->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'restrictAccountTypeView'), 90);
    }
    
    
}

