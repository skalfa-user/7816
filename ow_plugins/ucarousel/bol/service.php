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
class UCAROUSEL_BOL_Service
{

    private static $classInstance;

    /**
     *
     * @var UCAROUSEL_BOL_UserDao
     */
    private $userDao;

    /**
     * Returns class instance
     *
     * @return UCAROUSEL_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->userDao = UCAROUSEL_BOL_UserDao::getInstance();
    }

    public function findLatestList( $count, $withPhoto = true )
    {
        return $this->userDao->findLatestUsers($count, $withPhoto);
    }

    public function findRecentlyActiveList( $count, $withPhoto = true )
    {
        return $this->userDao->findRecentlyActiveList($count, $withPhoto);
    }

    public function findFeaturedList( $count, $withPhoto = true )
    {
        return $this->userDao->findFeaturedList($count, $withPhoto);
    }

    public function findOnlineList( $count, $withPhoto = true )
    {
        return $this->userDao->findOnlineList($count, $withPhoto);
    }
    
    public function findByAccountTypes( $count, $accountTypes, $withPhoto = true )
    {
        return $this->userDao->findByAccountTypes($count, $accountTypes, $withPhoto);
    }
    
    public function findByRoles( $count, $roles, $withPhoto = true )
    {
        return $this->userDao->findByRoleIds($count, $roles, $withPhoto);
    }
}