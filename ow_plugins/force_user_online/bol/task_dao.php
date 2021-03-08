<?php

/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


/**
 * Data access Object for `task` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.bol
 * @since 1.8.4
 */
class FORCE_BOL_TaskDao extends OW_BaseDao
{
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * @var FORCE_BOL_TaskDao
     */
    private static $classInstance;

    /**
     * @return FORCE_BOL_TaskDao
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
     *
     */
    public function getDtoClassName()
    {
        return 'FORCE_BOL_Task';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'force_task';
    }

    public function getUsersForOnline( $amountOfUsers )
    {

        $baseUserTableName = BOL_UserDao::getInstance()->getTableName();
        $baseUserOnlineTableName = BOL_UserOnlineDao::getInstance()->getTableName();

        $sql = "SELECT `u`.`id` FROM {$baseUserTableName} AS `u` LEFT JOIN {$baseUserOnlineTableName} AS `uo` ON `u`.`id` = `uo`.`userId` WHERE `uo`.`userId` IS NULL ORDER BY RAND() LIMIT :limit";

        $result = $this->dbo->queryForList($sql, array('limit' => (int)$amountOfUsers));

        return $result;
    }

    public function addUserOnline( $userId, $context = 1 )
    {
        $userOnlineDao = BOL_UserOnlineDao::getInstance();
        $userOnline = $userOnlineDao->findByUserId($userId);

        if ( $userOnline === null )
        {
            $userOnline = new BOL_UserOnline();
            $userOnline->setUserId($userId);
        }

        $activityStamp = 2147483647;

        $userOnline->setActivityStamp($activityStamp);

        $userOnline->setContext($context);
        $userOnlineDao->save($userOnline);
    }

    public function updateTaskData( $command, $restOfUsers )
    {
        $sql = "UPDATE {$this->getTableName()} AS `t` SET `t`.`command` = :command, `t`.`amount_of_users` = :restOfUsers";

        $this->dbo->query($sql, array('command' => $command, 'restOfUsers' => $restOfUsers));
    }

    public function deleteTask()
    {
        $sql = "DELETE FROM {$this->getTableName()}";

        $this->dbo->query($sql);
    }

    
    
}