<?php

namespace App;

/**
 * Skeleton subclass for representing a services for the TemplateForm entity.
 *
 * User
 *
 * You should add additional methods/hooks to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.GoatCheese.
 */
class TemplateFormWrapper extends TemplateForm
{
    public function __construct($request, $args)
    {
        parent::__construct($request, $args);
    }

    /**
     * Hook form after the data getter
     *
     * @param AuthyForm $obj
     * @param array $data
     * @param Template $dataObj
     * @return void
     */
    public function afterFormObj(array $data, Template &$dataObj)
    {
        $this->hookFormReadyJs = "
        var __dc = document.querySelector('.default-controls');
        if (__dc) { __dc.insertAdjacentHTML('afterend', '" . addslashes(button(span("View variables"), "id='viewVarNg' class='ac-button ac-light-red' style='    border-left: 1px solid #008bc5;'")) . "'); }
        var __vv = document.getElementById('viewVarNg');
        if (__vv) {
            __vv.addEventListener('click', function (){
                if (window.gcScreens) {
                    gcScreens.openPanel({
                        url: '" . _SITE_URL . "Template/viewVar',
                        title: 'Template variables',
                        refreshOnClose: false
                    });
                }
            });
        }
        ";
    }

    // NOTE: the old "insert image at cursor" pair (startChildListRowTemplateFile +
    // the [j=insertToCursor] click wiring) was removed. startChildListRowTemplateFile
    // was never dispatched by the framework (the real per-child-row hook is
    // beforeListTr<Child>), so its [j=insertToCursor] icons were never emitted and
    // the click wiring was a no-op. The live equivalent is the framework's
    // "To editor" control ([j=copy_link]), emitted by is_file_upload_table with
    // image_support:"yes" on a wysiwyg table and gated by the
    // add_child_insert_wysiwyg_tables parameter.
    public function afterList(&$request, &$pmpoData){}
    public function beforeListTr(&$altValue, $data, $i, $param, &$hookListColumns){}
    public function beforeListSearch(&$q, &$search){}
    public function afterListSearch(&$q, &$search){}
    public function beforeChildSearchTemplateFile(&$q){}
    public function beginSelectbox(&$pcDataO, &$q){}
}
