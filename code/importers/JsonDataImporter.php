<?php

/**
 * @author marcus
 */
class JsonDataImporter extends ExternalContentImporter
{
    public function __construct()
    {
        parent::__construct();
        
        $transformer = new JsonDataTransformer($this);
        $this->contentTransforms['jsondata'] = $transformer;
        
    }
    protected function getExternalType($item)
    {
        return 'jsondata';
    }
}
