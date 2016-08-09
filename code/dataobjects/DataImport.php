<?php

/**
 * @author marcus
 */
class DataImport extends DataObject
{

    private static $db = array(
        'Title' => 'Varchar(255)',
        'ExternalId' => 'Varchar(128)',
        'RawData' => 'MultiValueField',
        'DataType' => 'Varchar(64)',
    );
    private static $indexes = array(
        'DataType',
    );
    private static $has_one = array(
        'Source' => 'ExternalContentSource',
    );

    public function setProp($fieldName, $val)
    {
        $props = $this->RawData->getValues();
        $props[$fieldName] = $val;
        $this->RawData = $props;
    }

    public function getProp($fieldName)
    {
        $props = $this->RawData->getValues();
        return ($props && isset($props[$fieldName])) ? $props[$fieldName] : null;
    }
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->replaceField('RawData', KeyValueField::create('RawData', 'Raw properties'));
        $fields->makeFieldReadonly('ExternalId');
        return $fields;
    }
}
