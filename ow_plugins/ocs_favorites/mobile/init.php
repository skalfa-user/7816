<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites/mobile
 * @since 1.6.0
 */

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.list', '/favorites', 'OCSFAVORITES_MCTRL_Favorites', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.added_list', '/favorites/added/me', 'OCSFAVORITES_MCTRL_Favorites', 'addedList')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.mutual_list', '/favorites/mutual', 'OCSFAVORITES_MCTRL_Favorites', 'mutualList')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.action', '/favorites/ajax/action', 'OCSFAVORITES_MCTRL_Ajax', 'action')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.responder', '/favorites/ajax/list-rsp', 'OCSFAVORITES_MCTRL_Favorites', 'responder')
);

OCSFAVORITES_MCLASS_EventHandler::getInstance()->init();