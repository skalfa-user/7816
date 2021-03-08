<?php

/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


/**
 * Data Transfer Object for `force_fake_online_users` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.bol
 * @since 1.8.4
 */
class FORCE_BOL_Actions extends OW_Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $amount;

    /**
     * @var int
     */
    public $hours;

    /**
     * @var int
     */
    public $minutes;

    /**
     * @var string
     */
    public $action;

    /**
     * @var int
     */
    public $triggered = 0;

}