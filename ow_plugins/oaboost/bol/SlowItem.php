<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

namespace oacompress\bol;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.5
 */
class SlowItem extends \OW_Entity
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $data;

    /**
     * @var int
     */
    public $timeStamp;

    /**
     * @var int
     */
    public $time;

    /**
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @return int
     */
    function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $type
     */
    function setType( $type )
    {
        $this->type = $type;
    }

    /**
     * @param string $data
     */
    function setData( $data )
    {
        $this->data = $data;
    }

    /**
     * @param int $timeStamp
     */
    function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = $timeStamp;
    }

    /**
     * @param int $time
     */
    function setTime( $time )
    {
        $this->time = $time;
    }
}
