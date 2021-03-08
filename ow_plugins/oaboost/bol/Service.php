<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

namespace oacompress\bol;

use oacompress\classes\FileWriteException;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.5
 */
final class Service extends AbstractService
{
    const CNFG_COMPRESS_JS = "compress_js";
    const CNFG_COMPRESS_CSS = "compress_css";
    const CNFG_ENCODE_CSS_IMAGE = "encode_image_size";
    const CNFG_MAX_FILE_SIZE = "max_file_size";
    const CNFG_COMPRESS_CONTENT = "compress_content";
    const CNFG_ITEMS_COUNT_TO_PROCESS = "count_to_process";
    /**/
    const CNFG_SQL_CACHE = "enable_sql_cache";
    /**/
    const CNFG_CACHE_DB_QUERIES = "cache_db_queries";
    const CNFG_CACHE_STORAGE = "cache_storage";
    const CNFG_T_OPTIMIZE_TS = "table_optimize_ts";
    const CNFG_T_OPTIMIZE_PERIOD = "table_optimize_period";
    const CNFG_MAX_TRASH_ITEMS_TO_DELETE = "max_trash_items_to_delete";
    const CNFG_SQL_BACKEND_HITS = "sql_backend_hits";
    const CNFG_SQL_BACKEND_MISSES = "sql_backend_misses";
    const CNFG_MEMCACHED_ATTRS = "memcached_attrs";
    const CNFG_MONGO_ATTRS = "mongo_attrs";
    const CNFG_PLUGIN_INIT = "plugin_init";
    const CNFG_GUESTS_PAGE_CACHE_STORAGE = "guest_page_cache_storage";
    const CNFG_SESSION_HANDLER_STORAGE = "guest_page_cache_storage";
    const CNFG_CACHE_PAGES_FOR_GUESTS = "sql_cache_page_for_guests";
    const CNFG_CLEAR_PLUGIN_CACHE = "clear_plugin_cache";
    const CNFG_COMPRESS_HTML = "compress_html";
    /* detecting slow items constants */
    const CNFG_LOG_SLOW_QUERY = "log_slow_query";
    const CNFG_LOG_SLOW_CMP = "log_slow_cmp";
    const CNFG_LOG_SLOW_INIT = "log_slow_init";
    const CNFG_SLOW_QUERY_TIME_LIMIT = "slow_query_time_limit";
    const CNFG_SLOW_CMP_TIME_LIMIT = "slow_cmp_time_limit";
    const CNFG_SLOW_INIT_TIME_LIMIT = "slow_init_time_limit";
    /* -------------------------------- */
    const LOG_TYPE_ERR = "error";
    const LOG_TYPE_WRN = "warning";
    const LOG_TYPE_MSG = "message";

    /* -------------------------------- */
    /**
     * @var ItemDao
     */
    private $itemDao;

    /**
     * @var SlowItemDao
     */
    private $slowItemDao;

    /**
     * @var boolean
     */
    private $clientAcceptsGzip;

    /**
     * @var \OW_Log
     */
    private $logger;

    /**
     * @var array
     */
    private $localDependencies = array(
        "underscore.js" => "underscore-min.map",
        "underscore-min.js" => "underscore-min.map",
        "backbone.js" => "backbone-min.map",
        "backbone-min.js" => "backbone-min.map"
    );

