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

class MEMBERX_BOL_EventDao extends EVENT_BOL_EventDao{
    
    private static $classInstance;
    
    
    /**
     * 
     * @return MEMBERX_BOL_EventDao
     */
    public static function getInstance() {
        if (self::$classInstance === null){
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }
    
    /**
     * @param integer $userId
     * @return array<EVENT_BOL_Event>
     */
    public function findLatestUserInvitedEvents( $userId, $count )
    {
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            INNER JOIN `" . EVENT_BOL_EventInviteDao::getInstance()->getTableName() . "` AS `ei` ON ( `e`.`id` = `ei`.`" . EVENT_BOL_EventInviteDao::EVENT_ID . "` )
            WHERE `e`.status = 1 AND `ei`.`" . EVENT_BOL_EventInviteDao::USER_ID . "` = :userId AND " . $this->getTimeClause(false, 'e') . "
            GROUP BY `ei`.`id` DESC LIMIT :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => (int) $userId, 'count' => (int) $count, 'startTime' => time(), 'endTime' => time()));
    }
    
    
    
    
    
    /**
     * Returns events with user status.
     *
     * @param integer $userId
     * @param integer $userStatus
     * @param integer $first
     * @param inetger $count
     * @return array
     */
    public function findLatestUserEventsWithStatus( $userId, $userStatus, $count, $addUnapproved = false )
    {
        $where = ' 1 ';
        
        if ( $addUnapproved )
        {
             $where = ' `e`.status = 1 ';
        }
        
        $query = "SELECT `e`.* FROM `" . $this->getTableName() . "` AS `e`
            LEFT JOIN `" . EVENT_BOL_EventUserDao::getInstance()->getTableName() . "` AS `eu` ON (`e`.`id` = `eu`.`eventId`)
            WHERE $where AND `eu`.`userId` = :userId AND `eu`.`" . EVENT_BOL_EventUserDao::STATUS . "` = :status AND " . $this->getTimeClause(false, 'e') . "
            ORDER BY `eu`.`id` DESC LIMIT :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('userId' => $userId, 'status' => $userStatus, 'count' => $count, 'startTime' => time(), 'endTime' => time()));
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
    
    
    private function getTimeClause( $past = false, $alias = null )
    {
        if ( $past )
        {
            return "( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::START_TIME_STAMP . "` <= :startTime AND ( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` IS NULL OR " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` <= :endTime ) )";
        }

        return "( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::START_TIME_STAMP . "` > :startTime OR ( " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` IS NOT NULL AND " . (!empty($alias) ? "`{$alias}`." : "" ) . "`" . self::END_TIME_STAMP . "` > :endTime ) )";
    }
}