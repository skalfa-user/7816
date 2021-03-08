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
class Item extends \OW_Entity
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $fileList;

    /**
     * @var boolean
     */
    public $status;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( $type )
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getFileList()
    {
        return $this->fileList;
    }

    /**
     * @param array $fileList
     */
    public function setFileList( $fileList )
    {
        $this->fileList = $fileList;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return (int) $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus( $status )
    {
        $this->status = (int) $status;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash( $hash )
    {
        $this->hash = $hash;
    }
}
