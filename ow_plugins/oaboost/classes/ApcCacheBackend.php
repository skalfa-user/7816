<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
/**
 * @author Madumarov Sardar <madumarov@gmail.com>
 * @package ow_plugins.oacompress.classes
 * @since 1.0
 */

namespace oacompress\classes;

class ApcCacheBackend implements IStorage
{

    public function __construct()
    {
        
    }

    public function save( $data, $key, array $tags = array(), $lifeTime )
    {
        if ( empty($key) || $this->test($key) )
        {
            return;
        }

        $key = $this->addPrefix($key);
        $tagsWithVersion = array();
        $success = false;

        foreach ( $tags as $tag )
        {
            $tagWithPrefix = $this->addPrefix($tag);
            $tagVersion = apc_fetch($tagWithPrefix, $success);

            if ( !$success )
            {
                $tagVersion = $this->generateNewTagVersion();
                apc_store($tagWithPrefix, $tagVersion);
            }

            $tagsWithVersion[$tag] = $tagVersion;
        }

        $compiledData = serialize(array($tagsWithVersion, $data));
        
        return apc_store($key, $compiledData, $lifeTime);
    }

    public function test( $key )
    {
        if ( empty($key) )
        {
            return false;
        }
        
        return $this->load($key) !== null;
    }

    public function load( $key )
    {
        if ( empty($key) )
        {
            return null;
        }

        $success = false;
        $key = $this->addPrefix($key);
        
        $data = apc_fetch($key, $success);        
        
        if ( !$success )
        {
            return null;
        }

        $data = unserialize($data);

        foreach ( $data[0] as $tag => $version )
        {
            $tagVersion = apc_fetch($this->addPrefix($tag), $success);

            if ( !$success || $tagVersion != $version )
            {
                apc_delete($key);
                return null;
            }
        }

        return $data[1];
    }

    public function remove( $key )
    {
        if ( empty($key) )
        {
            return;
        }

        apc_delete($this->addPrefix($key));
    }

    public function clean( array $tags, $mode )
    {
        if ( !$mode )
        {
            return false;
        }

        switch ( $mode )
        {
            case \OW_CacheManager::CLEAN_ALL:
                apc_clear_cache();
                break;

            case \OW_CacheManager::CLEAN_MATCH_ANY_TAG:
                if ( !$tags )
                {
                    return false;
                }

                foreach ( $tags as $tag )
                {
                    apc_delete($this->addPrefix($tag));
                }

                break;

            case \OW_CacheManager::CLEAN_MATCH_TAGS:
                break;

            case \OW_CacheManager::CLEAN_NOT_MATCH_TAGS:
                break;

            case \OW_CacheManager::CLEAN_OLD:
                break;
        }
    }

    /**
     * @return type
     */
    public static function getNamespace()
    {
        return "apc";
    }

    private function addPrefix( $key )
    {
        return self::getNamespace() . "." . $key;
    }

    private function generateNewTagVersion()
    {
        static $counter = 0;
        return md5(microtime() . getmypid() . uniqid("") . $counter++);
    }

    public static function checkAvailability()
    {
        return extension_loaded("apc");
    }

    public static function checkIfConfigured()
    {
        return true;
    }

    public static function getStats()
    {
        if ( !self::checkAvailability() )
        {
            return array();
        }

        $data = apc_cache_info("user");

        $hits = "NA";

        if ( array_key_exists("nhits", $data) )
        {
            $hits = (int) $data["nhits"];
        }
        else if ( array_key_exists("num_hits", $data) )
        {
            $hits = (int) $data["num_hits"];
        }

        $misses = "NA";

        if ( array_key_exists("nmisses", $data) )
        {
            $misses = (int) $data["nmisses"];
        }
        else if ( array_key_exists("num_misses", $data) )
        {
            $misses = (int) $data["num_misses"];
        }

        $entries = "NA";

        if ( array_key_exists("nentries", $data) )
        {
            $entries = (int) $data["nentries"];
        }
        else if ( array_key_exists("num_entries", $data) )
        {
            $entries = (int) $data["num_entries"];
        }

        $size = "NA";

        if ( array_key_exists("mem_size", $data) )
        {
            $size = round($data["mem_size"] / 1024 / 1024, 2);
        }

        return array(
            "hits" => $hits,
            "misses" => $misses,
            "size" => $size,
            "entries" => $entries
        );
    }

    public static function getRequirements()
    {
        return \oacompress\bol\Service::getInstance()->text(self::getNamespace() . "_requirements_text");
    }
}
