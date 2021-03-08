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
class UCAROUSEL_CLASS_GroupsBridge
{
    const WIDGET_PLACE = 'group';

    /**
     * Singleton instance.
     *
     * @var UCAROUSEL_CLASS_GroupsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UCAROUSEL_CLASS_GroupsBridge
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
        return OW::getPluginManager()->isPluginActive('groups');
    }

    public function findUsers( $groupId, $count, $withPhoto = true )
    {
        $userTable = BOL_UserDao::getInstance()->getTableName();

        $avatarJoin = !$withPhoto ? '' : "INNER JOIN `" . BOL_AvatarDao::getInstance()->getTableName() . "` as `a`
    			ON( `u`.`id` = `a`.`userId` )";

        $query = "
            SELECT `u`.* FROM `$userTable` AS `u`

            INNER JOIN `" . GROUPS_BOL_GroupUserDao::getInstance()->getTableName() . "` AS `g`
                    ON( `u`.`id` = `g`.`userId` )

            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                    ON( `u`.`id` = `s`.`userId` )

            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                    ON( `u`.`id` = `d`.`userId` )

            $avatarJoin

            WHERE g.groupId=:g AND `s`.`id` IS NULL AND `d`.`id` IS NULL
            ORDER BY `u`.`activityStamp` DESC
            LIMIT :ls, :le";

        return OW::getDbo()->queryForObjectList($query, BOL_UserDao::getInstance()->getDtoClassName(), array(
            'ls' => 0,
            'le' => $count,
            'g' => $groupId
        ));
    }

    public function addWidget( $widgetClass, $position = -1, $checkPlugin = true )
    {
        if ( $checkPlugin && !$this->isActive() ) return;

        $widgetService = BOL_ComponentAdminService::getInstance();

        try
        {
            $widget = $widgetService->addWidget($widgetClass, false);
            $placeWidget = $widgetService->addWidgetToPlace($widget, self::WIDGET_PLACE);
            $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_TOP, $position);
        }
        catch ( Exception $e ) {}
    }

    public function removeWidget( $widgetClass )
    {
        BOL_ComponentAdminService::getInstance()->deleteWidget($widgetClass);
    }

    public function onActivate( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['pluginKey'] != 'groups' )
        {
            return;
        }

        $this->addWidget('UCAROUSEL_CMP_GroupUsersWidget', -1, false);
    }

    public function onDeactivate(  OW_Event $event  )
    {
        $params = $event->getParams();

        if ( $params['pluginKey'] != 'groups' )
        {
            return;
        }
    }

    public function onUninstall(  OW_Event $event  )
    {
        $params = $event->getParams();

        if ( $params['pluginKey'] != 'groups' )
        {
            return;
        }

        $this->removeWidget('UCAROUSEL_CMP_GroupUsersWidget');
    }

    public function onInstall(  OW_Event $event  )
    {
        $params = $event->getParams();

        if ( $params['pluginKey'] != 'groups' )
        {
            return;
        }
    }



    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'onActivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'onDeactivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onUninstall'));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_INSTALL, array($this, 'onInstall'));
    }
}