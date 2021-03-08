<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ucarousel.components
 * @since 1.0
 */
class UCAROUSEL_CMP_UsersWidget extends BASE_CLASS_Widget
{
    const UNIQ_NAME = 'index-UCAROUSEL_CMP_UsersWidget';

    /**
     *
     * @var BASE_CLASS_WidgetParameter
     */
    protected $widgetParams;

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->widgetParams = $params;
        
        $count = (int) $params->customParamList['count'];
        $size = $params->customParamList['size'];
        $language = OW::getLanguage();

        $opts = array();
        
        if ( $params->customParamList['list'] == "by_role" )
        {
            $opts = $params->customParamList['roles'];
        }
        
        if ( $params->customParamList['list'] == "by_account_type" )
        {
            $opts = $params->customParamList['account_types'];
        }
        
        $list = $this->getList($params->customParamList['list'], $count, true, $opts);
        $users = new UCAROUSEL_CMP_Users($list, $size, $params->customParamList['infoLayout']);

        if ( !empty($list) )
        {
            $users->initCarousel(array
            (
                'autoplay' => $params->customParamList['autoplay'],
                'speed' => $params->customParamList['speed'],
                'dragging' => $params->customParamList['dragging'],
                'shape' => $params->customParamList['shape']
            ));
        }

