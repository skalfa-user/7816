<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.5
 */

namespace oacompress\classes;

use oacompress\bol\Service as Service;

class EventHandler
{
    const QUIRK_CACHE_EXP_TIME = 3600;
    // max size of data to be cached in bytes
    const QUIRK_CACHE_DATA_MAX_SIZE = 1048576;
    // avg data size for int|bool|float
    const QUIRK_CACHE_DATA_PRIMITIVE_AVG_SIZE = 15;
    const QUIRK_CACHE_TYPE_SELECT = "se";
    const QUIRK_CACHE_TYPE_DELETE = "de";
    const QUIRK_CACHE_TYPE_INSERT = "in";
    const QUIRK_CACHE_TYPE_UPDATE = "up";
    const QUIRK_CACHE_TYPE_REPLACE = "re";
    const QUIRK_CACHE_TYPE_ALTER = "al";
    const QUIRK_CACHE_TYPE_SHOW = "sh";
    const QUIRK_CACHE_TYPE_DROP = "dr";
    const QUIRK_CACHE_TYPE_TRUNCATE = "tr";
    const QUIRK_CACHE_TYPE_CREATE = "cr";

    /**
     * @var array
     */
    private static $readQeuryTypeList = array(
        self::QUIRK_CACHE_TYPE_SELECT,
        self::QUIRK_CACHE_TYPE_SHOW
    );

    /**
     * @var array
     */
    private static $criticalQeuryTypeList = array(
        self::QUIRK_CACHE_TYPE_INSERT,
        self::QUIRK_CACHE_TYPE_DELETE,
        self::QUIRK_CACHE_TYPE_UPDATE,
        self::QUIRK_CACHE_TYPE_REPLACE,
        self::QUIRK_CACHE_TYPE_ALTER,
        self::QUIRK_CACHE_TYPE_CREATE,
        self::QUIRK_CACHE_TYPE_DROP,
        self::QUIRK_CACHE_TYPE_TRUNCATE
    );

    /**
     * @var \OW_ICacheBackend 
     */
    private $cacheBackend;

    /**
     * @var array
     */
    private $blockQuirkCachingForTables;

    /**
     * @var bool
     */
    private $blockQueryCache = false;

    /**
     *
     * @var Service
     */
    private $service;

    /**
     * @var int
     */
    private $cacheLifeTime;

    /**
     * @var bool 
     */
    private $queryCacheEnabled;

    /**
     * @var bool
     */
    private $pageCacheEnabled;

    /**
     * @var \OACOMPRESS_BOL_PerformanceService
     */
    private $performanceService;

