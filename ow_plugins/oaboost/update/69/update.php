<?php

/**
 * Copyright (c) 2011 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package oaseo
 * @since 1.0
 */
if ( !UPDATER::getConfigService()->configExists('oacompress', 'mark_all_expired') )
{
    UPDATER::getConfigService()->addConfig('oacompress', 'mark_all_expired', 1);
}
