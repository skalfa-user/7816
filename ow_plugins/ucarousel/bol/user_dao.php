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
 * @package ucarousel.bol
 * @since 1.0
 */
class UCAROUSEL_BOL_UserDao extends OW_BaseDao
{
    const EMAIL = 'email';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const JOIN_DATETIME = 'joinDatetime';
    const ACTIVITY_DATETIME = 'activityDatetime';

    /**
     * Singleton instance.
     *
     * @var UCAROUSEL_BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UCAROUSEL_BOL_UserDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_User';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user';
    }

    protected function getQueryParts( $withPhoto = true )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id");
        
        if ( $withPhoto )
        {
            $queryParts["join"] .= " INNER JOIN `" . BOL_AvatarDao::getInstance()->getTableName() . "` as `a` ON( `u`.`id` = `a`.`userId` )";
        }
        
        return $queryParts;
    }


    private function findOrderedList( $count, $order, $withPhoto = true )
    {
        $queryParts = $this->getQueryParts($withPhoto);

        $query = "SELECT `u`.*
            FROM `{$this->getTableName()}` as `u`
            {$queryParts["join"]}
            WHERE {$queryParts["where"]}
            ORDER BY `u`.`$order` DESC
            LIMIT ?,? ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(0, $count));
    }

    public function findLatestUsers( $count, $withPhoto = true )
    {
        return $this->findOrderedList($count, 'joinStamp', $withPhoto);
    }

    public function findRecentlyActiveList( $count, $withPhoto = true )
    {
        return $this->findOrderedList($count, 'activityStamp', $withPhoto);
    }

    public function findFeaturedList( $count, $withPhoto = true )
    {
        $queryParts = $this->getQueryParts($withPhoto);

        $query = "
            SELECT `u`.* 
            FROM `{$this->getTableName()}` AS `u`

            INNER JOIN `" . BOL_UserFeaturedDao::getInstance()->getTableName() . "` AS `f`
                    ON( `u`.`id` = `f`.`userId` )

            {$queryParts["join"]}

            WHERE {$queryParts["where"]}

            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?,?
            ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(0, $count));
    }

    public function findOnlineList( $count, $withPhoto = true  )
    {
        $queryParts = $this->getQueryParts($withPhoto);
        
        $query = "
            SELECT `u`.*
            FROM `{$this->getTableName()}` AS `u`

            INNER JOIN `" . BOL_UserOnlineDao::getInstance()->getTableName() . "` AS `o`
                    ON(`u`.`id` = `o`.`userId`)

            {$queryParts["join"]}

            WHERE {$queryParts["where"]}

            ORDER BY `o`.`activityStamp` DESC
            LIMIT ?, ?
            ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(0, $count));
    }
    
    public function findByRoleIds( $count, $roles, $withPhoto = true  )
    {
        if ( empty($roles) )
        {
            return array();
        }
        
        $queryParts = $this->getQueryParts($withPhoto);

        $query = "
            SELECT DISTINCT `u`.*
            FROM `{$this->getTableName()}` AS `u`

    		{$queryParts["join"]}
                    
                INNER JOIN `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` as `ur`
    		 	ON( `u`.`id` = `ur`.`userId` )

    		WHERE {$queryParts["where"]} AND `ur`.`roleId` IN ( '" . implode("', '", $roles) ."' )

            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?, ?
            ";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(0, $count));
    }
    
    public function findByAccountTypes( $count, $accountTypes, $withPhoto = true  )
    {
        if ( empty($accountTypes) )
        {
            return array();
        }
        
        $queryParts = $this->getQueryParts($withPhoto);

        $query = "
            SELECT DISTINCT `u`.*
            FROM `{$this->getTableName()}` AS `u`

    		{$queryParts["join"]}
                    
    		WHERE {$queryParts["where"]} AND `u`.`accountType` IN ( '" . implode("', '", $accountTypes) ."' )

            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?, ?
            ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(0, $count));
    }

}