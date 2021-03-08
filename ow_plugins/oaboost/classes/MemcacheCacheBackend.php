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

namespace oacompress\classes;

use oacompress\bol\Service as Service;

/**
 * @author Madumarov Sardar <madumarov@gmail.com>
 * @since 1.0
 */
class MemcacheCacheBackend implements IStorage
{
    /**
     * @var Memcache
     */
    private $memcache;

    public function __construct( $host = null, $port = null )
    {
        $this->memcache = self::getMemcache($host, $port);
    }

    public function save( $data, $key, array $tags = array(), $lifeTime )
    {
        if ( empty($this->memcache) || empty($key) || $this->test($key) )
        {
            return;
        }

        $key = $this->addPrefix($key);

        $tagsWithVersion = array();

        foreach ( $tags as $tag )
        {
            $tagWithPrefix = $this->addPrefix($tag);
            $tagVersion = $this->memcache->get($tagWithPrefix);

            if ( $tagVersion === false )
            {
                $tagVersion = $this->generateNewTagVersion();
                $this->memcache->set($tagWithPrefix, $tagVersion);
            }

            $tagsWithVersion[$tag] = $tagVersion;
        }

        $compiledData = serialize(array($tagsWithVersion, $data));

        $this->memcache->set($key, $compiledData, 0, $lifeTime);
        return true;
    }

    public function test( $key )
    {
        if ( empty($this->memcache) || empty($key) )
        {
            return false;
        }

        return $this->load($key) !== null;
    }

    public function load( $key )
    {
        if ( !$this->memcache || empty($key) )
        {
            return null;
        }

        $key = $this->addPrefix($key);
        $data = $this->memcache->get($key);

        if ( $data === false )
        {
            return null;
        }

        $data = unserialize($data);

        foreach ( $data[0] as $tag => $version )
        {
            $tagVersion = $this->memcache->get($this->addPrefix($tag));

            if ( $tagVersion === false || $tagVersion != $version )
            {
                $this->memcache->delete($key);
                return null;
            }
        }

        return $data[1];
    }

    public function remove( $key )
    {
        if ( !$this->memcache || empty($key) )
        {
            return;
        }

        return $this->memcache->delete($this->addPrefix($key));
    }

    public function clean( array $tags, $mode )
    {
        if ( !$this->memcache || empty($mode) )
        {
            return false;
        }

        switch ( $mode )
        {
            case \OW_CacheManager::CLEAN_ALL:
                $this->memcache->flush();
                break;

            case \OW_CacheManager::CLEAN_MATCH_ANY_TAG:
                if ( !$tags )
                {
                    return false;
                }

                foreach ( $tags as $tag )
                {
                    $this->memcache->delete($this->addPrefix($tag));
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
        return "mcd";
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

    /**
     * @param string $host
     * @param int $port
     * @return \Memcache
     */
    private static function getMemcache( $host = null, $port = null )
    {
        if ( empty($host) || empty($port) )
        {
            $attrs = Service::getInstance()->getConfig(Service::CNFG_MEMCACHED_ATTRS);

            $attrArr = json_decode($attrs, true);

            $host = empty($attrArr["host"]) ? "" : $attrArr["host"];
            $port = empty($attrArr["port"]) ? "" : $attrArr["port"];

            if ( empty($host) || empty($port) )
            {
                return null;
            }
        }

        $md = new \Memcache();
        $md->addServer($host, $port);

        return $md;
    }

    public static function checkAvailability()
    {
        return extension_loaded("memcache");
    }

    public static function checkIfConfigured( array $params = null )
    {
        if ( !self::checkAvailability() )
        {
            return false;
        }

        if ( $params === null )
        {
            $params = json_decode(Service::getInstance()->getConfig(Service::CNFG_MEMCACHED_ATTRS), true);
        }

        if ( empty($params["host"]) || empty($params["port"]) )
        {
            return false;
        }

        $md = self::getMemcache($params["host"], $params["port"]);
        return $md !== null;
    }

    public static function getStats()
    {
        $md = self::getMemcache();

        if ( $md === null )
        {
            return array();
        }

        $jsonAttr = json_decode(Service::getInstance()->getConfig(Service::CNFG_MEMCACHED_ATTRS), true);

        $data = $md->getStats();

        return array(
            "hits" => $data["get_hits"],
            "misses" => $data["get_misses"],
            "size" => round($data["bytes"] / 1024 / 1024, 2),
            "entries" => $data["curr_items"]
        );
    }

    public static function getRequirements()
    {
        return Service::getInstance()->text("mcd_requirements_text");
    }
}
