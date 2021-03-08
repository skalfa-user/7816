<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Favorites action controller
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.mobile.controllers
 * @since 1.6.0
 */
class OCSFAVORITES_MCTRL_Favorites extends BASE_MCTRL_UserList
{
    public function __construct()
    {
        parent::__construct();

        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('ocsfavorites', 'favorites'));
        $this->setPageTitle($lang->text('ocsfavorites', 'favorites'));

        $this->setTemplate(
            OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'user_list_index.html'
        );
    }

    public function index( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticationException();
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $count = (int)OW::getConfig()->getValue('base', 'users_count_on_page');

        $data = $this->getData('user_favorites', array(), true, $count);
        $cmp = new BASE_MCMP_BaseUserList('user_favorites', $data, true);
        $this->addComponent('list', $cmp);

        OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                'component' => 'BASE_MCMP_BaseUserList',
                'listType' => 'user_favorites',
                'excludeList' => $data,
                'node' => '.owm_user_list',
                'showOnline' => true,
                'count' => $count,
                'responderUrl' => OW::getRouter()->urlForRoute('ocsfavorites.responder')
            )).");
        ", 50);

        if ( OW::getConfig()->getValue('ocsfavorites', 'can_view') )
        {
            $this->addComponent('menu', self::getMenu('my_favorites'));
        }
    }

    public function addedList()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticationException();
        }

        if ( !OW::getConfig()->getValue('ocsfavorites', 'can_view') )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('ocsfavorites', 'view_users') )
        {
            $this->setTemplate(
                OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'authorization_failed.html'
            );
            return;
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $count = (int)OW::getConfig()->getValue('base', 'users_count_on_page');

        $data = $this->getData('added_user', array(), true, $count);
        $cmp = new BASE_MCMP_BaseUserList('added_user', $data, true);
        $this->addComponent('list', $cmp);

        OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(".  json_encode(array(
            'component' => 'BASE_MCMP_BaseUserList',
            'listType' => 'added_user',
            'excludeList' => $data,
            'node' => '.owm_user_list',
            'showOnline' => true,
            'count' => $count,
            'responderUrl' => OW::getRouter()->urlForRoute('ocsfavorites.responder')
        )).");
        ", 50);

        $this->addComponent('menu', self::getMenu('added_me'));
    }

    public function mutualList()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticationException();
        }

        if ( !OW::getConfig()->getValue('ocsfavorites', 'can_view') )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('ocsfavorites', 'view_users') )
        {
            $this->setTemplate(
                OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'authorization_failed.html'
            );
            return;
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $count = (int)OW::getConfig()->getValue('base', 'users_count_on_page');

        $data = $this->getData('mutual', array(), true, $count);
        $cmp = new BASE_MCMP_BaseUserList('mutual', $data, true);
        $this->addComponent('list', $cmp);

        OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(".  json_encode(array(
            'component' => 'BASE_MCMP_BaseUserList',
            'listType' => 'mutual',
            'excludeList' => $data,
            'node' => '.owm_user_list',
            'showOnline' => true,
            'count' => $count,
            'responderUrl' => OW::getRouter()->urlForRoute('ocsfavorites.responder')
        )).");
        ", 50);

        $this->addComponent('menu', self::getMenu('mutual'));
    }

    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $listKey = empty($_POST['list']) ? 'user_favorites' : strtolower(trim($_POST['list']));
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? (int) OW::getConfig()->getValue('base', 'users_count_on_page') : (int)$_POST['count'];

        $data = $this->getData( $listKey, $excludeList, $showOnline, $count );

        echo json_encode($data);

        exit;
    }

    public function getData( $listType, $excludeList = array(), $showOnline = true, $count )
    {
        $list = array();

        $start = count($excludeList);

        $service = OCSFAVORITES_BOL_Service::getInstance();

        while ( $count > count($list) )
        {
            $tmpList =  $service->getDataForUsersList($listType, OW::getUser()->getId(), $start, $count);
            $itemList = $tmpList[0];
            $itemCount = $tmpList[1];

            if ( empty($itemList)  )
            {
                break;
            }

            foreach ( $itemList as $key => $item )
            {
                if ( count($list) == $count )
                {
                    break;
                }

                if ( !in_array($item->id, $excludeList) )
                {
                    $list[] = $item->id;
                }
            }

            $start += $count;

            if ( $start >= $itemCount )
            {
                break;
            }
        }

        return $list;
    }

    public static function getMenu( $activeList )
    {
        $menuItems = array();
        $lang = OW::getLanguage();
        $router = OW::getRouter();

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfavorites', 'my_favorites'));
        $item->setUrl($router->urlForRoute('ocsfavorites.list'));
        $item->setKey('my_favorites');
        $item->setIconClass('ow_ic_heart');
        $item->setOrder(1);
        $item->setActive($activeList == 'my_favorites');
        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfavorites', 'added_me'));
        $item->setUrl($router->urlForRoute('ocsfavorites.added_list'));
        $item->setKey('added_me');
        $item->setIconClass('ow_ic_heart');
        $item->setOrder(2);
        $item->setActive($activeList == 'added_me');
        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfavorites', 'mutual_attraction'));
        $item->setUrl($router->urlForRoute('ocsfavorites.mutual_list'));
        $item->setKey('mutual');
        $item->setIconClass('ow_ic_heart');
        $item->setOrder(3);
        $item->setActive($activeList == 'mutual');
        array_push($menuItems, $item);

        $menu = new BASE_MCMP_ContentMenu($menuItems);

        return $menu;
    }
}