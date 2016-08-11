<?php

/**
 * @author marcus
 */
class DataImportAdmin extends ModelAdmin
{
    private static $url_segment = 'data-imports';
    private static $menu_title = 'Data Imports';
    private static $managed_models = array('DataImport');

    public function getEditForm($ID = null, $fields = null)
    {
        $form = parent::getEditForm($ID, $fields);

        // Update the custom summary fields to be sortable.

        $gridfield = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
        $gridfield->getConfig()->getComponentByType('GridFieldSortableHeader')->setFieldSorting(array(
            'ExternalIdSummary' => 'ExternalId'
        ));
        return $form;
    }
}
