<?php



class MEMBERX_CMP_SearchResultSetting extends MEMBERX_CLASS_AbsEditionForm{
    
    
    const CONFIG_KEY = 'other_settings';
    const SHOW_SEARCHER_PROFILE = 'show_searcher_profile';
    const AVATAR_BACKGROUND_COLOR = 'search_result_avatar_background_color';
    const AVATAR_HIGHLIGHT_COLOR = 'search_result_avatar_highlight_color';
    const AVATAR_BACKGROUND_COLOR_MOBILE = 'search_result_avatar_background_color_mobile';
    const AVATAR_HIGHLIGHT_COLOR_MOBILE = 'search_result_avatar_highlight_color_mobile';
    const AVATAR_SHOW_BUTTON = 'show_button_on_search_result';
    const BUTTON_COLOR = 'button_color';
    const BUTTON_SIZE = 'button_size';
    const SHOW_JOIN_DATE = 'show_join_date_on_search_result';
    const SHOW_LAST_ACTIVITY = 'show_last_activity_on_search_result';
    const SHOW_GENDER_AND_AGE = 'show_gender_and_age';
    const AVATAR_SIZE = 'search_result_avatar_size';
    const SHOW_NEW_LABEL = 'show_new_label';
    const NEW_LABEL_COLOR = 'new_label_color';
    const ACCOUNT_TYPE_RESTRICT = 'restrict_search_to_different_account_type';
    const SEARCH_RESULT_LAYOUT = 'search_result_layout';
    const SEARCH_RESULT_LAYOUT_UD = 'updown';
    const SEARCH_RESULT_LAYOUT_LR = 'leftright';
    const SHOW_CONTEXT_MENU_ON_AVATAR = 'show_context_menu_on_avatar';
    const ENABLE_FEATURED_USER_LIST = 'enable_featured_users';
    const PAGING_MODE = 'paging_mode';
    const SHOW_PAGE_TITLE = 'show_page_title';
    //const USE_GENDER_OPTION = 'use_gender_option';
    //const USE_GENDER_OPTION_ON_QUICK_SEARCH = 'use_gender_option_on_quick_search';
    const NUMBER_OF_AVATARS_ON_INDEX_WIDGET = 'number_of_avatar_on_index_widget';
    const AVATAR_SIZE_ON_INDEX_WIDGET = 'avatar_size_on_index_widget';
    const SHOW_BUTTONS_ON_WIDGET = 'show_buttons_on_widget';
    //const LENGTH_OF_DISPLAY_NAME = 'lenght_of_display_name';
    const ENABLE_SEARCH_CACHE = 'search_result_cache_life_time';
    const SHOW_AGE_AND_LOCATION = 'show_age_and_location_on_result_list';
    //const MAX_LENGTH_FOR_PROFILE_INFO = 'max_length_for_profile_info_display';
    
    const FORM_NAME = 'memberx-other-settings';
    
    public static function saveDefaultValue(){
        
        $value = array(
            self::AVATAR_BACKGROUND_COLOR => '#F2F2F2',
            self::AVATAR_HIGHLIGHT_COLOR => 'yellow',
            self::AVATAR_BACKGROUND_COLOR_MOBILE => 'white',
            self::AVATAR_HIGHLIGHT_COLOR_MOBILE => 'yellow',
            self::AVATAR_SHOW_BUTTON => 'yes',
            self::BUTTON_COLOR => '#48E588',
            self::BUTTON_SIZE => '42',
            self::SHOW_JOIN_DATE => 'yes',
            self::SHOW_LAST_ACTIVITY => 'yes',
            self::SHOW_GENDER_AND_AGE => 'yes',
            self::AVATAR_SIZE => '224',
            self::SHOW_NEW_LABEL => '0',
            self::NEW_LABEL_COLOR => '#48E588',
            self::ACCOUNT_TYPE_RESTRICT => 'no',
            self::SEARCH_RESULT_LAYOUT => self::SEARCH_RESULT_LAYOUT_UD,
                
            
        );
        
        if (!OW::getConfig()->configExists('memberx', self::CONFIG_KEY)){
            OW::getConfig()->addConfig('memberx', self::CONFIG_KEY, json_encode($value));
        }
        
        return $value;
        
    }

