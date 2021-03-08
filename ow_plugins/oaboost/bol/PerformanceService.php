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
final class PerformanceService extends AbstractService
{
    /**
     * @var array
     */
    private $slowTimeConfigInMS;

    /**
     * @var array
     */
    private $slowItemsToInsert = array();

    /**
     * @var SlowItemDao
     */
    private $slowItemDao;

    /**
     * @var PerformanceService 
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PerformanceService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct("oacompress");

        $this->enableDboProfiler();

        $this->slowTimeConfigInMS = array(
            SlowItemDao::FIELD_VALUE_DATA_QUERY => 1,
            SlowItemDao::FIELD_VALUE_DATA_CMP_CONSTRUCT => 1,
            SlowItemDao::FIELD_VALUE_DATA_PLUGIN_INIT => 1,
            SlowItemDao::FIELD_VALUE_DATA_ITEM_RENDER => 1,
            SlowItemDao::FIELD_VALUE_DATA_ACTION_CALL => 1
        );
    }

    public function registerProfilerEvent( array $data )
    {
        if ( empty($data["key"]) )
        {
            return;
        }

        $key = $data["key"];

        switch ( $key )
        {
            case "plugin_init.start":
            case "plugin_init.end":

                $profiler = UTIL_Profiler::getInstance("oacompress.performance_" . $data["pluginKey"]);

                if ( $key == "plugin_init.end" )
                {
                    $this->addSlowItemToInsert(SlowItemDao::FIELD_VALUE_DATA_PLUGIN_INIT, $profiler->getTotalTime(), array("pluginKey" => $data["pluginKey"]));
                    $profiler->reset();
                }
                break;

            case "component_construct.start":
            case "component_construct.end":

                $inParams = $data["params"];
                $this->arrayKsort($inParams);
                $cmpKey = md5(json_encode($inParams));
                $profiler = UTIL_Profiler::getInstance("oacompress.performance_" . $cmpKey);

                if ( $key == "component_construct.end" )
                {
                    $this->addSlowItemToInsert(SlowItemDao::FIELD_VALUE_DATA_CMP_CONSTRUCT, $profiler->getTotalTime(), $inParams);
                    $profiler->reset();
                }
                break;

            case "controller_call.start":
            case "controller_call.end":

                $attrs = $data["handlerAttrs"];
                $this->arrayKsort($attrs);
                $profKey = md5(json_encode($attrs));
                $profiler = UTIL_Profiler::getInstance("oacompress.performance_" . $profKey);

                if ( $key == "controller_call.end" )
                {
                    $this->addSlowItemToInsert(SlowItemDao::FIELD_VALUE_DATA_ACTION_CALL, $profiler->getTotalTime(), $attrs);
                    $profiler->reset();
                }
                break;

            case "renderable_render.start":
            case "renderable_render.end":

                $inParams = $data["params"];
                $this->arrayKsort($inParams);
                $cmpKey = md5(json_encode($inParams));
                $profiler = UTIL_Profiler::getInstance("oacompress.performance_" . $cmpKey);


                if ( $data["key"] == "renderable_render.end" )
                {
                    $this->addSlowItemToInsert(SlowItemDao::FIELD_VALUE_DATA_ITEM_RENDER, $profiler->getTotalTime(), $inParams);
                    $profiler->reset();
                }
                break;
        }
    }

    public function processLocalData()
    {
        $queryLog = \OW::getDbo()->getQueryLog();
        
        foreach ( $queryLog as $item )
        {
            $execTime = $item["execTime"] * 1000;

            if ( $execTime > $this->slowTimeConfigInMS[SlowItemDao::FIELD_VALUE_DATA_QUERY] )
            {
                //printVar($execTime);
                $this->addSlowItemToInsert(SlowItemDao::FIELD_VALUE_DATA_QUERY, $execTime, $item);
            }
        }

        // write data to db
        //printVar($this->slowItemsToInsert);        
    }

    private function arrayKsort( array &$array )
    {
        ksort($array);
        foreach ( $array as $subArray )
        {
            if ( is_array($subArray) )
            {
                $this->arrayKsort($subArray);
            }
        }
    }

    private function enableDboProfiler()
    {
        $dbo = \OW::getDbo();

        $propArr = array(
            "isProfilerEnabled" => true,
            "profiler" => \UTIL_Profiler::getInstance('db'),
            "queryCount" => 0,
            "queryExecTime" => 0,
            "totalQueryExecTime" => 0,
            "queryLog" => array()
        );

        foreach ( $propArr as $propName => $proVal )
        {
            $prop = new \ReflectionProperty("\OW_Database", $propName);
            $prop->setAccessible(true);
            $prop->setValue($dbo, $proVal);
        }
    }
    /* slow items */

    public function addSlowItemToInsert( $type, $time, array $data )
    {
        if ( !isset($this->slowTimeConfigInMS[$type]) )
        {
            return;
        }

        //convert seconds to miliseconds
        $time = intval($time * 1000);

        if ( $time > $this->slowTimeConfigInMS[$type] )
        {
            $this->slowItemsToInsert[] = array(
                "type" => $type,
                "time" => $time,
                "data" => $data
            );
        }
    }

//    public function __destruct()
//    {
//        if ( empty($this->slowItemsToInsert) )
//        {
//            return;
//        }
//
//        //$this->getDboNewInstance()->batchInsertOrUpdateObjectList(\OACOMPRESS_BOL_SlowItemDao::getInstance()->getTableName(), $this->slowItemsToInsert);
//    }
}