        $this->addComponent('users', $users);
    }

    public function getList( $type, $count, $withPhoto = true, $opts = array() )
    {
        $userId = OW::getUser()->getId();

        $service = UCAROUSEL_BOL_Service::getInstance();
        $list = array();
        $idList = array();
        
        $eventParams = array(
            "listType" => $type,
            "count" => $count,
            "withPhoto" => $withPhoto,
            "opts" => $opts,
            "settings" => $this->widgetParams->customParamList,
            "widgetUniqName" => $this->widgetParams->widgetDetails->uniqName
        );
        
        OW::getEventManager()->trigger(new OW_Event("ucarousel.before_list_fetch", $eventParams));
        
        switch ( $type )
        {
            case 'latest':
                $list = $service->findLatestList($count, $withPhoto);
                break;

            case 'recently':
                $list = $service->findRecentlyActiveList($count, $withPhoto);
                break;

            case 'online':
                $list = $service->findOnlineList($count, $withPhoto);
                break;

            case 'featured':
                $list = $service->findFeaturedList($count, $withPhoto);
                break;
                
            case 'by_role':
                $list = $service->findByRoles($count, $opts, $withPhoto);
                break;
                
            case 'by_account_type':
                $list = $service->findByAccountTypes($count, $opts, $withPhoto);
                break;
                
            case 'hotlist':
                $idList = UCAROUSEL_CLASS_HotlistBridge::getInstance()->findUserIds($count);
                break;

            case 'friends':
                $idList = UCAROUSEL_CLASS_FriendsBridge::getInstance()->findUserIds($userId, $count);
                break;

            case 'friends_online':
                $idList = UCAROUSEL_CLASS_FriendsBridge::getInstance()->findUserIds($userId, $count, true);
                break;
        }
        
        $out = array();
        foreach ( $list as $user )
        {
            $out[] = $user->id;
        }
        
        return OW::getEventManager()->trigger(
                new OW_Event("ucarousel.after_list_fetch", $eventParams, array_merge($idList, $out))
        )->getData();
    }
    
    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        if ( $settingList["list"] == "by_role" && empty($settingList["roles"]) )
        {
            throw new WidgetSettingValidateException(OW::getLanguage()->text("ucarousel", "widget_settings_list_roles_error"), 'roles');
        }
        
        if ( $settingList["list"] == "by_account_type" && empty($settingList["account_types"]) )
        {
            throw new WidgetSettingValidateException(OW::getLanguage()->text("ucarousel", "widget_settings_list_roles_account_types"), 'account_types');
        }
    }

    public static function renderListTypeSelect($uniqName, $fieldName, $value)
    {
        $language = OW::getLanguage();
        $field = new UCAROUSEL_CLASS_ListField($fieldName);
        $uniqId = uniqid("select-list-");
        $field->setId($uniqId);
        
        $lists = array(
            'latest' => $language->text('ucarousel', 'widget_list_type_latest'),
            'recently' => $language->text('ucarousel', 'widget_list_type_recently'),
            'online' => $language->text('ucarousel', 'widget_list_type_online'),
            'featured' => $language->text('ucarousel', 'widget_list_type_featured'),
            'by_role' => $language->text('ucarousel', 'widget_list_type_by_role'),
            'by_account_type' => $language->text('ucarousel', 'widget_list_type_by_account_type')
        );
        
        if ( UCAROUSEL_CLASS_HotlistBridge::getInstance()->isActive() )
        {
            $lists['hotlist'] = $language->text('ucarousel', 'widget_list_type_hotlist');
        }

        if ( UCAROUSEL_CLASS_FriendsBridge::getInstance()->isActive() )
        {
            $lists['friends'] = array(
                "label" => $language->text('ucarousel', 'widget_list_type_friends'),
                "group" => $language->text('ucarousel', 'widget_list_fir_user_delimiter')
            );
        }

        if ( UCAROUSEL_CLASS_FriendsBridge::getInstance()->isActive() )
        {
            $lists['friends_online'] = array(
                "label" => $language->text('ucarousel', 'widget_list_type_friends_online'),
                "group" => $language->text('ucarousel', 'widget_list_fir_user_delimiter')
            );
        }
        
        $event = new BASE_CLASS_EventCollector("ucarousel.collect_list_types", array(
            "widgetUniqName" => $uniqName
        ));
        OW::getEventManager()->trigger($event);
        
        foreach ( $event->getData() as $item )
        {
            $lists[$item["name"]] = $item["label"];
        }
        
        $field->setOptions($lists);
        
        $field->setValue($value);
        
        if ($value != "by_role")
        {
            OW::getDocument()->addOnloadScript('$("#uc-role-setting").parents("tr:eq(0)").hide();');
        }
        
        if ($value != "by_account_type")
        {
            OW::getDocument()->addOnloadScript('$("#uc-account-type-setting").parents("tr:eq(0)").hide();');
        }
        
        OW::getDocument()->addOnloadScript('$("#' . $uniqId . '").change(function() { '
                . '$("#uc-role-setting").parents("tr:eq(0)").hide(); '
                . '$("#uc-account-type-setting").parents("tr:eq(0)").hide(); '
                . 'if ($(this).val() == "by_role") $("#uc-role-setting").parents("tr:eq(0)").show(); '
                . 'if ($(this).val() == "by_account_type") $("#uc-account-type-setting").parents("tr:eq(0)").show(); '
                . ' })');
        
        return $field->renderInput();
    }
    
    public static function renderRoleList($uniqName, $fieldName, $value)
    {
        $language = OW::getLanguage();
        $roleList = BOL_AuthorizationService::getInstance()->findNonGuestRoleList();
        $roleOptions = array();
        foreach ( $roleList as $role )
        {
            $roleOptions[$role->id] = $language->text("base", "authorization_role_" . $role->name);
        }
        
        $uniqId = "uc-role-setting";
       
        return '<div id="' . $uniqId . '">' . self::renderMultyCheck($fieldName, $roleOptions, empty($value) ? array() : $value) . '</div>';
    }
    
    public static function renderAccountTypeList($uniqName, $fieldName, $value)
    {
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypesWithLabels();
        $uniqId = "uc-account-type-setting";
     
        return '<div id="' . $uniqId . '">' . self::renderMultyCheck($fieldName, $accountTypes, empty($value) ? array() : $value) . '</div>';
    }
    
    private static function renderMultyCheck( $name, $options, $checked )
    {
        $out = array();
        
        foreach ($options as $value => $label)
        {
            $checkedAttr = in_array($value, $checked) ? 'checked="checked"' : "";
            $out[] = '<input type="checkbox" ' . $checkedAttr . ' class="ow_vertical_middle" value="' . $value . '" name="' .$name. '[]" />' . $label;
        }
        
        return implode("", $out);
    }
    
    public static function getSettingList( $uniqName = null )
    {
        $language = OW::getLanguage();

        $settingList = array();

        $settingList['list'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => array(__CLASS__, "renderListTypeSelect"),
            'label' => $language->text('ucarousel', 'widget_list_type'),
            "value" => "latest"
        );
        
        $settingList['roles'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => array(__CLASS__, "renderRoleList"),
            'label' => $language->text('ucarousel', 'widget_list_setting_role')
        );
        
        $settingList['account_types'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => array(__CLASS__, "renderAccountTypeList"),
            'label' => $language->text('ucarousel', 'widget_list_setting_account_type')
        );

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('ucarousel', 'widget_user_count'),
	    'optionList' => array(
                5 => 5,
                10 => 10,
                15 => 15,
                20 => 20,
                25 => 25
            ),
            'value' => 15
        );

        $settingList['size'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('ucarousel', 'widget_image_size'),
	    'optionList' => array(
                'small' => $language->text('ucarousel', 'widget_image_size_small'),
                'medium' => $language->text('ucarousel', 'widget_image_size_medium'),
                'big' => $language->text('ucarousel', 'widget_image_size_big')
            ),
            'value' => 'big'
        );

        $settingList['shape'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('ucarousel', 'widget_shape'),
            'optionList' => array(
                'lazySusan' => $language->text('ucarousel', 'widget_shape_lazy_susan'),
                'waterWheel' => $language->text('ucarousel', 'widget_shape_water_wheel'),
                'figure8' => $language->text('ucarousel', 'widget_shape_figure8'),
                'square' => $language->text('ucarousel', 'widget_shape_square'),
                'conveyorBeltLeft' => $language->text('ucarousel', 'widget_shape_conveyor_belt_left'),
                'conveyorBeltRight' => $language->text('ucarousel', 'widget_shape_conveyor_belt_right'),
                'diagonalRingLeft' => $language->text('ucarousel', 'widget_shape_diagonal_ring_left'),
                'diagonalRingRight' => $language->text('ucarousel', 'widget_shape_diagonal_ring_right'),
                'rollerCoaster' => $language->text('ucarousel', 'widget_shape_roller_coaster'),
                'tearDrop' => $language->text('ucarousel', 'widget_shape_tear_drop')
            ),
            'value' => 'lazySusan'
        );

        $settingList['infoLayout'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('ucarousel', 'widget_info_layout'),
            'optionList' => array(
                '1' => $language->text('ucarousel', 'widget_info_layout_1'),//'Name + Gender + Age',
                '2' => $language->text('ucarousel', 'widget_info_layout_2'), //'Name + Gender',
                '3' => $language->text('ucarousel', 'widget_info_layout_3'), //'Name + Age',
                '4' => $language->text('ucarousel', 'widget_info_layout_4') //'Name Only'
            ),
            'value' => '1'
        );

        $settingList['autoplay'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => $language->text('ucarousel', 'widget_autoplay'),
            'value' => true
        );

        $settingList['speed'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('ucarousel', 'widget_speed'),
            'optionList' => array(
                '1000' => $language->text('ucarousel', 'widget_speed_fast'),
                '3000' => $language->text('ucarousel', 'widget_speed_avg'),
                '5000' => $language->text('ucarousel', 'widget_speed_slow')
            ),
            'value' => '3000'
        );


        $settingList['dragging'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => $language->text('ucarousel', 'widget_dragging'),
            'value' => true
        );
        
        self::getPlaceData();
        
        $event = new BASE_CLASS_EventCollector("ucarousel.collect_settings", array(
            "widgetUniqName" => $uniqName
        ));
        OW::getEventManager()->trigger($event);
        
        foreach ( $event->getData() as $item )
        {
            $settingList[$item["name"]] = $item;
        }

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        $args = func_get_args();
        $uniqName = empty($args[0]) ? self::UNIQ_NAME : $args[0];
        $settings = BOL_ComponentAdminService::getInstance()->findSettingList($uniqName);
        $attrs = OW::getRequestHandler()->getHandlerAttributes();

        if ( in_array($attrs[OW_RequestHandler::ATTRS_KEY_CTRL], array("BASE_CTRL_ComponentPanel", "BASE_CTRL_AjaxComponentEntityPanel"))
            && in_array($attrs[OW_RequestHandler::ATTRS_KEY_ACTION], array("dashboard", "processQueue")) )
        {
            $userSettings = BOL_ComponentEntityService::getInstance()->findSettingList($uniqName, OW::getUser()->getId());

            $settings = array_merge($settings, $userSettings);
        }

        $list = empty($settings['list']) ? 'latest' : $settings['list'];
        $title = OW::getLanguage()->text('ucarousel', 'widget_list_type_' . $list);

        return array(
            self::SETTING_TITLE => $title, //OW::getLanguage()->text('ucarousel', 'widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}