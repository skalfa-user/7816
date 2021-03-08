<?php

/**
 * User list
 */
class CUSTOMINDEX_CMP_UserList extends BASE_CMP_UserList
{
    /**
     * Get users component
     * 
     * @param array $list
     * @return \BASE_CMP_AvatarUserList
     */
    protected  function getUsersCmp( array $list )
    {
        return new CUSTOMINDEX_CMP_AvatarUserList($list);
    }
}