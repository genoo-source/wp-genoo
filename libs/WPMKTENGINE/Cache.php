<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General Public License Ver. 2 (GPL)
 *  Licensed "As-Is"; all warranties are disclaimed.
 *  HTML: http://www.gnu.org/copyleft/gpl.html
 *  Text: http://www.gnu.org/copyleft/gpl.txt
 *
 * Proprietary Licensing:
 *  Remaining code elements, including without limitation:
 *  images, cascading style sheets, and JavaScript elements
 *  are licensed under restricted license.
 *  http://www.wpmktgengine.com/terms-of-service
 *  Copyright 2016 Genoo LLC. All rights reserved worldwide.
 */

namespace WPMKTENGINE;

use WPMKTENGINE\Utils\Json;

/**
 * Class Cache
 * @package WPMKTENGINE
 * Based on: http://jordifreek.github.io/CacheGadget/
 */
class Cache
{
    /** @var bool Enable/Disable cache. */
    private $enable = true;
    /** @var string Path to cache dir. Ensure your directory is writable. Default dir "./cache" */
    private $cacheDir = 'cache';
    /** @var string Default namespace/directory */
    private $cacheName = 'default';
    /** @var int Default cache time in seconds. 0 = unlimited */
    private $cacheTime  = 6000;
    /** @var bool True to find a key in other categories if not exists in default. Affect to method get(). Slowly if there so much categories */
    private $hardFind = false;
    /** @array list of disabled categories */
    private $disableCacheName = array();
    /** @var array */
    private $userConfig = array();


    /**
     * @param null $config
     */

    public function __construct($config = null)
    {
        if (is_string($config)){
            $this->cacheDir = rtrim($config, '/');
        } elseif (is_bool($config)){
            $this->enable = $config;
        } elseif (is_array($config)){
            $this->setConfig($config);
        }
    }


    /**
     * Save a new item or overwrite existing. Use {@hideLink add()} for
     * prevent replace.
     *
     * @param $key
     * @param $value
     * @param null $cache_time
     * @return bool
     */

    public function set($key, $value, $cache_time = null, $namespace = null)
    {
        $data  = array(
            'time'   => time(),
            'expire' => $this->formatTime($cache_time),
            'data'   => $value
        );
        return $this->saveKey($key, $namespace, $data);
    }


    /**
     * Similar to {@hideLink set()} but save item only if not exists (prevent overwrite)
     *
     * @param $key
     * @param $value
     * @param null $cache_time
     * @return bool
     * @throws CacheException
     */

    public function add($key, $value, $cache_time = null)
    {
        if (!$this->getKey($key)) {
            return $this->set($key, $value, $this->formatTime($cache_time));
        }
        throw new CacheException('You\re trying to add key that already exists.');
    }


    /**
     * Retrieve item value. Return false if not exists or expired.
     *
     * @param $key
     * @param null $cache_name
     * @return bool|mixed
     */

    public function get($key, $cache_name = null)
    {
        if ($cached = $this->getKey($key, $cache_name)) {
            return $cached['data'];
        }
        return false;
    }


    /**
     * Search a key in all categories (cache_names)
     *
     * @param $key
     * @param string $mode
     * @return bool|mixed
     * @throws CacheException
     */

    public function hardFind($key, $mode = 'get')
    {
        if ($this->hardFind) {
            $dir = dir($this->cacheDir);
            while (false !== ($cache_name = $dir->read())) {
                if ($cache_name != '.' and $cache_name != '..') {
                    if (is_dir($cache_name)) {
                        $file = $this->paths($key, $cache_name);
                        if (file_exists($file['path'])) {
                            if ($mode == 'get') {
                                return $this->get($key, $cache_name);
                            } elseif ($mode == 'exists') {
                                return $this->exists($key, $cache_name);
                            }
                        }
                    }
                }
            }
            $dir->close();
        }
        throw new CacheException('Cache file not found.');
    }


    /**
     * Remove an item.
     *
     * @param $key
     * @param null $cache_name
     * @return bool
     * @throws CacheException
     */

    public function remove($key, $cache_name = null)
    {
        if (!$this->checkKey($key)){ return false; }
        $file = $this->paths($key, $cache_name);
        if (file_exists($file['path'])) {
            if (unlink($file['path'])) {
                return true;
            } else {
                throw new CacheException('Operation failed. File does not exists?');
            }
        } else {
            throw new CacheException('Cache file not found.');
        }
    }


