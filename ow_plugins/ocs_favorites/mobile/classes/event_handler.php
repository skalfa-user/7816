<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 12/1/13
 * Time: 6:52 PM
 * To change this template use File | Settings | File Templates.
 */

class OCSFAVORITES_MCLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSFAVORITES_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return OCSFAVORITES_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        OCSFAVORITES_CLASS_EventHandler::getInstance()->genericInit();

        $em = OW::getEventManager();
        $em->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddFavoriteActionTool'));
        $em->bind('mobile.notifications.on_item_render', array($this, 'setNotificationData'));
    }

    public function onActionToolbarAddFavoriteActionTool( BASE_CLASS_EventCollector $event )
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

        if ( !OW::getUser()->isAuthorized('ocsfavorites', 'add_to_favorites') )
        {
            return;
        }

        $action = array(
            "group" => 'addition',
            "order" => 3
        );

        $eventParams = array('pluginKey' => 'ocsfavorites', 'action' => 'add_to_favorites');
        $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);

        $service = OCSFAVORITES_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $uniqId = uniqid("ocsfav-");
        $isFavorite = $service->isFavorite(OW::getUser()->getId(), $userId);

        $action["label"] = $isFavorite
            ? $lang->text('ocsfavorites', 'remove_favorite_button')
            : $lang->text('ocsfavorites', 'add_favorite_button');

        $toggleText = !$isFavorite
            ? $lang->text('ocsfavorites', 'remove_favorite_button')
            : $lang->text('ocsfavorites', 'add_favorite_button');

        $action["href"] = 'javascript://';
        $action["id"] = $uniqId;

        // check is blocked
        $blocked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId);
        if ( !$isFavorite && $blocked )
        {
            $error = $lang->text('base', 'user_block_message');
            $js =
                '$("#' . $uniqId . '").click(function(){
                OWM.error(' . json_encode($error) . ');
            });
            ';
        }
        elseif ( !$isFavorite && $credits === false )
        {
            $error = OW::getEventManager()->call('usercredits.error_message', $eventParams);
            $js =
                '$("#' . $uniqId . '").click(function(){
                OWM.error(' . json_encode($error) . ');
            });
            ';
        }
        else
        {
            $action["attributes"] = array();
            $action["attributes"]["data-command"] = $isFavorite ? "remove-favorite" : "add-favorite";

            $toggleCommand = !$isFavorite ? "remove-favorite" : "add-favorite";

            $js = UTIL_JsGenerator::newInstance();
            $js->jQueryEvent("#" . $uniqId, "click",
                'var self = this;
                $.ajax({
                    url: e.data.url,
                    type: "POST",
                    data: { favoriteId: e.data.userId, command: $(self).attr("data-command") },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result == true ) {
                            OW.info(data.msg);
                            OWM.Utils.toggleText($(".owm_context_action_list_item_c", self), e.data.toggleText);
                            OWM.Utils.toggleAttr(self, "data-command", e.data.toggleCommand);
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
                    "toggleCommand" => $toggleCommand
                ));
        }

        OW::getDocument()->addOnloadScript($js);

        $action["key"] = "ocsfavorites.add_to_favorites";
        $event->add($action);
    }

    public function setNotificationData( OW_Event $event )
    {
        $params = $event->getParams();
        if ( $params['entityType'] == 'ocsfavorites_add_favorite' )
        {
            $data = $params['data'];
            $event->setData($data);
        }
    }
}