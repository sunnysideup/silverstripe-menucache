<?php

class MenuCacheStatic extends DataObjectDecorator {

	protected static $class_names_to_cache = array();
		static function add_class_name_to_cache($className) {self::$class_names_to_cache[$className] = $className;}

	protected static $class_names_NOT_to_cache = array();
		static function remove_class_name_from_cache($className) {self::$class_names_NOT_to_cache[$className] = $className;}
		static function get_class_names_NOT_to_cache($className) {
			self::$class_names_NOT_to_cache["UserDefinedForm"] = "UserDefinedForm";
			return self::$class_names_NOT_to_cache;
		}

	protected static $custom_urls_to_add = array();
		static function add_custom_url_to_cache($url) {self::$custom_urls_to_add[$url] = $url;}

	function updateCMSFields(FieldSet &$fields) {
		$js = "window.open(this.href, 'publisher', 'toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=0,width=600,height=600'); return false;";
		$fields->addFieldToTab("Root.Caching", new LiteralField("PublishAllPages",'<p>This website runs a static cache to make it faster.  You may be required to manually publish the pages to the cache. You can <a href="/dev/buildcache/?flush=1" onclick="'.$js.'">publish all pages to cache</a> now...</p>'));
		return $fields;
	}

	//------------------ static publisher ------------------ ------------------ ------------------ ------------------
	/**
	 * Return a list of all the pages to cache
	 */
	function allPagesToCache() {
		// Get each page type to define its sub-urls
		$urls = array();
		// memory intensive depending on number of pages
		foreach(self::$class_names_to_cache as $className) {
			$pages = DataObject::get($className, "ClassName NOT IN('".implode("', '", self::$class_names_NOT_to_cache)."')");
			if($pages) {
				foreach($pages as $page) {
					$urls = array_merge($urls, (array)$page->subPagesToCache());
				}
			}
		}
		if(is_array(self::$custom_urls_to_add)) {
			if(count(self::$custom_urls_to_add)) {
				foreach(self::$custom_urls_to_add as $url) {
					$urls[] = $url;
				}
			}
		}
		return $urls;
	}

 /**
	 * Get a list of URLs to cache related to this page
	 */
	function subPagesToCache() {
		$urls = array();

		// add current page
		$urls[] = $this->owner->Link();

		// cache the RSS feed if comments are enabled
		if ($this->owner->ProvideComments) {
			$urls[] = Director::absoluteBaseURL() . "pagecomment/rss/" . $this->owner->ID;
		}

		return $urls;
	}

	function pagesAffectedByChanges() {
		$urls = $this->subPagesToCache();
		if($parent = DataObject::get_by_id("SiteTree", $this->owner->ParentID)) {
			$urls = array_merge((array)$urls, (array)$parent->subPagesToCache());
		}
		return $urls;
	}

}

