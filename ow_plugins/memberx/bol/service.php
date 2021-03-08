<?php


final class MEMBERX_BOL_Service
{
    const LIST_ORDER_LATEST_ACTIVITY = 'latest_activity';
    const LIST_ORDER_NEW = 'new';
    const LIST_ORDER_MATCH_COMPATIBILITY = 'match_compatibility';
    const LIST_ORDER_DISTANCE = 'distanse';
    const LIST_ORDER_WITHOUT_SORT = 'without_sort';
    
    /**
     * Class instance
     *
     * @var MEMBERX_BOL_Service
     */
    private static $classInstance;

    private $searchDao;
    /**
     * Class constructor
     */
    private function __construct() {
        
        $this->searchDao = MEMBERX_BOL_SearchDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return MEMBERX_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getOrderTypes()
    {
        $data = array();
        
        $config = OW::getConfig()->getValues('memberx');
        
        if ( !empty($config['order_latest_activity']) )
        {
            $data[MEMBERX_BOL_Service::LIST_ORDER_LATEST_ACTIVITY] = MEMBERX_BOL_Service::LIST_ORDER_LATEST_ACTIVITY;
        }
        
        if ( !empty($config['order_recently_joined']) )
        {
            $data[MEMBERX_BOL_Service::LIST_ORDER_NEW] = MEMBERX_BOL_Service::LIST_ORDER_NEW;
        }
        
        if ( !empty($config['order_match_compatibitity']) )
        {
            $data[MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY] = MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY;
        }
        
        if ( !empty($config['order_distance']) )
        {
            $data[MEMBERX_BOL_Service::LIST_ORDER_DISTANCE] = MEMBERX_BOL_Service::LIST_ORDER_DISTANCE;
        }
        
        $event = new OW_Event('memberx.get_list_order_types', array(), $data);
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function getPositionList()
    {
        return array(  'position1', 'position2', 'position3',  'position4'  );
    }

    public function saveQuickSerchQuestionPosition( array $value )
    {
        OW::getConfig()->saveConfig( 'memberx', 'quick_search_fields', json_encode($value) );
    }

    public function getQuickSerchQuestionNames()
    {
        $positions = $this->getQuickSerchQuestionPosition();

        $result = array();

        foreach ( $positions as $value )
        {
            if ( !empty($value) )
            {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function getQuickSerchQuestionPosition()
    {
        if ( !OW::getConfig()->configExists('memberx', 'quick_search_fields') )
        {
            OW::getConfig()->addConfig('memberx', 'quick_search_fields', '');
        }

        $questions = OW::getConfig()->getValue('memberx', 'quick_search_fields');
        
        $allowedFieldsList = $this->getAllowedQuickSerchQuestionNames();

        $result = array();

        if ( !empty($questions) )
        {
            $list = json_decode($questions, true);

            if ( !is_array($list) )
            {
                $result = array();
            }

            $tmpList = $allowedFieldsList;
            $positionList = $this->getPositionList();
            foreach (  $positionList as $position )
            {
                $question = array_shift($list);

                if ( in_array($question, $tmpList) )
                {
                    $result[$position] = $question;
                    unset($tmpList[$question]);
                }
                else
                {
                    $result[$position] = null;
                }
            }
        }

        if ( empty($result) )
        {
            $result = array('position1' => 'sex', 'position2' => 'match_sex', 'position3' => 'birthdate', 'position4' => null);
            
            if ( OW::getPluginManager()->isPluginActive('googlelocation') )
            {
                $result['position4'] = 'googlemap_location';
            }
        }

        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        
        if ( count($accountTypes) > 1 )
        {
            $result['position1'] = 'sex';
            $result['position2'] = 'match_sex';
        }

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($result);
        $searchQuestionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType('all');
        
        $searchList = array();
        
        foreach ( $searchQuestionList as $question )
        {
            $searchList[$question['name']] = $question;
        }
        
        foreach ( $result as $key => $item )
        {
            if ( empty($questions[$item]) )
            {
                $result[$key] = null;
            }
            
            if ( empty($searchList[$item]) && !in_array($item, array('sex', 'match_sex')) )
            {
                $result[$key] = null;
            }
            
            if ( count($accountTypes) <= 1 && in_array($item, array('sex', 'match_sex')) )
            {
                $result[$key] = null;
            }
        }

        return $result;
    }
    
    public function getAllowedQuickSerchQuestionNames()
    {
        $accountType2Question = BOL_QuestionService::getInstance()->getAccountTypesToQuestionsList();

        $searchQuestionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType('all');
        
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        $accountTypeList = array();
        
        foreach( $accountTypes as $item )
        {
            $accountTypeList[$item->name] = $item->name;
        }
        
        $searchQuestionNameList = array();
        $questionList = array();
        
        foreach ( $searchQuestionList as $key => $question )
        {
            $searchQuestionNameList[$question['name']] = $question['name'];
        }
        
        foreach( $accountType2Question as $dto )
        {
            if (  in_array($dto->questionName, $searchQuestionNameList) )
            {
                $questionList[$dto->accountType][$dto->questionName] = $dto->questionName;
            }
        }
        
        foreach ( $questionList as $accountType => $questions )
        {
            if ( in_array($accountType, $accountTypeList) )
            {
                if ( empty($result) )
                {
                    $result = $questions;
                }
                else
                {
                    $result = array_intersect($result, $questions);
                }
            }
        }

        $resultList = array();
        
        foreach ( $result as $key => $value )
        {
            $resultList[$value] = $value;
        }

        return $resultList;
    }

    public function getAccounTypeByGender( $gender )
    {
        $accountType2Gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        if ( !empty($accountType2Gender) )
        {
            foreach ( $accountType2Gender as $item )
            {
                if ( $item->genderValue == $gender )
                {
                    return $item->accountType;
                }
            }
        }

        return null;
    }

    public function getGenderByAccounType( $accountType )
    {
        $accountType2Gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        if ( !empty($accountType2Gender) )
        {
            foreach ( $accountType2Gender as $item )
            {
                if ( $item->accountType == $accountType )
                {
                    return $item->genderValue;
                }
            }
        }

        return null;
    }

    public function updateSearchData( $data )
    {
        
        return $data;
    }

    public function updateQuickSearchData( $data )
    {
        if ( empty($data) )
        {
            return array();
        }

        $questionNames = array_keys($data);

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNames);

        foreach ( $questions as $question )
        {
            if ( $question->presentation == BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX )
            {
                if ( !empty($question->name) )
                {
                    if( !is_array($data[$question->name]) && !empty($data[$question->name]) )
                    {
                        $data[$question->name] = array($data[$question->name]);
                    }
                }
            }
        }

        return $data;
    }
    
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        return $this->searchDao->getUserIdList($listId, $first, $count, $excludeList);
    }    
    
    public function getSearchResultList( $listId, $listType, $from, $count, $excludeList = array() )
    {
        if ( empty($excludeList) )
        {
            $excludeList = array();
        }
        
        if ( OW::getUser()->isAuthenticated() )
        {
            $searchSettings = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
            $showSearcherProfile = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE]) ?
                $searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE] : '';
            if ($showSearcherProfile == 'no'){
                $excludeList[] = OW::getUser()->getId();
            }
            
        }
        
        $userIdList = $this->getUserIdList($listId, 0, BOL_SearchService::USER_LIST_SIZE, $excludeList);
        //$userIdList = BOL_SearchService::getInstance()->getUserIdList($listId, 0, BOL_SearchService::USER_LIST_SIZE, $excludeList);
        
        if ( empty($userIdList) )
        {
            return array();
        }

        switch($listType)
        {
            case MEMBERX_BOL_Service::LIST_ORDER_NEW:
                
                return $this->searchDao->findSearchResultListOrderedByRecentlyJoined( $userIdList, $from, $count );
                
                break;
                
            case MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY:

                if ( OW::getPluginManager()->isPluginActive('matchmaking') && OW::getUser()->isAuthenticated() )
                {
                    $users = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
                    
                    $list = array();
                    
                    foreach ( $users as $user )
                    {
                        $list[$user->id] = $user;        
                    }
                    
                    $result = MATCHMAKING_BOL_Service::getInstance()->findCompatibilityByUserIdList( OW::getUser()->getId(), $userIdList, $from, $count);
                    $usersList = array();
                    
                    foreach ( $result as $item )
                    {
                        $usersList[$item['userId']] = $list[$item['userId']];
                    }
                    
                    return $usersList;
                }
                
                break;
                
            case MEMBERX_BOL_Service::LIST_ORDER_DISTANCE:

                if ( OW::getPluginManager()->isPluginActive('googlelocation') && OW::getUser()->isAuthenticated() )
                {
                    
                    
                    $result = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));
                    
                    if ( !empty($result[OW::getUser()->getId()]['googlemap_location']['json']) )
                    {
                        $location = $result[OW::getUser()->getId()]['googlemap_location'];
                        
                        return GOOGLELOCATION_BOL_LocationService::getInstance()->getListOrderedByDistance( $userIdList, $from, $count, $location['latitude'], $location['longitude'] );
                    }
                }
                
