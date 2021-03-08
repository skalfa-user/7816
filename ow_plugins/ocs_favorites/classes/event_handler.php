<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow_plugins.ocs_favorites.classes
 * @since 1.5.3
 */
class OCSFAVORITES_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSFAVORITES_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return OCSFAVORITES_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addProfileToolbarAction( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( OW::getUser()->getId() == $userId )
        {
            return;
        }

        $service = OCSFAVORITES_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $isFavorite = $service->isFavorite(OW::getUser()->getId(), $userId);
        $uniqId = uniqid("ocsfav-");

        $actionData = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3
        );

        $actionData[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;
        $actionData[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $isFavorite
            ? $lang->text('ocsfavorites', 'remove_favorite_button')
            : $lang->text('ocsfavorites', 'add_favorite_button');
        $actionData[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $isFavorite ? 'ow_mild_red' : 'ow_mild_green';

        $toggleText = !$isFavorite
            ? $lang->text('ocsfavorites', 'remove_favorite_button')
            : $lang->text('ocsfavorites', 'add_favorite_button');

        // check is blocked
        $blocked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId);
        if ( !$isFavorite && $blocked )
        {
            $error = $lang->text('base', 'user_block_message');
            $script =
                '$("#'.$uniqId.'").click(function(){
                    OW.error('.json_encode($error).');
                });';
            OW::getDocument()->addOnloadScript($script);
        }
        elseif ( !$isFavorite && !OW::getUser()->isAuthorized('ocsfavorites', 'add_to_favorites') )
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
            $actionData[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $isFavorite ? "remove-favorite" : "add-favorite";
            $toggleCommand = !$isFavorite ? "remove-favorite" : "add-favorite";
            $toggleClass = $isFavorite ? 'ow_mild_green' : 'ow_mild_red';

            $js = UTIL_JsGenerator::newInstance();
            $js->jQueryEvent("#" . $uniqId, "click",
                'var self = $(this);
                $.ajax({
                    url: e.data.url,
                    type: "POST",
                    data: { favoriteId: e.data.userId, command: self.attr("data-command") },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result == true ) {
                            OW.info(data.msg);
                            OW.Utils.toggleText(self, e.data.toggleText);
                            OW.Utils.toggleAttr(self, "class", e.data.toggleClass);
                            OW.Utils.toggleAttr(self, "data-command", e.data.toggleCommand);
                        }
                        else if ( data.error != undefined ) {
                            OW.error(data.error);
                        }
                    }
                });
                '
                , array("e"), array(
                    "url" => OW::getRouter()->urlForRoute("ocsfavorites.action"),
                    "userId" => $userId,
                    "toggleText" => $toggleText,
                    "toggleCommand" => $toggleCommand,
                    "toggleClass" => $toggleClass
                ));

            OW::getDocument()->addOnloadScript($js);
        }

        $event->add($actionData);
    }

    /**
     * @param OW_Event $event
     */
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];

        OCSFAVORITES_BOL_Service::getInstance()->deleteUserFavorites($userId);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $item = array(
            'ocsfavorites' => array(
                'label' => $language->text('ocsfavorites', 'auth_group_label'),
                'actions' => array(
                    'add_to_favorites' => $language->text('ocsfavorites', 'auth_action_label_add_favorite'),
                )
            )
        );

        if ( OW::getConfig()->getValue('ocsfavorites', 'can_view') )
        {
            $item['ocsfavorites']['actions']['view_users'] = $language->text('ocsfavorites', 'auth_action_label_view_users');
        }

        $event->add($item);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function adsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('ocsfavorites');
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addQuickLink( BASE_CLASS_EventCollector $event )
    {
        $service = OCSFAVORITES_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();

        $count = $service->countFavoritesForUser($userId);
        if ( $count > 0 )
        {
            $url = OW::getRouter()->urlForRoute('ocsfavorites.list');
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('ocsfavorites', 'my_favorites'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $url,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url,
            ));
        }

        $count = $service->countUsersWhoAddedUserAsFavorite($userId);
        if ( $count && OW::getConfig()->getValue('ocsfavorites', 'can_view')
            && OW::getUser()->isAuthorized('ocsfavorites', 'view_users') )
        {
            $url = OW::getRouter()->urlForRoute('ocsfavorites.added_list');
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('ocsfavorites', 'added_me'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $url,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url,
            ));

            $mutual = $service->countMutualFavorites($userId);
            if ( $mutual )
            {
                $url = OW::getRouter()->urlForRoute('ocsfavorites.mutual_list');
                $event->add(array(
                    BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('ocsfavorites', 'mutual_attraction'),
                    BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $url,
                    BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $mutual,
                    BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url,
                ));
            }
        }
    }

    /**
     * @param BASE_CLASS_EventCollector $e
     */
    public function addNotificationAction( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'ocsfavorites',
            'action' => 'ocsfavorites-add_favorite',
            'sectionIcon' => 'ow_ic_heart',
            'sectionLabel' => OW::getLanguage()->text('ocsfavorites', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('ocsfavorites', 'email_notifications_setting_post'),
            'selected' => true
        ));
    }

    /**
     * @param OW_Event $e
     */
    public function onAddFavorite( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $favoriteId = (int) $params['favoriteId'];
        $id = (int) $params['id'];

        if ( OW::getConfig()->getValue('ocsfavorites', 'can_view')
            && OW::getAuthorization()->isUserAuthorized($favoriteId, 'ocsfavorites', 'view_users') )
        {
            $params = array(
                'pluginKey' => 'ocsfavorites',
                'entityType' => 'ocsfavorites_add_favorite',
                'entityId' => $id,
                'action' => 'ocsfavorites-add_favorite',
                'userId' => $favoriteId,
                'time' => time()
            );

            $mutual = OCSFAVORITES_BOL_Service::getInstance()->isFavorite($favoriteId, $userId);

            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $data = array(
                'avatar' => $avatar[$userId],
                'string' => array(
                    'key' => 'ocsfavorites+email_notification_post' . ( $mutual ? '_mutual' : ''),
                    'vars' => array(
                        'userName' => $avatar[$userId]['title'],
                        'userUrl' => $avatar[$userId]['url']
                    )
                ),
                'url' => $avatar[$userId]['url']
            );

            $event = new OW_Event('notifications.add', $params, $data);
            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * @param OW_Event $e
     */
    public function onRemoveFavorite( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $favoriteId = (int) $params['favoriteId'];
        $id = (int) $params['id'];

        if ( OW::getConfig()->getValue('ocsfavorites', 'can_view')
            && OW::getAuthorization()->isUserAuthorized($favoriteId, 'ocsfavorites', 'view_users') )
        {
            $params = array(
                'entityType' => 'ocsfavorites_add_favorite',
                'entityId' => $id
            );
            $event = new OW_Event('notifications.remove', $params);
            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * @param OW_Event $e
     */
    public function initHint( OW_Event $e )
    {
        OCSFAVORITES_CLASS_HintBridge::getInstance()->init();
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        $em->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        $em->bind('notifications.collect_actions', array($this, 'addNotificationAction'));
        $em->bind('ocsfavorites.add_favorite', array($this, 'onAddFavorite'));
        $em->bind('ocsfavorites.remove_favorite', array($this, 'onRemoveFavorite'));

        $credits = new OCSFAVORITES_CLASS_Credits();
        $em->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
    }

    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileToolbarAction'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $em->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'addQuickLink'));
        $em->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'initHint'));
    }
}