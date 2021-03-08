<?php

/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


/**
 * Data Transfer Object for `task` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.bol
 * @since 1.8.4
 */
class FORCE_BOL_Task extends OW_Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $amount_of_users;

    /**
     * @var int
     */
    public $total_amount;

    /**
     * @var string
     */
    public $status = null;

    /**
     * @var string
     */
    public $command;
   
}