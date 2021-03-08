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

class MEMBERX_ACTRL_AndroidBase extends SKANDROID_ACTRL_Base{
    
    public function siteInfo(array $params = array()) {

        parent::siteInfo($params);
        
        
        if (!OW::getPluginManager()->isPluginActive('usearch') && OW::getPluginManager()->isPluginActive('memberx')){
            $menuInfo = $this->assignedVars['menuInfo'];
            $searchMenu = array(array("type" => SKANDROID_ABOL_Service::MENU_TYPE_MAIN, "key" => "search", "count" => 0));
            array_splice($menuInfo, 1, 0, $searchMenu);
            $this->assign('menuInfo', $menuInfo);
        }

    }
    
}