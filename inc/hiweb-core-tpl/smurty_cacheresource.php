<?php
/**
 * hiweb_tpl Internal Plugin
 *
 * @package    hiweb_tpl
 * @subpackage Cacher
 */

/**
 * Cache Handler API
 *
 * @package    hiweb_tpl
 * @subpackage Cacher
 * @author     Rodney Rehm
 */
abstract class Smurty_CacheResource
{
    /**
     * cache for Smurty_CacheResource instances
     *
     * @var array
     */
    public static $resources = array();

    /**
     * resource types provided by the core
     *
     * @var array
     */
    protected static $sysplugins = array(
        'file' => true,
    );

    /**
     * populate Cached Object with meta data from Resource
     *
     * @param Smurty_Template_Cached   $cached    cached object
     * @param Smurty_Internal_Template $_template template object
     *
     * @return void
     */
    abstract public function populate(Smurty_Template_Cached $cached, Smurty_Internal_Template $_template);

    /**
     * populate Cached Object with timestamp and exists from Resource
     *
     * @param Smurty_Template_Cached $cached
     *
     * @return void
     */
    abstract public function populateTimestamp(Smurty_Template_Cached $cached);

    /**
     * Read the cached template and process header
     *
     * @param Smurty_Internal_Template $_template template object
     * @param Smurty_Template_Cached   $cached    cached object
     *
     * @return boolean true or false if the cached content does not exist
     */
    abstract public function process(Smurty_Internal_Template $_template, Smurty_Template_Cached $cached = null);

    /**
     * Write the rendered template output to cache
     *
     * @param Smurty_Internal_Template $_template template object
     * @param string                   $content   content to cache
     *
     * @return boolean success
     */
    abstract public function writeCachedContent(Smurty_Internal_Template $_template, $content);

    /**
     * Return cached content
     *
     * @param Smurty_Internal_Template $_template template object
     *
     * @return null|string
     */
    public function getCachedContent(Smurty_Internal_Template $_template)
    {
        if ($_template->cached->handler->process($_template)) {
            ob_start();
            $_template->properties['unifunc']($_template);

            return ob_get_clean();
        }

        return null;
    }

    /**
     * Empty cache
     *
     * @param hiweb_tpl  $smurty   hiweb_tpl object
     * @param integer $exp_time expiration time (number of seconds, not timestamp)
     *
     * @return integer number of cache files deleted
     */
    abstract public function clearAll(hiweb_tpl $smurty, $exp_time = null);

    /**
     * Empty cache for a specific template
     *
     * @param hiweb_tpl  $smurty        hiweb_tpl object
     * @param string  $resource_name template name
     * @param string  $cache_id      cache id
     * @param string  $compile_id    compile id
     * @param integer $exp_time      expiration time (number of seconds, not timestamp)
     *
     * @return integer number of cache files deleted
     */
    abstract public function clear(hiweb_tpl $smurty, $resource_name, $cache_id, $compile_id, $exp_time);

    /**
     * @param hiweb_tpl                 $smurty
     * @param Smurty_Template_Cached $cached
     *
     * @return bool|null
     */
    public function locked(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        // theoretically locking_timeout should be checked against time_limit (max_execution_time)
        $start = microtime(true);
        $hadLock = null;
        while ($this->hasLock($smurty, $cached)) {
            $hadLock = true;
            if (microtime(true) - $start > $smurty->locking_timeout) {
                // abort waiting for lock release
                return false;
            }
            sleep(1);
        }

        return $hadLock;
    }

    /**
     * Check is cache is locked for this template
     *
     * @param hiweb_tpl                 $smurty
     * @param Smurty_Template_Cached $cached
     *
     * @return bool
     */
    public function hasLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        // check if lock exists
        return false;
    }

    /**
     * Lock cache for this template
     *
     * @param hiweb_tpl                 $smurty
     * @param Smurty_Template_Cached $cached
     *
     * @return bool
     */
    public function acquireLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        // create lock
        return true;
    }

    /**
     * Unlock cache for this template
     *
     * @param hiweb_tpl                 $smurty
     * @param Smurty_Template_Cached $cached
     *
     * @return bool
     */
    public function releaseLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        // release lock
        return true;
    }

    /**
     * Load Cache Resource Handler
     *
     * @param hiweb_tpl $smurty hiweb_tpl object
     * @param string $type   name of the cache resource
     *
     * @throws SmurtyException
     * @return Smurty_CacheResource Cache Resource Handler
     */
    public static function load(hiweb_tpl $smurty, $type = null)
    {
        if (!isset($type)) {
            $type = $smurty->caching_type;
        }

        // try smurty's cache
        if (isset($smurty->_cacheresource_handlers[$type])) {
            return $smurty->_cacheresource_handlers[$type];
        }

        // try registered resource
        if (isset($smurty->registered_cache_resources[$type])) {
            // do not cache these instances as they may vary from instance to instance
            return $smurty->_cacheresource_handlers[$type] = $smurty->registered_cache_resources[$type];
        }
        // try hiweb-core-tpl dir
        if (isset(self::$sysplugins[$type])) {
            if (!isset(self::$resources[$type])) {
                $cache_resource_class = 'Smurty_Internal_CacheResource_' . ucfirst($type);
                self::$resources[$type] = new $cache_resource_class();
            }

            return $smurty->_cacheresource_handlers[$type] = self::$resources[$type];
        }
        // try plugins dir
        $cache_resource_class = 'Smurty_CacheResource_' . ucfirst($type);
        if ($smurty->loadPlugin($cache_resource_class)) {
            if (!isset(self::$resources[$type])) {
                self::$resources[$type] = new $cache_resource_class();
            }

            return $smurty->_cacheresource_handlers[$type] = self::$resources[$type];
        }
        // give up
        throw new SmurtyException("Unable to load cache resource '{$type}'");
    }

    /**
     * Invalid Loaded Cache Files
     *
     * @param hiweb_tpl $smurty hiweb_tpl object
     */
    public static function invalidLoadedCache(hiweb_tpl $smurty)
    {
        foreach ($smurty->template_objects as $tpl) {
            if (isset($tpl->cached)) {
                $tpl->cached->valid = false;
                $tpl->cached->processed = false;
            }
        }
    }
}

