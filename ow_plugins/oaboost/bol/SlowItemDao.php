<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

namespace oacompress\bol;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.5
 */
class SlowItemDao extends \OW_BaseDao
{
    const TYPE = "type";
    const DATA = "data";
    const TIME_STAMP = "timeStamp";
    const TIME = "time";
    const FIELD_VALUE_DATA_QUERY = "query";
    const FIELD_VALUE_DATA_CMP_CONSTRUCT = "cmp_construct";
    const FIELD_VALUE_DATA_PLUGIN_INIT = "plugin_init";
    const FIELD_VALUE_DATA_ITEM_RENDER = "item_render";
    const FIELD_VALUE_DATA_ACTION_CALL = "action_call";

    /**
     * Singleton instance.
     *
     * @var \OACOMPRESS_BOL_SlowItemDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return \OACOMPRESS_BOL_SlowItemDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see \OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return "\oacompress\bol\SlowItem";
    }

    /**
     * @see \OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . "oacompress_slow_item";
    }
}
