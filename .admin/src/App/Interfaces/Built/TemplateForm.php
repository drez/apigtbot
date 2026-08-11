<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'Template' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class TemplateForm extends Template
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

        public $queryObjTemplateFile;
    public $listActionCellTemplateFile;


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
        $this->model_name = 'Template';
        $this->virtualClassName = 'Template';
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

        $q = new TemplateQuery();
        $q = $this->setAclFilter($q);
        

        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['Name']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Name'], $criteria);

            $q->filterByName($value, $criteria);
        }
            
        }else{
            ## standard list
            
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
                                var __se=document.querySelector(\"#TemplateListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
.th(_("Subject"), " th='sorted' c='Subject' title='" . _('Subject')."' ")
.th(_("Status"), " th='sorted' c='Status' title='" . _('Status')."' ")
.th(_("Language"), " th='sorted' c='Lang' title='" . _('Language')."' ")
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
                
                $data = [];
                $data['Name'] = ( !empty( $this->searchMs['Name'])) ? $this->searchMs['Name']:'';
            

                $trSearch = ''
                .form(div(div(input('text', 'Name', $this->searchMs['Name'], '  title="'._('Name').'" placeholder="'._('Search').' '._('Name').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.div(
                           button(span(_("Search")),'id="msTemplateBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msTemplateBtClear"')
                           .input('hidden', 'Seq', $data['Seq'] )
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsTemplate' class='va-mob-searchform' data-entity='Template'");;
                return $trSearch;

            case 'add':
            ###### ADD
                 if($_SESSION[_AUTH_VAR]->hasRights('Template', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addTemplate' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'Template';
        $altValue = array (
  'IdTemplate' => NULL,
  'Name' => NULL,
  'Subject' => NULL,
  'Color1' => NULL,
  'Color2' => NULL,
  'Color3' => NULL,
  'Status' => NULL,
  'Body' => NULL,
  'Footer' => NULL,
  'Lang' => NULL,
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
            $this->isChild = 'Template';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'Template/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'Template/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['Template/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'Template/');

        
        
        $default_order[]['DateCreation']='DESC';
        if(empty($this->searchOrder)){
            $this->searchOrder = $default_order;
        }
        
        
        
        

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
                    $gcGroupOn = false;
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
                if($_SESSION[_AUTH_VAR]->hasRights('Template', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteTemplate' ");
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
                
                
                
                

                $actionCell =  td($this->canDelete . $this->listActionCell, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." ", "   i='" . $__pkJsonEsc . "' c='Name' class=''  j='editTemplate'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Subject'] !== null ) ? $altValue['Subject'] : $data->getSubject())))." ", "   i='" . $__pkJsonEsc . "' c='Subject' class=''  j='editTemplate'") . span(htmlspecialchars((string)((($altValue['Status'] !== null ) ? $altValue['Status'] : isntPo($data->getStatus()))))." ", "   i='" . $__pkJsonEsc . "' c='Status' class='center'  j='editTemplate'") . span(htmlspecialchars((string)((($altValue['Lang'] !== null ) ? $altValue['Lang'] : isntPo($data->getLang()))))." ", "   i='" . $__pkJsonEsc . "' c='Lang' class='center'  j='editTemplate'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='TemplateRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." "), "  i='" . $__pkJsonEsc . "' c='Name' class=''  j='editTemplate'") .
                td(span(htmlspecialchars((string)((($altValue['Subject'] !== null ) ? $altValue['Subject'] : $data->getSubject())))." "), "  i='" . $__pkJsonEsc . "' c='Subject' class=''  j='editTemplate'") .
                td(span(htmlspecialchars((string)((($altValue['Status'] !== null ) ? $altValue['Status'] : isntPo($data->getStatus()))))." "), "  i='" . $__pkJsonEsc . "' c='Status' class='center'  j='editTemplate'") .
                td(span(htmlspecialchars((string)((($altValue['Lang'] !== null ) ? $altValue['Lang'] : isntPo($data->getLang()))))." "), "  i='" . $__pkJsonEsc . "' c='Lang' class='center'  j='editTemplate'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='TemplateDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountTemplate', $i);
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
                .div($controlsContent,'TemplateControlsList', "class='custom-controls'")
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
                        .span(_('Template'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('Template', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Name"), " type='button' th='sorted' c='Name' class='va-mob-sortrow' ") . button(_("Subject"), " type='button' th='sorted' c='Subject' class='va-mob-sortrow' ") . button(_("Status"), " type='button' th='sorted' c='Status' class='va-mob-sortrow' ") . button(_("Language"), " type='button' th='sorted' c='Lang' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='TemplateTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'TemplateListForm', " class='va-mob proto-app' data-model='Template' data-table='Template' data-ui='".$this->uiTabsId."' ");

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addTemplateAutoc');
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
    public function setCreateDefaultsTemplate(array $data): Template
    {

        unset($data['IdTemplate']);
        $e = new Template();


        if(!$data['Status']){
            $data['Status'] = 'Active';
        }
        if( $data['Lang'] == '' )unset($data['Lang']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //integer not required
        $e->setSubject( ($data['Subject'] == '' ) ? null : $data['Subject']);
        //integer not required
        $e->setColor1( ($data['Color1'] == '' ) ? null : $data['Color1']);
        //integer not required
        $e->setColor2( ($data['Color2'] == '' ) ? null : $data['Color2']);
        //integer not required
        $e->setColor3( ($data['Color3'] == '' ) ? null : $data['Color3']);
        //integer not required
        $e->setBody( ($data['Body'] == '' ) ? null : $data['Body']);
        //integer not required
        $e->setFooter( ($data['Footer'] == '' ) ? null : $data['Footer']);
        $e->setLang(($data['Lang'] == '' ) ? null : $data['Lang']);
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
    public function setUpdateDefaultsTemplate(array $data): ?Template
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(TemplateQuery::class, json_decode($data['i']), 'Template', 'w');
        if ($e === null) { return null; }


        if(!$data['Status']){
            $data['Status'] = 'Active';
        }
        if( $data['Lang'] == '' )unset($data['Lang']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['Subject'])){
            $e->setSubject( ($data['Subject'] == '' ) ? null : $data['Subject']);
        }
        if(isset($data['Color1'])){
            $e->setColor1( ($data['Color1'] == '' ) ? null : $data['Color1']);
        }
        if(isset($data['Color2'])){
            $e->setColor2( ($data['Color2'] == '' ) ? null : $data['Color2']);
        }
        if(isset($data['Color3'])){
            $e->setColor3( ($data['Color3'] == '' ) ? null : $data['Color3']);
        }
        if(isset($data['Body'])){
            $e->setBody( ($data['Body'] == '' ) ? null : $data['Body']);
        }
        if(isset($data['Footer'])){
            $e->setFooter( ($data['Footer'] == '' ) ? null : $data['Footer']);
        }
        if(isset($data['Lang'])){
            $e->setLang(($data['Lang'] == '' ) ? null : $data['Lang']);
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
     * Produce a formated form of Template
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

        $je = "TemplateTable";

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

                case 'AuthyGroup':
                    $data['IdGroupCreation'] = $data['ip'];
                    break;
                case 'Authy':
                    $data['IdModification'] = $data['ip'];
                    break;
            }
            $IdParent = $data['ip'];
        }


        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('Template', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addTemplate' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = TemplateQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('Template', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->Template['isNew'] = 'yes';
            $dataObj = new Template();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->Template['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;












$this->fields['Template']['Name']['html'] = stdFieldRow(_("Name"), input('text', 'Name', htmlentities((string)($dataObj->getName() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Name'))."' size='35'  v='NAME' s='d' class='req'  ")."", 'Name', "", $this->commentsName, $this->commentsName_css, ' half', ' ', 'no', 'v2');
$this->fields['Template']['Subject']['html'] = stdFieldRow(_("Subject"), input('text', 'Subject', htmlentities((string)($dataObj->getSubject() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Subject'))."' size='69'  v='SUBJECT' s='d' class=''  ")."", 'Subject', "", $this->commentsSubject, $this->commentsSubject_css, '', ' ', 'no', 'v2');
$this->fields['Template']['Color1']['html'] = stdFieldRow(_("Color 1"), input('text', 'Color1', htmlentities((string)($dataObj->getColor1() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Color 1'))."' size='15'  v='COLOR_1' s='d' class=''  ")."", 'Color1', "", $this->commentsColor1, $this->commentsColor1_css, ' half', ' ', 'no', 'v2');
$this->fields['Template']['Color2']['html'] = stdFieldRow(_("Color 2"), input('text', 'Color2', htmlentities((string)($dataObj->getColor2() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Color 2'))."' size='15'  v='COLOR_2' s='d' class=''  ")."", 'Color2', "", $this->commentsColor2, $this->commentsColor2_css, ' half', ' ', 'no', 'v2');
$this->fields['Template']['Color3']['html'] = stdFieldRow(_("Color 3"), input('text', 'Color3', htmlentities((string)($dataObj->getColor3() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Color 3'))."' size='15'  v='COLOR_3' s='d' class=''  ")."", 'Color3', "", $this->commentsColor3, $this->commentsColor3_css, ' half', ' ', 'no', 'v2');
$this->fields['Template']['Status']['html'] = stdFieldRow(_("Status"), selectboxCustomArray('Status', [ '0' => ['0'=>_("Active"), '1'=>"Active"],'1' => ['0'=>_("Inactive"), '1'=>"Inactive"], ], "", "s='d'  ", $dataObj->getStatus(), '', false), 'Status', "", $this->commentsStatus, $this->commentsStatus_css, ' half', ' ', 'no', 'v2');
$this->fields['Template']['Body']['html'] = stdFieldRow(_("Body"), textarea('Body', htmlentities((string)($dataObj->getBody() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Body'))."' cols='71' v='BODY' s='d'  class=' tinymce ' style='' spellcheck='false'"), 'Body', "", $this->commentsBody, $this->commentsBody_css, 'istinymce', ' ', 'no', 'v2');
$this->fields['Template']['Footer']['html'] = stdFieldRow(_("Footer"), textarea('Footer', htmlentities((string)($dataObj->getFooter() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Footer'))."' cols='71' v='FOOTER' s='d'  class=' tinymce ' style='' spellcheck='false'"), 'Footer', "", $this->commentsFooter, $this->commentsFooter_css, 'istinymce', ' ', 'no', 'v2');
$this->fields['Template']['Lang']['html'] = stdFieldRow(_("Language"), selectboxCustomArray('Lang', [ '0' => ['0'=>_("fr_CA"), '1'=>"fr_CA"],'1' => ['0'=>_("en_US"), '1'=>"en_US"], ], _('Language'), "s='d'  ", $dataObj->getLang(), '', true), 'Lang', "", $this->commentsLang, $this->commentsLang_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }



        if( !isset($this->Template['request']['ChildHide']) ) {

            # define child lists 'File'
            $ongletTab['0']['t'] = _('File');
            $ongletTab['0']['p'] = 'TemplateFile';
            $ongletTab['0']['lkey'] = 'IdTemplate';
            $ongletTab['0']['fkey'] = 'IdTemplate';
            $ongletTab['0']['icon'] = 'ri-attachment-2';
        if(!empty($ongletTab) and $dataObj->getIdTemplate()){
            foreach($ongletTab as $value){
                if($_SESSION[_AUTH_VAR]->hasRights($value['p'], 'r')){
                    $getLocalKey = "get".$value['lkey']."";
                    if($dataObj->$getLocalKey()){
                        // Per-parent filtered count for the tab badge.
                        // Falls back to '' if the child Query class or the
                        // filterBy{FK} method can't be located. The runtime
                        // ChildCountCache (APCu MicroCache, generation-bumped
                        // on child writes) owns the cached lookup; the elseif
                        // arm is the pre-cache behavior for older runtime pins.
                        $gcChildCountHtml = '';
                        $gcChildCount = null;
                        if (class_exists('\\ApiGoat\\Utility\\ChildCountCache')) {
                            $gcChildCount = \ApiGoat\Utility\ChildCountCache::count($value['p'], $value['fkey'], $dataObj->$getLocalKey());
                        } elseif (class_exists($gcChildQueryClass = '\\App\\'.$value['p'].'Query')
                            && method_exists($gcChildQueryClass, $gcChildFilter = 'filterBy'.$value['fkey'])) {
                            $gcChildCount = $gcChildQueryClass::create()->$gcChildFilter($dataObj->$getLocalKey())->count();
                        }
                        if ($gcChildCount !== null) {
                            $gcChildCountHtml = span($gcChildCount, ' class="child-tab-count" ');
                        }
                        // Icon resolved at build time from the child description
                        // (see the emitter-side map above); the fallback covers
                        // hand-written $ongletTab entries without an 'icon' key.
                        $gcChildIcon = !empty($value['icon']) ? $value['icon'] : 'ri-folder-line';
                        $ChildOnglet .= button(
                                        "<i class='".$gcChildIcon." child-tab-icon'></i>" . _($value['t']) . $gcChildCountHtml
                                    , ' type="button" class="child-tab" role="tab" j="conglet_Template" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" ')
                                    . htmlLink(
                                        "<i class='ri-add-line'></i>"
                                    , 'Javascript:;', ' class="child-tab-add header-controls" j="childadd_Template" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" title="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" aria-label="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" style="display:inline-flex;align-items:center;padding:0 7px;margin-right:4px;" ');
                    }
                }
            }

            if($ChildOnglet){
                // #23 S5: the legacy inline conglet click-handler is RETIRED.
                // screens.js's capture-phase document onClick (closest
                // [j^=conglet_][p] -> openChildList -> push the child as a screen +
                // run bindWithin) and drawer.js own child-tab loading + active
                // state. The capture-phase stopPropagation suppresses any
                // still-emitted inline handler on un-regenerated projects too, so
                // dropping this per-table onReadyJs causes no double-load and needs
                // no capability marker — the child tab markers (j/p/ip) are
                // unchanged and the client handles them generically.
                $childTable['onReadyJs'] = "";
            }
        }
        }


        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('Template','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('Template','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveTemplate', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedTemplate','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdTemplate', $dataObj->getIdTemplate(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
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
        //stage child-tabs + child-pannel for the drawer scaffold
        $childTabsHtml = '';
        $childPannelHtml = '';
        if($dataObj->getIdTemplate()) {
            if($ChildOnglet) {
                $childTabsHtml = div($ChildOnglet, '', " class='child-tabs' role='tablist' ");
                $childPannelHtml = div('', 'cntTemplateChild', ' class="child-pannel" ');
            }
        }

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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Template'), _SITE_URL.'Template', "class='nav-btn'")
                        .div(
                            span(_('Template'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'Template', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['Template']['Name']['html']
.$this->fields['Template']['Subject']['html']
.$this->fields['Template']['Color1']['html']
.$this->fields['Template']['Color2']['html']
.$this->fields['Template']['Color3']['html']
.$this->fields['Template']['Status']['html']
.$this->fields['Template']['Body']['html']
.$this->fields['Template']['Footer']['html']
.$this->fields['Template']['Lang']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntTemplate", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formTemplate' class='mainForm formContent' data-gc-upload-url='"._SITE_URL."TemplateFile/upload' data-gc-upload-parent='".htmlspecialchars((string)$id)."' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['Template']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['Template']['ixmemautocapp'];
            unset($_SESSION['mem']['Template']['ixmemautocapp']);
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

        $this->fieldsRo['Template']['Name']['html'] = stdFieldRow(_("Name"), div( htmlspecialchars((string)($dataObj->getName()), ENT_QUOTES), 'Name_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Name', $dataObj->getName(), "s='d'"), 'Name', "", $this->commentsName, $this->commentsName_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Subject']['html'] = stdFieldRow(_("Subject"), div( htmlspecialchars((string)($dataObj->getSubject()), ENT_QUOTES), 'Subject_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Subject', $dataObj->getSubject(), "s='d'"), 'Subject', "", $this->commentsSubject, $this->commentsSubject_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Color1']['html'] = stdFieldRow(_("Color 1"), div( htmlspecialchars((string)($dataObj->getColor1()), ENT_QUOTES), 'Color1_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Color1', $dataObj->getColor1(), "s='d'"), 'Color1', "", $this->commentsColor1, $this->commentsColor1_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Color2']['html'] = stdFieldRow(_("Color 2"), div( htmlspecialchars((string)($dataObj->getColor2()), ENT_QUOTES), 'Color2_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Color2', $dataObj->getColor2(), "s='d'"), 'Color2', "", $this->commentsColor2, $this->commentsColor2_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Color3']['html'] = stdFieldRow(_("Color 3"), div( htmlspecialchars((string)($dataObj->getColor3()), ENT_QUOTES), 'Color3_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Color3', $dataObj->getColor3(), "s='d'"), 'Color3', "", $this->commentsColor3, $this->commentsColor3_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Status']['html'] = stdFieldRow(_("Status"), div( htmlspecialchars((string)($dataObj->getStatus()), ENT_QUOTES), 'Status_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Status', $dataObj->getStatus(), "s='d'"), 'Status', "", $this->commentsStatus, $this->commentsStatus_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Body']['html'] = stdFieldRow(_("Body"), div( htmlspecialchars((string)($dataObj->getBody()), ENT_QUOTES), 'Body_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Body', $dataObj->getBody(), "s='d'"), 'Body', "", $this->commentsBody, $this->commentsBody_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Footer']['html'] = stdFieldRow(_("Footer"), div( htmlspecialchars((string)($dataObj->getFooter()), ENT_QUOTES), 'Footer_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Footer', $dataObj->getFooter(), "s='d'"), 'Footer', "", $this->commentsFooter, $this->commentsFooter_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['Template']['Lang']['html'] = stdFieldRow(_("Language"), div( htmlspecialchars((string)($dataObj->getLang()), ENT_QUOTES), 'Lang_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Lang', $dataObj->getLang(), "s='d'"), 'Lang', "", $this->commentsLang, $this->commentsLang_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['Template'] as $field => $ar) {
                $this->fields['Template'][$field]['html'] = $this->fieldsRo['Template'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['Template'][$field]['html'] = $this->fieldsRo['Template'][$field]['html'];
            }
        }
    }
    /**
     * function getTemplateFileList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getTemplateFileList(String $IdTemplate, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntTemplateChild' : $request['cui'];
        $_SESSION['mem']['Template']['child']['list']['active'] = 'TemplateFile';

        if (method_exists($this, 'beforeChildListTemplateFile')) {
            $this->beforeChildListTemplateFile();
        }

        $svcCls = class_exists('\\App\\TemplateFileServiceWrapper')
            ? '\\App\\TemplateFileServiceWrapper'
            : '\\App\\TemplateFileService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdTemplate);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

}