    /**
     * Remove all items from one category (cache_name).
     *
     * @param null $cache_name
     * @return bool
     * @throws CacheException
     */

    public function flush($cache_name = null)
    {
        if (!$cache_name) {
            $cache_name = $this->cacheName;
        }
        $directory = $this->cacheDir.'/'.$cache_name;
        if (is_dir($directory)){
            $files = glob($directory.'/*');
            if (is_array($files) and count($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (!unlink($file)) {
                            throw new CacheException('Error while deleting multiple files.');
                        }
                    }
                }
                if (!@rmdir($directory)) {
                    throw new CacheException('Can\'t remove dir (user permissions?)', 1000);
                }
                return true;
            } else {
                throw new CacheException('Directory is empty.');
            }
        }
        throw new CacheException('Cache file not found.');
    }


    /**
     * Remove all cached items in all categories
     *
     * @return Cache
     */

    public function clear()
    {
        $this->recursiveRemoveDirectory($this->cacheDir);
        // remove directories (cache names) after remove files
        $dirs = glob($this->cacheDir.'/*');
        if (is_array($dirs) and count($dirs)) {
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    @rmdir($dir);
                }
            }
        }
        return $this;
    }


    /**
     * Sets a new expiration value on the given key.
     *
     * @param $key
     * @param $cache_time
     * @param null $cache_name
     * @return bool
     */

    public function touch($key, $cache_time, $cache_name = null)
    {
        if ($cached = $this->getKey($key, $cache_name, false)) {
            // modify cached expiration time
            $cached['expire'] = (int) $cache_time;
            if ($this->saveKey($key, $cache_name, $cached)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Checks if a key exists.
     *
     * @param $key
     * @param null $cache_name
     * @return bool
     */

    public function exists($key, $cache_name = null)
    {
        if ($this->getKey($key, $cache_name)) {
            return true;
        }
        return false;
    }


    /**
     * Increment a numeric chached value.
     *
     * @param $key
     * @param int $number
     * @param null $cache_name
     * @return bool|float
     * @throws CacheException
     */

    public function increment($key, $number = 1, $cache_name = null)
    {
        if ($cached = $this->getKey($key, $cache_name)) {
            if (is_numeric($cached['data'])) {
                $cached['data'] += (float) $number;
                if ($this->saveKey($key, $cache_name, $cached)) {
                    return (float) $cached['data'];
                }
            } else {
                throw new CacheException('Affects to {@link increment()} where destination object is "not a number"');
            }
        }
        return false;
    }


    /**
     * Config options. You may pass one or more options in an array.
     *
     * @param array $data
     * @return Cache
     */

    public function setConfig($data = array())
    {
        $defaults = array(
            'enable'    => $this->enable,
            'cacheDir'  => $this->cacheDir,
            'cacheName' => $this->cacheName,
            'cacheTime' => $this->cacheTime,
            'hardFind'  => $this->hardFind,
        );
        $data = array_merge($defaults, $data);
        $this->userConfig = $data;
        $this->enable     = $data['enable'];
        $this->cacheDir   = $data['cacheDir'];
        $this->cacheName  = $data['cacheName'];
        $this->_cache_tinme = $data['cacheTime'];
        $this->hardFind   = $data['hardFind'];
        return $this;
    }


    /**
     * Enable/Disable cache. Methods affected: get, set and add.
     *
     * @param $enable
     * @param bool $global
     * @return Cache
     */
    public function setEnable($enable, $global = false)
    {
        if ($global){
            $this->setConfig(array('enable' => $enable));
        } else {
            $this->enable = $enable;
        }

        return $this;
    }


    /**
     * Set cache dir. Be sure is writable.
     *
     * @param $dir
     * @return Cache
     */
    public function setDir($dir)
    {
        $this->cacheDir = $dir;
        return $this;
    }


    /**
     * Set default cache time.
     *
     * @param $time
     * @return Cache
     */
    public function setTime($time)
    {
        $this->cacheTime = $time;
        return $this;
    }


    /**
     * Set cache name.
     *
     * @param null $name
     * @return Cache
     */
    public function setCache($name = null)
    {
        if (!$name) {
            $name = $this->cache_name;
        }
        $this->cacheName = $name;
        return $this;
    }


    /**
     * Set hardFind enable
     *
     * @param $enable
     * @return Cache
     */
    public function setHardFind($enable)
    {
        $this->hardFind = $enable;
        return $this;
    }


    /**
     * Return if cache is enable
     *
     * @return bool
     */
    public function isEnable(){ return $this->enable; }


    /**
     * Disable a cache name
     *
     * @param $cache_name
     * @return bool
     */
    public function disableCacheName($cache_name)
    {
        if ($cache_name and !in_array($cache_name, $this->disableCacheName)) {
            $this->disableCacheName[] = $cache_name;
            return true;
        }
        return false;
    }


    /**
     * Search key an return without check time expiration.
     *
     * @param $key
     * @param null $cache_name
     * @param bool $check_expiration
     * @return bool|CacheException|mixed
     * @throws CacheException
     */
    public function getKey($key, $cache_name = null, $check_expiration = false)
    {
        if (!$this->checkKey($key)) {
            $this->resetConfig();
            return false;
        }
        $file = $this->paths($key, $cache_name);
        if (file_exists($file['path'])) {
            $cached = file_get_contents($file['path']);
            $cached = Json::decode($cached, true);
            // check expire
            if ($cached['expire'] and $check_expiration) {
                $time_diff = time() - $cached['time'];
                if ($time_diff > $cached['expire']) {
                    throw new CacheException('$key is expired.');
                }
            }
            return $cached;
        } else {
            // use hardFind only if no $cache_name is provided
            if (!$cache_name or $cache_name == 'default') {
                return $this->hardFind($key, 'exists');
            }
        }
        return false;
    }


    /**
     * Save item. This private method assumes that destination file is writable.
     *
     * @param $key
     * @param $cache_name
     * @param $data
     * @return bool
     * @throws CacheException
     */

    private function saveKey($key, $cache_name, $data)
    {
        if (!$this->checkKey($key)) {
            $this->resetConfig();
            return false;
        }
        $file = $this->paths($key, $cache_name);

        chdir(dirname(__FILE__));

        // Make dir first
        $dirname = dirname($file['path']);
        // Check if exists
        if (!file_exists($dirname)){
            mkdir($dirname, 0777, true);
        }

        // Directory to create
        if (file_put_contents($file['path'], Json::encode($data))) {
            $this->resetConfig();
            return true;
        }
        throw new CacheException('Check write permissions.');
    }


    /**
     * Check if key is an string and cache is enabled.
     *
     * @param $key
     * @return bool
     * @throws CacheException
     */

    private function checkKey($key)
    {
        if(!$this->enable){
            throw new CacheException('Cache is disabled, operation canceled.');
        }
        if(in_array($this->cacheName, $this->disableCacheName)){
            throw new CacheException('Cache is disabled, operation canceled.');
        }
        if(!is_string($key)){
            throw new CacheException('$key not is a string');
        }
        return true;
    }


    /**
     * Reset user config after set() or add()
     */

    private function resetConfig(){ $this->setConfig($this->userConfig); }


    /**
     * Return the filename and path of a key
     *
     * @param $key
     * @param null $cache_name
     * @return array
     */

    private function paths($key, $cache_name = null)
    {
        if (!$cache_name) {
            $cache_name = $this->cacheName;
        }
        $file = array();
        $file['name'] = md5($key);
        $file['dir']  = $this->cacheDir.'/'.$cache_name;
        $file['path'] = $this->cacheDir.'/'.$cache_name.'/'.$file['name'];
        if (!file_exists($file['dir'])) {
            @mkdir($file['dir']);
        }
        return $file;
    }


    /**
     * Format time, set default time if null
     *
     * @param null $time
     * @return int
     */

    private function formatTime($time = null)
    {
        if ($time === null) {
            $time = $this->cacheTime;
        }
        return (int)$time;
    }


    /**
     * Recursive remove directories
     *
     * @param $directory
     * @return bool
     */

    private function recursiveRemoveDirectory($directory)
    {
        $directory = rtrim($directory, '/');
        if (!is_dir($directory)) {
            return false;
        } else {
            $handle = opendir($directory);
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $directory.'/'.$item;
                    if (is_dir($path)) {
                        $this->recursiveRemoveDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
        }
        return true;
    }
}

/**
 * Class CacheException
 * @package WPMKTENGINE
 */
class CacheException extends \Exception{}