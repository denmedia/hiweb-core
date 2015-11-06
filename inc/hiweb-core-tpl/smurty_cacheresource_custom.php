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
abstract class Smurty_CacheResource_Custom extends Smurty_CacheResource
{
    /**
     * fetch cached content and its modification time from data source
     *
     * @param  string  $id         unique cache content identifier
     * @param  string  $name       template name
     * @param  string  $cache_id   cache id
     * @param  string  $compile_id compile id
     * @param  string  $content    cached content
     * @param  integer $mtime      cache modification timestamp (epoch)
     *
     * @return void
     */
    abstract protected function fetch($id, $name, $cache_id, $compile_id, &$content, &$mtime);

    /**
     * Fetch cached content's modification timestamp from data source
     * {@internal implementing this method is optional.
     *  Only implement it if modification times can be accessed faster than loading the complete cached content.}}
     *
     * @param  string $id         unique cache content identifier
     * @param  string $name       template name
     * @param  string $cache_id   cache id
     * @param  string $compile_id compile id
     *
     * @return integer|boolean timestamp (epoch) the template was modified, or false if not found
     */
    protected function fetchTimestamp($id, $name, $cache_id, $compile_id)
    {
        return null;
    }

    /**
     * Save content to cache
     *
     * @param  string       $id         unique cache content identifier
     * @param  string       $name       template name
     * @param  string       $cache_id   cache id
     * @param  string       $compile_id compile id
     * @param  integer|null $exp_time   seconds till expiration or null
     * @param  string       $content    content to cache
     *
     * @return boolean      success
     */
    abstract protected function save($id, $name, $cache_id, $compile_id, $exp_time, $content);

    /**
     * Delete content from cache
     *
     * @param  string       $name       template name
     * @param  string       $cache_id   cache id
     * @param  string       $compile_id compile id
     * @param  integer|null $exp_time   seconds till expiration time in seconds or null
     *
     * @return integer      number of deleted caches
     */
    abstract protected function delete($name, $cache_id, $compile_id, $exp_time);

    /**
     * populate Cached Object with meta data from Resource
     *
     * @param  Smurty_Template_Cached   $cached    cached object
     * @param  Smurty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populate(Smurty_Template_Cached $cached, Smurty_Internal_Template $_template)
    {
        $_cache_id = isset($cached->cache_id) ? preg_replace('![^\w\|]+!', '_', $cached->cache_id) : null;
        $_compile_id = isset($cached->compile_id) ? preg_replace('![^\w\|]+!', '_', $cached->compile_id) : null;

        $cached->filepath = sha1($cached->source->filepath . $_cache_id . $_compile_id);
        $this->populateTimestamp($cached);
    }

    /**
     * populate Cached Object with timestamp and exists from Resource
     *
     * @param Smurty_Template_Cached $cached
     *
     * @return void
     */
    public function populateTimestamp(Smurty_Template_Cached $cached)
    {
        $mtime = $this->fetchTimestamp($cached->filepath, $cached->source->name, $cached->cache_id, $cached->compile_id);
        if ($mtime !== null) {
            $cached->timestamp = $mtime;
            $cached->exists = !!$cached->timestamp;

            return;
        }
        $timestamp = null;
        $this->fetch($cached->filepath, $cached->source->name, $cached->cache_id, $cached->compile_id, $cached->content, $timestamp);
        $cached->timestamp = isset($timestamp) ? $timestamp : false;
        $cached->exists = !!$cached->timestamp;
    }

    /**
     * Read the cached template and process the header
     *
     * @param  Smurty_Internal_Template $_template template object
     * @param  Smurty_Template_Cached   $cached    cached object
     *
     * @return boolean                 true or false if the cached content does not exist
     */
    public function process(Smurty_Internal_Template $_template, Smurty_Template_Cached $cached = null)
    {
        if (!$cached) {
            $cached = $_template->cached;
        }
        $content = $cached->content ? $cached->content : null;
        $timestamp = $cached->timestamp ? $cached->timestamp : null;
        if ($content === null || !$timestamp) {
            $this->fetch(
                $_template->cached->filepath,
                $_template->source->name,
                $_template->cache_id,
                $_template->compile_id,
                $content,
                $timestamp
            );
        }
        if (isset($content)) {
            /** @var Smurty_Internal_Template $_smurty_tpl
             * used in evaluated code
             */
            $_smurty_tpl = $_template;
            eval("?>" . $content);

            return true;
        }

        return false;
    }

