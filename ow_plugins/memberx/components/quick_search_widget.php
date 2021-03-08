<?php


class MEMBERX_CMP_QuickSearchWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->addComponent('cmp', OW::getClassInstance('MEMBERX_CMP_QuickSearch'));
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('memberx', 'quick_search'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => BASE_CLASS_Widget::ICON_LENS
        );
    }
}