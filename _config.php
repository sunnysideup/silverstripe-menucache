<?php
/**
 * developed by www.sunnysideup.co.nz
 * author: Nicolaas modules [at] sunnysideup.co.nz
 **/

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START menucache MODULE ----------------===================
//MENU CHACHING
//Object::add_extension('SiteTree', 'MenuCache');
//Object::add_extension('ContentController', 'MenuCache_Controller');
//MenuCache::set_fields(array("Header", "Menu", "Footer", "LayoutSection", "Other"));
//MenuCache::set_layout_field(3); //NOTA BENE - MAKE SURE TO SET TO ANYTHING BUT 0-4 IF YOU DO NOT INCLUDE A LAYOUT SECTION!

//STATIC PUBLISHING
//Object::add_extension('SiteTree', 'MenuCacheStatic');
//Object::add_extension("SiteTree", "FilesystemPublisher('cache/', 'html')");
//MenuCacheStatic::add_class_name_to_cache("SiteTree");
//OPTIONAL SETTINGS
//MenuCacheStatic::remove_class_name_from_cache("UserDefinedForm");
//MenuCacheStatic::add_custom_url_to_cache("SiteTree");
//StaticPublisher::$disable_realtime = false;
//StaticPublisher::set_echo_progress(false);
//StaticPublisher::set_static_publisher_theme(false);
//FilesystemPublisher::set_static_base_url()
//FilesystemPublisher::set_static_publisher_theme()
//===================---------------- END menucache MODULE ----------------===================

