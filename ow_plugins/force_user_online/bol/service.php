<?php
/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


/**
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.bol
 * @since 1.8.4
 */

class FORCE_BOL_Service
{

    const PLUGIN_KEY = 'force';

    const FREE_COMMAND = 'free';

    const DELETING_COMMAND = 'deleting';

    const ADDING_COMMAND = 'adding';

    const ONE_AMOUNT_OF_USERS = 15000;  //now many users will put online by one cron operation

    /**
     *
     * @var FORCE_BOL_TaskDao
     */
    private $taskDao;

    /**
     *
     * @var FORCE_BOL_FakeOnline
     */
    private $fakeOnlineDao;

    /**
     *
     * @var FORCE_BOL_ActionsDao
     */
    private $actionsDao;

    /**
     *
     * @return FORCE_BOL_Service
     */

    private static $classInstance;

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
        $this->taskDao = FORCE_BOL_TaskDao::getInstance();
        $this->fakeOnlineDao = FORCE_BOL_FakeOnlineDao::getInstance();
        $this->actionsDao = FORCE_BOL_ActionsDao::getInstance();
    }

    public function getPluginKey()
    {
        return SELF::PLUGIN_KEY;
    }

    public function makeOnline()   // if we want to make online a huge amount of users, we should separate it for subtask
    {
        $taskInfo = $this->findAllTasks();
        $oneOffAmountOfUsersForSubTask = null;
        $command = null;
        $currentCommand = null;

        if( empty($taskInfo) )
        {
            return;
        }

        $currentCommand = $taskInfo[0]->command;

        if( $taskInfo[0]->amount_of_users > self::ONE_AMOUNT_OF_USERS )
        {
            $oneOffAmountOfUsersForSubTask = self::ONE_AMOUNT_OF_USERS;
            $command = $taskInfo[0]->command;
        }
        else
        {
            $oneOffAmountOfUsersForSubTask = $taskInfo[0]->amount_of_users;
            $command = SELF::FREE_COMMAND;
        }

        if( $currentCommand == 'adding' )
        {
            $userForOnline = $this->taskDao->getUsersForOnline( $oneOffAmountOfUsersForSubTask );
        }
        else
        {
            $userForOnline = $this->getUserForDeleteFromOnline( $oneOffAmountOfUsersForSubTask );
        }

        foreach ( $userForOnline as $user )
        {
            if( $currentCommand == 'adding' )
            {
                $this->taskDao->addUserOnline($user['id']);  // make user to be online
                $this->addFakeOnlineUser($user['id']);   //add info into force user online table
            }
            else
            {
                BOL_UserService::getInstance()->onLogin($user->user_id, OW::getApplication()->getContext()); // set last activity
                $this->deleteFakeOnlineUserByUserId($user->user_id);
                $this->putUserOffline($user->user_id);
            }

        }

        $amountNewOnlineUsers = count($userForOnline) - (int)self::ONE_AMOUNT_OF_USERS;

        if ( $amountNewOnlineUsers < 0 )
        {
            $this->deleteTask();
        }

        else
        {
            $amountNewOnlineUsers = $taskInfo[0]->amount_of_users - self::ONE_AMOUNT_OF_USERS;

            if ( $amountNewOnlineUsers <= 0 )
            {
                $this->deleteTask();
                return;
            }

            $this->updateTaskData($command, $amountNewOnlineUsers);
        }

    }

    public function setTask( FORCE_BOL_Task $task )
    {
        $this->taskDao->save($task);
    }

    public function updateTaskData( $command, $amountNewOnlineUsers )
    {
        $this->taskDao->updateTaskData( $command, $amountNewOnlineUsers );
    }

    public function deleteTask()
    {
        $this->taskDao->deleteTask();
    }

    public function findAllTasks()
    {
        return $this->taskDao->findAll();
    }

    public function addFakeOnlineUser( $userId )
    {
        $fakeOnline = new FORCE_BOL_FakeOnline();
        $fakeOnline->user_id = $userId;
        $this->fakeOnlineDao->save( $fakeOnline );
    }

    public function deleteFakeOnlineUserByUserId( $userId )
    {

        $example = new OW_Example();
        $example->andFieldEqual('user_id', $userId);

        $this->fakeOnlineDao->deleteByExample($example);
    }

    public function getUserForDeleteFromOnline( $oneOffAmountOfUsers )
    {
        $example = new OW_Example();
        $example->setLimitClause(0, $oneOffAmountOfUsers);
        $result =  (array)$this->fakeOnlineDao->findListByExample($example);

        return $result;
    }

    public function putUserOffline( $userId )
    {
        $userOnlineDao = BOL_UserOnlineDao::getInstance();
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);

        $userOnlineDao->deleteByExample($ex);
    }

    public function getFakeOnlineUsersCount()
    {
        return $this->fakeOnlineDao->countAll();
    }

    public function deleteOldOnlineUsers()
    {
        $userOnlineDao = BOL_UserOnlineDao::getInstance();
        $example = new OW_Example();
        $example->andFieldGreaterThenOrEqual('activityStamp', 2147483647);
        $userOnlineDao->deleteByExample($example);
    }

    public function getAllActions()
    {
        $example = new OW_Example();
        $example->andFieldEqual('triggered', 0);

        return $this->actionsDao->findListByExample($example);
    }

    public function getTimeTable()
    {
        return $this->actionsDao->findAll();
    }

    public function addAction( FORCE_BOL_Actions $action )
    {
        $this->actionsDao->save($action);
    }

    public function deleteAction( $actionId )
    {
        $this->actionsDao->deleteById($actionId);
    }

    public function getUsersForOnline( $amount )
    {
        return $this->taskDao->getUsersForOnline( $amount );
    }

    public function updateActionStatusById( $actionId, $status )
    {
        $this->actionsDao->updateActionStatusById($actionId, $status);
    }

    public function checkForReset()
    {
        $actions = $this->actionsDao->findAll();

        foreach( $actions as $action )
        {
            if( $action->triggered == 0 ) return;
        }

        $this->actionsDao->resetAllActionsStatus();
    }

    public function onPluginDelete()
    {
        $fakeOnlineUsersList =  $this->fakeOnlineDao->findAll();

        foreach( $fakeOnlineUsersList as $user )
        {
            BOL_UserService::getInstance()->onLogin($user->user_id, OW::getApplication()->getContext());
            $this->putUserOffline($user->user_id);
        }
    }


}