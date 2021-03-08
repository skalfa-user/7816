<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.oaboost
 * @since 1.5
 */
class OACOMPRESS_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();
        $this->addJob("deleteTrashDirs", 60);
    }

    public function run()
    {
        $service = oacompress\bol\Service::getInstance();

        if ( !OW_DEV_MODE )
        {
            $service->processItems();
            $service->deleteNextExpiredItem();
            $service->optimizeTables();
        }
    }

    public function deleteTrashDirs()
    {
        oacompress\bol\Service::getInstance()->deleteTrashItems();
    }
}
