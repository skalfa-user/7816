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
 * @package ow.ow_plugins.ocs_favorites
 * @since 1.5.3
 */

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.admin', '/admin/plugins/ocsfavorites', 'OCSFAVORITES_CTRL_Admin', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.list', '/favorites', 'OCSFAVORITES_CTRL_Favorites', 'mylist')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.added_list', '/favorites/added/me', 'OCSFAVORITES_CTRL_Favorites', 'addedList')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.mutual_list', '/favorites/mutual', 'OCSFAVORITES_CTRL_Favorites', 'mutualList')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsfavorites.action', '/favorites/ajax/action', 'OCSFAVORITES_CTRL_Ajax', 'action')
);

OCSFAVORITES_CLASS_EventHandler::getInstance()->init();