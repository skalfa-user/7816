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
class MEMBERX_BOL_SearchIdDao extends OW_BaseDao{
    
    private static $classInstance;
    
    
    /**
     * 
     * @return MEMBERX_BOL_SearchIdDao
     */
    public static function getInstance() {
        if (self::$classInstance === null){
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }
   
    
    public function getDtoClassName()
    {
        return 'MEMBERX_BOL_SearchId';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'memberx_'. 'search_id';
    }
    
    
    
}
