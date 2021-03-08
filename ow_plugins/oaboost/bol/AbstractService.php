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
 */

namespace oacompress\bol;

abstract class AbstractService
{
    protected $key;
    protected $configs = array();

    protected function __construct( $key )
    {
        $this->key = trim($key);
    }

    /**
     * @param string $name
     * @return string
     */
    public function text( $name, $vars = array() )
    {
        return \OW::getLanguage()->text($this->key, $name, $vars);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getConfig( $name )
    {
        return \OW::getConfig()->getValue($this->key, $name);
    }

    /**
     * @param string $name
     * @param mixed $val
     */
    public function saveConfig( $name, $val )
    {
        \OW::getConfig()->saveConfig($this->key, $name, $val);
    }

    /**
     * @return string
     */
    public function getPluginKey()
    {
        return $this->key;
    }

    /**
     * @return \OW_Plugin
     */
    public function getPlugin()
    {
        return \OW::getPluginManager()->getPlugin($this->getPluginKey());
    }
}
