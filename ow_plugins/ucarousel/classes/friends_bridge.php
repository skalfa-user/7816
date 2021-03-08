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
class UCAROUSEL_CLASS_FriendsBridge
{
    /**
     * Singleton instance.
     *
     * @var UCAROUSEL_CLASS_FriendsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UCAROUSEL_CLASS_FriendsBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $filterOnline = false;

    private function __construct()
    {

    }

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('friends');
    }

    public function onCollectQueryFilter( BASE_CLASS_QueryBuilderEvent $event )
    {
        if ( !$this->filterOnline )
        {
            return;
        }

        $params = $event->getParams();

        $userTable = "base_user_table_alias";
        $userField = "id";

        // Support future versions of query builder
        if ( isset($params["tables"]) && isset($params["fields"]) )
        {
            $userTable = $params["tables"][BASE_CLASS_QueryBuilderEvent::TABLE_USER];
            $userField = $params["fields"][BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID];
        }

        $onlineTable = BOL_UserOnlineDao::getInstance()->getTableName();
        $event->addJoin("INNER JOIN `$onlineTable` AS `online` ON `$userTable`.`$userField` = `online`.`userId`");
    }

    public function findUserIds( $userId, $count, $onlineOnly = false )
    {
        $this->filterOnline = $onlineOnly;

        $idList = OW::getEventManager()->call("plugin.friends.get_friend_list", array(
            "userId" => $userId,
            "first" => 0,
            "count" => $count
        ));

        $this->filterOnline = false;

        if ( empty($idList) )
        {
            return array();
        }

        return $idList;
    }

    public function init()
    {
        OW::getEventManager()->bind(BOL_UserService::EVENT_USER_QUERY_FILTER, array($this, "onCollectQueryFilter"));
    }
}