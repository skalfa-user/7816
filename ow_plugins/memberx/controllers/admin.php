<?php


class MEMBERX_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public $contentMenu;
    
    public function __construct() {
        parent::__construct();
        
        $this->addContentMenu();
    }
    
    private function addContentMenu()
    {
        $language = OW::getLanguage();

        $router = OW_Router::getInstance();

        $menuItems = array();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('general_settings')->setLabel($language->text('memberx', 'general_settings'))->setUrl($router->urlForRoute('memberx.admin_general_setting'))->setOrder('1');
        $menuItem->setIconClass('ow_ic_gear_wheel');
        $menuItems[] = $menuItem;

        //$menuItem = new BASE_MenuItem();
        //$menuItem->setKey('quick_search_settings')->setLabel($language->text('memberx', 'quick_search_settings'))->setUrl($router->urlForRoute('memberx.admin_quick_search_setting'))->setOrder('4');
        //$menuItem->setIconClass('ow_ic_lens');
        //$menuItems[] = $menuItem;
        
        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('profile_field_setting')->setLabel($language->text('memberx', 'profile_field_setting'))->setUrl($router->urlForRoute('memberx.admin_profile_field_setting'))->setOrder('5');
        $menuItem->setIconClass('ow_ic_lens');
        $menuItems[] = $menuItem;

        $this->contentMenu = new BASE_CMP_ContentMenu($menuItems);
        
        $this->addComponent('contentMenu', $this->contentMenu);
    }
    
    public function profileFieldSettings(){
        
        $this->contentMenu->getElement('profile_field_setting')->setActive(true);
        $router = OW_Router::getInstance();
        $menuItems = array();
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypesWithLabels();
        
        if (empty($accountTypes)){
            return;
        }

        foreach($accountTypes as $name => $label){
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($name)->
                    setLabel($label)->
                    setUrl($router->urlForRoute('memberx.admin_profile_field_setting') . '?type=' . $name);
            $menuItem->setIconClass('ow_ic_lens');

            $menuItems[] = $menuItem;
        }
        

        $contentMenu2 = new BASE_CMP_ContentMenu($menuItems);
        $this->addComponent('contentMenu2', $contentMenu2);
        
        $accountType = isset($_GET['type']) ? $_GET['type'] : false;

        if (!$accountType || !array_key_exists($accountType, $accountTypes)){
            $accountType = array_keys($accountTypes)[0];
        }
        
        if (OW::getRequest()->isPost()){
            if (isset($_POST['form_name']) && $_POST['form_name'] === MEMBERX_CMP_ProfileFieldSettings::FORM_NAME){
                $result = (new MEMBERX_CMP_ProfileFieldSettings($accountType))->process();
                if ($result){
                    OW::getFeedback()->info(OW::getLanguage()->text('memberx', 'setting_saved'));
                }
                $this->redirect();
            }
        }
        
        $contentMenu2->getElement($accountType)->setActive(true);
        $profileFieldConfig = (new MEMBERX_CMP_ProfileFieldSettings($accountType))->instatiate();
        $this->addComponent('profileFieldConfig', $profileFieldConfig);
        
       
    }
    
    /**
     * Default action
     */
    public function generalSettings( $params )
    {
        $this->contentMenu->getElement('general_settings')->setActive(true);
        
        $form = new GeneralSettingsForm();
        
        if ( !OW::getRequest()->isAjax() && OW::getRequest()->isPost() )
        {
            
            if (isset($_POST['form_name'])){
                switch ($_POST['form_name']){
                    case MEMBERX_CMP_HighlightRoleSettings::FORM_NAME:
                        $result = (new MEMBERX_CMP_HighlightRoleSettings())->process();
                        break;
                    case MEMBERX_CMP_SearchResultSetting::FORM_NAME:
                        $result = (new MEMBERX_CMP_SearchResultSetting())->process();
                        break;
                    case MEMBERX_CMP_PossibleButtonSetting::FORM_NAME:
                        $result = (new MEMBERX_CMP_PossibleButtonSetting())->process();
                        break;
                    case $form->getName():
                        if( $form->isValid($_POST) )
                        {
                            $form->process($_POST);
                            $this->redirect();
                        }
                    default: 
                        $result = false;
                }
                
                if ($result){
                    OW::getFeedback()->info(OW::getLanguage()->text(MEMBERX_CMP_SearchResultSetting::PLUGIN_KEY, 'setting_saved'));
                }
                
                $this->redirect();
            }
            
            
        }
        
        $this->addForm($form);
        $highlightRole = (new MEMBERX_CMP_HighlightRoleSettings())->instatiate();
        $this->addComponent('highlightRole', $highlightRole);
        
        $otherSetting = new MEMBERX_CMP_SearchResultSetting();
        $this->addComponent('otherSetting', $otherSetting);
        
        $possibleButtons = (new MEMBERX_CMP_PossibleButtonSetting())->instatiate();
        $this->addComponent('possibleButtons', $possibleButtons);
    }
    
    /**
     * Default action
     */
    public function quickSearchSettings( $params )
    {
        $this->contentMenu->getElement('quick_search_settings')->setActive(true);
        
        $language = OW::getLanguage();
        
        $allQuestionNameList = MEMBERX_BOL_Service::getInstance()->getAllowedQuickSerchQuestionNames();
        $position2questionName = MEMBERX_BOL_Service::getInstance()->getQuickSerchQuestionPosition();

        $allowedQuestionNameList = array_diff($allQuestionNameList, $position2questionName);

        $allowedQuestionList = BOL_QuestionService::getInstance()->findQuestionByNameList($allowedQuestionNameList);
        $list = BOL_QuestionService::getInstance()->findQuestionByNameList($position2questionName);

        foreach( $position2questionName as $item )
        {
            if ( !empty($list[$item]) )
            {
                $quickSearchQuestionList[$item] = $list[$item];
            }
        }

        $tmpList = $quickSearchQuestionList;

        //$positionList = MEMBERX_BOL_Service::getInstance()->getPositionList();
        $this->assign('allowedQuestionList', $allowedQuestionList);
        $this->assign('quickSearchQuestionList', $quickSearchQuestionList);
        $this->assign('positions', $position2questionName);

        $allowedQuestionListItems = array();
        $quickSearchQuestionListItems = array();

        foreach ( $allowedQuestionList as $question )
        {
            $allowedQuestionListItems[$question->name] = BOL_QuestionService::getInstance()->getQuestionLang($question->name);
        }

        foreach ( $quickSearchQuestionList as $question )
        {
            $quickSearchQuestionListItems[$question->name] = BOL_QuestionService::getInstance()->getQuestionLang($question->name);
        }

        $allQuestionList = array();

        $searchQuestionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType('all');

        foreach ( $searchQuestionList as $question )
        {
            $allQuestionList[$question['name']] = BOL_QuestionService::getInstance()->getQuestionLang($question['name']);
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('memberx')->getStaticJsUrl().'quick_search_settings.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.sticky.js');

        OW::getDocument()->addOnloadScript( " $('.ow_quicksearch_layout').sticky( { topSpacing:60 } ); " );

        OW::getDocument()->addOnloadScript( "

                var allowedModel = new MEMBERX_ListModel( ".json_encode($allowedQuestionListItems)." );
                var quickSearchModel = new MEMBERX_QuickSearchModel( ".json_encode($quickSearchQuestionListItems).", ".json_encode($position2questionName)."  );

                var QuickSearchView = new MEMBERX_QuickSearchView(".json_encode( OW::getRouter()->urlFor('MEMBERX_CTRL_Admin', 'responder') ).");
                QuickSearchView.init(quickSearchModel);

                var ListView = new MEMBERX_ListView();
                ListView.init(allowedModel, ".  json_encode($allQuestionList).");

                $('#quicksearch_preview').click(
                    function(){
                        var button = $(this);
                        button.addClass('ow_inprogress');
                        
                        var params = {
                            width:330,
                            iconClass: 'ow_ic_user',
                            title: " . json_encode(OW::getLanguage()->text('memberx', 'quick_search')) . ",
                            onLoad: function() {
                                window.owForms['QuickSearchForm'].events = {
                                    submit:[],
                                    success:[]
                                };

                                //window.owForms['QuickSearchForm'].bind( 'submit', function() { return false; } )
                                $('form[name=QuickSearchForm]').unbind( 'submit' );
                                $('form[name=QuickSearchForm]').bind( 'submit', function() { return false; } );
                                $('form[name=QuickSearchForm]').bind( 'submit', function() { return false; } );
                                $('form[name=QuickSearchForm] .ow_qs_btn .ow_qs_label a').attr('href', 'javascript://');

                                button.removeClass('ow_inprogress');
                            }
                        };
                        searchFloatBox = OW.ajaxFloatBox('MEMBERX_CMP_QuickSearch', [], params);
                    }
                );
            " );
    }

    public function responder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }

        $lang = OW::getLanguage();

        if ( !OW::getUser()->isAdmin() )
        {
            exit(json_encode(array('result' => false)));
        }

        if ( isset($_POST['positions']) && is_array($_POST['positions']) )
        {
            $positions = $_POST['positions'];

            $allowedQuestionNameList = MEMBERX_BOL_Service::getInstance()->getAllowedQuickSerchQuestionNames();

            $allowedQuestionNameList['sex'] = 'sex';
            $allowedQuestionNameList['match_sex'] = 'match_sex';

            $positionList = MEMBERX_BOL_Service::getInstance()->getPositionList();
            $result = array();

            foreach ( $positionList as $position )
            {
                $result[$position] = null;

                if ( !empty($positions[$position]) && in_array($positions[$position], $allowedQuestionNameList) )
                {
                    $result[$position] = $positions[$position];
                    unset($allowedQuestionNameList[$positions[$position]]);
                }
            }

            MEMBERX_BOL_Service::getInstance()->saveQuickSerchQuestionPosition($result);

            exit(json_encode(array('result' => true)));
        }
        else
        {
            exit(json_encode(array('result' => false)));
        }
    }
}

class GeneralSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('memberx_general_settings');

        $language = OW::getLanguage();

        $config = OW::getConfig()->getValues('memberx');
        
        $element = new CheckboxField('enable_username_search');
        $element->setValue($config['enable_username_search']);
        $element->setLabel($language->text('memberx', 'enable_username_search'));
        
        $this->addElement($element);

        $element = new CheckboxField('latest_activity');
        $element->setValue($config['order_latest_activity']);
        $element->setLabel($language->text('memberx', 'order_latest_activity'));

        $this->addElement($element);
        
        $element = new CheckboxField('recently_joined');
        $element->setLabel($language->text('memberx', 'order_recently_joined'));
        $element->setValue($config['order_recently_joined']);
        $this->addElement($element);
        
        
        $element = new CheckboxField('match_compatibitity');
        $element->setLabel($language->text('memberx', 'order_match_compatibitity'));
        $element->setValue($config['order_match_compatibitity']);
        $this->addElement($element);
        
        $element = new CheckboxField('distance');
        $element->setLabel($language->text('memberx', 'order_distance'));
        $element->setValue($config['order_distance']);
        $this->addElement($element);

        $element = new TextField('hide_user_activity_after');
        $element->setRequired(true);
        $element->addAttribute('style','width:50px');
        $validator = new IntValidator(1, 10000);
        $element->addValidator($validator);
        $element->setValue((int)$config['hide_user_activity_after']);
        $this->addElement($element->setLabel($language->text('memberx', 'hide_user_activity')));

        $submit = new Submit('save');
        $submit->setValue($language->text('memberx', 'save'));
        $this->addElement($submit);
    }
    
    public function process( $data )
    {
        OW::getConfig()->saveConfig('memberx', 'enable_username_search', !empty($data['enable_username_search']) ? true : false);
        OW::getConfig()->saveConfig('memberx', 'order_latest_activity', !empty($data['latest_activity']) ? true : false);
        OW::getConfig()->saveConfig('memberx', 'order_recently_joined', !empty($data['recently_joined']) ? true : false);
        OW::getConfig()->saveConfig('memberx', 'order_match_compatibitity', !empty($data['match_compatibitity']) ? true : false);
        OW::getConfig()->saveConfig('memberx', 'order_distance', !empty($data['distance']) ? true : false);
        
        OW::getConfig()->saveConfig('memberx', 'hide_user_activity_after', (int)$data['hide_user_activity_after']);
        
        OW::getFeedback()->info(OW::getLanguage()->text('memberx', 'settings_saved'));
    }
}
