<?php

/**
 * Copyright (c) 2009, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
$updateDir = dirname(__FILE__) . DS;

try
{
    UPDATER::getWidgetService()->deleteWidget('USERCREDITS_CMP_MyCreditsWidget');
}
catch( Exception $e ) {}

Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'usercredits');
