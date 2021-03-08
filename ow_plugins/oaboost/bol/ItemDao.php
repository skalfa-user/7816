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
class ItemDao extends \OW_BaseDao
{
    const TYPE = "type";
    const HASH = "hash";
    const FILE_LIST = "fileList";
    const STATUS = "status";
    const FIELD_VALUE_TYPE_JS = "js";
    const FIELD_VALUE_TYPE_CSS = "css";
    const FIELD_VALUE_TYPE_RAW_JS = "raw_js";
    const FIELD_VALUE_TYPE_RAW_CSS = "raw_css";
    const FIELD_VALUE_STATUS_QUEUED = 0;
    const FIELD_VALUE_STATUS_PROCESSED = 1;
    const FIELD_VALUE_STATUS_EXPIRED = 2;
    const FIELD_VALUE_STATUS_PROCESSED_BUT_NA = 3;

    /**
     * Singleton instance.
     *
     * @var ItemDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ItemDao
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
        return "\oacompress\bol\Item";
    }

    /**
     * @see \OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . "oacompress_item";
    }

    /**
     * @param string $hash
     * @return Item
     */
    public function findItemByHashAndType( $hash, $type )
    {
        $example = new \OW_Example();
        $example->andFieldEqual(self::HASH, $hash);
        $example->andFieldEqual(self::TYPE, $type);
        $example->andFieldNotEqual(self::STATUS, self::FIELD_VALUE_STATUS_EXPIRED);

        return $this->findObjectByExample($example);
    }

    /**
     * @return array<Item>
     */
    public function findItemsToProcess()
    {
        $example = new \OW_Example();
        $example->andFieldEqual(self::STATUS, self::FIELD_VALUE_STATUS_QUEUED);
        $example->andFieldInArray(self::TYPE, array(self::FIELD_VALUE_TYPE_CSS, self::FIELD_VALUE_TYPE_JS));

        return $this->findListByExample($example);
    }

    /**
     * @param string $fileName
     * @return Item
     */
    public function findRawCssFile( $fileName )
    {
        $example = new \OW_Example();
        $example->andFieldEqual(self::HASH, $fileName);
        $example->andFieldNotEqual(self::STATUS, self::FIELD_VALUE_STATUS_EXPIRED);
        $example->andFieldEqual(self::TYPE, self::FIELD_VALUE_TYPE_RAW_CSS);

        return $this->findObjectByExample($example);
    }

    /**
     * 
     */
    public function markAllExpired()
    {
        $this->dbo->query("UPDATE `" . $this->getTableName() . "` SET `" . self::STATUS . "` = " . self::FIELD_VALUE_STATUS_EXPIRED);
    }

    /**
     * @return Item
     */
    public function findNextExpiredItem()
    {
        $example = new \OW_Example();
        $example->andFieldEqual(self::STATUS, self::FIELD_VALUE_STATUS_EXPIRED);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * @return array
     */
    public function findAllIdList()
    {
        return array_map("intval", $this->dbo->queryForColumnList("SELECT `id` FROM `" . $this->getTableName() . "`"));
    }
}
