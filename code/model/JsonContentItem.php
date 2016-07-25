<?php
/**
 * An JSON entry from an external JOSN feed.
 */
class JsonContentItem extends ExternalContentItem
{

    protected $item;
    protected $propertyPaths;

    /**
     * @param RssContentSource $source
     * @param SimplePie_Item $item
     */
    public function __construct($source = null, $item = null, $propertyPaths = null)
    {
        if (!$propertyPaths) {
            $propertyPaths = array();
        }
        $this->propertyPaths = $propertyPaths;
        
        if (is_object($item) || is_array($item)) {
            $this->item = $item;
            // $propertyPaths may have an ID path
            foreach ($propertyPaths as $name => $jsonPath) {
                $pathBits = explode('|', $jsonPath);
                if ($jsonPath == '$.*.8') {
                    $o = 0;
                }
                $valBits = array();
                $separator = '';
                foreach ($pathBits as $jpath) {
                    if ($jpath{0} != '$') {
                        // we have a separator
                        $separator = $jpath;
                        continue;
                    }
                    $path = (new Flow\JSONPath\JSONPath($this->item))->find($jpath);
                    $valBits[] = $path[0];
                }
                $this->$name = implode($separator, $valBits);
            }
            $item = isset($propertyPaths['ID']) ? $this->ID : (isset($item->id) ? $item->ID : null);
        }

        parent::__construct($source, $item);
    }

    public function init()
    {
//        unset($this->item);
    }

    public function numChildren()
    {
        return 0;
    }

    public function stageChildren($showAll = false)
    {
        return new ArrayList();
    }

    public function getType()
    {
        return 'sitetree';
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Behaviour', new ReadonlyField(
            'ShowInMenus', null, $this->ShowInMenus
        ));

        return $fields;
    }

    public function getGuid()
    {
        return $this->externalId;
    }

    public function canImport()
    {
        return false;
    }
}