    /**
     * Singleton instance.
     *
     * @var Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return Service
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
        $this->plugin = \OW::getPluginManager()->getPlugin($this->getPluginKey());
        $this->configs[self::CNFG_MAX_FILE_SIZE] = $this->getConfig("encode_image_size");
        $this->configs[self::CNFG_ITEMS_COUNT_TO_PROCESS] = 2;
        $this->configs[self::CNFG_MAX_TRASH_ITEMS_TO_DELETE] = 5;

        $this->itemDao = ItemDao::getInstance();
        $this->slowItemDao = SlowItemDao::getInstance();
        $this->logger = \OW::getLogger($this->getPluginKey());
        $this->logger->setLogWriter(new \BASE_CLASS_FileLogWriter($this->getLogFilePath()));

        $this->clientAcceptsGzip = $this->clientAcceptsGzip($_SERVER);
    }

    public function processItems()
    {
        if ( !$this->isCompressRequirementsSatisfied() )
        {
            return;
        }

        $items = $this->itemDao->findItemsToProcess();

        $processed = 0;

        /* @var $item Item */
        foreach ( $items as $item )
        {
            if ( $processed >= $this->configs[self::CNFG_ITEMS_COUNT_TO_PROCESS] )
            {
                break;
            }

            $result = false;

            if ( $item->getType() == ItemDao::FIELD_VALUE_TYPE_JS )
            {
                $result = $this->processJsItem($item);
            }
            else if ( $item->getType() == ItemDao::FIELD_VALUE_TYPE_CSS )
            {
                $result = $this->processCssItem($item);
            }

            if ( $result )
            {
                $headers = $this->parseHeaders(get_headers($this->getCompressedUrl($item->getId(), $item->getType())));

                if ( !empty($headers["reponse_code"]) && $headers["reponse_code"] == 200 &&
                    !empty($headers["Content-Type"]) && in_array($headers["Content-Type"],
                        array("text/javascript", "text/css")) &&
                    !empty($headers["Content-Encoding"]) && $headers["Content-Encoding"] == "gzip"
                )
                {
                    $item->setStatus(ItemDao::FIELD_VALUE_STATUS_PROCESSED);
                }
                else
                {
                    $item->setStatus(ItemDao::FIELD_VALUE_STATUS_PROCESSED_BUT_NA);
                    $this->logger->addEntry("Failed to load item via http - {$item->getId()}", self::LOG_TYPE_ERR);
                }
                $this->itemDao->save($item);
            }
            else
            {
                $this->logger->addEntry("Failed to process item - {$item->getId()}", self::LOG_TYPE_ERR);
                continue;
            }

            $processed++;
        }
    }

    public function deleteNextExpiredItem()
    {
        $item = $this->itemDao->findNextExpiredItem();

        if ( $item === null )
        {
            return;
        }

        $removeEntry = true;

        if ( in_array($item->getType(), array(ItemDao::FIELD_VALUE_TYPE_RAW_CSS, ItemDao::FIELD_VALUE_TYPE_RAW_JS)) )
        {
            $dirToRemove = $this->getTempCompressedDir() . $item->getId();

            if ( file_exists($dirToRemove) )
            {
                \UTIL_File::removeDir($dirToRemove);

                if ( file_exists($dirToRemove) )
                {
                    $this->logger->addEntry("Can't remove dir - `{$dirToRemove}`", self::LOG_TYPE_ERR);
                    $removeEntry = false;
                }
            }
        }
        else
        {
            $dirToRemove = $this->getCompressedDir() . $item->getId();

            if ( $this->getStorage()->fileExists($dirToRemove) )
            {
                $this->getStorage()->removeDir($dirToRemove);

                if ( $this->getStorage()->fileExists($dirToRemove) )
                {
                    $this->logger->addEntry("Can't remove dir - `{$dirToRemove}`", self::LOG_TYPE_ERR);
                    $removeEntry = false;
                }
            }
        }

        if ( $removeEntry )
        {
            $this->itemDao->delete($item);
        }
    }

    public function deleteTrashItems()
    {
        $idList = $this->itemDao->findAllIdList();

        $storage = \OW::getStorage();

        $dirList = array_merge($storage->getFileNameList($this->getTempCompressedDir()),
            $storage->getFileNameList($this->getCompressedDir()));

        foreach ( $dirList as $dir )
        {
            if ( strstr($dir, OW_DIR_ROOT) && !in_array((int) basename($dir), $idList) )
            {
                $storage->removeDir($dir);

                if ( $storage->fileExists($dir) )
                {
                    $this->logger->addEntry("Can't remove dir - `{$dir}`", self::LOG_TYPE_ERR);
                }
            }
        }
    }
    /* Database section */

    public function optimizeTables()
    {
        if ( ((int) $this->getConfig(self::CNFG_T_OPTIMIZE_TS) + (int) $this->getConfig(self::CNFG_T_OPTIMIZE_PERIOD) > time()) || (int) $this->getConfig(self::CNFG_T_OPTIMIZE_PERIOD) === 0 )
        {
            return;
        }

        $dbo = \OW::getDbo();
        $tables = $dbo->queryForColumnList("SHOW TABLES");

        foreach ( $tables as $table )
        {
            try
            {
                $dbo->query("OPTIMIZE TABLE `{$table}`");
            }
            catch ( Exception $ex )
            {
                $this->logger->addEntry("Failed to optimize table - {$table}", self::LOG_TYPE_ERR);
            }

            try
            {
                $dbo->query("REPAIR TABLE `{$table}`");
            }
            catch ( Exception $ex )
            {
                $this->logger->addEntry("Failed to repair table - {$table}", self::LOG_TYPE_ERR);
            }
        }

        $this->logger->addEntry("Tables optimization completed", self::LOG_TYPE_MSG);
        $this->saveConfig(self::CNFG_T_OPTIMIZE_TS, time());
    }

    public function processPage( \OW_Document $document )
    {
        if ( (bool) $this->getConfig(self::CNFG_COMPRESS_JS) )
        {
            $jsData = $document->getJavaScripts();
            $headJsToAdd = array();
            $jsToAdd = array();
            $loadedJsList = array();

            ksort($jsData["items"]);
            $document->setJavaScripts(array("added" => array(), "items" => array()));

            foreach ( $jsData["items"] as $orderKey => $orderItem )
            {
                foreach ( $orderItem as $typeKey => $typeItem )
                {
                    foreach ( $typeItem as $value )
                    {
                        if ( strstr($value, '?') )
                        {
                            $valueJ = substr($value, 0, strpos($value, '?'));
                        }
                        else
                        {
                            $valueJ = $value;
                        }

                        if ( !in_array(\UTIL_File::getExtension($valueJ), array("js")) || !mb_strstr($valueJ,
                                OW_URL_HOME) || mb_strstr($valueJ, "ckeditor.js") ) // hotfix for ckeditor
                        {
                            if ( $orderKey == -100 )
                            {
                                if ( !empty($headJsToAdd) )
                                {
                                    $result = $this->getCompressedItem(array_keys($headJsToAdd),
                                        ItemDao::FIELD_VALUE_TYPE_JS);

                                    if ( $result == null )
                                    {
                                        foreach ( $headJsToAdd as $tempVal )
                                        {
                                            $document->addScript($tempVal, "text/javascript", -100);
                                        }
                                    }
                                    else
                                    {
                                        $loadedJsList = array_merge(array_keys($headJsToAdd), $loadedJsList);
                                        $document->addScript($result, "text/javascript", -100);
                                    }
                                }

                                $document->addScript($value, "text/javascript", -100);
                                $headJsToAdd = array();
                            }
                            else
                            {
                                if ( !empty($jsToAdd) )
                                {
                                    $result = $this->getCompressedItem(array_keys($jsToAdd),
                                        ItemDao::FIELD_VALUE_TYPE_JS);

                                    if ( $result == null )
                                    {
                                        foreach ( $jsToAdd as $tempVal )
                                        {
                                            $document->addScript($tempVal);
                                        }
                                    }
                                    else
                                    {
                                        $loadedJsList = array_merge(array_keys($jsToAdd), $loadedJsList);
                                        $document->addScript($result);
                                    }
                                }

                                $document->addScript($value);

                                $jsToAdd = array();
                            }

                            continue;
                        }

                        if ( $orderKey == -100 )
                        {
                            $headJsToAdd[str_replace(OW_URL_HOME, "", $valueJ)] = $value;
                        }
                        else
                        {
                            $jsToAdd[str_replace(OW_URL_HOME, "", $valueJ)] = $value;
                        }
                    }
                }
            }

            if ( !empty($headJsToAdd) )
            {
                $result = $this->getCompressedItem(array_keys($headJsToAdd), ItemDao::FIELD_VALUE_TYPE_JS);

                if ( $result == null )
                {
                    foreach ( $headJsToAdd as $tempVal )
                    {
                        $document->addScript($tempVal, "text/javascript", -100);
                    }
                }
                else
                {
                    $loadedJsList = array_merge(array_keys($headJsToAdd), $loadedJsList);
                    $document->addScript($result, "text/javascript", -100);
                }
            }

            if ( !empty($jsToAdd) )
            {
                $result = $this->getCompressedItem(array_keys($jsToAdd), ItemDao::FIELD_VALUE_TYPE_JS);

                if ( $result == null )
                {
                    foreach ( $jsToAdd as $tempVal )
                    {
                        $document->addScript($tempVal);
                    }
                }
                else
                {
                    $loadedJsList = array_merge(array_keys($jsToAdd), $loadedJsList);
                    $document->addScript($result);
                }
            }

            // need to add list of loaded JS files    
            $arr = array();
            foreach ( $loadedJsList as $item )
            {
                $arr[OW_URL_HOME . $item] = true;
            }

            \OW::getDocument()->addScriptDeclaration(
                "window.OW.cstLoadedScriptFiles = " . json_encode($arr) . ";
    window.OW.loadScriptFiles = function( urlList, callback, options ){
        
        if ( $.isPlainObject(callback) ) {
            options = callback;
            callback = null;
        }
        
        var addScript = function(url) {
            if( window.OW.cstLoadedScriptFiles[url] ){
                return;
            }
            
            return jQuery.ajax($.extend({
                dataType: 'script',
                cache: true,
                url: url
            }, options || {})).done(function() {
                window.OW.cstLoadedScriptFiles[url] = true;
            });
        };
        
        if( urlList && urlList.length > 0 ) {
            var recursiveInclude = function(urlList, i) {
                if( (i+1) === urlList.length )
                {
                    addScript(urlList[i]).done(callback);
                    return;
                }

                addScript(urlList[i]).done(function() {
                    recursiveInclude(urlList, ++i);
                });
            };
            recursiveInclude(urlList, 0);
        } else {
            callback.apply(this);
        }
    };
    
    window.OW.addScriptFiles = function( urlList, callback, once ) {
        if ( once === false ) {
            this.loadScriptFiles(urlList, callback);
            return;
        }
        
        $('script').each(function() {
            window.OW.cstLoadedScriptFiles[this.src] = true;
        });
        
        var requiredScripts = $.grep(urlList, function(url) {
            return !window.OW.cstLoadedScriptFiles[url];
        });

        this.loadScriptFiles(requiredScripts, callback);
    };", "text/javascript", 1);
        }

        if ( (bool) $this->getConfig(self::CNFG_COMPRESS_CSS) )
        {
            $styleList = array();
            $cssData = $document->getStyleSheets();
            ksort($cssData["items"]);
            $document->setStyleSheets(array("added" => array(), "items" => array()));

            $userfileStyleSheets = array();

            foreach ( $cssData["items"] as $orderKey => $orderItem )
            {
                foreach ( $orderItem as $typeKey => $typeItem )
                {
                    foreach ( $typeItem as $key => $value )
                    {
                        if ( strstr($value, "?") )
                        {
                            $valueC = substr($value, 0, strpos($value, "?"));
                        }
                        else
                        {
                            $valueC = $value;
                        }

                        if ( \UTIL_File::getExtension($valueC) != "css" || !strstr($value, OW_URL_HOME) )
                        {
                            if ( !empty($styleList) )
                            {
                                if ( !empty($userfileStyleSheets) )
                                {
                                    $styleList = array_merge($styleList, $userfileStyleSheets);
                                }

                                $result = $this->getCompressedItem(array_keys($styleList), ItemDao::FIELD_VALUE_TYPE_CSS);

                                if ( $result == null )
                                {
                                    foreach ( $styleList as $tempVal )
                                    {
                                        $document->addStyleSheet($tempVal);
                                    }
                                }
                                else
                                {
                                    $document->addStyleSheet($result);
                                }
                            }

                            $document->addStyleSheet($value);

                            $styleList = array();
                        }
                        else
                        {
                            $key = str_replace(OW_URL_HOME, "", $valueC);

                            // hack to send custom css to the end of list
                            if ( mb_strstr($valueC, OW_URL_USERFILES) )
                            {
                                $userfileStyleSheets[$key] = $value;
                            }
                            else
                            {
                                $styleList[$key] = $value;
                            }
                        }
                    }
                }
            }

            if ( !empty($styleList) )
            {
                if ( !empty($userfileStyleSheets) )
                {
                    $styleList = array_merge($styleList, $userfileStyleSheets);
                }

                $result = $this->getCompressedItem(array_keys($styleList), ItemDao::FIELD_VALUE_TYPE_CSS);

                if ( $result == null )
                {
                    foreach ( $styleList as $tempVal )
                    {
                        $document->addStyleSheet($tempVal);
                    }
                }
                else
                {
                    $document->addStyleSheet($result);
                }
            }
        }
    }

    public function getStorages()
    {
        $result = array(
            \oacompress\classes\ApcCacheBackend::getNamespace() => array("label" => "APC", "class" => "\oacompress\classes\ApcCacheBackend"),
            \oacompress\classes\MemcachedCacheBackend::getNamespace() => array("label" => "Memcached", "class" => "\oacompress\classes\MemcachedCacheBackend")
        );

        if ( !extension_loaded("memcached") && extension_loaded("memcache") )
        {
            $result[\oacompress\classes\MemcachedCacheBackend::getNamespace()]["class"] = "\oacompress\classes\MemcacheCacheBackend";
            $result[\oacompress\classes\MemcachedCacheBackend::getNamespace()]["label"] = "Memcache";
        }

        return $result;
    }

    public function dropAllStoragesCache()
    {
        $storages = $this->getStorages();

        foreach ( $storages as $key => $val )
        {
            if ( call_user_func(array($val["class"], "checkAvailability")) && call_user_func(array($val["class"], "checkIfConfigured")) )
            {
                $storage = new $storages[$key]["class"]();
                $storage->clean(array(), \OW_CacheManager::CLEAN_ALL);
            }
        }
    }

    public function markAllExpired()
    {
        $this->itemDao->markAllExpired();
    }

    /**
     * @return \OW_Storage
     */
    private function getStorage()
    {
        $storage = \OW::getStorage();

        if ( $storage instanceof \BASE_CLASS_AmazonCloudStorage )
        {
            $refS3Property = new \ReflectionProperty("BASE_CLASS_AmazonCloudStorage", "s3");
            $refS3Property->setAccessible(true);

            $refBuProperty = new \ReflectionProperty("BASE_CLASS_AmazonCloudStorage", "bucketName");
            $refBuProperty->setAccessible(true);

            $storage = new \oacompress\classes\AmazonCloudStorage($refS3Property->getValue($storage),
                $refBuProperty->getValue($storage));
        }

        return $storage;
    }

    /**
     * @param \oacompress\bol\Item $item
     * @return boolean
     */
    private function processCssItem( Item $item )
    {
        $list = unserialize($item->getFileList());
        $itemDir = $this->getCompressedDir() . $item->getId() . DS;
        $storage = $this->getStorage();

        if ( !file_exists($itemDir) )
        {
            $storage->mkdir($itemDir);
            $storage->chmod($itemDir, 0777);
        }

        $finalCss = "";

        foreach ( $list as $file )
        {
            /* @var $fileDto Item */
            $fileDto = $this->itemDao->findRawCssFile($file);

            if ( $fileDto === null )
            {
                $fileDto = $this->processCssFile($file);
            }

            if ( empty($fileDto) )
            {
                $this->logger->addEntry("Couldn't process raw CSS `{$file}`", self::LOG_TYPE_ERR);
                continue;
            }

            $tempDir = $this->getTempCompressedDir() . $fileDto->getId() . DS;
            $finalCss .= file_get_contents("{$tempDir}base.css") . PHP_EOL;
        }

        $finalCss = gzencode($finalCss);
        $tempFilePath = "{$this->getTempCompressedDir()}tbase.css.gzip";
        file_put_contents($tempFilePath, $finalCss);

        if ( !file_exists($tempFilePath) )
        {
            $this->logger->addEntry("Couldn't write temp file - `{$tempFilePath}`", self::LOG_TYPE_ERR);
            return false;
        }

        //cache css files for 1 year
        $secondsInYear = 60 * 60 * 24 * 365;

        $metaHeaders = array(
            "Cache-Control" => "max-age={$secondsInYear}",
            "Content-Type" => "text/css",
            "Content-Encoding" => "gzip"
        );

        $pathToCopy = "{$itemDir}base.css.gzip";

        $result = $this->copyFile($tempFilePath, $pathToCopy, $metaHeaders);

        unlink($tempFilePath);

        if ( !$result )
        {
            $this->logger->addEntry("Couldn't copy file - `{$pathToCopy}`", self::LOG_TYPE_ERR);
        }

        return $result;
    }

    /**
     * @param string $file
     * @return \oacompress\bol\Item
     */
    private function processCssFile( $file )
    {
        $filePath = OW_DIR_ROOT . str_replace("/", DS, $file);
        $fileDirPath = dirname($filePath) . DS;

        if ( !file_exists($filePath) )
        {
            return null;
        }

        // add entry for the item
        $item = new Item();
        $item->setType(ItemDao::FIELD_VALUE_TYPE_RAW_CSS);
        $item->setHash($file);
        $item->setStatus(ItemDao::FIELD_VALUE_STATUS_QUEUED);
        $item->setFileList('-');

        $this->itemDao->save($item);

        $dirPath = $this->getTempCompressedDir() . $item->getId() . DS;

        if ( file_exists($dirPath) )
        {
            \UTIL_File::removeDir($dirPath);

            if ( file_exists($dirPath) )
            {
                $this->logger->addEntry("Couldn't clear dir `{$dirPath}`", self::LOG_TYPE_ERR);
                return null;
            }
        }

        // create dir for css and images
        if ( !file_exists($dirPath) )
        {
            mkdir($dirPath);
        }

        $cssContents = file_get_contents($filePath);

        $resultArray = array();
        preg_match_all("/url\(([^\{\}\(\)]+)\)/", $cssContents, $resultArray);
        $searchArray = array();
        $replaceArray = array();
        $newUrlPrefix = OW_URL_HOME . str_replace(DS, '/', str_replace(OW_DIR_ROOT, '', dirname($filePath))) . '/';

        $finalArray = array_unique($resultArray[1]);

        foreach ( $finalArray as $image )
        {
            $filePath = $fileDirPath . str_replace(array('\'', '"', '/'), array("", "", DS), $image);

            //remove get params
            if ( strstr($filePath, '?') )
            {
                $filePath = substr($filePath, 0, strpos($filePath, '?'));
            }

            if ( strstr($filePath, "base64") || !file_exists($filePath) || strstr($image, "http:") || substr($image, 0,
                    4) == "www." )
            {
                continue;
            }

            switch ( \UTIL_File::getExtension($filePath) )
            {
                case "gif":
                    $mime = "image/gif";
                    break;

                case "png":
                    $mime = "image/png";
                    break;

                case "jpg":
                case "jpeg":
                    $mime = "image/jpeg";

                default:
                    $mime = null;
            }

            $searchArray[] = $image;

            if ( (bool) $this->getConfig(self::CNFG_ENCODE_CSS_IMAGE) && $mime != null && filesize($filePath) < $this->configs[self::CNFG_MAX_FILE_SIZE] )
            {
                $replaceArray[] = "data:{$mime};base64," . base64_encode(file_get_contents($filePath));
            }
            else
            {
                $replaceArray[] = $newUrlPrefix . str_replace(array("'", '"'), array("", ""), $image);
            }
        }

        //need to optimize CSS source
        $cssOutput = str_replace($searchArray, $replaceArray, $cssContents);
        $filePath = "{$dirPath}base.css";

        file_put_contents($filePath, $cssOutput);

        if ( !file_exists($filePath) )
        {
            $this->logger->addEntry("Couldn't write file `{$filePath}`", self::LOG_TYPE_ERR);
            return null;
        }

        chmod($filePath, 0666);

        return $item;
    }

    /**
     * @param \oacompress\bol\Item $item
     * @return boolean
     */
    private function processJsItem( Item $item )
    {
        $storage = $this->getStorage();

        $list = unserialize($item->getFileList());
        $itemDir = $this->getCompressedDir() . $item->getId() . DS;

        if ( !file_exists($itemDir) )
        {
            $storage->mkdir($itemDir);
            $storage->chmod($itemDir, 0777);
        }

        $finalJs = "";

        foreach ( $list as $file )
        {
            $filePath = OW_DIR_ROOT . str_replace('/', DS, $file);
            $finalJs .= PHP_EOL . file_get_contents($filePath) . ";" . PHP_EOL . PHP_EOL;
            $fileName = basename($filePath);

            if ( in_array($fileName, array_keys($this->localDependencies)) )
            {
                $src = dirname($filePath) . DS . $this->localDependencies[$fileName];
                $dest = $itemDir . $this->localDependencies[$fileName];

                if ( !$this->copyFile($src, $dest) )
                {
                    $this->logger->addEntry("Couldn't copy `{$src}` to `{$dest}`");
                }
            }
        }

        $finalJs = gzencode($finalJs);
        $tempJsPath = "{$this->getTempCompressedDir()}tbase.jquery.js.gzip";

        file_put_contents($tempJsPath, $finalJs);

        if ( !file_exists($tempJsPath) )
        {
            $this->logger->addEntry("Couldn't write temp file - `{$tempJsPath}`", self::LOG_TYPE_ERR);
            return false;
        }

        $secondsInYear = 60 * 60 * 24 * 365;

        $metaHeaders = array(
            "Cache-Control" => "max-age={$secondsInYear}",
            "Content-Type" => "text/javascript",
            "Content-Encoding" => "gzip"
        );

        $pathToCopy = $itemDir . "base.jquery.js.gzip";

        $result = $this->copyFile($tempJsPath, $pathToCopy, $metaHeaders);

        unlink($tempJsPath);

        if ( !$result )
        {
            $this->logger->addEntry("Couldn't copy file - `{$pathToCopy}`", self::LOG_TYPE_ERR);
        }

        return $result;
    }

    /**
     * @param string $source
     * @param string $dest
     * @param array $headers
     * @return boolean
     */
    private function copyFile( $source, $dest, array $headers = array() )
    {
        $storage = $this->getStorage();
        $storage->copyFile($source, $dest, $headers);

        if ( !$storage->fileExists($dest) )
        {
            $this->logger->addEntry("Can't copy file `{$source}` to `{$dest}`", self::LOG_TYPE_ERR);
            return false;
        }

        $storage->chmod($dest, 0666);

        return true;
    }

    /**
     * @return string
     */
    private function getCompressedDir()
    {
        return $this->getPlugin()->getUserFilesDir();
    }

    /**
     * @return string
     */
    private function getTempCompressedDir()
    {
        return $this->getPlugin()->getPluginFilesDir();
    }

    /**
     * @return string
     */
    private function getCompressedUrl( $id, $type )
    {
        $url = $this->getStorage()->getFileUrl($this->getPlugin()->getUserFilesDir());

        if ( substr($url, -1) != '/' )
        {
            $url = $url . '/';
        }

        $postfix = \OW::getConfig()->getValue("base", "cachedEntitiesPostfix");

        if ( $type == ItemDao::FIELD_VALUE_TYPE_JS )
        {
            $type = "jquery.{$type}";
        }

        return "{$url}{$id}/base.{$type}.gzip?{$postfix}";
    }

    /**
     * @return string
     */
    private function getLogFilePath()
    {
        return OW_DIR_LOG . "{$this->getPluginKey()}_error.log";
    }

    /**
     * @return boolean
     */
    private function isCompressRequirementsSatisfied()
    {
        return extension_loaded("zlib");
    }

    /**
     * @param array $headers
     * @return boolean
     */
    private function clientAcceptsGzip( array $headers )
    {
        return (!empty($headers["HTTP_ACCEPT_ENCODING"]) && mb_strpos($headers["HTTP_ACCEPT_ENCODING"], "gzip") !== false );
    }

    /**
     * @param array $itemList
     * @param string $type
     * @return mixed
     */
    private function getCompressedItem( $itemList, $type )
    {
        if ( !$this->clientAcceptsGzip || empty($itemList) || !in_array($type,
                array(ItemDao::FIELD_VALUE_TYPE_CSS, ItemDao::FIELD_VALUE_TYPE_JS)) )
        {
            return null;
        }

        $hash = md5(implode("", $itemList));

        $item = $this->itemDao->findItemByHashAndType($hash, $type);

        if ( $item === null )
        {
            $item = new Item();
            $item->setFileList(serialize($itemList));
            $item->setHash($hash);
            $item->setType($type);
            $item->setStatus(ItemDao::FIELD_VALUE_STATUS_QUEUED);

            $this->itemDao->save($item);
        }
        else if ( $item->getStatus() == ItemDao::FIELD_VALUE_STATUS_PROCESSED )
        {
            return $this->getCompressedUrl($item->getId(), $type);
        }

        return null;
    }

    private function parseHeaders( $headers )
    {
        if ( !$headers )
        {
            return array();
        }

        $head = array();
        foreach ( $headers as $k => $v )
        {
            $t = explode(':', $v, 2);
            if ( isset($t[1]) )
                $head[trim($t[0])] = trim($t[1]);
            else
            {
                $head[] = $v;
                if ( preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out) )
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }

    public function isHtmlCompressionAvailable()
    {
        if ( !$this->clientAcceptsGzip || !$this->getConfig(self::CNFG_COMPRESS_HTML) || OW_DEBUG_MODE )
        {
            return false;
        }

        //If zlib is not ALREADY compressing the page - and ob_gzhandler is set
        if ( ( ini_get("zlib.output_compression") == "On" || ini_get("zlib.output_compression_level") > 0 ) || ini_get("output_handler") == "ob_gzhandler" )
        {
            return false;
        }

        // check apache extensions
        if ( function_exists("apache_get_modules") )
        {
            $modules = apache_get_modules();

            foreach ( $modules as $module )
            {
                if ( mb_strstr($module, "deflate") )
                {
                    return false;
                }
            }
        }

        return true;
    }
}
