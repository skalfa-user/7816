<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

try
{
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'ocsfavorites.list', 'ocsfavorites', 'favorites', OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch ( Exception $e ) { }
