<?php


class MEMBERX_CLASS_QuickSearchForm extends BASE_CLASS_UserQuestionForm
{
    const FORM_SESSEION_VAR = MEMBERX_CLASS_MainSearchForm::MEMBERX_SEARCH_DATA;

    public $questionService;

    public $searchService;

    public $displayAccountType;

    public function __construct( $controller )
    {
        parent::__construct('QuickSearchForm');

        //$displayGender = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::USE_GENDER_OPTION_ON_QUICK_SEARCH);
        $this->questionService = BOL_QuestionService::getInstance();
        $this->searchService = MEMBERX_BOL_Service::getInstance();
        $lang = OW::getLanguage();
        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlForRoute('memberx.quick_search_action'));
        $this->setAjaxResetOnSuccess(false);

        $questionNameList = $this->searchService->getQuickSerchQuestionNames();
      
        foreach($questionNameList as $id => $questionName){
            if ($questionName === 'match_sex'){
                unset($questionNameList[$id]);
            }
        }
        
        
        $questionValueList = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);
        $sessionData = OW::getSession()->get(self::FORM_SESSEION_VAR);
        
        
        

        if ( $sessionData === null )
        {
            $sessionData = array();

            if ( OW::getUser()->isAuthenticated() )
            {
                $data = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('sex', 'match_sex'));

                if ( !empty($data[OW::getUser()->getId()]['sex']) )
                {
                    $sessionData['sex'] = $data[OW::getUser()->getId()]['sex'];
                }

                $sessionData['googlemap_location']['distance'] = 50;

                OW::getSession()->set(self::FORM_SESSEION_VAR, $sessionData);
            }
        }

        /* ------------------------- */
        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        $questions = array();
        $questionList = array();
        $orderedQuestionList = array();

        /* @var $question BOL_Question */
        foreach ( $questionDtoList as $key => $question )
        {
            if (!$question->onSearch){
                continue;
            }
            $dataList = (array) $question;
            $questions[$question->name] = $dataList;
            
            $questionList[$question->name] = $dataList;
            $orderedQuestionList[] = $questionDtoList[$question->name];
        }

        $this->addQuestions($questions, $questionValueList, array());

        if ($this->getElement('match_sex')){
            $this->getElement('match_sex')->setRequired(false);
            if (isset($sessionData['match_sex'])){
                $this->getElement('match_sex')->setValue($sessionData['match_sex']);
            }
        }

        $locationField = $this->getElement('googlemap_location');
        if ( $locationField )
        {
            $value = $locationField->getValue();
            if ( empty($value['distance']) )
            {
                $locationField->setDistance(50);
            }
        }



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


        $accountTypeSelect = new Selectbox('accountType');
        $accountTypeSelect->setRequired();
        $accountTypeSelect->setOptions($accountList);
        $accountTypeSelect->setLabel(OW::getLanguage()->text('memberx', 'account_type'));
        $this->addElement($accountTypeSelect);
        
        if (!empty($sessionData) && isset($sessionData['accountType']) && $sessionData['accountType']) {
            $accountType = $sessionData['accountType'];
            $accountTypeSelect->setValue($accountType);
        } else {
            $accountTypeSelect->setValue(array_keys($accountList)[0]);
        }
        
        
        if (count($accountList) < 2){
            $accountTypeSelect->addAttribute('class', 'ow_hidden');
            $accountTypeSelect->setLabel('');
        }

        $controller->assign('questionList', $orderedQuestionList);
        $controller->assign('displayAccountType', $this->displayAccountType);

        // 'online' field
        $onlineField = new CheckboxField('online');
        if ( isset($sessionData['online']) )
        {
            $onlineField->setValue($sessionData['online']);
        }
        $onlineField->setLabel($lang->text('memberx', 'online_only'));
        $this->addElement($onlineField);

//        if ( OW::getPluginManager()->isPluginActive('photo') )
//        {
            // with photo
            $withPhoto = new CheckboxField('with_photo');
            if (isset($sessionData['with_photo']))
            {
                $withPhoto->setValue($sessionData['with_photo']);
            }
            $withPhoto->setLabel($lang->text('memberx', 'with_photo'));
            $this->addElement($withPhoto);
