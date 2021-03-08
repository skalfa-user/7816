<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kubatbekov Rahat <kubatbekovdev@gmail.com>
 */
OW::getRouter()->addRoute(new OW_Route('cusredirect.user_search', 'cusredirect/user-search', 'CUSREDIRECT_CTRL_Search', 'index'));

CUSREDIRECT_CLASS_EventHandler::getInstance()->init();