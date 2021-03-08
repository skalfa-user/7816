<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class HIDEADMIN_BOL_Service
{
    use OW_Singleton;

    /**
     * Get admin ids
     *
     * @return array
     */
    public function getAdminsIds()
    {
        $moderators = BOL_AuthorizationService::getInstance()->getModeratorList();

        $usersIds = array();
        foreach ( $moderators as $moderator )
        {
            $usersIds[] = $moderator->userId;
        }

        return $usersIds;
    }
}
