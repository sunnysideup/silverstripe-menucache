<?php

class MenuCacheStatic extends DataExtension
{

    private static $class_names_to_cache = array();
    public static function add_class_name_to_cache($className)
    {
        self::$class_names_to_cache[$className] = $className;
    }

    private static $class_names_NOT_to_cache = array();
    public static function remove_class_name_from_cache($className)
    {
        self::$class_names_NOT_to_cache[$className] = $className;
    }
    public static function get_class_names_NOT_to_cache($className)
    {
        self::$class_names_NOT_to_cache["UserDefinedForm"] = "UserDefinedForm";
        return self::$class_names_NOT_to_cache;
    }

    private static $custom_urls_to_add = array();
    public static function add_custom_url_to_cache($url)
    {
        self::$custom_urls_to_add[$url] = $url;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $js = "window.open(this.href, 'publisher', 'toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=0,width=600,height=600'); return false;";
        $fields->addFieldToTab("Root.Caching", new LiteralField("PublishAllPages", '<p>This website runs a static cache to make it faster.  You may be required to manually publish the pages to the cache. You can <a href="/dev/buildcache/?flush=1" onclick="'.$js.'">publish all pages to cache</a> now...</p>'));
        return $fields;
    }

    //------------------ static publisher ------------------ ------------------ ------------------ ------------------
    /**
     * Return a list of all the pages to cache
     */
    public function allPagesToCache()
    {
        // Get each page type to define its sub-urls
        $urls = array();
        // memory intensive depending on number of pages
        foreach (self::$class_names_to_cache as $className) {
            $pages = $className::get()
                ->exclude(array("ClassName" => self::$class_names_NOT_to_cache));
            if ($pages->count()) {
                foreach ($pages as $page) {
                    $urls = array_merge($urls, (array)$page->subPagesToCache());
                }
            }
        }
        if (is_array(self::$custom_urls_to_add)) {
            if (count(self::$custom_urls_to_add)) {
                foreach (self::$custom_urls_to_add as $url) {
                    $urls[] = $url;
                }
            }
        }
        return $urls;
    }

 /**
     * Get a list of URLs to cache related to this page
     */
    public function subPagesToCache()
    {
        $urls = array();

        // add current page
        $urls[] = $this->owner->Link();

        // cache the RSS feed if comments are enabled
        if ($this->owner->ProvideComments) {
            $urls[] = Director::absoluteBaseURL() . "pagecomment/rss/" . $this->owner->ID;
        }

        return $urls;
    }

    public function pagesAffectedByChanges()
    {
        $urls = $this->subPagesToCache();
        if ($parent = SiteTree::get()->byID($this->owner->ParentID)) {
            $urls = array_merge((array)$urls, (array)$parent->subPagesToCache());
        }
        return $urls;
    }
}
