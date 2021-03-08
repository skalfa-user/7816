<?php


class MEMBERX_CMP_SearchResultList extends OW_Component
{
    const EVENT_NAME = 'base.add_profile_action_toolbar';
    const EVENT_PROCESS_TOOLBAR = 'base.process_profile_action_toolbar';
    protected $items;
    protected $orderType;
    protected $page;
    protected $actions;
    protected $location;
    protected $extraQuestionKeys;
    protected $showButtions = true;

    public function __construct( $items, $page, $orderType = null, $actions = false, $extraQuestionKeys = null, $layout = null, $showButtons = true)
    {
        parent::__construct();

        $this->items = $items;
        $this->actions = $actions;
        $this->page = $page;
        $this->orderType = $orderType;
        $this->extraQuestionKeys = $extraQuestionKeys;
        $this->showButtions = $showButtons;

        $data = OW::getSession()->get('memberx_search_data');
        
        if ( $this->orderType == MEMBERX_BOL_Service::LIST_ORDER_DISTANCE )
        {
            //$location = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));
            
            if ( !empty($data['googlemap_location']['json']) )
            {
                $this->location = $data['googlemap_location'];
            }
        }

        $url = OW::getPluginManager()->getPlugin('memberx')->getStaticCssUrl() . 'search.css';
        OW::getDocument()->addStyleSheet($url);
        
        $this->assign('config', MEMBERX_CMP_SearchResultSetting::getSavedConfig());
        $this->assign('searchUrl', OW::getRouter()->urlForRoute('users-search'));
        
        $config = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
        if (!$layout){
            if (!isset($config[MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT])){
                $layout = MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_UD;
            }  else { 
                if ($config[MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT] === MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_LR){
                    $layout = MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_LR;
                }else{
                    $layout = MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_UD;
                }
            }
        }
        
        if (!empty($this->extraQuestionKeys) || $layout === MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_LR){
            if (!empty($this->extraQuestionKeys)){
                $this->assign('maxButtonSize', 32);
            }

            $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getCmpViewDir() . 'search_result_list_leftright.html');
        }else{

            $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getCmpViewDir() . 'search_result_list_updown.html');
        }
        
    }

    public function getFields( $userIdList)
    {
        $fields = array();
        $qs = array();
        $accountType = BOL_UserService::getInstance()->findUserById($userIdList[0])->getAccountType();
        $configKey = MEMBERX_CMP_ProfileFieldSettings::CONFIG_KEY_PREFIX . $accountType;
        $config = OW::getConfig();
        $extraQs = $this->extraQuestionKeys;
        $showAgeAndLocation = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::SHOW_AGE_AND_LOCATION);
        
        
        
        $this->assign('extraQs', $extraQs);
        if (empty($extraQs) && $config->configExists(MEMBERX_CMP_ProfileFieldSettings::PLUGIN_KEY, $configKey)){
            $qsTemp = json_decode($config->getValue(MEMBERX_CMP_ProfileFieldSettings::PLUGIN_KEY, $configKey), true);
            foreach($qsTemp as $key => $item){
                if ($item === 1){
                    $extraQs[] = $key;
                }
            }
        }
        
		$qs[] = 'birthdate';
        $qs[] = 'sex';

        $qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');
        if ( $qLocation && $qLocation->onView )
        {
            $qs[] = 'googlemap_location';
        }
        

        

