<?php

/**
 * @author marcus
 */
class DataImportAdmin extends ModelAdmin
{
    private static $url_segment = 'data-imports';
    private static $menu_title = 'Data Imports';
    private static $managed_models = array('DataImport');
}
