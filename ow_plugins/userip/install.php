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
BOL_LanguageService::getInstance()->addPrefix('userip', 'User IP Tracker');

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('userip')->getRootDir() . 'langs.zip', 'userip');

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS  " . OW_DB_PREFIX . "userip_address (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `ip` char(20) NOT NULL,
  PRIMARY KEY  (id));");

$authorization = OW::getAuthorization();
$groupName = 'userip';
$authorization->addGroup($groupName);