/**
 * hiweb_tpl Resource Data Object
 * Cache Data Container for Template Files
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 */
class Smurty_Template_Cached
{
    /**
     * Source Filepath
     *
     * @var string
     */
    public $filepath = false;

    /**
     * Source Content
     *
     * @var string
     */
    public $content = null;

    /**
     * Source Timestamp
     *
     * @var integer
     */
    public $timestamp = false;

    /**
     * Source Existence
     *
     * @var boolean
     */
    public $exists = false;

    /**
     * Cache Is Valid
     *
     * @var boolean
     */
    public $valid = false;

    /**
     * Cache was processed
     *
     * @var boolean
     */
    public $processed = false;

    /**
     * CacheResource Handler
     *
     * @var Smurty_CacheResource
     */
    public $handler = null;

    /**
     * Template Compile Id (Smurty_Internal_Template::$compile_id)
     *
     * @var string
     */
    public $compile_id = null;

    /**
     * Template Cache Id (Smurty_Internal_Template::$cache_id)
     *
     * @var string
     */
    public $cache_id = null;

    /**
     * Id for cache locking
     *
     * @var string
     */
    public $lock_id = null;

    /**
     * flag that cache is locked by this instance
     *
     * @var bool
     */
    public $is_locked = false;

    /**
     * Source Object
     *
     * @var Smurty_Template_Source
     */
    public $source = null;

    /**
     * create Cached Object container
     *
     * @param Smurty_Internal_Template $_template template object
     */
    public function __construct(Smurty_Internal_Template $_template)
    {
        $this->compile_id = $_template->compile_id;
        $this->cache_id = $_template->cache_id;
        $this->source = $_template->source;
        $_template->cached = $this;
        $smurty = $_template->smurty;

        //
        // load resource handler
        //
        $this->handler = $handler = Smurty_CacheResource::load($smurty); // Note: prone to circular references

        //
        //    check if cache is valid
        //
        if (!($_template->caching == hiweb_tpl::CACHING_LIFETIME_CURRENT || $_template->caching == hiweb_tpl::CACHING_LIFETIME_SAVED) || $_template->source->recompiled) {
            $handler->populate($this, $_template);

            return;
        }
        while (true) {
            while (true) {
                $handler->populate($this, $_template);
                if ($this->timestamp === false || $smurty->force_compile || $smurty->force_cache) {
                    $this->valid = false;
                } else {
                    $this->valid = true;
                }
                if ($this->valid && $_template->caching == hiweb_tpl::CACHING_LIFETIME_CURRENT && $_template->cache_lifetime >= 0 && time() > ($this->timestamp + $_template->cache_lifetime)) {
                    // lifetime expired
                    $this->valid = false;
                }
                if ($this->valid || !$_template->smurty->cache_locking) {
                    break;
                }
                if (!$this->handler->locked($_template->smurty, $this)) {
                    $this->handler->acquireLock($_template->smurty, $this);
                    break 2;
                }
            }
            if ($this->valid) {
                if (!$_template->smurty->cache_locking || $this->handler->locked($_template->smurty, $this) === null) {
                    // load cache file for the following checks
                    if ($smurty->debugging) {
                        Smurty_Internal_Debug::start_cache($_template);
                    }
                    if ($handler->process($_template, $this) === false) {
                        $this->valid = false;
                    } else {
                        $this->processed = true;
                    }
                    if ($smurty->debugging) {
                        Smurty_Internal_Debug::end_cache($_template);
                    }
                } else {
                    continue;
                }
            } else {
                return;
            }
            if ($this->valid && $_template->caching === hiweb_tpl::CACHING_LIFETIME_SAVED && $_template->properties['cache_lifetime'] >= 0 && (time() > ($_template->cached->timestamp + $_template->properties['cache_lifetime']))) {
                $this->valid = false;
            }
            if (!$this->valid && $_template->smurty->cache_locking) {
                $this->handler->acquireLock($_template->smurty, $this);

                return;
            } else {
                return;
            }
        }
    }

    /**
     * Write this cache object to handler
     *
     * @param Smurty_Internal_Template $_template template object
     * @param string                   $content   content to cache
     *
     * @return boolean success
     */
    public function write(Smurty_Internal_Template $_template, $content)
    {
        if (!$_template->source->recompiled) {
            if ($this->handler->writeCachedContent($_template, $content)) {
                $this->content = null;
                $this->timestamp = time();
                $this->exists = true;
                $this->valid = true;
                if ($_template->smurty->cache_locking) {
                    $this->handler->releaseLock($_template->smurty, $this);
                }

                return true;
            }
        }

        return false;
    }
}