    /**
     * @var bool
     */
    private $htmlCompressAvailable = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service = Service::getInstance();
        $this->performanceService = \oacompress\bol\PerformanceService::getInstance();
        $this->cacheLifeTime = (int) $this->service->getConfig(Service::CNFG_CACHE_DB_QUERIES);
        $this->queryCacheEnabled = $this->cacheLifeTime > 0;
        $this->pageCacheEnabled = (int) $this->service->getConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS) > 0;
        $this->htmlCompressAvailable = $this->service->isHtmlCompressionAvailable();

        if ( OW_DEV_MODE )
        {
            $this->queryCacheEnabled = false;
            $this->pageCacheEnabled = false;
            $this->htmlCompressAvailable = false;
        }

        //set cache backend
        if ( \OW::getConfig()->configExists($this->service->getPluginKey(), Service::CNFG_CACHE_STORAGE) )
        {
            $key = $this->service->getConfig(Service::CNFG_CACHE_STORAGE);
            $storages = $this->service->getStorages();

            if ( !empty($storages[$key]["class"]) && call_user_func(array($storages[$key]["class"], "checkAvailability")) && call_user_func(array(
                    $storages[$key]["class"], "checkIfConfigured")) )
            {
                $this->cacheBackend = new $storages[$key]["class"]();
            }
            else
            {
                $this->service->saveConfig(Service::CNFG_CACHE_STORAGE, "");
            }
        }
        $this->blockQuirkCachingForTables = array(
            "oacompress_cache_item",
            "oacompress_cache_tag_item",
        );

        $addTablesToExclude = explode(",", $this->service->getConfig("sql_cache_exclude_table"));

        if ( $addTablesToExclude )
        {
            foreach ( $addTablesToExclude as $addTable )
            {
                $this->blockQuirkCachingForTables[] = trim($addTable);
            }
        }
    }

    public function init()
    {
        $eventManager = \OW::getEventManager();

        $eventManager->bind(\OW_EventManager::ON_AFTER_ROUTE, array($this, "onAfterRoute"));
        $eventManager->bind(\OW_EventManager::ON_AFTER_PLUGIN_UPDATE, array($this, "dropStorageCache"));

        $this->genericInit();
    }

    public function mobileInit()
    {
        $this->genericInit();
    }

    private function genericInit()
    {
        $eventManager = \OW::getEventManager();
        $eventManager->bind("core.after_master_page_render", array($this, "compressHandler"));
        $eventManager->bind("base.update_cache_entities", array($this, "clearCompressedCache"));
        $eventManager->bind("base.update_custom_css_file", array($this, "clearCompressedCache"));
        $eventManager->bind("core.sql.get_query_result", array($this, "quirkCacheGetHandler"));
        $eventManager->bind("core.sql.set_query_result", array($this, "quirkCacheSetHandler"));
        $eventManager->bind("core.sql.exec_query", array($this, "quirkCacheExecHandler"));
        $eventManager->bind("core.performance_test", array($this, "performanceHandler"));
        //$eventManager->bind("core.exit", array($this, "mainPerformanceHandler"));
        $eventManager->bind("core.exit", array($this, "flushHtmlGz"));

        if ( !\OW::getUser()->isAuthenticated() )
        {
            $eventManager->bind(\OW_EventManager::ON_AFTER_DOCUMENT_RENDER, array($this, "pageSetHandler"));
            $eventManager->bind(\OW_EventManager::ON_AFTER_ROUTE, array($this, "pageGetHandler"));
        }

        $this->clearPluginCache();
        $this->startHtmlGz();
    }

    /**
     * Handler to disable caching for admin panel
     */
    public function onAfterRoute()
    {
        $params = \OW::getRequestHandler()->getHandlerAttributes();

        if ( !empty($params[\OW_RequestHandler::ATTRS_KEY_CTRL]) && is_subclass_of($params[\OW_RequestHandler::ATTRS_KEY_CTRL], "ADMIN_CTRL_Abstract") )
        {
            $this->blockQueryCache = true;
            $this->htmlCompressAvailable = false;
        }
    }

    public function startHtmlGz()
    {
        if ( !$this->htmlCompressAvailable )
        {
            return;
        }

        ob_start();
    }

    public function flushHtmlGz()
    {
        if ( !$this->htmlCompressAvailable )
        {
            return;
        }

        $this->sendCompressedContent(ob_get_clean());
    }

    private function sendCompressedContent( $content )
    {
        header("Content-Encoding: gzip");
        echo(gzcompress($content, -1, ZLIB_ENCODING_GZIP));
    }

    /**
     * Cleares cache for all storages
     */
    public function dropStorageCache()
    {
        $this->service->dropAllStoragesCache();
    }

    public function quirkCacheExecHandler( \OW_Event $e )
    {
        $params = $e->getParams();
        $queryType = $this->getQueryType($params["sql"]);

        if ( !in_array($queryType, self::$criticalQeuryTypeList) )
        {
            return;
        }

        $tableTags = $this->getTableTags($params["sql"]);

        if ( empty($tableTags) || array_intersect($this->blockQuirkCachingForTables, $tableTags) )
        {
            return;
        }

        if ( $this->protectFromClean($params["sql"], $params["params"], $tableTags) )
        {
            return;
        }

        if ( $this->cacheBackend !== null )
        {
            try
            {
                $this->cacheBackend->clean($tableTags, \OW_CacheManager::CLEAN_MATCH_ANY_TAG);
            }
            catch ( Exception $e )
            {
                
            }
        }
    }

    public function quirkCacheGetHandler( \OW_Event $e )
    {
        if ( !$this->queryCacheEnabled || $this->blockQueryCache || $this->cacheBackend == null )
        {
            return array("result" => false);
        }

        $params = $e->getParams();
        $queryType = $this->getQueryType($params["sql"]);

        if ( !in_array($queryType, self::$readQeuryTypeList) )
        {
            return array("result" => false);
        }

        $tableTags = $this->getTableTags($params["sql"]);

        if ( empty($tableTags) || array_intersect($this->blockQuirkCachingForTables, $tableTags) )
        {
            return array("result" => false);
        }

        $this->processSqlParams($params["sql"], $params["params"], $tableTags);

        $key = $this->getKey($params["sql"], $params["params"]);

        $result = $this->cacheBackend->load($key);

        if ( $result !== null )
        {
            return array("value" => unserialize($result), "result" => true);
        }

        return array("result" => false);
    }

    public function quirkCacheSetHandler( \OW_Event $e )
    {
        if ( !$this->queryCacheEnabled || $this->blockQueryCache || $this->cacheBackend == null )
        {
            return;
        }

        $params = $e->getParams();

        if ( !in_array($this->getQueryType($params["sql"]), self::$readQeuryTypeList) )
        {
            return;
        }

        $tableTags = $this->getTableTags($params["sql"]);

        if ( empty($tableTags) || array_intersect($this->blockQuirkCachingForTables, $tableTags) )
        {
            return;
        }

        if ( !$this->checkSizeLimit($params["result"]) )
        {
            return;
        }

        $serializedData = serialize($params["result"]);

        $this->processSqlParams($params["sql"], $params["params"], $tableTags);

        $this->cacheBackend->save($serializedData, $this->getKey($params["sql"], $params["params"]), $tableTags, self::QUIRK_CACHE_EXP_TIME);
    }

    private function checkSizeLimit( $data )
    {
        if ( is_int($data) || is_bool($data) || is_float($data) )
        {
            return self::QUIRK_CACHE_DATA_PRIMITIVE_AVG_SIZE;
        }

        $size = 0;

        if ( is_string($data) )
        {
            $size = mb_strlen($data, "8bit");

            if ( $size > self::QUIRK_CACHE_DATA_MAX_SIZE )
            {
                return false;
            }

            return $size;
        }

        if ( is_object($data) || is_array($data) )
        {
            foreach ( $data as $key => $value )
            {
                $result = is_object($data) ? $this->checkSizeLimit($data->$key) : $this->checkSizeLimit($data[$key]);

                if ( $result === false )
                {
                    return false;
                }

                $size += $result;

                if ( $size > self::QUIRK_CACHE_DATA_MAX_SIZE )
                {
                    return false;
                }
            }
        }

        return $size;
    }

    // spec hack to clean params from dynamic values
    private function processSqlParams( $sql, &$params, $tableTags )
    {
        if ( in_array("event_item", $tableTags) )
        {
            if ( isset($params["startTime"]) )
            {
                unset($params["startTime"]);
            }

            if ( isset($params["endTime"]) )
            {
                unset($params["endTime"]);
            }
        }

        if ( in_array("newsfeed_action", $tableTags) )
        {
            if ( isset($params["st"]) )
            {
                unset($params["st"]);
            }
        }
    }

    // spec hack to prvent ow_base_user cache invalidation
    private function protectFromClean( $sql, $params, $tableTags )
    {
        if ( mb_stristr($sql, "UPDATE LOW_PRIORITY `" . \BOL_UserDao::getInstance()->getTableName()) )
        {
            return true;
        }

        if ( mb_stristr($sql, "UPDATE LOW_PRIORITY `" . \BOL_UserOnlineDao::getInstance()->getTableName()) )
        {
            return true;
        }

        return false;
    }

    private function getQueryType( $sql )
    {
        return mb_strtolower(mb_substr(trim($sql), 0, 2));
    }

    private function getTableTags( $sql )
    {
        $tables = array();
        preg_match_all("/" . str_replace("_", "\_", OW_DB_PREFIX) . "[A-Za-z0-9\_]+/", $sql, $tables);

        $tables = array_unique($tables[0]);
        $prefixCount = mb_strlen(OW_DB_PREFIX);

        foreach ( $tables as $key => $table )
        {
            if ( mb_substr($table, 0, $prefixCount) != OW_DB_PREFIX )
            {
                unset($tables[$key]);
            }
            else
            {
                $tables[$key] = mb_substr($table, $prefixCount);
            }
        }

        return $tables;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return string
     */
    private function getKey( $sql, $params )
    {
        if ( !is_array($params) )
        {
            $params = array();
        }

        ksort($params);
        return md5(OW_URL_HOME . trim($sql) . serialize($params)) . "qc";
    }

    public function clearCompressedCache()
    {
        $this->service->markAllExpired();
    }

    public function compressHandler()
    {
        if ( OW_DEV_MODE || \OW::getRequest()->isAjax() || \OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            return;
        }

        $this->service->processPage(\OW::getDocument());
    }

    public function pageSetHandler()
    {
        if ( !$this->pageHandlerEnable() )
        {
            return;
        }

        $response = \OW::getResponse();
        $handlerAttrs = \OW::getRequestHandler()->getHandlerAttributes();

        $key = $this->getPageHandlerKey($handlerAttrs);

        $refObj = new \ReflectionObject($response);
        $refProp = $refObj->getProperty("headers");
        $refProp->setAccessible(true);

        $data = array(
            "handlerAttrs" => $handlerAttrs,
            "headers" => $refProp->getValue($response),
            "markup" => $response->getMarkup()
        );

        $this->cacheBackend->save(serialize($data), $key, array("oa.page.cache"), (int) $this->service->getConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS) * 60);
    }

    public function pageGetHandler()
    {
        if ( !$this->pageHandlerEnable() )
        {
            return;
        }

        $handlerAttrs = \OW::getRequestHandler()->getHandlerAttributes();
        $key = $this->getPageHandlerKey($handlerAttrs);

        if ( $this->cacheBackend->test($key) )
        {
            $data = unserialize($this->cacheBackend->load($key));

            if ( !empty($data["handlerAttrs"]) && $handlerAttrs == $data["handlerAttrs"] )
            {
                $cacheTimeInSeconds = intval($this->service->getConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS)) * 60;

                $this->sendHeaders($data["headers"]);
                header("Cache-Control:max-age={$cacheTimeInSeconds}");

                if ( $this->htmlCompressAvailable )
                {
                    $this->sendCompressedContent($data["markup"]);
                }
                else
                {
                    echo $data["markup"];
                }
                exit();
            }
        }
    }

    /**
     * @return boolean
     */
    private function pageHandlerEnable()
    {
        // check basic requirements
        if ( !$this->pageCacheEnabled || \OW::getUser()->isAuthenticated() || empty($this->cacheBackend) )
        {
            return false;
        }

        // check request type
        if ( \OW::getRequest()->isPost() || \OW::getRequest()->isAjax() )
        {
            return false;
        }

        // check excludes
        if ( $this->skipPageCache(\OW::getRequestHandler()->getHandlerAttributes()) )
        {
            return false;
        }

        return true;
    }

    /**
     * @param array $handlerAttrs
     * @return string
     */
    private function getPageHandlerKey( array $handlerAttrs )
    {
        return md5(OW_URL_HOME . serialize($handlerAttrs));
    }

    public function performanceHandler( \OW_Event $e )
    {
        return;
        $this->performanceService->registerProfilerEvent($e->getParams());
    }

    public function mainPerformanceHandler()
    {
        return;
        $this->performanceService->processLocalData();
    }

    private function sendHeaders( $headers )
    {
        if ( !headers_sent() )
        {
            foreach ( $headers as $headerName => $headerValue )
            {
                if ( substr(mb_strtolower($headerName), 0, 4) === 'http' )
                {
                    header($headerName . ' ' . $headerValue);
                }
                else if ( mb_strtolower($headerName) === 'status' )
                {
                    header(ucfirst(mb_strtolower($headerName)) . ': ' . $headerValue, null, (int) $headerValue);
                }
                else
                {
                    header($headerName . ':' . $headerValue);
                }
            }
        }
    }

    private function clearPluginCache()
    {
        //TODO check if there are any logic duplication
        if ( (bool) $this->service->getConfig("mark_all_expired") )
        {
            $this->clearCompressedCache();
            $this->service->saveConfig('mark_all_expired', 0);
        }

        if ( (bool) $this->service->getConfig(Service::CNFG_CLEAR_PLUGIN_CACHE) )
        {
            $this->service->markAllExpired();
            $this->service->dropAllStoragesCache();

            $this->service->saveConfig(Service::CNFG_CLEAR_PLUGIN_CACHE, 0);
        }
    }
    private $skipCachingForItems = array(
        array(
            \OW_RequestHandler::ATTRS_KEY_CTRL => "BASE_CTRL_Join"
        )
    );

    private function skipPageCache( array $handlerAttributes )
    {
        foreach ( $this->skipCachingForItems as $exclude )
        {
            if ( $exclude[\OW_RequestHandler::ATTRS_KEY_CTRL] == $handlerAttributes[\OW_RequestHandler::ATTRS_KEY_CTRL] )
            {
                if ( empty($exclude[\OW_RequestHandler::ATTRS_KEY_ACTION]) || $exclude[\OW_RequestHandler::ATTRS_KEY_ACTION] == $this->handlerAttributes[\OW_RequestHandler::ATTRS_KEY_ACTION] )
                {
                    if ( empty($exclude[\OW_RequestHandler::ATTRS_KEY_VARLIST]) || $exclude[\OW_RequestHandler::ATTRS_KEY_VARLIST] == $this->handlerAttributes[\OW_RequestHandler::ATTRS_KEY_VARLIST] )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