    /**
     * Write the rendered template output to cache
     *
     * @param  Smurty_Internal_Template $_template template object
     * @param  string                   $content   content to cache
     *
     * @return boolean                  success
     */
    public function writeCachedContent(Smurty_Internal_Template $_template, $content)
    {
        return $this->save(
            $_template->cached->filepath,
            $_template->source->name,
            $_template->cache_id,
            $_template->compile_id,
            $_template->properties['cache_lifetime'],
            $content
        );
    }

    /**
     * Empty cache
     *
     * @param  hiweb_tpl  $smurty   hiweb_tpl object
     * @param  integer $exp_time expiration time (number of seconds, not timestamp)
     *
     * @return integer number of cache files deleted
     */
    public function clearAll(hiweb_tpl $smurty, $exp_time = null)
    {
        $this->cache = array();

        return $this->delete(null, null, null, $exp_time);
    }

    /**
     * Empty cache for a specific template
     *
     * @param  hiweb_tpl  $smurty        hiweb_tpl object
     * @param  string  $resource_name template name
     * @param  string  $cache_id      cache id
     * @param  string  $compile_id    compile id
     * @param  integer $exp_time      expiration time (number of seconds, not timestamp)
     *
     * @return integer number of cache files deleted
     */
    public function clear(hiweb_tpl $smurty, $resource_name, $cache_id, $compile_id, $exp_time)
    {
        $this->cache = array();
        $cache_name = null;

        if (isset($resource_name)) {
            $_save_stat = $smurty->caching;
            $smurty->caching = true;
            $tpl = new $smurty->template_class($resource_name, $smurty);
            $smurty->caching = $_save_stat;

            if ($tpl->source->exists) {
                $cache_name = $tpl->source->name;
            } else {
                return 0;
            }
            // remove from template cache
            if ($smurty->allow_ambiguous_resources) {
                $_templateId = $tpl->source->unique_resource . $tpl->cache_id . $tpl->compile_id;
            } else {
                $_templateId = $smurty->joined_template_dir . '#' . $resource_name . $tpl->cache_id . $tpl->compile_id;
            }
            if (isset($_templateId[150])) {
                $_templateId = sha1($_templateId);
            }
            unset($smurty->template_objects[$_templateId]);
            // template object no longer needed
            unset($tpl);
        }

        return $this->delete($cache_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Check is cache is locked for this template
     *
     * @param  hiweb_tpl                 $smurty hiweb_tpl object
     * @param  Smurty_Template_Cached $cached cached object
     *
     * @return boolean               true or false if cache is locked
     */
    public function hasLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        $id = $cached->filepath;
        $name = $cached->source->name . '.lock';

        $mtime = $this->fetchTimestamp($id, $name, null, null);
        if ($mtime === null) {
            $this->fetch($id, $name, null, null, $content, $mtime);
        }

        return $mtime && time() - $mtime < $smurty->locking_timeout;
    }

    /**
     * Lock cache for this template
     *
     * @param hiweb_tpl                 $smurty hiweb_tpl object
     * @param Smurty_Template_Cached $cached cached object
     *
     * @return bool|void
     */
    public function acquireLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        $cached->is_locked = true;

        $id = $cached->filepath;
        $name = $cached->source->name . '.lock';
        $this->save($id, $name, null, null, $smurty->locking_timeout, '');
    }

    /**
     * Unlock cache for this template
     *
     * @param hiweb_tpl                 $smurty hiweb_tpl object
     * @param Smurty_Template_Cached $cached cached object
     *
     * @return bool|void
     */
    public function releaseLock(hiweb_tpl $smurty, Smurty_Template_Cached $cached)
    {
        $cached->is_locked = false;

        $name = $cached->source->name . '.lock';
        $this->delete($name, null, null, null);
    }
}
