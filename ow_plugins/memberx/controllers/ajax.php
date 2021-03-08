<?php


class MEMBERX_CTRL_Ajax extends OW_ActionController
{
    
    public $service;
    
    public function __construct() {
        parent::__construct();
        $this->service = MEMBERX_BOL_Service::getInstance();
    }
    
    public function quickSearch()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }
        
        $lang = OW::getLanguage();
        $form = new MEMBERX_CLASS_QuickSearchForm($this);
        
        $data = $_POST;
        
        if (array_key_exists('googlemap_location', $data)){
            $mapData = $data['googlemap_location'];
            if (!isset($mapData['address']) || empty($mapData['address'])){
                unset($data['googlemap_location']);
                $form->deleteElement('googlemap_location');
            }
        }

        $isValid = $form->isValid($data);
        
        
        if ( !empty($data['match_sex']) || !empty($data['accountType']) )
        {
            OW::getSession()->set(MEMBERX_CLASS_QuickSearchForm::FORM_SESSEION_VAR, $data);
        }

        if ( $isValid )
        {
            if ( !OW::getUser()->isAuthorized('base', 'search_users') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                
                exit(json_encode(
                    array('result' => false, 'error' => $status['msg'])//$lang->text('base', 'user_search_authorization_warning'))
                ));
            }


            
            $listId = $this->service->getSearchListId($data);
            if (!$listId){

                $addParams = array('join' => '', 'where' => '');

                if ( $data['online'] )
                {
                    $addParams['join'] .= " INNER JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` `online` ON (`online`.`userId` = `user`.`id`) ";
                }

                if ( $data['with_photo'] )
                {
                    $addParams['join'] .= " INNER JOIN `".OW_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";

                    //$addParams['join'] .= " INNER JOIN `".OW_DB_PREFIX . "photo_album` album ON (`album`.`userId` = `user`.`id`)
                    //        INNER JOIN `". OW_DB_PREFIX . "photo` `photo` ON (`album`.`id` = `photo`.`albumId`) ";
                }

                $data = MEMBERX_BOL_Service::getInstance()->updateSearchData( $data );
                $data = MEMBERX_BOL_Service::getInstance()->updateQuickSearchData( $data );

                $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues(
                    $data, 0, BOL_SearchService::USER_LIST_SIZE, false, $addParams
                );


                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }
                
                if (!$listId){
                    $listId = 0;
                }
                
                $this->service->saveSearchListId($listId, $data);
            }

            
            
            
            OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);
            OW::getSession()->set('memberx_search_data', $data);

            BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

            exit(json_encode(
                array('result' => true, 'url' => OW::getRouter()->urlForRoute("users-search-result", array()))
            ));
        }
        
        exit(json_encode(
            array('result' => true, 'url' => OW::getRouter()->urlForRoute("users-search"))
        ));
    }

    public function follow()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_follow_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];

        OW::getEventManager()->call('feed.add_follow', array('feedType' => 'user', 'feedId' => $userId, 'userId' => OW::getUser()->getId()));

        $name = BOL_UserService::getInstance()->getDisplayName($userId);
        $msg = OW::getLanguage()->text('memberx', 'follow_complete_message', array('displayName' => $name));

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }

    public function unfollow()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_unfollow_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];

        OW::getEventManager()->call('feed.remove_follow', array('feedType' => 'user', 'feedId' => $userId, 'userId' => OW::getUser()->getId()));

        $name = BOL_UserService::getInstance()->getDisplayName($userId);
        $msg = OW::getLanguage()->text('memberx', 'unfollow_complete_message', array('displayName' => $name));

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }

    public function block()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_block_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];
        BOL_UserService::getInstance()->block($userId);

        $msg = OW::getLanguage()->text('memberx', 'block_complete_message');

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }

    public function unblock()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_unblock_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];
        BOL_UserService::getInstance()->unblock($userId);

        $msg = OW::getLanguage()->text('memberx', 'unblock_complete_message');

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }

    public function addfriend()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_addfriend_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];

        OW::getEventManager()->call('friends.send_friend_request', array('requesterId' => OW::getUser()->getId(), 'userId' => $userId));

        $msg = OW::getLanguage()->text('memberx', 'addfriend_complete_message');

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }

    public function removefriend()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_removefriend_signin_required'))));
        }

        if ( empty($_POST['userId']) || !(int) $_POST['userId'] )
        {
            exit(json_encode(array('result' => false)));
        }

        $userId = (int) $_POST['userId'];

        $event = new OW_Event('friends.cancelled', array(
            'senderId' => OW::getUser()->getId(),
            'recipientId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        $msg = OW::getLanguage()->text('memberx', 'removefriend_complete_message');

        exit(json_encode(array('result' => true, 'message' => $msg)));
    }
    
    
    public function invitetoevent(){
    	
    	$errorMsg = OW::getLanguage()->text('memberx', 'can_not_invite_this_user_to_this_event');
    	$successMsg = OW::getLanguage()->text('memberx', 'invitation_sent');
    	$result = array('result'=>false, 'message' => $errorMsg);
    	
    	if (!OW::getRequest()->isAjax()){
    		die(json_encode($result));
    	}
    	
    	$formName = isset($_POST['form_name']) ? $_POST['form_name'] : 0;
    	if ($formName !== MEMBERX_CMP_EventSelector::INVITE_FORM_NAME){
        	die(json_encode($result));
        }
        
        $s = MEMBERX_CMP_EventSelector::processInvite();
        
        $result['result'] = $s;
        if ($s){
        	$result['message'] = $successMsg;
        }
        
        die(json_encode($result));
    
    }
    
    public function invitetogroups(){
    	
    	$errorMsg = OW::getLanguage()->text('memberx', 'can_not_invite_this_user_to_this_group');
    	$successMsg = OW::getLanguage()->text('memberx', 'invitation_sent');
    	$result = array('result'=>false, 'message' => $errorMsg, 'data' => $_POST);
    	
    	if (!OW::getRequest()->isAjax()){
    		die(json_encode($result));
    	}
    	
    	$formName = isset($_POST['form_name']) ? $_POST['form_name'] : 0;
    	if ($formName !== MEMBERX_CMP_GroupSelector::INVITE_FORM_NAME){
        	die(json_encode($result));
        }
        
        $s = MEMBERX_CMP_GroupSelector::processInvite();
            
        $result['result'] = $s;
        if ($s){
        	$result['message'] = $successMsg;
        }
        
        die(json_encode($result));
    
    }
    
    public function loadList()
    {
        if ( !OW::getRequest()->isAjax() || empty($_POST['command']) )
        {
            exit(json_encode(array('result' => false)));
        }

        $command = !empty($_POST['command']) ? $_POST['command'] : null;
        $listId = !empty($_POST['listId']) ? (int)$_POST['listId'] : null;
        $orderType = !empty($_POST['orderType']) ? $_POST['orderType'] : null;
        $excludeList = !empty($_POST['excludeList']) ? $_POST['excludeList'] : null;
        $count = !empty($_POST['count']) ? (int)$_POST['count'] : 0;
        $startFrom = !empty($_POST['startFrom']) ? (int)$_POST['startFrom'] : 1;
        $page = !empty($_POST['page']) ? (int)$_POST['page'] : 1;
                
        $lang = OW::getLanguage();

//        if ( !OW::getUser()->isAuthorized() )
//        {
//            exit(json_encode(array('result' => false, 'error' => $lang->text('memberx', 'action_removefriend_signin_required'))));
//        }

        if ( empty($listId)  )
        {
            exit(json_encode(array('result' => false)));
        }

        switch ( $command )
        {
            case 'getNext':
                
                $from = ($startFrom-1) * $count;
                
                $list = MEMBERX_BOL_Service::getInstance()->getSearchResultList($listId, $orderType, $from, $count, $excludeList);
                break;
            
            case 'getPrev':
                if ( $startFrom == 0 )
                {
                    exit(json_encode(array('result' => true, 'items' => array(), 'content' => '' )));
                }
                
                $from = ($startFrom-1) * $count;
                
                $list = MEMBERX_BOL_Service::getInstance()->getSearchResultList($listId, $orderType, $from, $count, $excludeList);
                break;
        }

        if ( empty($list) )
        {
            exit(json_encode(array('result' => true, 'items' => array(), 'content' => '' )));
        }
        
        $idList = array();
        
        foreach ( $list as $dto )
        {
            $idList[] = $dto->id;
        }
        
        $cmp = OW::getClassInstance('MEMBERX_CMP_SearchResultList', $list, $page, $orderType);
        exit(json_encode(array('result' => true, 'items' => $idList, 'content' => $cmp->render())));
    }
    
    
}