                break;  
                
            default:                
                $params = array(
                    'idList' => $userIdList,
                    'orderType' => $listType,
                    'from' => $from,
                    'count' => $count,
                    'userId' => OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0
                );
                
                $event = new OW_Event('memberx.get_ordered_list', $params, array());
                OW::getEventManager()->trigger($event);
                
                $data = $event->getData();
                
                if ( !empty($data) && is_array($data) )
                {
                    return $data;
                }
        }

        return $this->searchDao->findSearchResultListByLatestActivity( $userIdList, $from, $count );
    }
    
    public function getAccountTypeToQuestionList()
    {
        $accounType2QuestionList = BOL_QuestionService::getInstance()->getAccountTypesToQuestionsList();
        
        $list = array();
        /* @var $dto BOL_QuestionToAccountType */
        foreach ( $accounType2QuestionList as $dto )
        {
            $list[$dto->accountType][$dto->questionName] = $dto->questionName;
        }
        
        return $list;
    }
    
    public function getSearchResultMenu($order) {
        $lang = OW::getLanguage();
        $router = OW::getRouter();
        $config = OW::getConfig()->getValues('memberx');
        
        $items = array();
        
        
        
        if ( $config['order_recently_joined'] )
        {
            $item = array(
                'key' => 'recently_joined',
                'label' => $lang->text('memberx', 'recently_joined'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => MEMBERX_BOL_Service::LIST_ORDER_NEW)),
                'isActive' => $order == MEMBERX_BOL_Service::LIST_ORDER_NEW);
            
            array_push($items, $item);
        }
        
        if ( $config['order_latest_activity'] )
        {
            $item = array(
                'key' => 'latest_activity',
                'label' => $lang->text('memberx', 'latest'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => MEMBERX_BOL_Service::LIST_ORDER_LATEST_ACTIVITY)),
                'isActive' => $order == MEMBERX_BOL_Service::LIST_ORDER_LATEST_ACTIVITY);
            
            array_push($items, $item);
        }
        
        if ( OW::getPluginManager()->isPluginActive('matchmaking') && $config['order_match_compatibitity'] && OW::getUser()->isAuthenticated() )
        {
            $item = array(
                'key' => 'match_compatibitity',
                'label' => $lang->text('memberx', 'match_compatibitity'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY)),
                'isActive' => $order == MEMBERX_BOL_Service::LIST_ORDER_MATCH_COMPATIBILITY);
            
            array_push($items, $item);
        }

        if ( OW::getPluginManager()->isPluginActive('googlelocation') && $config['order_distance'] && OW::getUser()->isAuthenticated() )
        {
            $item = array(
                'key' => 'distance',
                'label' => $lang->text('memberx', 'distance'),
                'url' => $router->urlForRoute('users-search-result', array('orderType' => MEMBERX_BOL_Service::LIST_ORDER_DISTANCE)),
                'isActive' => $order == MEMBERX_BOL_Service::LIST_ORDER_DISTANCE);
            
            array_push($items, $item);
            
        }
        
        $event = new OW_Event('memberx.get_list_order_menu', array('order' => $order), $items);
        
        OW::getEventManager()->trigger($event);

        $items = $event->getData();
        
        return $items;
    }
    
    public function findUserIdListByQuestionValues( $questionValues, $first, $count, $isAdmin = false, $aditionalParams = array() )
    {
        $first = (int) $first;
        $count = (int) $count;

        $data = array(
            'data' => $questionValues,
            'first' => $first,
            'count' => $count,
            'isAdmin' => $isAdmin,
            'aditionalParams' => $aditionalParams
        );

        $event = new OW_Event("base.question.before_user_search", $data, $data);

        OW_EventManager::getInstance()->trigger($event);

        $data = $event->getData();
//print_r($data);
//exit();
        return $this->searchDao->findUserIdListByQuestionValues($data['data'], $data['first'], $data['count'], $data['isAdmin'], $data['aditionalParams']);
    }
    
    
    public function getSearchListId($data, $cacheLifetime = 0){
        
        if (!is_array($data)){
            return 0;
        }
        
        if (!$cacheLifetime){
            $cacheLifetime = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::ENABLE_SEARCH_CACHE);
        }
        
        if (!$cacheLifetime){
            return 0;
        }
        
        
        if (isset($data['csrf_token'])){
            unset($data['csrf_token']);
        }
        
        if (isset($data['form_name'])){
            unset($data['form_name']);
        }
        
        if (isset($data['SearchFormSubmit'])){
            unset($data['SearchFormSubmit']);
        }
        
        $md5 = md5(json_encode($data));
        
        $example = new OW_Example();
        $example->andFieldEqual('md5', $md5);
        $example->andFieldGreaterThan('creationTime', time() - $cacheLifetime);
        
        $object = MEMBERX_BOL_SearchIdDao::getInstance()->findObjectByExample($example);
        if ($object){
            return $object->searchId;
        }
        
        return 0;
        
        
    }
    
    public function saveSearchListId($listId, $data){
        
        
        if (!is_array($data)){
            return;
        }
        
        $cacheLifetime = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::ENABLE_SEARCH_CACHE);
        
        if (!$cacheLifetime){
            return 0;
        }
        
        if (isset($data['csrf_token'])){
            unset($data['csrf_token']);
        }
        
        if (isset($data['form_name'])){
            unset($data['form_name']);
        }
        
        if (isset($data['SearchFormSubmit'])){
            unset($data['SearchFormSubmit']);
        }
        
        
        $entity = new MEMBERX_BOL_SearchId();
        $entity->searchId = $listId;
        $entity->data = json_encode($data);
        $entity->md5 = md5($entity->data);
        $entity->creationTime = time();
        
        MEMBERX_BOL_SearchIdDao::getInstance()->save($entity);
        
    }
}