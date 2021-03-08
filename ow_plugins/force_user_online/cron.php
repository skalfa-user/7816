<?php

/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


/**
 * Farce online cron job.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_online
 * @since 1.8.4
 */
class FORCE_Cron extends OW_Cron
{
    protected $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = FORCE_BOL_Service::getInstance();
        $this->addJob('trackFakeOnline', 1);
        $this->addJob('triggerAction', 1);
    }

    public function run()
    {
        
    }

    public function trackFakeOnline()
    {
        $this->service->makeOnline();
    }

    public function triggerAction()
    {
        $hours = (int)date('H');
        $minutes = (int)date('i');

        $actionList = $this->service->getAllActions();

        foreach ( $actionList as $item )
        {
            if( ($hours > $item->hours || ($hours = $item->hours && $minutes == $item->minutes)) ) // if time to action
            {
                if($item->action == 'add') // check what action we need to trigger
                {
                    $usersForOnline = $this->service->getUsersForOnline($item->amount);

                    foreach ($usersForOnline as $user)
                    {
                        $this->service->addFakeOnlineUser($user['id']);
                    }
                }
                else
                {
                    $usersForDelete = $this->service->getUserForDeleteFromOnline($item->amount);

                    foreach ($usersForDelete as $user)
                    {
                        $this->service->deleteFakeOnlineUserByUserId($user->user_id);
                    }
                }

                $this->service->updateActionStatusById($item->id, 1);
            }
        }

        $this->service->checkForReset();
    }

}