    public function __construct() {
        parent::__construct();
    }
    
    public function getTitle() {
        return $this->langs->text(self::PLUGIN_KEY, 'other_settings');
    }
    
    public function getFromName() {
        return self::FORM_NAME;
    }
    
    public function createForm() {
        $form = new Form($this->getFromName());
        
        $accountTypeRestrict = new Selectbox(self::ACCOUNT_TYPE_RESTRICT);
        $accountTypeRestrict->setRequired();
        $accountTypeRestrict->setLabel($this->langs->text(self::PLUGIN_KEY, self::ACCOUNT_TYPE_RESTRICT));
        
        $showSearchProfile = new Selectbox(self::SHOW_SEARCHER_PROFILE);
        $showSearchProfile->setRequired();
        $showSearchProfile->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_SEARCHER_PROFILE));
        
        $avatarBackgroundColor = new TextField(self::AVATAR_BACKGROUND_COLOR);
        $avatarBackgroundColor->setRequired();
        $avatarBackgroundColor->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_BACKGROUND_COLOR));
        
        $avatarBackgroundColorMobile = new TextField(self::AVATAR_BACKGROUND_COLOR_MOBILE);
        $avatarBackgroundColorMobile->setRequired();
        $avatarBackgroundColorMobile->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_BACKGROUND_COLOR_MOBILE));
        
        $avatarHighlightColor = new TextField(self::AVATAR_HIGHLIGHT_COLOR);
        $avatarHighlightColor->setRequired();
        $avatarHighlightColor->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_HIGHLIGHT_COLOR));
        
        $avatarHighlightColorMobile = new TextField(self::AVATAR_HIGHLIGHT_COLOR_MOBILE);
        $avatarHighlightColorMobile->setRequired();
        $avatarHighlightColorMobile->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_HIGHLIGHT_COLOR_MOBILE));
        
        $avatarSize = new TextField(self::AVATAR_SIZE);
        $avatarSize->setRequired();
        $avatarSize->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_SIZE));
        
        $showButton = new Selectbox(self::AVATAR_SHOW_BUTTON);
        $showButton->setRequired();
        $showButton->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_SHOW_BUTTON));
        
        $buttonColor = new TextField(self::BUTTON_COLOR);
        $buttonColor->setRequired();
        $buttonColor->setLabel($this->langs->text(self::PLUGIN_KEY, self::BUTTON_COLOR));
        
        $buttonSize = new TextField(self::BUTTON_SIZE);
        $buttonSize->setRequired();
        $buttonSize->setLabel($this->langs->text(self::PLUGIN_KEY, self::BUTTON_SIZE));
        
        $showGenderAge = new Selectbox(self::SHOW_GENDER_AND_AGE);
        $showGenderAge->setRequired();
        $showGenderAge->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_GENDER_AND_AGE));
        
        $showJoinDate = new Selectbox(self::SHOW_JOIN_DATE);
        $showJoinDate->setRequired();
        $showJoinDate->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_JOIN_DATE));
        
        $showLastActivity = new Selectbox(self::SHOW_LAST_ACTIVITY);
        $showLastActivity->setRequired();
        $showLastActivity->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_LAST_ACTIVITY));
        
        $showNewLabel = new TextField(self::SHOW_NEW_LABEL);
        $showNewLabel->setRequired();
        $showNewLabel->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_NEW_LABEL));
        
        $newLabelColor = new TextField(self::NEW_LABEL_COLOR);
        $newLabelColor->setRequired();
        $newLabelColor->setLabel($this->langs->text(self::PLUGIN_KEY, self::NEW_LABEL_COLOR));
        
        $layout = new Selectbox(self::SEARCH_RESULT_LAYOUT);
        $layout->setRequired();
        $layout->setLabel($this->langs->text(self::PLUGIN_KEY, self::SEARCH_RESULT_LAYOUT));
        
        $contextMenu = new Selectbox(self::SHOW_CONTEXT_MENU_ON_AVATAR);
        $contextMenu->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_CONTEXT_MENU_ON_AVATAR));
        
        $enableFeaturedList = new Selectbox(self::ENABLE_FEATURED_USER_LIST);
        $enableFeaturedList->setLabel($this->langs->text(self::PLUGIN_KEY, self::ENABLE_FEATURED_USER_LIST));
        
        $pagingMode = new Selectbox(self::PAGING_MODE);
        $pagingMode->setLabel($this->langs->text(self::PLUGIN_KEY, self::PAGING_MODE));
        
        $showPageTitle = new Selectbox(self::SHOW_PAGE_TITLE);
        $showPageTitle->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_PAGE_TITLE));
        
        //$useGenderOption = new Selectbox(self::USE_GENDER_OPTION);
        //$useGenderOption->setLabel($this->langs->text(self::PLUGIN_KEY, self::USE_GENDER_OPTION));
        
        //$useGenderOptionOnQuickSearch = new Selectbox(self::USE_GENDER_OPTION_ON_QUICK_SEARCH);
        //$useGenderOptionOnQuickSearch->setLabel($this->langs->text(self::PLUGIN_KEY, self::USE_GENDER_OPTION_ON_QUICK_SEARCH));
        
        $numberOfAvatarsOnIndexWidget = new TextField(self::NUMBER_OF_AVATARS_ON_INDEX_WIDGET);
        $numberOfAvatarsOnIndexWidget->setLabel($this->langs->text(self::PLUGIN_KEY, self::NUMBER_OF_AVATARS_ON_INDEX_WIDGET));
        
        $avatarSizeOnWidget = new TextField(self::AVATAR_SIZE_ON_INDEX_WIDGET);
        $avatarSizeOnWidget->setLabel($this->langs->text(self::PLUGIN_KEY, self::AVATAR_SIZE_ON_INDEX_WIDGET));
        
        $showButtonsOnWidget = new Selectbox(self::SHOW_BUTTONS_ON_WIDGET);
        $showButtonsOnWidget->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_BUTTONS_ON_WIDGET));
        
        //$lengthOfDisplayName = new TextField(self::LENGTH_OF_DISPLAY_NAME);
        //$lengthOfDisplayName->setLabel($this->langs->text(self::PLUGIN_KEY, self::LENGTH_OF_DISPLAY_NAME));
        
        $enableSearchCache = new TextField(self::ENABLE_SEARCH_CACHE);
        $enableSearchCache->setLabel($this->langs->text(self::PLUGIN_KEY, self::ENABLE_SEARCH_CACHE));
        
        
        $showAgeAndLocation = new Selectbox(self::SHOW_AGE_AND_LOCATION);
        $showAgeAndLocation->setLabel($this->langs->text(self::PLUGIN_KEY, self::SHOW_AGE_AND_LOCATION));
        
        //$maxProfileInfoLength = new TextField(self::MAX_LENGTH_FOR_PROFILE_INFO);
        //$maxProfileInfoLength->setLabel($this->langs->text(self::PLUGIN_KEY, self::MAX_LENGTH_FOR_PROFILE_INFO));
        
        $layoutOptions = array(
            self::SEARCH_RESULT_LAYOUT_UD => $this->langs->text(self::PLUGIN_KEY, self::SEARCH_RESULT_LAYOUT_UD),
            self::SEARCH_RESULT_LAYOUT_LR => $this->langs->text(self::PLUGIN_KEY, self::SEARCH_RESULT_LAYOUT_LR),
        );
        
        $yesNo = array(
            'no' => $this->langs->text(self::PLUGIN_KEY, 'no'),
            'yes' => $this->langs->text(self::PLUGIN_KEY, 'yes')
        );
        
        $pagingOptaions = array(
            'pages' => $this->langs->text(self::PLUGIN_KEY, 'pages'),
            'scroll' => $this->langs->text(self::PLUGIN_KEY, 'scroll')
        );
        
        $layout->setOptions($layoutOptions);
        $accountTypeRestrict->setOptions($yesNo);
        $showSearchProfile->setOptions($yesNo);
        $showButton->setOptions($yesNo);
        $showGenderAge->setOptions($yesNo);
        $showJoinDate->setOptions($yesNo);
        $showLastActivity->setOptions($yesNo);
        $contextMenu->setOptions($yesNo);
        $enableFeaturedList->setOptions($yesNo);
        $pagingMode->setOptions($pagingOptaions);
        $showPageTitle->setOptions($yesNo);
        //$useGenderOption->setOptions($yesNo);
        //$useGenderOptionOnQuickSearch->setOptions($yesNo);
        $showButtonsOnWidget->setOptions($yesNo);
        $showAgeAndLocation->setOptions($yesNo);
        //$enableSearchCache->setOptions($yesNo);
        
        
        $form->addElement($accountTypeRestrict);
        $form->addElement($showSearchProfile);
        $form->addElement($avatarSize);
        $form->addElement($avatarBackgroundColor);
        $form->addElement($avatarBackgroundColorMobile);
        $form->addElement($avatarHighlightColor);
        $form->addElement($avatarHighlightColorMobile);
        $form->addElement($showGenderAge);
        $form->addElement($showJoinDate);
        $form->addElement($showLastActivity);
        $form->addElement($showButton);
        $form->addElement($buttonColor);
        $form->addElement($buttonSize);
        $form->addElement($showNewLabel);
        $form->addElement($newLabelColor);
        $form->addElement($layout);
        $form->addElement($contextMenu);
        $form->addElement($enableFeaturedList);
        $form->addElement($pagingMode);
        //$form->addElement($showPageTitle);
        //$form->addElement($useGenderOption);
        //$form->addElement($useGenderOptionOnQuickSearch);
        $form->addElement($numberOfAvatarsOnIndexWidget);
        $form->addElement($avatarSizeOnWidget);
        $form->addElement($showButtonsOnWidget);
        //$form->addElement($lengthOfDisplayName);
        $form->addElement($enableSearchCache);
        $form->addElement($showAgeAndLocation);
        //$form->addElement($maxProfileInfoLength);
        
        
        $savedConfig = self::getSavedConfig();
        foreach ($form->getElements() as $element){
            if (isset($savedConfig[$element->getName()])){
                $element->setValue($savedConfig[$element->getName()]);
            }
        }
        
        
        
        return $form;
    }
    
    public function getConfigKey() {
        if (!OW::getConfig()->configExists('memberx', self::CONFIG_KEY)){
            self::saveDefaultValue();
        }
        return self::CONFIG_KEY;
    }
    
    public static function getSavedConfig() {
        
        $savedConfig = parent::getConfig(self::CONFIG_KEY);
        if (empty($savedConfig)){
            return self::saveDefaultValue();
        }else{
            return $savedConfig;
        }
    }
    
    public static function getBoolean($configKey){
        $config = self::getSavedConfig();
        
        if (isset($config[$configKey]) && $config[$configKey] === 'yes'){
            return true;
        }else{
            return false;
        }
    }
    
    public static function getInt($configKey){
        $config = self::getSavedConfig();
        
        if (isset($config[$configKey]) && $config[$configKey]){
            return (int)$config[$configKey];
        }else{
            return 0;
        }
    }
    
    public static function getString($configKey){
        $config = self::getSavedConfig();
        
        if (isset($config[$configKey]) && $config[$configKey]){
            return $config[$configKey];
        }else{
            return '';
        }
    }
    
    
}