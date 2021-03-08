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

class MEMBERX_BOL_GroupDao extends GROUPS_BOL_GroupDao{
    
    private static $classInstance;
    
    
    /**
     * 
     * @return MEMBERX_BOL_GroupDao
     */
    public static function getInstance() {
        if (self::$classInstance === null){
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }
    
    
    public function findUserRecentlyInvitedGroups( $userId, $count )
    {
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . GROUPS_BOL_InviteDao::getInstance()->getTableName() . "` AS `ei` ON ( `e`.`id` = `ei`.`groupId` )
            WHERE `e`.`status` = 'active' AND `ei`.`userId` = :userId 
            GROUP BY `ei`.`id` DESC LIMIT :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => (int) $userId, 'count' => (int) $count));
    }
    
    
    public function findUserRecentlyJoinedGroups( $userId, $count )
    {

        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . GROUPS_BOL_GroupUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`groupId`)
            WHERE `e`.`status` = 'active' AND `eu`.`userId` = :userId 
            ORDER BY `eu`.`id` DESC LIMIT :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => $userId, 'count' => $count, ));
    }
    
    
    
    /**
     * Returns user created events.
     *
     * @param integer $userId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestUserCreatedEvents( $userId, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldEqual(self::STATUS, 1);
        $example->setOrder(self::START_TIME_STAMP );
        $example->andFieldGreaterThan(self::START_TIME_STAMP, time());
        $example->setOrder('`id` DESC');
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }
    
    
    public function findInvitableGroups( $userId, $first = null, $count = null )
    {
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();

        $limit = '';
        if ( $first !== null && $count !== null )
        {
            $limit = "LIMIT $first, $count";
        }

        $query = "SELECT g.* FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE u.userId=:u AND g.status=:s AND (`g`.`userId` = :u OR `g`.`whoCanInvite` = 'participant') ORDER BY `g`.`id` DESC " . $limit;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => $userId,
            's' => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }
    
    public function isGroupCanInvite( $userId, $groupId )
    {
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();

        $query = "SELECT g.* FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId
            WHERE `g`.`id` = :gid AND u.userId=:u AND g.status=:s AND (`g`.`userId` = :u OR `g`.`whoCanInvite` = 'participant') ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'gid' => $groupId,
            'u' => $userId,
            's' => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }
}