<?php

/**
 * @author marcus
 */
class QueuedJsonDataImporter extends QueuedExternalContentImporter
{
    protected function getExternalType($item)
    {
        return 'jsondata';
    }

    protected function init() {
        $transformer = new JsonDataTransformer($this);
        $this->contentTransforms['jsondata'] = $transformer;
    }
}
