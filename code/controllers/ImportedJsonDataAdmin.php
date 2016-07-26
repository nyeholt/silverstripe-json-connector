<?php

/**
 * @author marcus
 */
class ImportedJsonDataAdmin extends ModelAdmin
{
    private static $url_segment = 'jsondata';
    private static $menu_title = 'JSON Data';
    private static $managed_models = array('ImportedJsonObject');
}
