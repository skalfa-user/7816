<?php


class MEMBERX_CTRL_Search extends OW_ActionController
{
    
    public function __construct() {
        parent::__construct();
        
        $metaParams = array(
            "sectionKey" => "base.users",
            "entityKey" => "userLists",
            "title" => "base+meta_title_user_list",
            "description" => "base+meta_desc_user_list",
            "keywords" => "base+meta_keywords_user_list",
            "vars" => array('user_list' => OW::getLanguage()->text('memberx', 'latest'))
        );
        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $metaParams));
       
    }
    
    private function getMenu($activeItem = null)
    {
        $lang = OW::getLanguage();
        $router = OW::getRouter();

        if ($activeItem === null || $activeItem === 'photo_gallery'){
            $activeItem = 'user_list';
        }else if($activeItem === 'photo_gallery_online'){
            $activeItem = 'online_users';
        }
        
        
        
        $items = array();
        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('memberx', 'user_list'));
        $item->setOrder(11);
        $item->setKey('user_list');
        $item->setIconClass('ow_ic_picture');
        $item->setUrl($router->urlForRoute('memberx.members', array('orderType' => 'new')));
        $item->setActive($activeItem === 'user_list');
        array_push($items, $item);

        
        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('memberx', 'online_users'));
        $item->setOrder(20);
        $item->setKey('online_users');
        $item->setIconClass('ow_ic_push_pin');
        $item->setUrl($router->urlForRoute('memberx.members', array('orderType' => 'new')) . '?online=yes');
        $item->setActive($activeItem === 'online_users');
        array_push($items, $item);
        
        if (MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ENABLE_FEATURED_USER_LIST)){
            $item = new BASE_MenuItem();
            $item->setLabel($lang->text('memberx', 'featured_users_1'));
            $item->setOrder(10);
            $item->setKey('featured_users');
            $item->setIconClass('ow_ic_push_pin');
            $item->setUrl($router->urlForRoute('memberx.featured_users', array('orderType' => 'new')));
            $item->setActive($activeItem === 'featured_users');
            array_push($items, $item);
        }
        
        if (OW::getPluginManager()->isPluginActive('matchmaking') && OW::getUser()->isAuthenticated()){
            $item = new BASE_MenuItem();
            $item->setLabel($lang->text('memberx', 'my_matches'));
            $item->setOrder(30);
            $item->setKey('my_matches');
            $item->setIconClass('ow_ic_heart');            
            $item->setUrl($router->urlForRoute('matchmaking_members_page'));
            $item->setActive($activeItem === 'my_matches');
            array_push($items, $item);
        }

        if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $item = new BASE_MenuItem();
            $item->setLabel($lang->text('memberx', 'map'));
            $item->setOrder(40);
            $item->setKey('map');
            $item->setIconClass('ow_ic_places');
            $item->setUrl($router->urlForRoute('memberx.map'));
            $item->setActive($activeItem === 'map');
            array_push($items, $item);
        }
        
        
        
        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('memberx', 'search'));
        $item->setOrder(50);
        $item->setKey('search');
        $item->setIconClass('ow_ic_lens');
        $item->setUrl($router->urlForRoute('users-search'));
        $item->setActive($activeItem === 'search');
        array_push($items, $item);

        
        if (MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::SHOW_PAGE_TITLE)){
            $this->setPageHeading($lang->text('memberx', $activeItem));
        }
        
        return new BASE_CMP_ContentMenu($items);
    }

    public function form()
    {
        
        $url = OW::getPluginManager()->getPlugin('memberx')->getStaticCssUrl() . 'search.css';
        OW::getDocument()->addStyleSheet($url);
        
        $mainSearchForm = OW::getClassInstance('MEMBERX_CLASS_MainSearchForm', $this);
        $mainSearchForm->process($_POST);
        $this->addForm($mainSearchForm);

        $usernameSearchEnabled = OW::getConfig()->getValue('memberx', 'enable_username_search');
        $this->assign('usernameSearchEnabled', $usernameSearchEnabled);
        if ($usernameSearchEnabled)
        {
            $usernameSearchForm =  OW::getClassInstance('MEMBERX_CLASS_UsernameSearchForm', $this);
            $usernameSearchForm->process($_POST);
            $this->addForm($usernameSearchForm);
        }

        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userSearch",
            "title" => "base+meta_title_user_search",
            "description" => "base+meta_desc_user_search",
            "keywords" => "base+meta_keywords_user_search"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
        $this->addComponent('menu', $this->getMenu('search'));
    }

    protected function getOrderType( $params )
    {
        $orderTypes = MEMBERX_BOL_Service::getInstance()->getOrderTypes();
        
        $orderType = !empty($params['orderType']) ? $params['orderType'] : MEMBERX_BOL_Service::LIST_ORDER_NEW;
        
        if ( empty($orderTypes)  )
        {
            $orderType = MEMBERX_BOL_Service::LIST_ORDER_NEW;
            
        }
        else if( !in_array($orderType, $orderTypes) )
        {
            $orderType = reset($orderTypes);
        }
        
        return $orderType;
    }
    
    public function searchResultMenu( $order )
    {
        $items = MEMBERX_BOL_Service::getInstance()->getSearchResultMenu($order);
        
        if ( !empty($items) )
        {
            return new BASE_CMP_SortControl($items);
        }
        
        return null;
    }
    
    public function usercategoryResult(){
        
        $routeName = OW::getRouter()->getUsedRoute()->getRouteName();
        $link = USERCATEGORY_BOL_LinksDao::getInstance()->findByRouteName($routeName);
        
        $metaParams = array(
            "sectionKey" => "base.users",
            "entityKey" => "userLists",
            "title" => "base+meta_title_user_list",
            "description" => "base+meta_desc_user_list",
            "keywords" => "base+meta_keywords_user_list",
            "vars" => array('user_list' => $link->pageTitle)
        );
        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $metaParams));
        
        $this->setPageTitle($link->pageTitle);
        $this->setPageDescription($link->pageDescription);
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->setPageHeading($link->pageTitle);
        
        $params = array();
        $data = json_decode($link->data, true);
        if (isset($data['order_type'])){
            $params['orderType'] = $data['order_type'];
        }
        
        $params['noJs'] = true;
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getCtrlViewDir() . 'search_search_result.html');
        $this->assign('subTitle', $link->pageDescription);
        $this->searchResult($params, $data);
        
    }
    
    public function searchResult($params, $data = null)
    {
 
        if (!OW::getUser()->isAuthorized('base', 'search_users')) {
            if (!OW::getUser()->isAuthenticated()){
                $this->redirect(OW::getRouter()->urlForRoute('base_index'));
            }
            
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            OW::getFeedback()->warning($status['msg']);

            $this->redirect(OW::getRouter()->urlForRoute('users-search'));
        }

        $actvityMenu = 'photo_gallery';
        $usedRoute = OW::getRouter()->getUsedRoute()->getRouteName();
        $featuredEnabled = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ENABLE_FEATURED_USER_LIST);
        
        if (isset($_GET['page'])){
            $listId = OW::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        }else if ($usedRoute && ($usedRoute === 'memberx.members' /*|| $usedRoute === 'base_default_index'*/)){
            
            $mainSearchForm = OW::getClassInstance('MEMBERX_CLASS_MainSearchForm', $this);
            
            $online = isset($_GET['online']);
            $photo = isset($_GET['photo']);
            $accountType = isset($_GET['act']) ? $_GET['act'] : false;
            
            $data = array();
            $data['form_name'] = $mainSearchForm->getName();
            $data['SearchFormSubmit'] = 'Search';
            //$data['birthdate'] = array('from' => '25', 'to' => '87');
            
            if ($accountType){
                $data['accountType'] = $accountType;
            }else{
                
                $searchConfig = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
                if (isset($searchConfig[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT]) &&
                        $searchConfig[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT] === 'yes' &&
                        OW::getUser()->isAuthenticated()){
                    $accountList = BOL_QuestionService::getInstance()->findAllAccountTypes();
                    
                    
                    if (count($accountList) > 1){
                        foreach($accountList as $key => $acType){
                            $myAccountType = OW::getUser()->getUserObject()->accountType;
                            if ($acType->name !== $myAccountType){
                                $data['accountType'] = $accountList[$key]->name;
                                break;
                            }
                        }
                        
                        
                    }
                }
            }
            
            if ($online){
                $data['online'] = 'on';
                $actvityMenu = 'photo_gallery_online';
            }
            
            if ($photo){
                $data['with_photo'] = 'on';
            }
            
        }
        
        $extraQs = null;
        if ($data){
        	
        	if (isset($data['avatarFields'])){
        		$extraQs = $data['avatarFields'];
        		unset($data['avatarFields']);
        	}
                
            $mainSearchForm = new MEMBERX_CLASS_MainSearchForm($this);//OW::getClassInstance('MEMBERX_CLASS_MainSearchForm', $this);
            $data['form_name'] = $mainSearchForm->getName();
            $data['SearchFormSubmit'] = 'Search';
            
            $listId = $mainSearchForm->process($data, true, false);
        }else if($usedRoute === 'matchmaking_members_page'){
            $mainSearchForm = new MEMBERX_CLASS_MainSearchForm($this);
            $params['orderType'] = MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY;
            $listId = $mainSearchForm->processMyMatches();
            $actvityMenu = 'my_matches';
        }else if($usedRoute === 'memberx.featured_users' || $usedRoute === 'base_default_index'){
            $actvityMenu = 'featured_users';
            $mainSearchForm = new MEMBERX_CLASS_MainSearchForm($this);
            $listId = $mainSearchForm->processFeaturedUsers();
        }else{
            $listId = OW::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        }
        
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? (int)$_GET['page'] : 1;
        
        $orderType = $this->getOrderType($params);
        
        if ( !OW::getUser()->isAuthenticated()  )
        {
            if ( in_array($orderType, array(MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY, MEMBERX_BOL_Service::LIST_ORDER_DISTANCE)) )
            {
                throw new Redirect404Exception();
            }
        }
        
       
        //$event = new OW_Event('memberx.get_search_result_limit', array(), 24);    
        //OW::getEventManager()->trigger($event);
        //$limit = $event->getData();
        //if(empty($limit))
        //{
        //    $limit = 24;
        //}
        if (!$listId){
            $listId = 0;
        }
        
        $limit = 24;
        $offset = ($page -1) * $limit;
        
        $cacheData = array(
            'listId' => $listId,
            'orderType' => $orderType,
            'offset' => $offset,
            'limit' => $limit
        );
        
        $searchResult = $this->findCacheData($listId, $cacheData);
        
        if (!empty($searchResult)){
            $itemCount = $searchResult->itemCount;
            $list = json_decode($searchResult->dtoList);
            
        }else{
            $itemCount = BOL_SearchService::getInstance()->countSearchResultItem($listId);
            $list = MEMBERX_BOL_Service::getInstance()->getSearchResultList($listId, $orderType, $offset, $limit);
            $this->saveCacheData($listId, $itemCount, $cacheData, $list);
        }

      
