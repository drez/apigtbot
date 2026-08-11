<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'ApiRbac' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class ApiRbacForm extends ApiRbac
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

        public $queryObjApiLog;
    public $listActionCellApiLog;
    public $arrayIdApiRbacOptions;
    public $arrayIdAuthyOptions;


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
        $this->model_name = 'ApiRbac';
        $this->virtualClassName = 'ApiRbac';
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

        $q = new ApiRbacQuery();
        $q = $this->setAclFilter($q);
        

        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['Scope']) ) {
            $criteria = \Criteria::EQUAL;
            $value = $this->searchMs['Scope'];

            $q->filterByScope($value, $criteria);
        }
        if( isset($this->searchMs['Model']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Model'], $criteria);

            $q->filterByModel($value, $criteria);
        }
        if( isset($this->searchMs['Action']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Action'], $criteria);

            $q->filterByAction($value, $criteria);
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
                                var __se=document.querySelector(\"#ApiRbacListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
                $trHead = th(_("Date"), " th='sorted' c='DateCreation' title='" . _('Date')."' ")
.th(_("Description"), " th='sorted' c='Description' title='" . _('Description')."' ")
.th(_("Model"), " th='sorted' c='Model' title='" . _('Model')."' ")
.th(_("Action"), " th='sorted' c='Action' title='" . _('Action')."' ")
.th(_("Body"), " th='sorted' c='Body' title='" . _('Body')."' ")
.th(_("Method"), " th='sorted' c='Method' title='" . _('Method')."' ")
.th(_("Scope"), " th='sorted' c='Scope' title='" . _('Scope')."' ")
.th(_("Rule"), " th='sorted' c='Rule' title='" . _('Rule')."' ")
.th(_("Used count"), " th='sorted' c='Count' title='" . _('Used count')."' ")
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
                $data['Scope'] = ( !empty( $this->searchMs['Scope'])) ? $this->searchMs['Scope']:'';
            $data['Model'] = ( !empty( $this->searchMs['Model'])) ? $this->searchMs['Model']:'';
            $data['Action'] = ( !empty( $this->searchMs['Action'])) ? $this->searchMs['Action']:'';
            

                $trSearch = ''
                .form(div(div(input('text', 'Model', $this->searchMs['Model'], '  title="'._('Model').'" placeholder="'._('Search').' '._('Model').', '._('Action').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('ApiRbac')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('ApiRbac')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(
                        div(_('Search in'),''," class='va-fblock-lbl' ")
                        .div(''.button(span(_('Model'))," type='button' class='va-fchip active' data-msfield='Model' ").button(span(_('Action'))," type='button' class='va-fchip active' data-msfield='Action' "),''," class='va-fchips' ")
                        .div(input('text', 'Action', $this->searchMs['Action'], '  title="'._('Action').'" placeholder="'._('Action').'"',''),'','class="ac-search-item"')
                    ,''," class='va-fblock' data-block='searchin' ").div(
                        div(_('Scope'),''," class='va-fblock-lbl' ")
                        .div(
                            button(span(_('All'))," type='button' class='va-fseg-opt active' data-msval='' ")
                            .(function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("Private"), '1'=>"Private"],'1' => ['0'=>_("Public"), '1'=>"Public"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fseg-opt' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })()
                        ,''," class='va-fseg' data-msfield='Scope' ")
                        .div(selectboxCustomArray('Scope', [ '0' => ['0'=>_("Private"), '1'=>"Private"],'1' => ['0'=>_("Public"), '1'=>"Public"], ], _('Scope'), '  size="1" t="1"   ', $this->searchMs['Scope']), '', 'class=" ac-search-item"  title="'._('Scope').'"')
                    ,''," class='va-fblock va-fblock-seg' data-block='seg' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('ApiRbac')."' hidden ").div(
                           button(span(_("Search")),'id="msApiRbacBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msApiRbacBtClear"')
                           .input('hidden', 'Seq', $data['Seq'] )
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsApiRbac' class='va-mob-searchform' data-entity='ApiRbac'");;
                return $trSearch;

            case 'add':
            ###### ADD
                 if($_SESSION[_AUTH_VAR]->hasRights('ApiRbac', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addApiRbac' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'ApiRbac';
        $altValue = array (
  'IdApiRbac' => NULL,
  'DateCreation' => NULL,
  'Description' => NULL,
  'Model' => NULL,
  'Action' => NULL,
  'Body' => NULL,
  'Query' => NULL,
  'Method' => NULL,
  'Scope' => NULL,
  'Rule' => NULL,
  'Count' => NULL,
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
            $this->isChild = 'ApiRbac';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'ApiRbac/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';

            $_gcSel = (array)($this->searchMs['Scope'] ?? []);
            $_gcChips = button(span(_('All')), " type='button' class='va-mob-chip".(empty($_gcSel)?' active':'')."' data-msfield='Scope' data-msval='' ");
            foreach( [ '0' => ['0'=>_("Private"), '1'=>"Private"],'1' => ['0'=>_("Public"), '1'=>"Public"], ] as $_gcCo ) {
                $_gcCv = is_array($_gcCo) ? (string)$_gcCo[1] : (string)$_gcCo;
                $_gcCl = is_array($_gcCo) ? (string)$_gcCo[0] : (string)$_gcCo;
                $_gcChips .= button(span(htmlspecialchars($_gcCl)), " type='button' class='va-mob-chip".(in_array($_gcCv, $_gcSel, true)?' active':'')."' data-msfield='Scope' data-msval='".htmlspecialchars($_gcCv)."' ");
            }
            $trChips = div($_gcChips, '', " class='va-mob-chips' ");

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'ApiRbac/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['ApiRbac/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'ApiRbac/');

        
        
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
            $gcGroupCol = 'DateCreation';
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
                if($_SESSION[_AUTH_VAR]->hasRights('ApiRbac', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteApiRbac' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['DateCreation'] !== null ) ? $altValue['DateCreation'] : $data->getDateCreation()));
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
   div('' . span(htmlspecialchars((string)((($altValue['DateCreation'] !== null ) ? $altValue['DateCreation'] : $data->getDateCreation())))." ", "   i='" . $__pkJsonEsc . "' c='DateCreation' class=''  j='editApiRbac'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Description'] !== null ) ? $altValue['Description'] : substr(strip_tags((string)($data->getDescription() ?? '')), 0, 100))))." ", "   i='" . $__pkJsonEsc . "' c='Description' class=''  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Model'] !== null ) ? $altValue['Model'] : $data->getModel())))." ", "   i='" . $__pkJsonEsc . "' c='Model' class=''  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Action'] !== null ) ? $altValue['Action'] : $data->getAction())))." ", "   i='" . $__pkJsonEsc . "' c='Action' class=''  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Body'] !== null ) ? $altValue['Body'] : substr(strip_tags((string)($data->getBody() ?? '')), 0, 100))))." ", "   i='" . $__pkJsonEsc . "' c='Body' class=''  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Method'] !== null ) ? $altValue['Method'] : isntPo($data->getMethod()))))." ", "   i='" . $__pkJsonEsc . "' c='Method' class='center'  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Scope'] !== null ) ? $altValue['Scope'] : isntPo($data->getScope()))))." ", "   i='" . $__pkJsonEsc . "' c='Scope' class='center'  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Rule'] !== null ) ? $altValue['Rule'] : isntPo($data->getRule()))))." ", "   i='" . $__pkJsonEsc . "' c='Rule' class='center'  j='editApiRbac'") . span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." ", "   i='" . $__pkJsonEsc . "' c='Count' class=''  j='editApiRbac'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='ApiRbacRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['DateCreation'] !== null ) ? $altValue['DateCreation'] : $data->getDateCreation())))." "), "  i='" . $__pkJsonEsc . "' c='DateCreation' class=''  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Description'] !== null ) ? $altValue['Description'] : substr(strip_tags((string)($data->getDescription() ?? '')), 0, 100))))." "), "  i='" . $__pkJsonEsc . "' c='Description' class=''  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Model'] !== null ) ? $altValue['Model'] : $data->getModel())))." "), "  i='" . $__pkJsonEsc . "' c='Model' class=''  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Action'] !== null ) ? $altValue['Action'] : $data->getAction())))." "), "  i='" . $__pkJsonEsc . "' c='Action' class=''  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Body'] !== null ) ? $altValue['Body'] : substr(strip_tags((string)($data->getBody() ?? '')), 0, 100))))." "), "  i='" . $__pkJsonEsc . "' c='Body' class=''  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Method'] !== null ) ? $altValue['Method'] : isntPo($data->getMethod()))))." "), "  i='" . $__pkJsonEsc . "' c='Method' class='center'  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Scope'] !== null ) ? $altValue['Scope'] : isntPo($data->getScope()))))." "), "  i='" . $__pkJsonEsc . "' c='Scope' class='center'  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Rule'] !== null ) ? $altValue['Rule'] : isntPo($data->getRule()))))." "), "  i='" . $__pkJsonEsc . "' c='Rule' class='center'  j='editApiRbac'") .
                td(span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." "), "  i='" . $__pkJsonEsc . "' c='Count' class=''  j='editApiRbac'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='ApiRbacDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountApiRbac', $i);
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
                .div($controlsContent,'ApiRbacControlsList', "class='custom-controls'")
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
                        .span(_('API ACL'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('ApiRbac', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Date"), " type='button' th='sorted' c='DateCreation' class='va-mob-sortrow' ") . button(_("Description"), " type='button' th='sorted' c='Description' class='va-mob-sortrow' ") . button(_("Model"), " type='button' th='sorted' c='Model' class='va-mob-sortrow' ") . button(_("Action"), " type='button' th='sorted' c='Action' class='va-mob-sortrow' ") . button(_("Body"), " type='button' th='sorted' c='Body' class='va-mob-sortrow' ") . button(_("Method"), " type='button' th='sorted' c='Method' class='va-mob-sortrow' ") . button(_("Scope"), " type='button' th='sorted' c='Scope' class='va-mob-sortrow' ") . button(_("Rule"), " type='button' th='sorted' c='Rule' class='va-mob-sortrow' ") . button(_("Used count"), " type='button' th='sorted' c='Count' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='ApiRbacTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'ApiRbacListForm', " class='va-mob proto-app' data-model='ApiRbac' data-table='ApiRbac' data-ui='".$this->uiTabsId."' ");

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addApiRbacAutoc');
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
    public function setCreateDefaultsApiRbac(array $data): ApiRbac
    {

        unset($data['IdApiRbac']);
        $e = new ApiRbac();


        if(!$data['Method']){
            $data['Method'] = 'GET';
        }
        if(!$data['Scope']){
            $data['Scope'] = 'Private';
        }
        if(!$data['Rule']){
            $data['Rule'] = 'Deny';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        $e->setDateCreation( ($data['DateCreation'] == '' || $data['DateCreation'] == 'null' || substr($data['DateCreation'], 0, 10) == '-0001-11-30')?date('Y-m-d'):$data['DateCreation'] );
        //integer not required
        $e->setDescription( ($data['Description'] == '' ) ? null : $data['Description']);
        //integer not required
        $e->setAction( ($data['Action'] == '' ) ? null : $data['Action']);
        //integer not required
        $e->setBody( ($data['Body'] == '' ) ? null : $data['Body']);
        //integer not required
        $e->setQuery( ($data['Query'] == '' ) ? null : $data['Query']);
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
    public function setUpdateDefaultsApiRbac(array $data): ?ApiRbac
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(ApiRbacQuery::class, json_decode($data['i']), 'ApiRbac', 'w');
        if ($e === null) { return null; }


        if(!$data['Method']){
            $data['Method'] = 'GET';
        }
        if(!$data['Scope']){
            $data['Scope'] = 'Private';
        }
        if(!$data['Rule']){
            $data['Rule'] = 'Deny';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['DateCreation'])){
            $e->setDateCreation( ($data['DateCreation'] == '' || $data['DateCreation'] == 'null' || substr($data['DateCreation'], 0, 10) == '-0001-11-30') ? null : $data['DateCreation'] );
        }
        if(isset($data['Description'])){
            $e->setDescription( ($data['Description'] == '' ) ? null : $data['Description']);
        }
        if(isset($data['Action'])){
            $e->setAction( ($data['Action'] == '' ) ? null : $data['Action']);
        }
        if(isset($data['Body'])){
            $e->setBody( ($data['Body'] == '' ) ? null : $data['Body']);
        }
        if(isset($data['Query'])){
            $e->setQuery( ($data['Query'] == '' ) ? null : $data['Query']);
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
     * Produce a formated form of ApiRbac
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

        $je = "ApiRbacTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('ApiRbac', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addApiRbac' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = ApiRbacQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('ApiRbac', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->ApiRbac['isNew'] = 'yes';
            $dataObj = new ApiRbac();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->ApiRbac['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;












$this->fields['ApiRbac']['DateCreation']['html'] = stdFieldRow(_("Date"), input('date', 'DateCreation', $dataObj->getDateCreation(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD' size='10'  s='d' class='' title='Date'"), 'DateCreation', "", $this->commentsDateCreation, $this->commentsDateCreation_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Description']['html'] = stdFieldRow(_("Description"), textarea('Description', htmlentities((string)($dataObj->getDescription() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Description'))."' cols='71' v='DESCRIPTION' s='d'  class=' ' style='' spellcheck='false'"), 'Description', "", $this->commentsDescription, $this->commentsDescription_css, '', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Model']['html'] = stdFieldRow(_("Model"), input('text', 'Model', htmlentities((string)($dataObj->getModel() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Model'))."' size='69'  v='MODEL' s='d' class='req'  ")."", 'Model', "", $this->commentsModel, $this->commentsModel_css, '', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Action']['html'] = stdFieldRow(_("Action"), input('text', 'Action', htmlentities((string)($dataObj->getAction() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Action'))."' size='69'  v='ACTION' s='d' class=''  ")."", 'Action', "", $this->commentsAction, $this->commentsAction_css, '', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Body']['html'] = stdFieldRow(_("Body"), textarea('Body', htmlentities((string)($dataObj->getBody() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Body'))."' cols='71' v='BODY' s='d'  class=' ' style='' spellcheck='false'"), 'Body', "", $this->commentsBody, $this->commentsBody_css, '', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Query']['html'] = stdFieldRow(_("Query"), textarea('Query', htmlentities((string)($dataObj->getQuery() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Query'))."' cols='71' v='QUERY' s='d'  class=' ' style='' spellcheck='false'"), 'Query', "", $this->commentsQuery, $this->commentsQuery_css, '', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Method']['html'] = stdFieldRow(_("Method"), selectboxCustomArray('Method', [ '0' => ['0'=>_("GET"), '1'=>"GET"],'1' => ['0'=>_("POST"), '1'=>"POST"],'2' => ['0'=>_("PATCH"), '1'=>"PATCH"],'3' => ['0'=>_("PUT"), '1'=>"PUT"],'4' => ['0'=>_("DELETE"), '1'=>"DELETE"],'5' => ['0'=>_("ALL"), '1'=>"ALL"], ], "", "s='d'  ", $dataObj->getMethod(), '', false), 'Method', "", $this->commentsMethod, $this->commentsMethod_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Scope']['html'] = stdFieldRow(_("Scope"), selectboxCustomArray('Scope', [ '0' => ['0'=>_("Private"), '1'=>"Private"],'1' => ['0'=>_("Public"), '1'=>"Public"], ], "", "s='d'  ", $dataObj->getScope(), '', false), 'Scope', "", $this->commentsScope, $this->commentsScope_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Rule']['html'] = stdFieldRow(_("Rule"), selectboxCustomArray('Rule', [ '0' => ['0'=>_("Allow"), '1'=>"Allow"],'1' => ['0'=>_("Deny"), '1'=>"Deny"], ], "", "s='d'  ", $dataObj->getRule(), '', false), 'Rule', "", $this->commentsRule, $this->commentsRule_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiRbac']['Count']['html'] = stdFieldRow(_("Used count"), input('number', 'Count', $dataObj->getCount(), " step='1' placeholder='".str_replace("'","&#39;",_('Used count'))."' v='COUNT' size='0' s='d' class='req'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }



        if( !isset($this->ApiRbac['request']['ChildHide']) ) {

            # define child lists 'API log'
            $ongletTab['0']['t'] = _('API log');
            $ongletTab['0']['p'] = 'ApiLog';
            $ongletTab['0']['lkey'] = 'IdApiRbac';
            $ongletTab['0']['fkey'] = 'IdApiRbac';
            $ongletTab['0']['icon'] = 'ri-calendar-event-line';
        if(!empty($ongletTab) and $dataObj->getIdApiRbac()){
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
                                    , ' type="button" class="child-tab" role="tab" j="conglet_ApiRbac" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" ')
                                    . htmlLink(
                                        "<i class='ri-add-line'></i>"
                                    , 'Javascript:;', ' class="child-tab-add header-controls" j="childadd_ApiRbac" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" title="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" aria-label="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" style="display:inline-flex;align-items:center;padding:0 7px;margin-right:4px;" ');
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
            if( ($_SESSION[_AUTH_VAR]->hasRights('ApiRbac','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('ApiRbac','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveApiRbac', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedApiRbac','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdApiRbac', $dataObj->getIdApiRbac(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
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
        if($dataObj->getIdApiRbac()) {
            if($ChildOnglet) {
                $childTabsHtml = div($ChildOnglet, '', " class='child-tabs' role='tablist' ");
                $childPannelHtml = div('', 'cntApiRbacChild', ' class="child-pannel" ');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('API ACL'), _SITE_URL.'ApiRbac', "class='nav-btn'")
                        .div(
                            span(_('API ACL'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'ApiRbac', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['ApiRbac']['DateCreation']['html']
.$this->fields['ApiRbac']['Description']['html']
.$this->fields['ApiRbac']['Model']['html']
.$this->fields['ApiRbac']['Action']['html']
.$this->fields['ApiRbac']['Body']['html']
.$this->fields['ApiRbac']['Query']['html']
.$this->fields['ApiRbac']['Method']['html']
.$this->fields['ApiRbac']['Scope']['html']
.$this->fields['ApiRbac']['Rule']['html']
.$this->fields['ApiRbac']['Count']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntApiRbac", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formApiRbac' class='mainForm formContent' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['ApiRbac']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['ApiRbac']['ixmemautocapp'];
            unset($_SESSION['mem']['ApiRbac']['ixmemautocapp']);
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

        $this->fieldsRo['ApiRbac']['DateCreation']['html'] = stdFieldRow(_("Date"), div( htmlspecialchars((string)($dataObj->getDateCreation()), ENT_QUOTES), 'DateCreation_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DateCreation', $dataObj->getDateCreation(), "s='d'"), 'DateCreation', "", $this->commentsDateCreation, $this->commentsDateCreation_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Description']['html'] = stdFieldRow(_("Description"), div( htmlspecialchars((string)($dataObj->getDescription()), ENT_QUOTES), 'Description_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Description', $dataObj->getDescription(), "s='d'"), 'Description', "", $this->commentsDescription, $this->commentsDescription_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Model']['html'] = stdFieldRow(_("Model"), div( htmlspecialchars((string)($dataObj->getModel()), ENT_QUOTES), 'Model_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Model', $dataObj->getModel(), "s='d'"), 'Model', "", $this->commentsModel, $this->commentsModel_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Action']['html'] = stdFieldRow(_("Action"), div( htmlspecialchars((string)($dataObj->getAction()), ENT_QUOTES), 'Action_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Action', $dataObj->getAction(), "s='d'"), 'Action', "", $this->commentsAction, $this->commentsAction_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Body']['html'] = stdFieldRow(_("Body"), div( htmlspecialchars((string)($dataObj->getBody()), ENT_QUOTES), 'Body_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Body', $dataObj->getBody(), "s='d'"), 'Body', "", $this->commentsBody, $this->commentsBody_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Query']['html'] = stdFieldRow(_("Query"), div( htmlspecialchars((string)($dataObj->getQuery()), ENT_QUOTES), 'Query_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Query', $dataObj->getQuery(), "s='d'"), 'Query', "", $this->commentsQuery, $this->commentsQuery_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Method']['html'] = stdFieldRow(_("Method"), div( htmlspecialchars((string)($dataObj->getMethod()), ENT_QUOTES), 'Method_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Method', $dataObj->getMethod(), "s='d'"), 'Method', "", $this->commentsMethod, $this->commentsMethod_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Scope']['html'] = stdFieldRow(_("Scope"), div( htmlspecialchars((string)($dataObj->getScope()), ENT_QUOTES), 'Scope_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Scope', $dataObj->getScope(), "s='d'"), 'Scope', "", $this->commentsScope, $this->commentsScope_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Rule']['html'] = stdFieldRow(_("Rule"), div( htmlspecialchars((string)($dataObj->getRule()), ENT_QUOTES), 'Rule_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Rule', $dataObj->getRule(), "s='d'"), 'Rule', "", $this->commentsRule, $this->commentsRule_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiRbac']['Count']['html'] = stdFieldRow(_("Used count"), div( htmlspecialchars((string)($dataObj->getCount()), ENT_QUOTES), 'Count_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Count', $dataObj->getCount(), "s='d'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['ApiRbac'] as $field => $ar) {
                $this->fields['ApiRbac'][$field]['html'] = $this->fieldsRo['ApiRbac'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['ApiRbac'][$field]['html'] = $this->fieldsRo['ApiRbac'][$field]['html'];
            }
        }
    }
    /**
     * function getApiLogList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getApiLogList(String $IdApiRbac, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntApiRbacChild' : $request['cui'];
        $_SESSION['mem']['ApiRbac']['child']['list']['active'] = 'ApiLog';

        if (method_exists($this, 'beforeChildListApiLog')) {
            $this->beforeChildListApiLog();
        }

        $svcCls = class_exists('\\App\\ApiLogServiceWrapper')
            ? '\\App\\ApiLogServiceWrapper'
            : '\\App\\ApiLogService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdApiRbac);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

}
