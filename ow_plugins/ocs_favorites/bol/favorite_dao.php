<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Data Access Object for `ocsfavorites_favorite` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.bol
 * @since 1.5.3
 */
class OCSFAVORITES_BOL_FavoriteDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var OCSFAVORITES_BOL_FavoriteDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return OCSFAVORITES_BOL_FavoriteDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     */
    public function getDtoClassName()
    {
        return 'OCSFAVORITES_BOL_Favorite';
    }
    
    /**
     * @see OW_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ocsfavorites_favorite';
    }

    /**
     * @param $userId
     * @param $favoriteId
     * @return OCSFAVORITES_BOL_Favorite
     */
    public function findFavorite( $userId, $favoriteId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->andFieldEqual('favoriteId', $favoriteId);
    	
    	return $this->findObjectByExample($example);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findUserFavorites( $userId, $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->setLimitClause($first, $limit);
    	$example->setOrder('`addTimestamp` DESC');
    	
    	return $this->findListByExample($example);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countUserFavorites( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->countByExample($example);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countUsersWhoAddedAsFavorite( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('favoriteId', $userId);

        return $this->countByExample($example);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findUsersWhoAddedAsFavorite( $userId, $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;

        $example = new OW_Example();
        $example->andFieldEqual('favoriteId', $userId);
        $example->setLimitClause($first, $limit);
        $example->setOrder('`addTimestamp` DESC');

        return $this->findListByExample($example);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countMutualFavorites( $userId )
    {
        $sql = "SELECT COUNT(`f1`.`id`) FROM `".$this->getTableName()."` AS `f1`
            INNER JOIN `".$this->getTableName()."` AS `f2` ON(`f1`.`userId`=`f2`.`favoriteId` AND `f1`.`favoriteId`=`f2`.`userId`)
            AND `f1`.`userId` = :userId";

        return $this->dbo->queryForColumn($sql, array('userId' => $userId));
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findMutualFavorites( $userId, $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;

        $sql = "SELECT * FROM `".$this->getTableName()."` AS `f1`
            INNER JOIN `".$this->getTableName()."` AS `f2` ON(`f1`.`userId`=`f2`.`favoriteId` AND `f1`.`favoriteId`=`f2`.`userId`)
            AND `f1`.`userId` = :userId
            ORDER BY `f1`.`addTimestamp` DESC
            LIMIT :first, :limit";

        return $this->dbo->queryForObjectList(
            $sql,
            $this->getDtoClassName(),
            array('userId' => $userId, 'first' => $first, 'limit' => $limit)
        );
    }

    /**
     * @param $userId
     */
    public function deleteUserFavorites( $userId )
    {
    	$sql = "DELETE FROM `".$this->getTableName()."` 
    	   WHERE `userId` = ? OR `favoriteId` = ?";
    	
    	$this->dbo->query($sql, array($userId, $userId));
    }
}
