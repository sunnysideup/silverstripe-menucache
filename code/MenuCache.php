<?php

class MenuCache extends DataObjectDecorator {
	/**
	* fields are typicall header, menu, footer
	*/

	protected static $fields = array(
		0 => "Header",
		1 => "Menu",
		2 => "Footer",
		3 => "LayoutSection",
		4 => "other",
	);
		static function set_fields(array $array) {self::$fields = $array;}
		static function get_fields() {return self::$fields;}

	/* sets the cache number used for getting the "$Layout" of the individual page */
	protected static $layout_field = 3;
		static function set_layout_field($number) {self::$layout_field = $number;}
		static function get_layout_field() {return self::$layout_field;}

	protected static $tables_to_clear = array("SiteTree", "SiteTree_Live", "SiteTree_versions");
		static function get_tables_to_clear() {return self::$tables_to_clear;}

	function extraStatics(){
		$dbArray = array(
			"DoNotCacheMenu" => "Boolean"
		);
		foreach(self::$fields as $key => $field) {
			$dbArray[self::field_maker($key)]  = "HTMLText";
		}
		return array(
			'db' =>  $dbArray
		);
	}

	public static function field_maker($fieldNumber) {
		return "CachedSection".$fieldNumber;
	}

	public static function fields_exists($number) {
		return (isset(self::$fields[$number]));
	}

	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab("Root.Caching", new CheckboxField("DoNotCacheMenu","Do Not Cache Menu"));
		$fields->addFieldToTab("Root.Caching", new LiteralField("ClearCache","<a href=\"".$this->owner->Link("clearallfieldcaches")."\">clear cache (do this at the end of all edit sessions)</a>"));
		return $fields;
	}

	//-------------------- menu cache ------------------ ------------------ ------------------ ------------------ ------------------ ------------------

	function clearfieldcache ($showoutput = false) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$fieldsToClear = array();
		foreach(self::get_fields() as $key => $field) {
			$fieldName = self::field_maker($key);
			$fieldsToClear[] = "{$bt}".$fieldName."{$bt} = ''";
		}
		if(count($fieldsToClear)) {
			foreach(self::get_tables_to_clear() as $table) {
				$msg = '';
				$sql = "UPDATE {$bt}".$table."{$bt} SET ".implode(", ", $fieldsToClear);
				if(Director::URLParam("ID") == "days" && $days = intval(Director::URLParam("OtherID"))) {
					$sql .= ' WHERE {$bt}LastEdited{$bt} > ( NOW() - INTERVAL '.$days.' DAY )';
					$msg .= ', created before the last '.$days.' days';
				}
				elseif(Director::URLParam("ID") == "thispage") {
					$sql .= " WHERE  {$bt}".$table."{$bt}.{$bt}ID{$bt} = ".$this->owner->ID;
					$msg .= ', for page with ID = '.$this->owner->ID;
				}
				if($showoutput) {
					DB::alteration_message("Deleting cached data from $table, ".$msg);
					debug::show($sql);
				}
				DB::query($sql);
			}
		}
		return array();
	}

	//add this function to your page class if needed
	function onBeforeWrite() {
		//$this->clearfieldcache(); // technically this should be done, but it puts a lot of strain on saving so instead we encourage people to use ?flush=1
		parent::onBeforeWrite();
	}


}

class MenuCache_Controller extends Extension {

	public static $allowed_actions = array("showcachedfield","clearfieldcache","showuncachedfield", "clearallfieldcaches");

	protected function getHtml($fieldNumber) {
		if(MenuCache::get_layout_field() == $fieldNumber) {
			$className = $this->owner->ClassName;
			if("Page" == $className) {
				$className = "PageCached";
			}
			return $this->owner->renderWith(array($className, "PageCached"));
		}
		else {
			return $this->owner->renderWith('UsedToCreateCache'.$fieldNumber);
		}
	}

	function CachedField($fieldNumber) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$fieldName = MenuCache::field_maker($fieldNumber);
		if(isset($_REQUEST["flush"])) {
			$this->owner->clearfieldcache();
		}
		if(!(MenuCache::fields_exists($fieldNumber))) {
			user_error("$fieldName is not a field that can be cached", E_USER_ERROR);
		}
		else {
			if(!$this->owner->$fieldName || $this->owner->DoNotCacheMenu) {
				$fieldID = $fieldNumber;
				$content = $this->getHtml($fieldNumber);
				$sql = "Update {$bt}SiteTree_Live{$bt} Set {$bt}".$fieldName."{$bt} = '".$this->compressAndPrepareHTML($content)."' WHERE {$bt}ID{$bt} = ".$this->owner->ID." LIMIT 1";
				DB::query($sql);
				return $content;
			}
			else {
				return $this->owner->$fieldName;
			}
		}
	}


	private function compressAndPrepareHTML($html) {
		$pat[0] = "/^\s+/";
		$pat[1] = "/\s{2,}/";
		$pat[2] = "/\s+\$/";
		$rep[0] = "";
		$rep[1] = " ";
		$rep[2] = "";
		$html = preg_replace($pat, $rep, $html);
		$html = trim($html);
		return addslashes($html);
	}



	function showcachedfield($httpRequest = null) {
		$fieldNumber = $httpRequest->param("ID");
		return $this->getHtml($fieldNumber);
	}

	function showuncachedfield($httpRequest = null) {
		$this->owner->clearfieldcache();
		return $this->showcachedfield($httpRequest);
	}

	function clearallfieldcaches($httpRequest = null) {
		$this->owner->clearfieldcache(true);
		return 'fields have been cleared, <a href="/?flush=all">click to continue...</a>';
	}


}
