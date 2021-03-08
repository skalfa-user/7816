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
 * @package ow.ow_plugins.ocs_favorites.controllers
 * @since 1.5.3
 */
class OCSFAVORITES_CTRL_Favorites extends OW_ActionController
{
    public function mylist()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $lang = OW::getLanguage();
        $service = OCSFAVORITES_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = (int) OW::getConfig()->getValue('base', 'users_count_on_page');

        $favorites = $service->findFavoritesForUser($userId, $page, $limit);

        $userIdList = array();
        $fList = array();
        if ( $favorites )
        {
            foreach ( $favorites as $f )
            {
                if ( !in_array($f->favoriteId, $userIdList) )
                {
                    $userIdList[] = $f->favoriteId;
                }
                $fList[$f->favoriteId] = $f;
            }
            $count = $service->countFavoritesForUser($userId);
            $data = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
            $cmp = new OCSFAVORITES_CMP_Users($data, $count, $limit, true, $fList);
            $this->addComponent('favorites', $cmp);
        }
        else
        {
            $this->assign('favorites', null);
        }

        if ( OW::getConfig()->getValue('ocsfavorites', 'can_view') )
        {
            $this->addComponent('menu', $this->getMenu());
        }

        OW::getDocument()->setHeading($lang->text('ocsfavorites', 'favorites'));
        OW::getDocument()->setTitle($lang->text('ocsfavorites', 'favorites'));
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
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfavorites', 'view_users');
            throw new AuthorizationException($status['msg']);
        }

        $lang = OW::getLanguage();
        $service = OCSFAVORITES_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = (int) OW::getConfig()->getValue('base', 'users_count_on_page');

        $favorites = $service->findUsersWhoAddedUserAsFavorite($userId, $page, $limit);

        $userIdList = array();
        $fList = array();
        if ( $favorites )
        {
            foreach ( $favorites as $f )
            {
                if ( !in_array($f->userId, $userIdList) )
                {
                    $userIdList[] = $f->userId;
                }
                $fList[$f->userId] = $f;
            }
            $count = $service->countUsersWhoAddedUserAsFavorite($userId);
            $data = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
            $cmp = new OCSFAVORITES_CMP_Users($data, $count, $limit, true, $fList);
            $this->addComponent('favorites', $cmp);
        }
        else
        {
            $this->assign('favorites', null);
        }

        $this->addComponent('menu', $this->getMenu());

        OW::getDocument()->setHeading($lang->text('ocsfavorites', 'favorites'));
        OW::getDocument()->setTitle($lang->text('ocsfavorites', 'favorites'));

        $this->setTemplate(OW::getPluginManager()->getPlugin('ocsfavorites')->getCtrlViewDir() . 'favorites_mylist.html');
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
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfavorites', 'view_users');
            throw new AuthorizationException($status['msg']);
        }

        $lang = OW::getLanguage();
        $service = OCSFAVORITES_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = (int) OW::getConfig()->getValue('base', 'users_count_on_page');

        $favorites = $service->findMutualFavorites($userId, $page, $limit);

        $userIdList = array();
        $fList = array();
        if ( $favorites )
        {
            foreach ( $favorites as $f )
            {
                if ( !in_array($f->userId, $userIdList) )
                {
                    $userIdList[] = $f->userId;
                }
                $fList[$f->userId] = $f;
            }
            $count = $service->countMutualFavorites($userId);
            $data = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
            $cmp = new OCSFAVORITES_CMP_Users($data, $count, $limit, true, $fList);
            $this->addComponent('favorites', $cmp);
        }
        else
        {
            $this->assign('favorites', null);
        }

        $this->addComponent('menu', $this->getMenu());

        OW::getDocument()->setHeading($lang->text('ocsfavorites', 'favorites'));
        OW::getDocument()->setTitle($lang->text('ocsfavorites', 'favorites'));

        $this->setTemplate(OW::getPluginManager()->getPlugin('ocsfavorites')->getCtrlViewDir() . 'favorites_mylist.html');
    }

    public function remove()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array()));
        }

        if ( empty($_POST['favoriteId']) )
        {
            exit(json_encode(array()));
        }

        $lang = OW::getLanguage();
        $service = OCSFAVORITES_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();
        $favoriteId = (int) $_POST['favoriteId'];

        $user = BOL_UserService::getInstance()->findUserById($favoriteId);

        if ( !$user )
        {
            exit(json_encode(array()));
        }

        $favorite = $service->isFavorite($userId, $favoriteId);
        if ( !$favorite )
        {
            exit(json_encode(array()));
        }

        $service->deleteFavorite($userId, $favoriteId);

        exit(json_encode(array('result' => true, 'msg' => $lang->text('ocsfavorites', 'favorite_removed'))));
    }

    private function getMenu()
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
        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfavorites', 'added_me'));
        $item->setUrl($router->urlForRoute('ocsfavorites.added_list'));
        $item->setKey('added_me');
        $item->setIconClass('ow_ic_heart');
        $item->setOrder(2);
        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($lang->text('ocsfavorites', 'mutual_attraction'));
        $item->setUrl($router->urlForRoute('ocsfavorites.mutual_list'));
        $item->setKey('mutual');
        $item->setIconClass('ow_ic_heart');
        $item->setOrder(3);
        array_push($menuItems, $item);

        $menu = new BASE_CMP_ContentMenu($menuItems);

        return $menu;
    }
}