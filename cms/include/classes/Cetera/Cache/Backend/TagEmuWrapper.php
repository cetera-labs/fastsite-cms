<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$ 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Cache\Backend; 

class TagEmuWrapper extends \Zend\Cache\Storage\Adapter\AbstractAdapter implements \Zend\Cache\Storage\TaggableInterface 
{
    
    private $backend = null;
    
    private $tags = [];
    
    
    public function __construct(\Zend\Cache\Storage\Adapter\AbstractAdapter $backend)
    {
        $this->backend = $backend;
    }
    
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null) {
        $serialized = $this->backend->internalGetItem($normalizedKey, $success, $casToken);
        $success = false;
        
        if ($serialized === false) {
            return false;
        }
        $combined = unserialize($serialized);
        if (!is_array($combined)) {
            return false;
        } 
        // Test if all tags has the same version as when the slot was created
        // (i.e. still not removed and then recreated).
        if (is_array($combined[0]) && $combined[0]) {
                foreach ($combined[0] as $tag => $savedTagVersion) {
                    $actualTagVersion = $this->backend->getItem($this->_mangleTag($tag));
                    if ($actualTagVersion !== $savedTagVersion) {
                        return false;
                    }
                }
        }
        $success = true;
        return $combined[1];        
    }
    
    protected function internalSetItem(& $normalizedKey, & $value) {
        return $this->backend->internalSetItem($normalizedKey, $value);
        
        // Save/update tags as usual infinite keys with value of tag version.
        // If the tag already exists, do not rewrite it. 
        $tagsWithVersion = [];
        if (is_array($this->tags)) {
            foreach ($this->tags as $tag) {
                $mangledTag = $this->_mangleTag($tag);
                $tagVersion = $this->backend->getItem($mangledTag);
                if ($tagVersion === false) {
                    $tagVersion = $this->_generateNewTagVersion();
                    $this->backend->setItem($mangledTag, $tagVersion);
                }
                $tagsWithVersion[$tag] = $tagVersion;
            }
        }
        // Data is saved in form of: array(tagsWithVersionArray, anyData).
        $combined = [$tagsWithVersion, $value];
        $serialized = serialize($combined);
        return $this->backend->internalSetItem($normalizedKey, $serialized);        
    }
    
    protected function internalRemoveItem(& $normalizedKey) {
        return $this->backend->internalRemoveItem($normalizedKey);
    }
    
    public function setTags($key, array $tags) {
        $this->tags = $tags;
        
    }

    public function getTags($key) {
        return $this->tags;
    }

    public function clearByTags(array $tags, $disjunction = false) {
        foreach ($tags as $tag) {
            $this->backend->removeItem($this->_mangleTag($tag));
        }
    }
    
    /**
     * Mangles the name to deny intersection of tag keys & data keys.
     * Mangled tag names are NOT saved in memcache $combined[0] value,
     * mangling is always performed on-demand (to same some space).
     * 
     * @param string $tag    Tag name to mangle.
     * @return string        Mangled tag name.
     */
    private function _mangleTag($tag)
    {
        return __CLASS__ . "_" . $tag;
    }

    /**
     * Generates a new unique identifier for tag version.
     * 
     * @return string Globally (hopefully) unique identifier.
     */
    private function _generateNewTagVersion()
    {
        static $counter = 0;
        $counter++;
        return md5(microtime() . getmypid() . uniqid('') . $counter); 
    }
}