//print_r($list);
        $idList = array();
        
        foreach ( $list as $key => $dto )
        {
            if (OW::getUser()->isAuthenticated() && $dto->id === OW::getUser()->getId()){
                unset($list[$key]);
                continue;
            }
            $idList[] = $dto->id;

        }
        
        $searchResultMenu = $this->searchResultMenu($orderType);
        
        if ( !empty($searchResultMenu) )
        {
            $this->addComponent('searchResultMenu', $searchResultMenu);
        }

        $cmp = OW::getClassInstance('MEMBERX_CMP_SearchResultList', $list, $page, $orderType, true, $extraQs);
        $this->addComponent('cmp', $cmp);
        
        $script = '$(".back_to_search_button").click(function(){
            window.location = ' . json_encode(OW::getRouter()->urlForRoute('users-search')) . ';
        });  ';
        
        OW::getDocument()->addOnloadScript($script);
        
        
        $jsParams = array(
            'excludeList' => $idList,
            'respunderUrl' => OW::getRouter()->urlForRoute('memberx.load_list_action'),
            'orderType' => $orderType,
            'page' => $page,
            'listId' => $listId,
            'count' => $limit,
            'userId' => OW::getUser()->getId()
            
        );
        
        if (MEMBERX_CMP_SearchResultSetting::getString(MEMBERX_CMP_SearchResultSetting::PAGING_MODE) === 'pages' && OW::getApplication()->isDesktop()){
            $params['noJs'] = true;
        }
        
        if (isset($params['noJs']) && $params['noJs'] === true){
            $jsParams['noScroll'] = true;
            $pageCmp = new BASE_CMP_Paging($page, round($itemCount / $limit), 8);
            $this->addComponent('pageCmp', $pageCmp);
        }
        
        if (OW::getPluginManager()->isPluginActive('winks')){
            $jsParams['winkRsp'] = OW::getRouter()->urlForRoute('winks.rsp');
            $jsParams['bookmarkRsp'] = OW::getPluginManager()->isPluginActive('bookmarks') ? OW::getRouter()->urlForRoute('bookmarks.mark_state') : '';
            $jsParams['wink_success_msg'] = OW::getLanguage()->text('winks', 'wink_sent_success_msg');
            $jsParams['wink_double_sent_error'] = OW::getLanguage()->text('winks', 'wink_double_sent_error');
            $jsParams['bookmark_added_msg'] = OW::getLanguage()->text('memberx', 'bookmark_added');
            $jsParams['bookmark_removed_msg'] = OW::getLanguage()->text('memberx', 'bookmark_removed');
        }
        
        if (OW::getPluginManager()->isPluginActive('bookmarks')){
            $jsParams['bookmarkRsp'] = OW::getRouter()->urlForRoute('bookmarks.mark_state');
            $jsParams['bookmark_added_msg'] = OW::getLanguage()->text('memberx', 'bookmark_added');
            $jsParams['bookmark_removed_msg'] = OW::getLanguage()->text('memberx', 'bookmark_removed');
        }
        
        $script = ' MEMBERX_ResultList.init('.  json_encode($jsParams).', $(".ow_search_results_photo_gallery_container")); ';
        OW::getDocument()->addOnloadScript($script);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('memberx')->getStaticJsUrl().'result_list.js');

        
        $quickSearchCmp = new MEMBERX_CMP_QuickSearch();
        $quickSearchCmp->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getCmpViewDir() . 'quick_search_horiz.html');
        $this->addComponent('quickSearchCmp', $quickSearchCmp);
        $this->addComponent('menu', $this->getMenu($actvityMenu));
        $this->assign('activeMenu', $actvityMenu);
        $this->assign('itemCount', $itemCount);
        $this->assign('page', $page);
        $this->assign('searchUrl', OW::getRouter()->urlForRoute('users-search'));

        //OW::getDocument()->setHeading(OW::getLanguage()->text('memberx', 'search_result'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));
        
        //$eventSelector = new MEMBERX_CMP_EventSelector(3);
        //$this->addComponent('eventSelector', $eventSelector);
    }
    
    public function findCacheData($searchId, $arrayData){
        
        $cacheLifetime = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::ENABLE_SEARCH_CACHE);
        if (!$cacheLifetime){
            return 0;
        }
        
        $data = json_encode($arrayData);
        $md5 = md5($data);
        
        $example = new OW_Example();
        $example->andFieldEqual('searchId', $searchId);
        $example->andFieldEqual('md5', $md5);
        
        $object = MEMBERX_BOL_SearchResultDao::getInstance()->findObjectByExample($example);
        
        if (!empty($object)){
            return $object;
        }else{
            return 0;
        }
    }
    
    public function saveCacheData($searchId, $itemCount, $arrayData, $dtoList){
        
        $cacheLifetime = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::ENABLE_SEARCH_CACHE);
        if (!$cacheLifetime){
            return 0;
        }
        
        $data = json_encode($arrayData);
        $md5 = md5($data);
        
        $object = new MEMBERX_BOL_SearchResult();
        $object->searchId = $searchId;
        $object->data = $data;
        $object->md5 = $md5;
        $object->itemCount = $itemCount;
        $object->dtoList = json_encode($dtoList);
        $object->creationTime = time();
        
        MEMBERX_BOL_SearchResultDao::getInstance()->save($object);
        
        return $object;
    }
    
    public function map()
    {
        $listId = OW::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        $list = BOL_UserService::getInstance()->findSearchResultList($listId, 0, BOL_SearchService::USER_LIST_SIZE);
        $this->assign('searchUrl', OW::getRouter()->urlForRoute('users-search'));

        $userIdList = array();
        if ( $list )
        {
            foreach ( $list as $dto )
            {
                $userIdList[] = $dto->getId();
            }

            $event = new OW_Event('googlelocation.get_map_component', array('userIdList' => $userIdList));
            OW::getEventManager()->trigger($event);
            $cmp = $event->getData();
            if ( $cmp )
            {
                $cmp->displaySearchInput(true);
                $cmp->disableDefaultUI(false);
                $cmp->disableInput(false);
                $cmp->disableZooming(false);
                $cmp->disablePanning(false);

                $this->assign('mapCmp', $cmp);
            }
        }
        else
        {
            $this->assign('mapCmp', null);        }
        
        $this->addComponent('menu', $this->getMenu('map'));

        //OW::getDocument()->setHeading(OW::getLanguage()->text('memberx', 'search_result'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));
    }
}