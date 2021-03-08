<?php
/**
 * Copyright (c) 2017, Pryadkin Sergey <GiperProger@gmail.com>
 * All rights reserved.
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.classes
 * @since 1.8.4
 */
class FORCE_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FORCE_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @var FORCE_BOL_Service
     */
    protected $service;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FORCE_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    private function __construct()
    {
        $this->service = FORCE_BOL_Service::getInstance();
    }

    public function init()
    {
        $em = OW::getEventManager();
        $em->bind('base.ping', array($this,'forcePing'));
    }


    /**
     * Add ping command for checking status of operation
     *
     * @param OW_Event $event
     */
    function forcePing( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['command'] != 'force_users_check_status' ) // confirm ping only form 'force user online' plugin
        {
            return;
        }

        $response = array();
        $task = $this->service->findAllTasks();
        $fakeOnlineUsersCount = $this->service->getFakeOnlineUsersCount();

        if( isset($task[0]) )
        {
            $response['rest'] = $task[0]->amount_of_users;
            $response['totalAmount'] = $task[0]->total_amount;
            $response['status'] = $task[0]->command;
        }
        else
        {
            $response['rest'] = 0;
            $response['status'] = 'complete';
        }

        $response['count'] = $fakeOnlineUsersCount;
        $event->setData($response);
    }
}