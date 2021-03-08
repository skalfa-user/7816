<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Data Transfer Object for `ocsfavorites_favorite` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.bol
 * @since 1.5.3
 */
class OCSFAVORITES_BOL_Favorite extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $favoriteId;
    /**
     * @var int
     */
    public $addTimestamp;
    /**
     * @var int
     */
    public $viewed = 0;
}