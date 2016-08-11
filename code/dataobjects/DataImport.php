<?php

/**
 * @author marcus
 */
class DataImport extends DataObject
{

    private static $db = array(
        'ExternalId' => 'Varchar(128)',
        'Title' => 'Varchar(255)',
        'RawData' => 'MultiValueField',
        'DataType' => 'Varchar(64)',
    );
    private static $summary_fields = array(
        'ExternalIdSummary' => 'External ID',
        'Title' => 'Title',
        'DataType' => 'Data Type'
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

    public function getExternalIdSummary()
    {
        return $this->ExternalId ? $this->ExternalId : '-';
    }
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->replaceField('RawData', KeyValueField::create('RawData', 'Raw properties'));
        $fields->makeFieldReadonly('ExternalId');
        return $fields;
    }
}
