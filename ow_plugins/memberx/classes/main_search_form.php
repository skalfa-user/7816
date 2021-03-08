<?php


class MEMBERX_CLASS_MainSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'SearchFormSubmit';
    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';
    const MEMBERX_SEARCH_DATA = 'memberx_search_data';
    
    const SECTION_TR_PREFIX = 'memberx_section_';
    const QUESTION_TR_PREFIX = 'memberx_question_';

    protected $controller;
    protected $displayMainSearch = true;
    protected $mainSearchQuestionList = array();
    protected $searchService;
    /**
     * @param OW_ActionController $controller
     * @param string $name
     */
    public function __construct( $controller, $name = '' )
    {
        if ($name)
        {
            parent::__construct($name);
        }
        else
        {
            parent::__construct('MainSearchForm');
        }

        $this->initForm($controller);
        $this->searchService = MEMBERX_BOL_Service::getInstance();
    }

    protected function initForm($controller)
    {
        $this->controller = $controller;

        $controller->assign('section_prefix', self::SECTION_TR_PREFIX);
        $controller->assign('question_prefix', self::QUESTION_TR_PREFIX);

        $questionService = BOL_QuestionService::getInstance();

        $this->setId('MainSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        // get default question values
        $questionData = $this->getQuestionData();

        // prepare account types list
        $accountList = $this->getAccountTypes();
        $searchConfig = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
        
        if (isset($searchConfig[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT]) &&
                $searchConfig[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT] === 'yes' &&
                count($accountList) > 1 &&
                OW::getUser()->isAuthenticated()){
            $myAccountType = OW::getUser()->getUserObject()->accountType;
            
            foreach($accountList as $key => $label){
                if ($key === $myAccountType){
                    unset($accountList[$key]);
                    break;
                }
            }
        }
        
        
        $accountTypeFiled = new Selectbox('accountType');
        $accountTypeFiled->setLabel(OW::getLanguage()->text('memberx', 'account_type'));
        $accountTypeFiled->setOptions($accountList);
        $accountTypeFiled->setRequired();
        $accountTypeFiled->setId('memberx-account-type');
        $accountTypeFiled->addAttribute('change-url', OW::getRouter()->urlForRoute('users-search'));
        
        if (count($accountList) < 2){
            $accountTypeFiled->addAttribute('class', 'ow_hidden');
            $accountTypeFiled->setLabel('');
        }
        
        $this->addElement($accountTypeFiled);
        
        
        $keys = array_keys($accountList);
        $accountType = $keys[0];
        // set account type
        
        if (isset($_GET['accounttype'])){
            $accountType = $_GET['accounttype'];
        }else{
            $searchData = OW::getSession()->get(self::MEMBERX_SEARCH_DATA);
            if (!empty($searchData) && isset($searchData['accountType'])){
                $accountType = $searchData['accountType'];
            }
        }
        
        if (!array_key_exists($accountType, $accountList)){
            $accountType = $keys[0];
        }
        
        $accountTypeFiled->setValue($accountType);
        
        $questions = $questionService->findSearchQuestionsForAccountType('all');
        // prepare questions list
        $this->mainSearchQuestionList = array();
        $questionNameList = array();

        foreach ( $questions as $key => $question )
        {
            
            //if ($question['name'] === 'sex' || $question['name'] === 'match_sex'){
            //if ($question['name'] === 'sex'){
                //unset($questions[$key]);
            //    continue;
            //}
            
            //if (!MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::USE_GENDER_OPTION)){
            //    if ($question['name'] === 'match_sex'){
            //        unset($questions[$key]);
            //        continue;
            //    }
            //}
            
            $sectionName = $question['sectionName'];
            $questionNameList[] = $question['name'];
            //$isRequired = in_array($question['name'], array('match_sex')) ? 1 : 0;
            $questions[$key]['required'] = false; //$isRequired;

            $this->mainSearchQuestionList[$sectionName][] = $question;
        }
        // -- end --

        $visibilityList = $this->getVisibilityList($accountType, $this->mainSearchQuestionList);

        $controller->assign('visibilityList', $visibilityList);

        // get question values list
        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        // prepare add sex and match sex questions
        unset($questionData['sex']);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $locationField = $this->getElement('googlemap_location');
        if ( $locationField )
        {
            $value = $locationField->getValue();
            if ( empty($value['json']) )
            {
                $locationField->setDistance(50);
            }
        }

        
        
        
        $controller->assign('questionList', $this->mainSearchQuestionList);

        // add 'online' field
        $onlineField = new CheckboxField('online');
        if ( !empty($questionData) && is_array($questionData) && array_key_exists('online', $questionData) )
        {
            $onlineField->setValue($questionData['online']);
        }
        $onlineField->setLabel(OW::getLanguage()->text('memberx', 'online_only'));
        $this->addElement($onlineField);

        // add with photo field
        $withPhoto = new CheckboxField('with_photo');
        if ( !empty($questionData) && is_array($questionData) && array_key_exists('with_photo', $questionData) )
        {
            $withPhoto->setValue($questionData['with_photo']);
        }
        $withPhoto->setLabel(OW::getLanguage()->text('memberx', 'with_photo'));
        $this->addElement($withPhoto);
        $controller->assign('hasFriends', false);
        
        // add friends only field -- will slow down the server
        /*if (OW::getPluginManager()->isPluginActive('friends')){
            $friendsOnly = new Selectbox('friends_only');
            $friendsOnly->setLabel(OW::getLanguage()->text('memberx', 'friends_only'));
            $yesNo = array(
                'yes' => OW::getLanguage()->text('memberx', 'yes'),
                'no' => OW::getLanguage()->text('memberx', 'no')
            );
            
            $friendsOnly->setOptions($yesNo);
            
            if ( !empty($questionData) && is_array($questionData) && array_key_exists('friends_only', $questionData) )
            {
                $friendsOnly->setValue($questionData['friends_only']);
            }
            $this->addElement($friendsOnly);
            $controller->assign('hasFriends', true);
        }*/

    }

    protected function getFormSessionVar()
    {
        return self::FORM_SESSEION_VAR;
    }

    public function updateSearchData( $data )
    {        

        if (!isset($data['accountType'])){
            return $data;
        }

        $accountType = $data['accountType'];
        $questions = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType($accountType);
        $questionList = array();
        
        if ( empty($questions) )
        {
            return $data;
        }
        
        foreach($questions as $key => $value)
        {
            $questionList[$value['name']] = $value['name'];
        }
        
        foreach ( $data as $key => $value)
        {

            if ( in_array($key, array('with_photo', 'sex', 'match_sex', 'online', 'accountType', 'friends_only')) )
            {
                continue;
            }
            
            if ( !in_array($key, $questionList) )
            {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
    
    public function processMyMatches(){
        
        $data = array('userId' => OW::getUser()->getId(), 'listType' => 'my_match');
        $listId = $this->searchService->getSearchListId($data);
        
        if ($listId){
            return $listId;
        }
        
        
        
        $matchMakingService = MATCHMAKING_BOL_Service::getInstance();
        $dtoList = $matchMakingService->findMatchList(OW::getUser()->getId(), 0, BOL_SearchService::USER_LIST_SIZE);
        
        
        
        $userIdList = array();
        foreach($dtoList as $matchItem){
            $userIdList[] = $matchItem['id'];
        }
        
        //$userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE, false, $addParams);
        $listId = 0;

        /*$searchSettings = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
        $showSearcherProfile = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE]) ?
                $searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE] : '';

        if ($showSearcherProfile == 'no') {
            if (OW::getUser()->isAuthenticated()) {
                foreach ($userIdList as $key => $id) {
                    if (OW::getUser()->getId() == $id) {
                        unset($userIdList[$key]);
                    }
                }
            }
        }*/

        if (count($userIdList) > 0) {
            $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
        }

        if ($listId){
            
        }
        $this->searchService->saveSearchListId($listId, $data);
        OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

        BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

        return $listId;
    }
    
    
    public function processFeaturedUsers(){
        
        $featuredDao = BOL_UserFeaturedDao::getInstance();
        $userDao = BOL_UserDao::getInstance();
        
        
        
        $sql = "SELECT `a`.* FROM `{$featuredDao->getTableName()}` `a` "
        . "INNER JOIN `{$userDao->getTableName()}` `b` ON (`a`.`userId` = `b`.`id`) ";
        
        
        if (class_exists('VADMIN_BOL_DisabledUserDao')){
            $disabledUserDao = VADMIN_BOL_DisabledUserDao::getInstance();
            $sql = $sql . " LEFT JOIN `{$disabledUserDao->getTableName()}` `c` ON (`a`.`userId` = `c`.`userId`) ";
        }
        
        $sql = $sql .  " WHERE 1 ";
        
        if (class_exists('VADMIN_BOL_DisabledUserDao')){
            $disabledUserDao = VADMIN_BOL_DisabledUserDao::getInstance();
            $sql = $sql . " AND `c`.`id` IS NULL ";
        }
        
        if (MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT)){
            if (OW::getUser()->isAuthenticated()){
                $myAccountType = OW::getUser()->getUserObject()->accountType;
                $sql .= " AND `b`.`accountType` != '{$myAccountType}' ";
            }
            
        }

        $featuredList = OW::getDbo()->queryForObjectList($sql, $featuredDao->getDtoClassName());
        $listId = 0;
        
        $userIdList = array();
        if (!empty($featuredList)){
            foreach ($featuredList as $dto){
                $userIdList[] = $dto->userId;
            }
        }

        if (count($userIdList) > 0) {
            $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
        }

        OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

        BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

        return $listId;
    }
    
    
    
    
    public function process( $data, $returnListId = false, $updateSearchData = true, $userListSize = 0, $returnType = 'listId', $order = '`user`.`activityStamp` DESC')
    {

        
        if (!$this->isAjax() && isset($data['form_name']) && $data['form_name'] === $this->getName() )
        {   
        
            if ($updateSearchData){
                OW::getSession()->set($this->getFormSessionVar(), $data);
                OW::getSession()->set(self::MEMBERX_SEARCH_DATA, $data);
            }
            
            if ($userListSize === 0){
                $userListSize = BOL_SearchService::USER_LIST_SIZE;
            }
           
            if ( isset($data[self::SUBMIT_NAME]) && !$this->isAjax() )
            {
                if ( !OW::getUser()->isAuthorized('base', 'search_users') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                    OW::getFeedback()->warning($status['msg']);
                    
                    $this->controller->redirect(OW::getRouter()->urlForRoute('users-search'));
                }

                $data = $this->updateSearchData($data);
                $data = MEMBERX_BOL_Service::getInstance()->updateSearchData( $data );
                
                $listId = $this->searchService->getSearchListId($data);
                
                if ($listId){
                    
                    if ($returnType === 'idList'){
                        $excludeIdList = array();
                        if (OW::getUser()->isAuthenticated()){
                            $excludeIdList[OW::getUser()->getId()];
                        }
                        $list = MEMBERX_BOL_Service::getInstance()->getUserIdList($listId, 0, $userListSize, $excludeIdList);
                        if (!empty($list)){
                            return $list;
                        }
                    
                        //return $userIdList;
                    }else if($returnListId){
                        OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);
                        BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');
                        return $listId;
                    }else {
                        OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);
                        BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');
                        $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
                    }
                    

                }
                
                $addParams = array('join' => '', 'where' => '', 'order' => $order);

                if ( !empty($data['online']) )
                {
                    $addParams['join'] .= " INNER JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` `online` ON (`online`.`userId` = `user`.`id`) ";
                }

                if ( !empty($data['with_photo']) )
                {
                     $addParams['join'] .= " INNER JOIN `".OW_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";
                }
                
                

                $searchSettings = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
                $restrictToOtherAccountType = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT]) ?
                $searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT] : 'no';
                
                if ($restrictToOtherAccountType === 'yes' && OW::getUser()->isAuthenticated() && !isset($data['accountType'])){
                                    
                    $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
                    if (count($accountTypes) > 1){
                        $myAccountType = OW::getUser()->getUserObject()->accountType;
                        
                        foreach ($accountTypes as $accountType){
                            if ($accountType->name != $myAccountType){
                                $data['accountType'] = $accountType->name;
                                break;
                            }
                        }
                        
                    }
                }
                
                $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, $userListSize, false, $addParams);
                
                $listId = 0;

                //$showSearcherProfile = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE]) ?
                //$searchSettings[MEMBERX_CMP_SearchResultSetting::SHOW_SEARCHER_PROFILE] : '';
                
                /*if ($showSearcherProfile == 'no'){
                    if ( OW::getUser()->isAuthenticated() )
                    {
                        foreach ( $userIdList as $key => $id )
                        {
                            if ( OW::getUser()->getId() == $id )
                            {
                                unset($userIdList[$key]);
                            }
                        }
                    }
                }*/
                
                
                if (count($userIdList) === 1){
                    
                    if (MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT)){
                        if (OW::getUser()->isAuthenticated()){
                            $myAccountType = OW::getUser()->getUserObject()->accountType;
                            $userId = $userIdList[0];
                            $userObject = BOL_UserService::getInstance()->findUserById($userId);
                            if ($myAccountType === $userObject->accountType){
                                unset($userIdList[0]);
                            }
                        }
                    }
                }
                
                if ($returnType === 'idList'){
                    return $userIdList;
                }

                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }
                
                $this->searchService->saveSearchListId($listId, $data);
                OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);
                BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');
                
                if ($returnListId){
                    return $listId;
                }
                
                $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
            }
            
            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search"));
        }
    }
    
    

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        parent::setFieldOptions($formField, $questionName, $questionValues);

        //if ( $questionName == 'match_sex' )
        //{
        //    $options = array_reverse($formField->getOptions(), true);
        //    $formField->setOptions($options);
        //}

        $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_label'));
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {
        if ( !empty($value) )
        {
            $value = BOL_QuestionService::getInstance()->prepareFieldValueForSearch($presentation, $value);
            $formField->setValue($value);
        }
    }
    
    protected function getQuestionData()
    {
        $questionData = OW::getSession()->get($this->getFormSessionVar());

        
        if ( $questionData === null )
        {
            $questionData = array();

            if ( OW::getUser()->isAuthenticated() )
            {
                $data = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('match_sex'));
                $questionData['match_sex'] = $data[OW::getUser()->getId()]['match_sex'];
                $questionData['googlemap_location']['distance'] = 50;
                OW::getSession()->set($this->getFormSessionVar(), $questionData);
            }
        }
        
        else if ( !empty($questionData['match_sex']) )
        {
            if ( !is_array($questionData['match_sex']) )
            {

                for ( $i = 0; $i < 31; $i++ )
                {
                    if( pow(2, $i) & $questionData['match_sex'] )
                    {
                        $questionData['match_sex'] = pow(2, $i);
                        break;
                    }
                }
            }
            
        }

        return $questionData;
    }
    
    /*
    protected function addGenderQuestions($controller, $accounts, $questionValueList, $questionData)
    {
        $controller->assign('displayGender', false);
        $controller->assign('displayAccountType', false);
        
        if ( count($accounts) > 1  )
        {
            $controller->assign('displayAccountType', true);
            
            if ( !OW::getUser()->isAuthenticated() )
            {
                $controller->assign('displayGender', true);

                $sex = new Selectbox('sex');
                $sex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('sex'));
                $sex->setRequired();
                $sex->setHasInvitation(false);

                //$accountType->setHasInvitation(false);
                $this->setFieldOptions($sex, 'sex', $questionValueList['sex']);

                if ( !empty($questionData['sex']) )
                {
                    $sex->setValue($questionData['sex']);
                }

                $this->addElement($sex);
            }
            else
            {
                $sexData = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('sex'));
                
                        
                if ( !empty($sexData[OW::getUser()->getId()]['sex']) )
                {
                    $sex = new HiddenField('sex');
                    $sex->setValue($sexData[OW::getUser()->getId()]['sex']);
                    $this->addElement($sex);
                }
            }

            $matchSex = new Selectbox('match_sex');
            $matchSex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('match_sex'));
            $matchSex->setRequired();
            $matchSex->setHasInvitation(false);

            //$accountType->setHasInvitation(false);
            $this->setFieldOptions($matchSex, 'match_sex', $questionValueList['sex']);
            
            if ( !empty($questionData['match_sex']) )
            {
                $matchSex->setValue($questionData['match_sex']);
            }

            $this->addElement($matchSex);
        }
    }*/
    
    protected function getVisibilityList($accountType, $questionBySectionList)
    {
        $accountTypeToQuestionList = MEMBERX_BOL_Service::getInstance()->getAccountTypeToQuestionList();
        
        $visibleList = !empty($accountTypeToQuestionList[$accountType]) && is_array($accountTypeToQuestionList[$accountType]) 
                ? $accountTypeToQuestionList[$accountType] : array();
        
        $visibleQuestionsList = array();
        $visibleSectionList = array();
        
        foreach( $questionBySectionList as $section => $questions )
        {
            $visibleSectionList[$section] = false;
            
            $visibleQuestionCount = 0;
            foreach( $questions as $question )
            {
                $visibleQuestionsList[$question['name']] = false;
                if ( !empty($question['name']) && in_array($question['name'], $visibleList) )
                {
                    $visibleQuestionsList[$question['name']] = true;
                    $visibleQuestionCount++;
                }
            }
            
            if ($visibleQuestionCount > 0 )
            {
                $visibleSectionList[$section] = true;
            }
        }
        
        return array('sections' => $visibleSectionList, 'questions' => $visibleQuestionsList);
    }
    
    public function isValid( $data )
    {
        $valid = true;

        if ( !is_array($data) )
        {
            throw new InvalidArgumentException('Array should be provided for validation!');
        }
        
        //$matchSex = !empty($data['match_sex']) ? $data['match_sex'] : null;
        //if ($matchSex)
        //{
        //    $accounType = MEMBERX_BOL_Service::getInstance()->getAccounTypeByGender($matchSex);
        //    $visibilityList = $this->getVisibilityList($accounType, $this->mainSearchQuestionList);
        //}

        /* @var $element FormElement */
        foreach ( $this->elements as $element )
        {
            $element->setValue(( isset($data[$element->getName()]) ? $data[$element->getName()] : null));

            if ( !empty($visibilityList['questions']) && isset($visibilityList['questions'][$element->getName()]) && $visibilityList['questions'][$element->getName()] == false )
            {
                continue;
            }
            
            if ( !$element->isValid() )
            {
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    
}