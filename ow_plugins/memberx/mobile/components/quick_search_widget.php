<?php


class MEMBERX_MCMP_QuickSearchWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        $this->assign("url", OW::getRouter()->urlForRoute('memberx.quick_search'));        
        $this->assign("buttonLabel", !empty($paramObject->customParamList['buttonLabel']) ? $paramObject->customParamList['buttonLabel'] : OW::getLanguage()->text('memberx', 'quick_search'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCmpViewDir().'quick_search_widget.html');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('memberx', 'quick_search'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => BASE_CLASS_Widget::ICON_LENS            
        );
    }
    
    public static function getSettingList()
    {
        $lang = OW::getLanguage();
        $settingList = array();
        
        $settingList['buttonLabel'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('memberx', 'quick_search_button_label'),
            'value' => OW::getLanguage()->text('memberx', 'quick_search')
        );
        
        return $settingList;
    }
}