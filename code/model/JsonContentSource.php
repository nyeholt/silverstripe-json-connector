<?php

use Flow\JSONPath\JSONPath;

/**
 * @author marcus
 */
class JsonContentSource extends ExternalContentSource
{

    const DEFAULT_CACHE_LIFETIME = 1800;

    private static $db = array(
        'Url'           => 'Varchar(255)',
        'Method'        => 'Varchar',
        'CacheLifetime' => 'Int',
        'CollectionPath'    => 'Varchar(255)',
        'ItemValuePaths'        => 'MultiValueField',
        
        'ImportType'            => 'Varchar(64)',
        'ImportProperties'      => 'MultiValueField',
    );

    
    private static $has_one = array(
        'DataFile'      => 'File',
    );
    
    private static $defaults = array(
        'CacheLifetime' => self::DEFAULT_CACHE_LIFETIME
    );
    
    private static $selectable_types = array(
        'DataImport' => 'Data Import Object',
        'Page' => 'Standard Page',
    );
    
    private static $icon = 'rssconnector/images/rssconnector';

    protected $client;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        foreach (self::$db as $name => $du) {
            $fields->removeByName($name);
        }
        $fields->removeByName('DataFile');
        
        Requirements::css('rssconnector/css/RssContentAdmin.css');

        $dataSourceField = new SelectionGroup(
            "DataSourceField",
            array(
                new SelectionGroup_Item(
                    "url",
                    array(
                        new TextField('Url', 'JSON Feed URL'),
                        DropdownField::create('Method', 'Request type', array('GET' => 'GET', 'POST' => 'POST'))
                    ),
                    'From URL'
                ),
                new SelectionGroup_Item(
                    'file',
                    UploadField::create('DataFile', 'Data file'),
                    'From File'
                )
            )
        );
        
        if ($this->DataFileID) {
            $dataSourceField->setValue('file');
        } else {
            $dataSourceField->setValue('url');
        }
        
        $fields->addFieldToTab('Root.Main', $dataSourceField, 'ShowContentInMenu');

        $fields->addFieldToTab(
            'Root.Advanced',
            new NumericField('CacheLifetime', 'Cache Lifetime (in seconds)'));

        if (!$this->Url) {
            return $fields;
        }

        $fields->addFieldsToTab('Root.Main', array(
            new HeaderField('FeedDetailsHeader', 'Feed Details'),
            new LiteralField('JsonPathHelper', 'See <a href="http://jsonpath.com/" target="_blank">this</a> and <a href="https://jsonpath.curiousconcept.com" target="_blank">to test</a>'),
            
            new TextField('CollectionPath', 'JSONPath expression for collection'),
            KeyValueField::create('ItemValuePaths', 'PropertyName => JSONPath selectors')->setRightTitle('Set at least one mapping for ID. Use | to concat multiple fields together')
        ));
        
        $data = $this->jsonData();
        if ($data) {
            $fields->addFieldsToTab('Root.Main', array(
                new TextareaField('SampleContent', 'Matched collection (truncated...)', substr(json_encode($data, JSON_PRETTY_PRINT), 0, 2000)),
            ));
        }
        
        $fields->addFieldsToTab('Root.Import', array(
            DropdownField::create('ImportType', 'Import as object type', $this->config()->selectable_types),
            KeyValueField::create('ImportProperties', 'Static properties')->setRightTitle('Set these fields for all imported items'),
        ));

        return $fields;
    }

    /**
     * Attempts to get an RSS content item by GUID.
     *
     * @param  string|int $id
     * @return RssContentItem
     */
    public function getObject($id)
    {
        $decodedId    = $this->decodeId($id);
        $children = $this->stageChildren();

        foreach ($children as $child) {
            if ($child->getExternalID() == $decodedId) {
                return $child;
            }
        }
        return null;
    }

    public function getRoot()
    {
        return $this;
    }

    public function stageChildren($showAll = false)
    {
        $items    = $this->jsonData();
        $children = new ArrayList();
        
        if ($items) {
            foreach ($items as $item) {
                $newItem = new JsonContentItem($this, $item, $this->ItemValuePaths->getValues());
                if ($newItem->ID) {
                    $children->push($newItem);
                }
            }
        }
        
        return $children;
    }
    
    protected $json;

    /**
     * @return 
     */
    public function jsonData()
    {
        if (!$this->json && (strlen($this->Url) || $this->DataFileID) && $this->CollectionPath) {
            $raw = $this->getRawData();
            $this->json = @json_decode($raw);
            $this->json = (new JSONPath($this->json))->find($this->CollectionPath)->data();
        }

        return $this->json;
    }

    protected function getRawData() {
        if ($this->DataFileID) {
            $file = $this->DataFile();
            if ($file->hasMethod('ensureLocalFile') && $file->CDNFile && $file->reader() && $file->reader()->exists()) {
                $file->ensureLocalFile();
                if ($file->localFileExists()) {
                    $data = file_get_contents($this->DataFile()->getFullPath());
                    return $data;
                }
            } else {
                $data = '';
                if (file_exists($this->DataFile()->getFullPath())) {
                    $data = file_get_contents($this->DataFile()->getFullPath());
                }
                return $data;
            }
        } else if ($this->Url) {
            $client = RestfulService::create($this->Url, $this->CacheLifetime);
            $method = $this->Method ? $this->Method : 'GET';
            $res = $client->request('', $method);
            if ($res->getStatusCode() == 200) {
                return $res->getBody();
            }
        }
    }

    public function getContentImporter($target = null)
    {
        return Injector::inst()->get('JsonDataImporter');
    }

    public function allowedImportTargets()
    {
        return array('sitetree' => true);
    }

    public function canImport()
    {
        return $this->Url;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return ($t = $this->getField('CacheLifetime')) ? $t : self::DEFAULT_CACHE_LIFETIME;
    }
}