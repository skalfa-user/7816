<?php

function userip_members_action_tool(BASE_CLASS_EventCollector $event) {
    if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin() || !OW::getUser()->isAuthorized('base')) {
        return;
    }

    $params = $event->getParams();

    $targetUserID = $params['userId'];

    $ip = USERIP_BOL_AddressDao::getInstance()->getIpByUserId($targetUserID);

    if (is_null($ip)) {
        if ($user = BOL_UserService::getInstance()->findUserById($targetUserID)) {
            $ip = long2ip($user->joinIp);
        }
    }

    if (is_null($ip)) {
        return;
    }

    $linkId = 'userip' . rand(10, 1000000);

    $script = '$("#' . $linkId . '").click(function(){
                   window.open(($(this).attr("href")));
                   return false;
           });';

    OW::getDocument()->addOnloadScript($script);

    $resultArray = array(
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $ip,
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => "http://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $ip,
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
        BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "userip.view_ip");

    $event->add($resultArray);
}

OW::getEventManager()->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, 'userip_members_action_tool');
