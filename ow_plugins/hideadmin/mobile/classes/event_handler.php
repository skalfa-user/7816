<?php
/**
* Copyright (c) 2015, Pryadkin Sergey <GiperProger@gmail.com>
* All rights reserved.

* ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
* and is licensed under Oxwall Store Commercial License.
* Full text of this license can be found at http://www.oxwall.org/store/oscl
*/

/**
* @author Pryadkin Sergey <GiperProger@gmail.com>
* @package ow_plugins.guestredirect.classes
* @since 1.0
*/



class HIDEADMIN_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var HIDEADMIN_MCLASS_EventHandler
     */
    private static $classInstance;
    

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return HIDEADMIN_MCLASS_EventHandler
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

    }

    public function init()
    {
        HIDEADMIN_CLASS_EventHandler::getInstance()->genericInit();
    }

    

    
}