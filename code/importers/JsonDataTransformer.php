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
        $current = Versioned::current_stage();
        Versioned::reading_stage('Stage');
        
        $source = $item->getSource();
        
        $allowedTypes = Config::inst()->get('JsonContentSource', 'selectable_types');
        $selectedType = $source->ImportType;
        $selectedType = isset($allowedTypes[$selectedType]) ? $selectedType : 'DataImport';
        
        $existing = null;
        $newObject = new $selectedType;
        
        
        if ($newObject instanceof SiteTree) {
            $existing = $selectedType::get()->filter(array('Title' => $item->Title, 'ParentID' => $parentObject->ID))->first();
            
        } else if ($newObject instanceof DataImport) {
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
        
        // set now, so that later functionality can overwrite. 
        $newObject->ParentID = $parentObject->ID;
        $doPublish = false;
        
        $fixedProperties = $source->ImportProperties->getValues();
        if ($fixedProperties && count($fixedProperties)) {
            foreach ($fixedProperties as $prop => $val) {
                $newObject->$prop = $val;
                if ($prop == 'DoPublish' && $val) {
                    $doPublish = true;
                }
            }
        }
        
        $remoteProps = $item->getRemoteProperties();
        unset($remoteProps['ID']);
        foreach ($remoteProps as $field => $val) {
            $newObject->$field = $val;
        }
        // set the raw data
        if ($newObject instanceof DataImport) {
            $newObject->ExternalId = $item->getExternalId();
            $newObject->RawData = $remoteProps;
            $newObject->SourceID = $source->ID;
        }
        
        $newObject->write();
        
        $newObject->extend('onJsonDataImport', $item);
        
        if ($doPublish) {
            $newObject->publish('Stage', 'Live');
        }
        
        Versioned::reading_stage($current);
        return $newObject;
    }
}