//        if ( $this->listType == 'details' )
//        {
//            $qs[] = 'aboutme';
//        }

        $promotedRoles = json_decode(OW::getConfig()->getValue(MEMBERX_CMP_HighlightRoleSettings::PLUGIN_KEY, MEMBERX_CMP_HighlightRoleSettings::CONFIG_KEY), true);
        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);
        $userViewQuestionData = array();
        $matchCompatibility = array();

        if ( $this->orderType == MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY )
        {
            if ( OW::getPluginManager()->isPluginActive('matchmaking') && OW::getUser()->isAuthenticated() )
            {
                $maxCompatibility = MATCHMAKING_BOL_QuestionMatchDao::getInstance()->getMaxPercentValue();
                $matchCompatibilityList = MATCHMAKING_BOL_Service::getInstance()->findCompatibilityByUserIdList( OW::getUser()->getId(), $userIdList, 0, 1000 );
                
                foreach ( $matchCompatibilityList as $compatibility )
                {
                    $matchCompatibility[$compatibility['userId']] = (int)$compatibility['compatibility'];
                }
            }
        }
        
        foreach ( $questionList as $uid => $q )
        {
            $fields[$uid] = array();
            $age = '';
            
            if ($extraQs){
                $userViewQuestionData[$uid] = BOL_UserService::getInstance()->getUserViewQuestions($uid, false, $extraQs);
                
            }

            $fields[$uid]['promoted'] = false;
            $fields[$uid]['age'] = '19';
            
            if (!empty($promotedRoles)){
                $userRoles = BOL_AuthorizationService::getInstance()->findUserRoleList($uid);
                foreach($userRoles as $role){
                    if (array_key_exists($role->name, $promotedRoles) && $promotedRoles[$role->name] === 1){
                        $fields[$uid]['promoted'] = true;
                        break;
                    }
                }
            }
            
            if ( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                $fields[$uid]['age'] = $age;
            }

            if ( !empty($q['sex']) )
            {
                $sex = $q['sex'];
                $sexValue = '';

                for ( $i = 0 ; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val  )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid]['base'][] = array(
                    'label' => '', 'value' => $sexValue . ' ' . $age
                );
            }

            if ( !empty($q['aboutme']) )
            {
                $fields[$uid]['aboutme'] = array(
                    'label' => '', 'value' => $q['aboutme']
                );
            }

            if ( !empty($q['googlemap_location']['address']) )
            {
                $fields[$uid]['location'] = array(
                    'label' => '', 'value' => $q['googlemap_location']['address']
                );
            }
            
            if ( isset($matchCompatibility[$uid]) )
            {
                $fields[$uid]['match_compatibility'] = array(
                        'label' => '', 'value' => $matchCompatibility[$uid].'%'
                    );
            }
            
            if ( $this->orderType == MEMBERX_BOL_Service::LIST_ORDER_DISTANCE )
            {
                if ( OW::getPluginManager()->isPluginActive('googlelocation') && !empty($q['googlemap_location']) && !empty($this->location) )
                {
                    $event = new OW_Event('googlelocation.calc_distance', array(
                        'lat' => $this->location['latitude'], 
                        'lon' => $this->location['longitude'], 
                        'lat1' => $q['googlemap_location']['latitude'], 
                        'lon1' => $q['googlemap_location']['longitude']));
                    
                    OW::getEventManager()->trigger($event);
                    
                    $data = $event->getData();
                    
                    if ( $data['units'] == 'miles' )
                    {
                        $html = '&nbsp;<span>'.OW::getLanguage()->text('memberx', 'miles').'</span>';
                    }
                    else 
                    {
                        $html = '&nbsp;<span>'.OW::getLanguage()->text('memberx', 'kms').'</span>';
                    }
                    
                    
                    $fields[$uid]['distance'] = array(
                        'label' => '', 'value' => $data['distance'].$html
                    );
                }
            }
        }
        
        $realExtraQeustionData = array();
        foreach($userViewQuestionData as $userId => $qd1){
                
            foreach($qd1['labels'] as $qName => $qLabel){
                
                if ($showAgeAndLocation && ($qName == 'birthdate' || $qName == 'googlemap_location')){
                    
                    if (!isset($qd1['data'][$userId]['birthdate'])){
                        $qd1['data'][$userId]['birthdate'] = "";
                    }
                    
                    if (!isset($qd1['data'][$userId]['googlemap_location'])){
                        $qd1['data'][$userId]['googlemap_location'] = OW::getLanguage()->text('memberx', 'unknown_location');
                    }
                    
                    if (isset($qd1['data'][$userId]['birthdate']) && isset($qd1['data'][$userId]['googlemap_location'])){
                        //$birthdate = $qd1['data'][$userId]['birthdate'];
                        $location = $qd1['data'][$userId]['googlemap_location'];
                        
                        $location = str_replace('<div', '<span', $location);
                        $location = str_replace('</div>', '</span>', $location);
                        $location = preg_replace( '/\r|\n/', ' ', $location);
                        //$labelStrictTag = trim(strip_tags($location));
                        
                        //if (strlen($labelStrictTag) > $this->maxStringLenght){
                        //    $nlabelStrictTag = substr($labelStrictTag, 0, $this->maxStringLenght) . '...';
                        //    $location = str_replace($labelStrictTag, $nlabelStrictTag, $location);
                        //}
                        
                        if (!trim($location)){
                            $location = '<span class="googlemap_pin ic_googlemap_pin"></span>';
                        }
                    
                        $ageAndLocation =  $fields[$userId]['age'] . ' • ' . $location;
                        
                        
                        
                        $realExtraQeustionData[$userId . '-age-location'] = $ageAndLocation;
                    }
                    
                    continue;
                }
                
                if (isset($qd1['data'][$userId][$qName])){
                    
                    if (is_array($qd1['data'][$userId][$qName])){
                        $label = '';
                        foreach($qd1['data'][$userId][$qName] as $item){
                            $label .= (' ' . $item . ',');
                        }

                        $label = substr($label, 0, strlen($label) - 1);
                    }else{
                        $label = $qd1['data'][$userId][$qName];
                    }
                }else{
                    $label = '';
                }
                
                $label = str_replace('<div', '<span', $label);
                $label = str_replace('</div>', '</span>', $label);
                $label = preg_replace( '/\r|\n/', ' ', $label);
                $labelStrictTag = trim(strip_tags($label));
                
                
                
                //if (strlen($labelStrictTag) > $this->maxStringLenght){
                //	$nlabelStrictTag = substr($labelStrictTag, 0, $this->maxStringLenght) . '...';
                //        $label = str_replace($labelStrictTag, $nlabelStrictTag, $label);
                //}
                
                if ($qName === 'googlemap_location'){
                    if (!trim($label)){
                        $label = '<span class="googlemap_pin ic_googlemap_pin"></span>';
                    }
                }
                
                $realExtraQeustionData[$userId][$qLabel] = $label;
                
                
            }
            
            
            
            //print_r($realExtraQeustionData); exit();
        }

        /*$realExtraQeustionData2 = array();
        $questionNameList = array();
        if (!empty($extraQs)){
            foreach($extraQs as $questionName){
                $qLang = BOL_QuestionService::getInstance()->getQuestionLang($questionName);

                foreach($realExtraQeustionData as $key => $userQuestion){
                    if (!isset($realExtraQeustionData[$key][$qLang])){
                         $realExtraQeustionData2[$key][$qLang] = '';
                    }else{
                        $realExtraQeustionData2[$key][$qLang] = $realExtraQeustionData[$key][$qLang];
                    }
                    
                }

            }
        }
        
        print_r($realExtraQeustionData2);*/
        return array('fields' => $fields, 'extraQuestionList' => $realExtraQeustionData);
    }

    
    
    
        
    
    
    private function process( $list )
    {
        $service = BOL_UserService::getInstance();
        $setting = MEMBERX_CMP_SearchResultSetting::getSavedConfig();

        $winkList = array();
        $winkLimitList = array();
        $mailActionData = array();
        $idList = array();
        $userList = array();
        $hasMailboxPlugin = OW::getPluginManager()->isPluginActive('mailbox');
        $possibleButtions = $this->showButtions ?  MEMBERX_CMP_PossibleButtonSetting::getSavedValue() : array();
        foreach ( $list as $dto )
        {
            
            
            if (isset($setting['show_button_on_search_result']) && 
                    isset($possibleButtions['mail']) && 
                    $setting['show_button_on_search_result'] === 'yes' &&
                    $hasMailboxPlugin){
                
                $mailboxService = MAILBOX_BOL_ConversationService::getInstance();
                $mailActionData[$dto->id] = json_encode($mailboxService->getUserInfo($dto->id));
                
            }
            
            $resultSetting = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
            $showNewLabel = isset($resultSetting[MEMBERX_CMP_SearchResultSetting::SHOW_NEW_LABEL]) ? (int)$resultSetting[MEMBERX_CMP_SearchResultSetting::SHOW_NEW_LABEL] : 0;
            $newLabelColor = isset($resultSetting[MEMBERX_CMP_SearchResultSetting::NEW_LABEL_COLOR]) ? $resultSetting[MEMBERX_CMP_SearchResultSetting::NEW_LABEL_COLOR] : 0;
 
            if ($showNewLabel && ($showNewLabel + $dto->joinStamp) > time() && $newLabelColor){
                $dto->showNewLabel = true;
                $dto->newLabelColor = $newLabelColor;
            }else{
                $dto->showNewLabel = false;
            }
            
            $userList[] = array('dto' => $dto);
            $idList[] = $dto->id;
            
            
        }
        
        if (OW::getPluginManager()->isPluginActive('winks')) {
            foreach ($idList as $id) {
                $winkList[$id] = WINKS_BOL_Service::getInstance()->findWinkByUserIdAndPartnerId(OW::getUser()->getId(), $id);
                $winkLimitList[$id] = WINKS_BOL_Service::getInstance()->isLimited(OW::getUser()->getId(), $id);
            }
        }

        $displayNameList = array();
        $questionList = array();
        $bookmarkList = array();
        $contextActionList = array();
        
        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, false, true, true, true);
            $vatarsSrc = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);
            //$displayLength = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::LENGTH_OF_DISPLAY_NAME);
            
            foreach ( $avatars as $userId => $avatarData )
            {
                $avatars[$userId]['src'] = $vatarsSrc[$userId];
                $displayName = isset($avatarData['title']) ? $avatarData['title'] : '';
                
                //if ($displayLength && strlen($displayName) > $displayLength){
                //    $displayName = substr($displayName, 0, $displayLength) . '...';
                //}
                
                $displayNameList[$userId] = $displayName;
            }

            $usernameList = $service->getUserNamesForList($idList);
            
            //foreach($usernameList as $userId => $username){
            //    if ($username && strlen($username) > $displayLength){
                    //$username = substr($username, 0, $displayLength) . '...';
            //    }
                
            //    $usernameList[$userId] = $username;
            //}
            
            
            $onlineInfo = $service->findOnlineStatusForUserList($idList);
            
            $showPresenceList = array();
            $ownerIdList = array();

            foreach ( $onlineInfo as $userId => $isOnline )
            {
                $ownerIdList[$userId] = $userId;
            }

            $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerIdList' => $ownerIdList,
                'viewerId' => OW::getUser()->getId()
            );

            $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

            foreach ( $onlineInfo as $userId => $isOnline )
            {
                // Check privacy permissions
                if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
                {
                    $showPresenceList[$userId] = false;
                    continue;
                }

                $showPresenceList[$userId] = true;
            }
            
            $actions = array();
            if ( $this->actions )
            {
                $actions = MEMBERX_CLASS_EventHandler::getInstance()->collectUserListActions($idList);
                $this->assign('actions', $actions);
                
            }
            
            
            $extrqQuestionList = $this->getFields($idList)['extraQuestionList'];
           
            $this->assign('winkList', $winkList);
            $this->assign('winkLimitList', $winkLimitList);
            $this->assign('searchId', OW::getUser()->getId());
            $this->assign('urlHome', OW_URL_HOME);
            $this->assign('isLogin', OW::getUser()->isAuthenticated());
            $this->assign('hasBookmarkPlugin', OW::getPluginManager()->isPluginActive('bookmarks')); 
            $this->assign('hasWinksPlugin', OW::getPluginManager()->isPluginActive('winks')); 
            $this->assign('hasEventPlugin', OW::getPluginManager()->isPluginActive('event')); 
            $this->assign('hasGroupPlugin', OW::getPluginManager()->isPluginActive('groups'));
            $this->assign('hasVideoCallPlugin', OW::getPluginManager()->isPluginActive('videoim')); 
            $this->assign('hasVirtualGiftPlugin', OW::getPluginManager()->isPluginActive('virtualgifts'));
            $this->assign('possbleButtions', $possibleButtions);
            $this->assign('hasMailboxPlugin', $hasMailboxPlugin);
            $this->assign('showPresenceList', $showPresenceList);
            $this->assign('fields', $this->getFields($idList)['fields']);
            $this->assign('extraQuestionList', $extrqQuestionList); 
            $this->assign('questionList', $questionList);
            $this->assign('usernameList', $usernameList);
            $this->assign('avatars', $avatars);
            $this->assign('displayNameList', $displayNameList);
            $this->assign('onlineInfo', $onlineInfo);
            $this->assign('page', $this->page);
            $this->assign('mailActionData', $mailActionData);
            $this->assign('list', $userList);
            
          
            $activityShowLimit = OW::getConfig()->getValue('memberx', 'hide_user_activity_after');
            $this->assign('activityShowLimit', time() - ((int)$activityShowLimit)*24*60*60);

            $event = new OW_Event('mailbox.get_active_mode_list');
		        OW::getEventManager()->trigger($event);
		        $data = $event->getData();
		
		        $mailboxAction = array('mail'=>0, 'chat'=>0);
		        if (!empty($data)){
		            foreach($data as $item){
		                $mailboxAction[$item] = true;
		            }
		        }
		        
		        $this->assign('mailboxAction', $mailboxAction);
            
            $showContextMenu = true;
            $pluginConfig = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
            if (isset($pluginConfig[MEMBERX_CMP_SearchResultSetting::SHOW_CONTEXT_MENU_ON_AVATAR])){
                if ($pluginConfig[MEMBERX_CMP_SearchResultSetting::SHOW_CONTEXT_MENU_ON_AVATAR] === 'no'){
                    $showContextMenu = false;
                }
            }
            if (!$showContextMenu){
                $this->assign('showContextMenu', $showContextMenu);
                $this->assign('itemMenu', $contextActionList);
                return;
            }
            
            if ( OW::getPluginManager()->isPluginActive('bookmarks') && OW::getUser()->isAuthenticated() ){
                $bookmarkList = BOOKMARKS_BOL_MarkDao::getInstance()->getMarkedListByUserId(OW::getUser()->getId(), $idList);
                $this->assign('bookmarkActive', TRUE);
                $this->assign('bookmarkList', $bookmarkList);

                $bookmarkActions = array();
                if (!MEMBERX_CMP_PossibleButtonSetting::getBoolean('bookmark')){
                    foreach($bookmarkList as $id => $markStatus){
                        $label = !empty($bookmarkList[$id]) ? OW::getLanguage()->text('bookmarks', 'unmark_toolbar_label') : OW::getLanguage()->text('bookmarks', 'mark_toolbar_label');
                        $bookmark = array(
                            'key' => 'bookmark',
                            'label' => $label,
                            'href' => 'javascript://',
                            'linkClass' => 'ow_ulist_big_avatar_bookmark memberx_bookmark download',
                            'attributes' => Array('data-user-id' => $id)

                        );

                        $bookmarkActions[$id] = $bookmark;
                    }
                }
                
                $actions[] = $bookmarkActions;
            }

            if (!$actions){
                $actions = array();
            }
            foreach ($idList as $id) {

                $contextAction = new BASE_CMP_ContextAction();

                $contextParentAction = new BASE_ContextAction();
                $contextParentAction->setKey('userlist_menu');
                $contextParentAction->setClass('ow_memberx_userlist_menu ow_newsfeed_context ');
                $contextAction->addAction($contextParentAction);

                foreach ($actions as $key => $actionList) {
                    if (!isset($actionList[$id])){
                        continue;
                    }
                    $actionItem = $actionList[$id];

                    $action = new BASE_ContextAction();
                    if (isset($actionItem['key'])) {
                        $action->setKey($actionItem['key']);
                    }
                    if (isset($actionItem['label'])) {
                        $action->setLabel($actionItem['label']);
                    }
                    if (isset($actionItem['linkClass'])) {
                        $action->setClass($actionItem['linkClass']);
                        $action->addAttribute('style', 'background-image: url()');
                    }
                    if (isset($actionItem['href'])) {
                        $action->setUrl($actionItem['href']);
                    }

                    if (isset($actionItem['id'])) {
                        $action->setId($actionItem['id']);
                    }

                    $action->setParentKey($contextParentAction->getKey());
                    $action->setOrder($key);

                    if (isset($actionItem['attributes'])) {
                        foreach ($actionItem['attributes'] as $attrKey => $attrValue) {
                            $action->addAttribute($attrKey, $attrValue);
                        }
                    }
                    $contextAction->addAction($action);
                }


                $contextActionList[$id] = $contextAction->render();
                
            }
        }

        $this->assign('itemMenu', $contextActionList);
        
        
        
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->items);
    }
}