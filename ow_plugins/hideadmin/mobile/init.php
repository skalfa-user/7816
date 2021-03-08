<?php

/**
 * Copyright (c) 2015, Pryadkin Sergey <GiperProger@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */


HIDEADMIN_CLASS_EventHandler::getInstance()->genericInit();

OW::getRouter()->addRoute(new OW_Route('upgrade-to-view', '/users/denided', 'HIDEADMIN_MCTRL_Controller', 'newRedirect'));
