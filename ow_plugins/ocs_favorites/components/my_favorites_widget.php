<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Favorites widget
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.components
 * @since 1.5.3
 */
class OCSFAVORITES_CMP_MyFavoritesWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['count'];
        $userId = OW::getUser()->getId();

        $service = OCSFAVORITES_BOL_Service::getInstance();
        $lang = OW::getLanguage();
        $router = OW::getRouter();

        $multiple = OW::getConfig()->getValue('ocsfavorites', 'can_view') && OW::getUser()->isAuthorized('ocsfavorites', 'view_users');

        $toolbar = array();
        $lists = array();
        $resultList = array();

        $toolbar['my'] = array(
            'label' => $lang->text('base', 'view_all'),
            'href' => $router->urlForRoute('ocsfavorites.list')
        );

        $lists['my'] = $service->findFavoritesForUser($userId, 1, $count);

        if ( $multiple )
        {
            $toolbar['me'] = array(
               'label' => $lang->text('base', 'view_all'),
                'href' => $router->urlForRoute('ocsfavorites.added_list')
            );

            $lists['me'] = $service->findUsersWhoAddedUserAsFavorite($userId, 1, $count);

            $toolbar['mutual'] = array(
                'label' => $lang->text('base', 'view_all'),
                'href' => $router->urlForRoute('ocsfavorites.mutual_list')
            );

            $lists['mutual'] = $service->findMutualFavorites($userId, 1, $count);
        }

        $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['my']));

        $resultList['my'] = array(
            'menu-label' => $lang->text('ocsfavorites', 'my'),
            'menu_active' => true,
            'userIds' => $this->getIds($lists['my'], 'favoriteId'),
            'toolbar' => array($toolbar['my'])
        );

        if ( $multiple )
        {
            if ( $lists['me'] )
            {
                $resultList['me'] = array(
                    'menu-label' => $lang->text('ocsfavorites', 'who_added_me'),
                    'userIds' => $this->getIds($lists['me'], 'userId'),
                    'toolbar' => array($toolbar['me'])
                );
            }

            if ( $lists['mutual'] )
            {
                $resultList['mutual'] = array(
                    'menu-label' => $lang->text('ocsfavorites', 'mutual'),
                    'userIds' => $this->getIds($lists['mutual'], 'userId'),
                    'toolbar' => array($toolbar['mutual'])
                );
            }
        }

        return $resultList;
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('ocsfavorites', 'favorites_list_widget_settings_count'),
            'value' => '6'
        );

        return $settingList;
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => true,
        	self::SETTING_SHOW_TITLE => true,
        	self::SETTING_ICON => self::ICON_FRIENDS,
        	self::SETTING_TITLE => OW::getLanguage()->text('ocsfavorites', 'favorites')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    private function getIds( $favorites, $name )
    {
        $resultArray = array();

        if ( $favorites )
        {
            foreach ( $favorites as $f )
            {
                $resultArray[] = $f->$name;
            }
        }

        return $resultArray;
    }
}