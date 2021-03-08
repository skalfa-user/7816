<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Hint bridge class
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.classes
 * @since 1.5.3
 */
class OCSFAVORITES_CLASS_HintBridge
{
    /**
     * Class instance
     *
     * @var OCSFAVORITES_CLASS_HintBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return OCSFAVORITES_CLASS_HintBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct() { }

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('hint');
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];

        if ( !OW::getUser()->isAuthenticated() || $userId == OW::getUser()->getId() )
        {
            return;
        }

        $service = OCSFAVORITES_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $isFavorite = $service->isFavorite(OW::getUser()->getId(), $userId);

        $uniqId = uniqid("hint-favorites-");

        if ( $isFavorite )
        {
            $command = "favorites.remove";
            $label = $lang->text('ocsfavorites', 'remove_favorite_button');
        }
        else
        {
            $command = "favorites.add";
            $label = $lang->text('ocsfavorites', 'add_favorite_button');
        }

        if ( !OW::getUser()->isAuthorized('ocsfavorites', 'add_to_favorites') )
        {
            try
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('ocsfavorites', 'add_to_favorites');

                if ( $status['status'] != BOL_AuthorizationService::STATUS_PROMOTED )
                {
                    return;
                }

                $script =
                '$("#'.$uniqId.'").click(function(){
                    OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                });';
                OW::getDocument()->addOnloadScript($script);
            }
            catch ( Exception $e ) { }
        }
        else
        {
            $js = UTIL_JsGenerator::newInstance();
            $js->jQueryEvent('#' . $uniqId, 'click', '
                var self = $(this), command = self.data("command");
                HINT.UTILS.toggleText(this, e.data.l1, e.data.l2);
                self.data("command", command == "favorites.remove" ? "favorites.add" : "favorites.remove");
                HINT.UTILS.query(command, e.data.params); return false;',
                array('e'),
                array(
                    "l1" => $lang->text('ocsfavorites', 'add_favorite_button'),
                    "l2" => $lang->text('ocsfavorites', 'remove_favorite_button'),
                    "params" => array(
                        "userId" => $userId
                    )
                ));

            OW::getDocument()->addOnloadScript($js);
        }

        $button = array(
            "key" => "ocsfavorites",
            "label" => $label,
            "attrs" => array("id" => $uniqId, "data-command" => $command),
        );

        $event->add($button);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("ocsfavorites", "add_favorite_button");

        $button = array(
            "key" => "ocsfavorites",
            "label" => $label,
            "attrs" => array("href" => "javascript://")
        );

        $event->add($button);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("ocsfavorites", "add_favorite_button");
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "ocsfavorites");

        $button = array(
            "key" => "ocsfavorites",
            "active" => $active === null ? true : $active,
            "label" => $label
        );

        $event->add($button);
    }

    /**
     * @param OW_Event $event
     */
    public function onHintRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
    }

    /**
     * @param OW_Event $event
     */
    public function onQuery( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('ocsfavorites', 'add_to_favorites') )
        {
            return;
        }

        $params = $event->getParams();

        if ( !in_array($params["command"], array("favorites.add", "favorites.remove")) )
        {
            return;
        }

        $userId = OW::getUser()->getId();
        $favoriteId = $params["params"]['userId'];
        $service = OCSFAVORITES_BOL_Service::getInstance();

        $info = null;
        $error = null;

        switch ( $params["command"] )
        {
            case "favorites.remove":
                $service->deleteFavorite($userId, $favoriteId);
                $info = OW::getLanguage()->text('ocsfavorites', 'favorite_removed');

                break;

            case "favorites.add":
                $service->addFavorite($userId, $favoriteId);
                $info = OW::getLanguage()->text('ocsfavorites', 'favorite_added');

                break;
        }

        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }

    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}