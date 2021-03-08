<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Favorites service class
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.bol
 * @since 1.5.3
 */
final class OCSFAVORITES_BOL_Service
{
    /**
     * @var OCSFAVORITES_BOL_FavoriteDao
     */
    private $favoriteDao;
    /**
     * Class instance
     *
     * @var OCSFAVORITES_BOL_Service
     */
    private static $classInstance;
    
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->favoriteDao = OCSFAVORITES_BOL_FavoriteDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return OCSFAVORITES_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findFavoritesForUser( $userId, $page, $limit )
    {
    	if ( !$userId )
        {
            return null;
        }
    	
    	$favorites = $this->favoriteDao->findUserFavorites($userId, $page, $limit);
    	
    	foreach ( $favorites as &$f )
    	{
    		$f->addTimestamp = UTIL_DateTime::formatDate($f->addTimestamp, false);
    	}
    	
    	return $favorites;
    }

    /**
     * @param $userId
     * @return int
     */
    public function countFavoritesForUser( $userId )
    {
        return $this->favoriteDao->countUserFavorites($userId);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findMutualFavorites( $userId, $page, $limit )
    {
        return $this->favoriteDao->findMutualFavorites($userId, $page, $limit);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countMutualFavorites( $userId )
    {
        return $this->favoriteDao->countMutualFavorites($userId);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findUsersWhoAddedUserAsFavorite( $userId, $page, $limit )
    {
        return $this->favoriteDao->findUsersWhoAddedAsFavorite($userId, $page, $limit);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countUsersWhoAddedUserAsFavorite( $userId )
    {
        return $this->favoriteDao->countUsersWhoAddedAsFavorite($userId);
    }

    /**
     * @param $userId
     * @return bool
     */
    public function deleteUserFavorites( $userId )
    {
    	$this->favoriteDao->deleteUserFavorites($userId);
    	
    	return true;
    }

    /**
     * @param $userId
     * @param $favoriteId
     * @return bool
     */
    public function isFavorite( $userId, $favoriteId )
    {
        return (bool) $this->favoriteDao->findFavorite($userId, $favoriteId);
    }

    /**
     * @param $id
     * @return OCSFAVORITES_BOL_Favorite
     */
    public function findFavoriteById( $id )
    {
        return $this->favoriteDao->findById($id);
    }

    /**
     * @param $userId
     * @param $favoriteId
     * @return bool
     */
    public function addFavorite( $userId, $favoriteId )
    {
        $fav = new OCSFAVORITES_BOL_Favorite();
        $fav->userId = $userId;
        $fav->favoriteId = $favoriteId;
        $fav->addTimestamp = time();

        $this->favoriteDao->save($fav);

        $params = array('userId' => $userId, 'favoriteId' => $favoriteId, 'id' => $fav->id);
        $event = new OW_Event('ocsfavorites.add_favorite', $params);
        OW::getEventManager()->trigger($event);

        return true;
    }

    /**
     * @param $userId
     * @param $favoriteId
     * @return bool
     */
    public function deleteFavorite( $userId, $favoriteId )
    {
        $favorite = $this->favoriteDao->findFavorite($userId, $favoriteId);

        if ( !$favorite )
        {
            return false;
        }

        $params = array('userId' => $userId, 'favoriteId' => $favoriteId, 'id' => $favorite->id);
        $this->favoriteDao->deleteById($favorite->id);

        $event = new OW_Event('ocsfavorites.remove_favorite', $params);
        OW::getEventManager()->trigger($event);

        return true;
    }

    public function getDataForUsersList( $listType, $userId, $start, $count )
    {
        $page = intval($start / $count) + 1;

        switch ( $listType )
        {
            case 'user_favorites' :
                $list = $this->findFavoritesForUser($userId, $page, $count);
                $total = $this->countFavoritesForUser($userId);
                break;

            case 'added_user' :
                $list = $this->findUsersWhoAddedUserAsFavorite($userId, $page, $count);
                $total = $this->countUsersWhoAddedUserAsFavorite($userId);
                break;

            case 'mutual' :
                $list = $this->findMutualFavorites($userId, $page, $count);
                $total = $this->countMutualFavorites($userId);
                break;
        }

        if ( empty($list) )
        {
            return array(array(), 0);
        }

        $userIdList = array();
        foreach ( $list as $f )
        {
            if ( !in_array($f->favoriteId, $userIdList) )
            {
                $userIdList[] = $listType == 'user_favorites' ? $f->favoriteId : $f->userId;
            }
        }

        return array(BOL_UserService::getInstance()->findUserListByIdList($userIdList), $total);
    }
}