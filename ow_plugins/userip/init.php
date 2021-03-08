<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is a proprietary licensed product. 
 * For more information see License.txt in the plugin folder.

 * ---
 * Copyright (c) 2012, Purusothaman Ramanujam
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are not permitted provided.

 * This plugin should be bought from the developer by paying money to PayPal account (purushoth.r@gmail.com).

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
function userip_user_login(OW_Event $e) {
    $params = $e->getParams();
    $userId = !empty($params['userId']) ? (int) $params['userId'] : OW::getUser()->getId();

    $sql = 'SELECT * FROM ' . USERIP_BOL_AddressDao::getInstance()->getTableName() . ' WHERE userId = ? ';

    $userInfo = OW::getDbo()->queryForObject($sql, USERIP_BOL_AddressDao::getInstance()->getDtoClassName(), array($userId));

    if ($userInfo === null) {
        $userInfo = new USERIP_BOL_Address();
    }

    $userInfo->ip = USERIP_BOL_AddressDao::getInstance()->getIpAddress();
    $userInfo->userid = $userId;

    USERIP_BOL_AddressDao::getInstance()->save($userInfo);
}

OW::getEventManager()->bind(OW_EventManager::ON_USER_LOGIN, 'userip_user_login');

function userip_members_action_tool(BASE_CLASS_EventCollector $event) {
    if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin() || !OW::getUser()->isAuthorized('base')) {
        if (!OW::getUser()->isAuthorized('userip')) {
            return;
        }
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
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0,
        BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "userip.view_ip");

    $event->add($resultArray);
}

OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, 'userip_members_action_tool');

function userip_add_auth_labels(BASE_CLASS_EventCollector $event) {
    $language = OW::getLanguage();
    $event->add(
            array(
                'userip' => array(
                    'label' => "User IP Tracker",
                    'actions' => array(
                    )
                )
            )
    );
}

OW::getEventManager()->bind('admin.add_auth_labels', 'userip_add_auth_labels');