//        }

        // submit
        $submit = new Submit('search');
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);
        
        $this->bindJsFunction(Form::BIND_SUCCESS, "function(data){
            if ( data.result ) {
                document.location.href = data.url;
            }
            else {
                OW.warning(data.error);
            }
        }");
    }

    public function setColumnCount( $formElement, $question )
    {
        $formElement->setColumnCount(1);
        
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {
        if ( !empty($value) )
        {
            if ( $presentation == BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX )
            {
                if( is_array($value) )
                {
                    $value = array_shift();
                }
                else
                {
                    for ( $i = 0; $i < 31; $i++ )
                    {
                        if( pow(2, $i) & $value )
                        {
                            $value = pow(2, $i);
                            break;
                        }
                    }


                }
            }
            else
            {
                $value = BOL_QuestionService::getInstance()->prepareFieldValueForSearch($presentation, $value);
            }

            $formField->setValue($value);
        }
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        parent::setFieldOptions($formField, $questionName, $questionValues);

        if ( $questionName == 'match_sex' )
        {
            $options = array_reverse($formField->getOptions(), true);
            $formField->setOptions($options);
            $formField->setColumnCount(count($options));
            $formField->addAttribute("style", "width:auto");
            
        }

        $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_label'));
        
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        $event = new OW_Event('base.questions_field_get_label', array(
            'presentation' => $presentation,
            'fieldName' => $questionName,
            'configs' => $configs,
            'type' => 'edit'
        ));

        OW::getEventManager()->trigger($event);

        $label = $event->getData();

        $class = null;

        $event = new OW_Event('base.questions_field_init', array(
            'type' => 'search',
            'presentation' => $presentation,
            'fieldName' => $questionName,
            'configs' => $configs
        ));

        OW::getEventManager()->trigger($event);

        $class = $event->getData();

        if ( empty($class) )
        {
            switch ( $presentation )
            {
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA :
                    $class = new TextField($questionName);
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                    $class = new CheckboxField($questionName);
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_RADIO :
                case BOL_QuestionService::QUESTION_PRESENTATION_SELECT :
                case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX :
                    if ($questionName === 'match_sex'){
                        $class = new CheckboxGroup($questionName);
                    }else{
                        $class = new Selectbox($questionName);
                    }
                    
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :
                case BOL_QuestionService::QUESTION_PRESENTATION_AGE :

                    $class = new MEMBERX_CLASS_AgeRangeField($questionName);

                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new MEMBERX_CLASS_AgeRangeValidator($class->getMinAge(), $class->getMaxAge()));

                    break;

                case self::QUESTION_PRESENTATION_RANGE :
                    $class = new Range($questionName);

                    if ( empty($this->birthdayConfig) )
                    {
                        $birthday = $this->findQuestionByName("birthdate");
                        if ( !empty($birthday) )
                        {
                            $this->birthdayConfig = ($birthday->custom);
                        }
                    }

                    $rangeValidator = new RangeValidator();

                    if ( !empty($this->birthdayConfig) && mb_strlen( trim($this->birthdayConfig) ) > 0 )
                    {
                        $configsList = json_decode($this->birthdayConfig, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinValue(date("Y") - $value['to']);
                                $class->setMaxValue(date("Y") - $value['from']);

                                $rangeValidator->setMinValue(date("Y") - $value['to']);
                                $rangeValidator->setMaxValue(date("Y") - $value['from']);
                            }
                        }
                    }

                    $class->addValidator($rangeValidator);

                    break;

                case self::QUESTION_PRESENTATION_DATE :
                    $class = new DateRange($questionName);

                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new DateValidator($class->getMinYear(), $class->getMaxYear()));
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_URL :
                    $class = new TextField($questionName);
                    $class->addValidator(new UrlValidator());
                    break;
            }

            if ( !empty($label) )
            {
                $class->setLabel($label);
            }

            if ( empty($class) )
            {
                $class = BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
            }
        }

        return $class;
    }
}