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
class UCAROUSEL_CMP_GroupUsersWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = (int) $params->additionalParamList['entityId'];

        $count = (int) $params->customParamList['count'];
        $size = $params->customParamList['size'];
        $language = OW::getLanguage();

        $list = $this->getList($groupId, $count);
        $users = new UCAROUSEL_CMP_Users($list, $size, $params->customParamList['infoLayout']);

        if ( !empty($users) )
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

    public function getList( $groupId, $count, $withPhoto = true )
    {
        $users = UCAROUSEL_CLASS_GroupsBridge::getInstance()->findUsers($groupId, $count, $withPhoto);
        $list = array();
        foreach ( $users as $user )
        {
            $list[] = $user->id;
        }
        
        return $list;
    }

    public static function getSettingList()
    {
        $language = OW::getLanguage();

        $settingList = array();

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

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('ucarousel', 'widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}