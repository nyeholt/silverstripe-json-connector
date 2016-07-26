<?php

/**
 * @author marcus
 */
class JsonDataTransformer implements ExternalContentTransformer
{
    protected $importer;
    
    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function transform($item, $parentObject, $duplicateStrategy)
    {
        $source = $item->getSource();
        
        $allowedTypes = Config::inst()->get('JsonContentSource', 'selectable_types');
        $selectedType = $source->ImportType;
        $selectedType = isset($allowedTypes[$selectedType]) ? $selectedType : 'ImportedJsonObject';
        
        $existing = null;
        $newObject = new $selectedType;
        
        
        if ($newObject instanceof Page) {
            $existing = $selectedType::get()->filter(array('Title' => $item->Title, 'ParentID' => $parentObject->ID))->first();
            
        } else if ($newObject instanceof ImportedJsonObject) {
            $existing = $selectedType::get()->filter('ExternalId', $item->getExternalId())->first();
        } else {
            
        }
        
        if ($existing) {
            switch ($duplicateStrategy) {
                case ExternalContentTransformer::DS_SKIP: {
                    return;
                }
                case ExternalContentTransformer::DS_OVERWRITE: {
                    $newObject = $existing;
                    break;
                }
            }
        }
        
        $fixedProperties = $source->ImportProperties->getValues();
        if ($fixedProperties && count($fixedProperties)) {
            foreach ($fixedProperties as $prop => $val) {
                $newObject->$prop = $val;
            }
        }
        
        $remoteProps = $item->getRemoteProperties();
        unset($remoteProps['ID']);
        foreach ($remoteProps as $field => $val) {
            $newObject->$field = $val;
        }
        // set the raw data
        if ($newObject instanceof ImportedJsonObject) {
            $newObject->ExternalId = $item->getExternalId();
            $newObject->RawData = $remoteProps;
            $newObject->SourceID = $source->ID;
        }
        
        $newObject->write();
        return $newObject;
    }
}
