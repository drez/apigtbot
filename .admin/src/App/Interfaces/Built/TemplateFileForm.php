<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'TemplateFile' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class TemplateFileForm extends TemplateFile
{

    use Helper;
    use \ApiGoat\ACL\AuthyACL;

    /**
    *   Virtual name of the object (class)
    */
    public $request = null;
    public $args = null;
    public string $model_name = '';
    public $isChild;
    public $IdPk;
    public $in;
    public $TableName;
    public $tableDescription;
    public $uiTabsId;
    public $maxPerPage;
    public $childMaxPerPage;
    public $VirtualClassName;
    public $virtualClassName;
    public string $listActionCell = '';

    public $setReadOnly;
    public $forceInlineEdit;
    public $forcePopUpEdit;

    public $searchAr;
    public $searchMs;
    public $searchOrder;

    public $hookFormTop;
    public $hookFormInnerTop;
    public $hookFormBottom;
    public $hookFormInnerBottom;

    public $hookFormReadyJsFirst;
    public $hookFormReadyJs;
    public $hookFormIncludeJs;

    public $hookFormRoTop;
    public $hookFormRoBottom;

    public $hookChildListRoTop;
    public $hookChildListRoBottom;

    public $hookListTop;
    public $hookListBottom;
    public $hookListColumns;
    public $hookListSearchTop;
    public $hookListSearchButton;
    public $hookListReadyJs;
    public $hookListJs;
    public $hookListReadyJsFirst;
    public $setListRemoveDelete;
    public $hookSwHeader;
    public $printLink;
    public $formTitle;
    public $ccStdFormOptions;
    public $cCMainTableHeader;
    public $cCmoreColsHeader;
    public $hookLogin;

    public $canDelete;

    public $listAddButton;
    public $IdParent;
    public $SaveButtonJs;
    public $bindEditJs;
    public $formAddButton;
    public $dataObj;
    public $formSaveBtn;
    public $formSaveBar;
    public $omMap;



    /**
    *   Ressource object for the database
    *   @type object
    **/
    public $pmpoData;

    /**
     * Constructor
     *
     * @param Request|array|null $request
     * @param array $args
     */
    function __construct(Request|array|null $request, array $args)
    {
        $this->request = $request;
        $this->args = $args;
        $this->model_name = 'TemplateFile';
        $this->virtualClassName = 'TemplateFile';
        $this->childMaxPerPage = (defined('app_child_max_per_page') && (int) app_child_max_per_page > 0) ? (int) app_child_max_per_page : 30;
        $this->maxPerPage = (defined('app_max_per_page') && (int) app_max_per_page > 0) ? (int) app_max_per_page : 50;
        $this->hookFormBottom = '';
        $this->hookFormReadyJs = '';

    }

    /**
     * function getListSearch
     * @param integer $IdParent
     * @param array $search
     * @return type
     */
    public function getListSearch($IdParent='', $search='')
    {
        $this->in = 'getListSearch';

        $q = new TemplateFileQuery();
        $q = $this->setAclFilter($q);
        

        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form
            
            
        }else{
            ## standard list
            
        }
        
        $hasParent = json_decode((string) $IdParent);
        if (!empty($hasParent)) {
            $q->filterByIdTemplate($hasParent);
        }

        
            if(!empty($this->searchOrder)){
                $f=0;
                foreach($this->searchOrder as $order){
                    foreach($order as $col => $sens){
                        if($sens){
                            $tOrd = explode('.',$col);
                            if($tOrd[1]){
                                $q->join($tOrd[0]." order".$f);
                                $orderBy = "use".$tOrd[0]."Query";
                                $q->$orderBy("order".$f, 'left join')->orderBy($tOrd[1], $sens)->endUse();
                            }else{
                                $q->orderBy($col,$sens);
                            }
                            $this->orderReadyJsOrder .="
                                var __se=document.querySelector(\"#TemplateFileListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
                            ";
                        }
                        $f++;
                    }
                }
            }
            
        
        

        $this->pmpoData = $q;
        

        return $this->pmpoData;
    }

    /**
     * function getListHeader
     * @param string $act
     * @return string|null
     */
    public function getListHeader(string $act): ?string
    {
        $this->in = 'getListHeader';
        $trSearch = '';
        $trHeadMod = '';

        switch($act) {
            case 'head':
                $trHead = th(_("Name"), " th='sorted' c='Name' title='" . _('Name')."' ")
.th(_("File"), " th='sorted' c='File' title='" . _('File')."' ")
. $this->cCmoreColsHeader;
                if(!$this->setReadOnly){
                    $trHead .= th('&nbsp;',' class="actionrow delete" ');
                }
                $trHead = thead(tr($trHead));
                return $trHead;

            case 'list-button':
                $listButton = '';
                
                
                return $listButton;

            case 'search':
                
                
                ;
                return $trSearch;

            case 'add':
            ###### ADD
                if(!empty($this->IdParent)){ return htmlLink("<i class='ri-upload-2-line'></i>"._("Upload"), "Javascript:","id='pickfiles' title='"._('Upload new file')."' class='add-btn'").div('', 'filelist')."<span class='gc-upl-cfg' data-gc-upl='child' data-gc-upl-url='"._SITE_URL."TemplateFile/upload' data-gc-upl-conglet='Template' data-gc-upl-child='TemplateFile' data-gc-upl-max='10mb' data-gc-upl-ext='jpg,gif,png,jpeg' data-gc-upl-ip='".htmlspecialchars((string) $this->IdParent, ENT_QUOTES)."' data-gc-upl-user='".$_SESSION[_AUTH_VAR]->get('id')."'></span>"; }
             if($_SESSION[_AUTH_VAR]->hasRights('TemplateFile', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addTemplateFile' title='"._('Add')."' class='button-link-blue add-button'");
            }

            return $this->listAddButton;
            break;
            case 'quickadd':
                return $trHeadMod;
        }
    }

    /**
     * produce a list of table items
     * @param	string $uiTabsId	html destination container Id
     * @param	string $page		nbr. of line per pages
     * param	string $IdParent	Parent id (if necessary)
     * param	obj $pmpoDataIn	PropelModelPager reference to show instead of default search OR a standard propel collection
     * @param	array $search		search params for custom search query
     * 						[ms]	pre set with progXform/search_items behavior
     *					custom search
     *						[f]	filter	[v]	value	use by progXform/child_menu_query
     *						[u]	use		[f]	filter	[uv] use filter value
     * @return string
     */
    public function getList( $request, $uiTabsId = 'tabsContain', $IdParent = null , $pmpoDataIn = null)
    {
        $HelpDivJs = '';
        $HelpDiv = '';
        $this->in = 'getList';
        $this->isChild = '';
        $this->TableName = 'TemplateFile';
        $altValue = array (
  'IdTemplateFile' => NULL,
  'IdTemplate' => NULL,
  'Name' => NULL,
  'File' => NULL,
  'DateCreation' => NULL,
  'DateModification' => NULL,
  'IdGroupCreation' => NULL,
  'IdCreation' => NULL,
  'IdModification' => NULL,
);
        $tr = '';
        $trDt = '';
        $hook = [];
        $editEvent = '';
        $return = ['html' => '', 'js' => '', 'onReadyJs' => ''];
        $cCmoreCols = '';

        

        // SECURITY (review H7): uiTabsId comes from request['ui'] and is reflected
        // raw into the list container's data-ui attribute and into the quick-add
        // onReadyJs ($rowFormJs). Container ids are always [A-Za-z0-9_], so strip
        // anything else here — closes both the attribute and JS-string sinks.
        $uiTabsId = preg_replace('/[^A-Za-z0-9_]/', '', (string) $uiTabsId);
        $this->uiTabsId = $uiTabsId;

        
        $this->IdParent = $IdParent;
        // Child-tab / nested list: mark context for behaviors that branch on isChild.
        if ($IdParent !== null && $IdParent !== '') {
            $this->isChild = 'TemplateFile';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'TemplateFile/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'TemplateFile/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['TemplateFile/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'TemplateFile/');

        
        
        
        
        
        

        // Parent-scoped lists use the child pager size (same as former inlined getChildList).
        $maxPerPage = ($IdParent !== null && $IdParent !== '') ? $this->childMaxPerPage : $this->maxPerPage;
        if ((int) ($request['maxperpage'] ?? 0) > 0) {
            $maxPerPage = (int) $request['maxperpage'];
        }

        $resultsCount = 0;
        if(empty($pmpoDataIn)) {
            $pmpoData = $this->getListSearch($IdParent, $search);
            $pmpoData = $pmpoData->paginate($search['page'], $maxPerPage);
            $resultsCount = $pmpoData->getNbResults();

        }else{
            $pmpoData = $pmpoDataIn;
        }

        $trHead = $this->getListHeader('head');

        // Initialized before both branches: an empty list skips the row loop that
        // otherwise first sets it, and rowCount below reads it unconditionally.
        $i = 0;
        if( $pmpoData->isEmpty() ) {
            $tr .= tr(	td(p(span(_("Nothing here at the moment")),'class="no-results"'), "t='empty' colspan='100%' "));

        }else{
            if( get_class($pmpoData) == 'PropelModelPager' ) {
                $pcData = $pmpoData->getResults();
            }else{
                $pcData = $pmpoData;
            }

            /**
            *	Main list loop
            **/
            
            $i=0;
            $gcGroupCol = 'Name';
            $gcGroupNorm = function($s){ return strtolower(preg_replace('/[^a-z0-9]/i','', (string) $s)); };
            $gcGroupKey = $gcGroupNorm($gcGroupCol);
            // Use the RAW request order, not the resolved $this->searchOrder
            // (getListSearch mutates the latter). Empty => default landing
            // view => list is in its default (name) order => group A–Z, as
            // the guideline screenshots show. A user sort only keeps the
            // headers when it is the name column ascending.
            $gcReqOrder = $request['order'] ?? '';
            $gcGroupOn = false;
            // Child-context lists (IdParent set) never letter-group: their
            // default order is the child ranking/FK order, not the name
            // column, so the empty-order assumption below doesn't hold and
            // the letters render as stray one-letter rows in the drawer.
            if (empty($IdParent)) {
                if ($gcReqOrder === '' || $gcReqOrder === null) {
                    $gcGroupOn = true;
                } else {
                    $gcOd = is_array($gcReqOrder) ? $gcReqOrder : json_decode((string) $gcReqOrder, true);
                    if (is_array($gcOd) && isset($gcOd['col'])) {
                        $gcFc = (string) $gcOd['col'];
                        if (strpos($gcFc, '.') !== false) { $gcParts = explode('.', $gcFc); $gcFc = end($gcParts); }
                        $gcSens = strtolower((string) ($gcOd['sens'] ?? ''));
                        if ($gcGroupNorm($gcFc) === $gcGroupKey && $gcSens !== 'desc') { $gcGroupOn = true; }
                    }
                }
            }
            $gcGroupLetter = null;
            
            if(!$this->setReadOnly && !$this->setListRemoveDelete){
                if($_SESSION[_AUTH_VAR]->hasRights('TemplateFile', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteTemplateFile' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName()));
                    $gcL = mb_strtoupper(mb_substr(trim($gcVal), 0, 1));
                    if ($gcL !== '' && $gcL !== $gcGroupLetter) {
                        $gcGroupLetter = $gcL;
                        $tr .= div(htmlspecialchars($gcL), '', " class='va-mob-sect-head' ");
                    }
                }
                # hoist the row PK encodings once — reused by the mobile + desktop row wrappers below
                $__pkJsonEsc = htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES);
                $__pkEsc = htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES);
                $this->listActionCell = '';
                
                
                
                $this->listActionCell .= htmlLink(img(_SITE_URL.$data->getFile(), '25', '25'), _SITE_URL.$data->getFile(), "j='imageTemplateFile' i='".$__pkEsc."' target='_blank'");

                $actionCell =  td($this->canDelete . $this->listActionCell, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." ", "   i='" . $__pkJsonEsc . "' c='Name' class=''  j='editTemplateFile'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['File'] !== null ) ? $altValue['File'] : $data->getFile())))." ", "   i='" . $__pkJsonEsc . "' c='File' class=''  j='editTemplateFile'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='TemplateFileRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." "), "  i='" . $__pkJsonEsc . "' c='Name' class=''  j='editTemplateFile'") .
                td(span(htmlspecialchars((string)((($altValue['File'] !== null ) ? $altValue['File'] : $data->getFile())))." "), "  i='" . $__pkJsonEsc . "' c='File' class=''  j='editTemplateFile'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='TemplateFileDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountTemplateFile', $i);
        }

        

        ## @Paging
        $pagerRow = $this->getPager($pmpoData, $resultsCount, $search);
        $bottomRow = div($pagerRow,'bottomPagerRow', "class='tablesorter'");

        

        $controlsContent = $this->getListHeader('list-button');

        $return['html'] =
            $this->hookListTop
            .div(
                div(
                    href(span(_('Open/close menu')),'javascript:','class="toggle-menu button-link-blue trigger-menu"')
                    .$this->getListHeader('add')
                    
                ,'','class="default-controls"')
                .div($controlsContent,'TemplateFileControlsList', "class='custom-controls'")
                .$this->hookSwHeader.$HelpDiv
                
            ,'','class="sw-header"')

            /*.div(
                $this->getListHeader('add')
                .button('', 'class="scroll-top" type="button"')
            , '' ,'class="ac-list-form-header ac-show-scroll"')*/
            .div(
                div(
                    div(
                        button("<i class='ri-menu-line'></i>", " class='menu-btn' type='button' ")
                        .span(_('File'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('TemplateFile', 'a') && !$this->setReadOnly) ? (!empty($this->IdParent) ? href("<i class='ri-upload-2-line'></i>"._('Upload'), 'Javascript:', " class='add-btn' data-upload-trigger='pickfiles' ") : href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ")) : '')
                    ,''," class='va-mob-row1' ")
                    .div(
                        "<i class='ri-search-line'></i>"
                        .$this->getListHeader('search')
                    ,''," class='va-mob-search' ")
                ,''," class='va-mob-header' ")
                .$trChips
                .$gcSortClear
                .div(
                    div('',''," class='va-mob-sortsheet-dim' ")
                    .div(
                        div("", '', " class='sheet-handle' ")
                        .div(
                            span(_('Sort by'), " class='sheet-title' ")
                            .button("<i class='ri-close-line'></i>", " type='button' class='sheet-close va-mob-sortsheet-close' aria-label='"._('Close')."' ")
                        ,''," class='sheet-head' ")
                        .div("".$gcSortSheetClear . button(_("Name"), " type='button' th='sorted' c='Name' class='va-mob-sortrow' ") . button(_("File"), " type='button' th='sorted' c='File' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='TemplateFileTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'TemplateFileListForm', " class='va-mob proto-app' data-model='TemplateFile' data-table='TemplateFile' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='TemplateFile' data-parent='Template'" : ''));

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addTemplateFileAutoc');
        if(__ab){
            __ab.addEventListener('click', function () {
                var __b=new URLSearchParams();__b.set('a','ixmemautoc');__b.set('p','{$this->virtualClassName}');
                fetch('"._SITE_URL."GuiManager',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest'},body:__b.toString()}).then(function(){document.location='"._SITE_URL.$this->virtualClassName."/edit/';});
            });
        }
        
        
        ".$this->orderReadyJsOrder."
        ".$this->hookListReadyJs;
        
        $return['js'] .= script("". $this->hookListJs);
        return $return;
    }
    /*
    *	Make sure default value are set before save
    */
    public function setCreateDefaultsTemplateFile(array $data): TemplateFile
    {

        unset($data['IdTemplateFile']);
        $e = new TemplateFile();


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //integer not required
        $e->setName( ($data['Name'] == '' ) ? null : $data['Name']);
        //integer not required
        $e->setFile( ($data['File'] == '' ) ? null : $data['File']);
        $e->setDateCreation( ($data['DateCreation'] == '' || $data['DateCreation'] == 'null' || substr($data['DateCreation'],0,10) == '-0001-11-30') ? null : $data['DateCreation'] );
        $e->setDateModification( ($data['DateModification'] == '' || $data['DateModification'] == 'null' || substr($data['DateModification'],0,10) == '-0001-11-30') ? null : $data['DateModification'] );
        //foreign
        $e->setIdGroupCreation(( $data['IdGroupCreation'] == '' ) ? null : $data['IdGroupCreation']);
        //foreign
        $e->setIdCreation(( $data['IdCreation'] == '' ) ? null : $data['IdCreation']);
        //foreign
        $e->setIdModification(( $data['IdModification'] == '' ) ? null : $data['IdModification']);
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsTemplateFile(array $data): ?TemplateFile
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(TemplateFileQuery::class, json_decode($data['i']), 'TemplateFile', 'w');
        if ($e === null) { return null; }


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['Name'])){
            $e->setName( ($data['Name'] == '' ) ? null : $data['Name']);
        }
        if(isset($data['File'])){
            $e->setFile( ($data['File'] == '' ) ? null : $data['File']);
        }
        if(isset($data['DateCreation'])){
            $e->setDateCreation( ($data['DateCreation'] == '' || $data['DateCreation'] == 'null' || substr($data['DateCreation'],0,10) == '-0001-11-30') ? null : $data['DateCreation'] );
        }
        if(isset($data['DateModification'])){
            $e->setDateModification( ($data['DateModification'] == '' || $data['DateModification'] == 'null' || substr($data['DateModification'],0,10) == '-0001-11-30') ? null : $data['DateModification'] );
        }
        if( isset($data['IdGroupCreation']) ){
            $e->setIdGroupCreation(( $data['IdGroupCreation'] == '' ) ? null : $data['IdGroupCreation']);
        }
        if( isset($data['IdCreation']) ){
            $e->setIdCreation(( $data['IdCreation'] == '' ) ? null : $data['IdCreation']);
        }
        if( isset($data['IdModification']) ){
            $e->setIdModification(( $data['IdModification'] == '' ) ? null : $data['IdModification']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of TemplateFile
     * @param	string $id			PrimaryKey of the record to show
     * @param	string $uiTabsId	Present everywhere, javascript id of the html container
     * @param	string $data 		If present, will skip the query and show the data
     * @param	array $error			Error to display
     * @param	array $jsElement		container to append new event results
     * @param	array $jsElementType	container type to append new event results
     * @return	array standard html retrun array
    */

    public function getEditForm($id, $uiTabsId = 'tabsContain', $data=[], $error=[], $jsElement='', $jsElementType=''): array
    {
        $this->in = "getEditForm";

        $HelpDivJs = '';
        $HelpDiv = '';
        $childTable = [];
        $script_autoc_one = '';
        $ongletf = '';
        $mceInclude = '';
        $ip_save = '';
        $ip_save = '';
        $IdParent = 0;
        $editDialog = ( $data['dialog'] ) ? $data['dialog'] : 'editDialog';
        $uiTabsId = ( $uiTabsId === null ) ? 'tabsContain' : $uiTabsId;
        $jet = 'tr';

        $je = "TemplateFileTable";

        if($jsElement)	{
            $je = $jsElement;
        }

        if($jsElementType)	{
            $jet = $jsElementType;
        }

        if($data['data']['ip']){
            $data['ip'] = $data['data']['ip'];
            $data['pc'] = $data['data']['pc'];
            $data['tp'] = $data['data']['tp'];
        }

        if($data['pc']) {
            switch($data['pc']){

                case 'Template':
                    $data['IdTemplate'] = $data['ip'];
                    break;
                case 'AuthyGroup':
                    $data['IdGroupCreation'] = $data['ip'];
                    break;
                case 'Authy':
                    $data['IdModification'] = $data['ip'];
                    break;
            }
            $IdParent = $data['ip'];
        }

        if(!$IdParent && isset($data['ip']) && $data['ip'] !== '' && $data['ip'] != 0) {
            $IdParent = $data['ip'];
            $data['IdTemplate'] = $data['ip'];
        }

        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('TemplateFile', 'a') && !$this->setReadOnly) {

            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = TemplateFileQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('TemplateFile', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->TemplateFile['isNew'] = 'yes';
            $dataObj = new TemplateFile();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdTemplate";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->TemplateFile['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;












$this->fields['TemplateFile']['Name']['html'] = stdFieldRow(_("Name"), input('text', 'Name', htmlentities((string)($dataObj->getName() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Name'))."' size='35'  v='NAME' s='d' class=''  ")."", 'Name', "", $this->commentsName, $this->commentsName_css, ' half', ' ', 'no', 'v2');
$this->fields['TemplateFile']['File']['html'] = stdFieldRow(_("File"), input('text', 'File', htmlentities((string)($dataObj->getFile() ?? '')), "   placeholder='".str_replace("'","&#39;",_('File'))."' size='69'  v='FILE' s='d' class=''  ")."", 'File', "", $this->commentsFile, $this->commentsFile_css, '', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['TemplateFile']['IdTemplate']['html'] = input('hidden', 'IdTemplate', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('TemplateFile','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('TemplateFile','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveTemplateFile', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedTemplateFile','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdTemplateFile', $dataObj->getIdTemplateFile(), " s='d' pk").input('hidden', 'IdTemplate', $dataObj->getIdTemplate(), " s='d' nodesc").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }



        //Form header (legacy list-page chrome: toggle-menu + Add new).
        // Class renamed from .sw-header → .legacy-list-toolbar to avoid
        // collision with the new drawer scaffold header below.
        $header_top = div(
                            div(href(span(_('Display/Hide menu')),'javascript:','class="toggle-menu button-link-blue trigger-menu"')
                        .$this->formAddButton,'','class="default-controls"')
                        .$this->printLink
                            .$this->hookSwHeader.$HelpDiv
                        , '', 'class="legacy-list-toolbar"');
        $header_top_onglet = $this->formTitle.$ongletf;
        $identityHtml = '';

        $_gcFooterHtml = '';

        // === Edit-drawer footer (T16) ===
        $_gcDateCreation = (string)$dataObj->getDateCreation();
        $_gcCreatedSpan = ($_gcDateCreation !== '') ? "<span>" . _('Created') . " " . htmlspecialchars($_gcDateCreation) . "</span>" : '';
        $_gcDateModification = (string)$dataObj->getDateModification();
        $_gcModifiedSpan = ($_gcDateModification !== '') ? "<span> &middot; " . _('Last modified') . " " . htmlspecialchars($_gcDateModification) . "</span>" : '';
        // Read-only view (setReadOnly='all', e.g. crossref ro=1): no Delete.
        $_gcFooterDeleteBtn = $this->setReadOnly ? '' : "<button type='button' class='sw-delete' j='delete' rid='" . htmlspecialchars((string)$id) . "'><i class='ri-delete-bin-line'></i> " . _('Delete') . "</button>";
        $_gcFooterHtml =
            "<footer class='sw-footer'>"
            . "<div class='sw-meta'>" . $_gcCreatedSpan . $_gcModifiedSpan . "</div>"
            . $_gcFooterDeleteBtn
            . "</footer>";


        // Stage child-tabs + child-pannel for the .sw-drawer scaffold
        // (with_child_tables sets them if the entity has children;
        // otherwise they remain empty).
        $childTabsHtml = '';
        $childPannelHtml = '';


        // Form: spec scaffold (docs/superpowers/specs/2026-05-19-edit-drawer-fidelity-design.md).
        // .sw-drawer > .sw-header (form-nav + child-tabs) + .sw-identity
        // + .sw-tabnav + .sw-body (tab-panes + child-pannel) + .sw-footer.
        // .form-scroll / .form-nav / .proto-form classes are kept as
        // aliases on .sw-body / .sw-header / .sw-drawer so screens.js +
        // existing selectors keep working.
        $return = ['html' => '', 'js' => '', 'onReadyJs' => ''];
        $return['html'] =
        $this->hookFormTop
        .$mceInclude
        .$header_top
        .form(
            div(
                div(
                    div(
                        href('<i class="ri-arrow-left-s-line"></i>'._('File'), _SITE_URL.'TemplateFile', "class='nav-btn'")
                        .div(
                            span(_('File'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'TemplateFile', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['TemplateFile']['Name']['html']
.$this->fields['TemplateFile']['File']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntTemplateFile", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formTemplateFile' class='mainForm formContent' data-gc-upl='form' data-gc-upl-url='"._SITE_URL."TemplateFile/upload' data-gc-upl-done='"._SITE_URL."TemplateFile/list/' data-gc-upl-max='10mb' data-gc-upl-ext='jpg,gif,png,jpeg' data-gc-upl-file='File' data-gc-upl-fk='IdTemplate' data-gc-upl-pk='IdTemplateFile' data-gc-upl-parent='Template' data-gc-upl-table='TemplateFile' data-gc-upl-txt-upload='".htmlspecialchars(_('Upload'),ENT_QUOTES)."' data-gc-upl-txt-replace='".htmlspecialchars(_('Replace file'),ENT_QUOTES)."' data-gc-upl-txt-failed='".htmlspecialchars(_('Upload failed'),ENT_QUOTES)."' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['TemplateFile']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['TemplateFile']['ixmemautocapp'];
            unset($_SESSION['mem']['TemplateFile']['ixmemautocapp']);
        }

        $return['js'] .= $childTable['js']
        . script($this->hookFormIncludeJs) ."
        ";

        $return['onReadyJs'] =
        $this->hookFormReadyJsFirst.
        "

        ".$this->bindEditJs."
        ".$this->SaveButtonJs."

        ".$childTable['onReadyJs']."
        ".$error['onReadyJs']."
        ".$tabs_act."
        ".$this->hookFormReadyJs
        .$script_autoc_one
        .$HelpDivJs."
        ";
        // #23 S5: the #loader hide moved to screens.js push(); the setTimeout(400)
        // gcSelectBox.bindWithin re-bind was redundant (push() binds the screen,
        // idempotent). Both off the re-exec arm.
        return $return;
    }

    function lockFormField($fields, $dataObj)
    {

        $this->fieldsRo['TemplateFile']['Name']['html'] = stdFieldRow(_("Name"), div( htmlspecialchars((string)($dataObj->getName()), ENT_QUOTES), 'Name_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Name', $dataObj->getName(), "s='d'"), 'Name', "", $this->commentsName, $this->commentsName_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['TemplateFile']['File']['html'] = stdFieldRow(_("File"), div( htmlspecialchars((string)($dataObj->getFile()), ENT_QUOTES), 'File_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'File', $dataObj->getFile(), "s='d'"), 'File', "", $this->commentsFile, $this->commentsFile_css, 'readonly', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['TemplateFile'] as $field => $ar) {
                $this->fields['TemplateFile'][$field]['html'] = $this->fieldsRo['TemplateFile'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['TemplateFile'][$field]['html'] = $this->fieldsRo['TemplateFile'][$field]['html'];
            }
        }
    }
}
