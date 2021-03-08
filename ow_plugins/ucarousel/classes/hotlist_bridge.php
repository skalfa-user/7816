<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ucarousel.classes
 */
class UCAROUSEL_CLASS_HotlistBridge
{
    /**
     * Singleton instance.
     *
     * @var UCAROUSEL_CLASS_HotlistBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UCAROUSEL_CLASS_HotlistBridge
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

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('hotlist');
    }

    public function findUserIds( $count )
    {
        $idList = OW::getEventManager()->trigger(new OW_Event("hotlist.get_id_list", array(
            "offset" => 0,
            "count" => $count
        )))->getData();
        
        if ( empty($idList) ) 
        {    
            return array();
        }
        
        return $idList;
    }

    public function init()
    {
        
    }
}