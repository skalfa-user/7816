<?php

/* 
 * Copyright 2015 Daniel Shum 
 * Contact: denny.shum@gmail.com
 * 
 * Licensed under the OSCL (the License); you may not 
 * use this file except in compliance with the License.
 * 
 * You may obtain a copy of the License at 
 * 
 * 	https://developers.oxwall.com/store/oscl
 * 
 * 
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

$pluginName = 'memberx';


Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'memberx');

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "{$pluginName}_search_id` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `searchId` int(10) NOT NULL DEFAULT 0,
  `md5` VARCHAR(64) NOT NULL DEFAULT '',
  `data` TEXT NOT NULL DEFAULT '',
  `creationTime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "{$pluginName}_search_result` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `searchId` int(10) NOT NULL DEFAULT 0,
  `md5` VARCHAR(64) NOT NULL DEFAULT '',
  `data` TEXT NOT NULL DEFAULT '',
  `itemCount` int(10) NOT NULL DEFAULT 0,
  `dtoList` TEXT NOT NULL DEFAULT '',
  `creationTime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);