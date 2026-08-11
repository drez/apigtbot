<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'ApiLog' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class ApiLogForm extends ApiLog
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
        $this->model_name = 'ApiLog';
        $this->virtualClassName = 'ApiLog';
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

        $q = new ApiLogQuery();
        $q = $this->setAclFilter($q);
        

        $q

                #required api_log
                ->leftJoinWith('ApiRbac')
                #default
                ->leftJoinWith('Authy');
        if(is_array( $this->searchMs )){
            # main search form
            
            
        }else{
            ## standard list
            
        }
        
        $hasParent = json_decode((string) $IdParent);
        if (!empty($hasParent)) {
            $q->filterByIdApiRbac($hasParent);
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
                                var __se=document.querySelector(\"#ApiLogListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
                $trHead = th(_("API ACL model"), " th='sorted' c='ApiRbac.Model' title='"._('ApiRbac.Model')."' ")
.th(_('API ACL action'), "t='mc' th='sorted' c='ApiRbac.Action'").th(_('API ACL query'), "t='mc' th='sorted' c='ApiRbac.Query'").th(_("User username"), " th='sorted' c='Authy.Username' title='"._('Authy.Username')."' ")
.th(_("Time"), " th='sorted' c='Time' title='" . _('Time')."' ")
.th(_("Raw parameters"), " th='sorted' c='RawParameters' title='" . _('Raw parameters')."' ")
.th(_("Count"), " th='sorted' c='Count' title='" . _('Count')."' ")
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
                 if($_SESSION[_AUTH_VAR]->hasRights('ApiLog', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addApiLog' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'ApiLog';
        $altValue = array (
  'IdApiLog' => NULL,
  'IdApiRbac' => NULL,
  'IdAuthy' => NULL,
  'Time' => NULL,
  'RawParameters' => NULL,
  'Count' => NULL,
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
            $this->isChild = 'ApiLog';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'ApiLog/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'ApiLog/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['ApiLog/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'ApiLog/');

        
        
        $default_order[]['Time']='DESC';
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
            $gcGroupCol = 'ApiRbac.Model';
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
                if($_SESSION[_AUTH_VAR]->hasRights('ApiLog', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteApiLog' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['IdApiRbac'] !== null ) ? $altValue['IdApiRbac'] : $altValue['ApiRbac_Model']));
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
                
                

        $altValue['ApiRbac_Model'] = "";
        if($data->getApiRbac()){
            $altValue['ApiRbac_Model'] = $data->getApiRbac()->getModel();
        }
        $altValue['ApiRbac_Action'] = "";
        if($data->getApiRbac()){
            $altValue['ApiRbac_Action'] = $data->getApiRbac()->getAction();
        }
        $altValue['ApiRbac_Query'] = "";
        if($data->getApiRbac()){
            $altValue['ApiRbac_Query'] = $data->getApiRbac()->getQuery();
        }
        $altValue['Authy_Username'] = "";
        if($data->getAuthy()){
            $altValue['Authy_Username'] = $data->getAuthy()->getUsername();
        }
                

                $actionCell =  td($this->canDelete . $this->listActionCell, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['IdApiRbac'] !== null ) ? $altValue['IdApiRbac'] : $altValue['ApiRbac_Model'])))." ", "   i='" . $__pkJsonEsc . "' c='IdApiRbac' class=''  j='editApiLog'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['IdAuthy'] !== null ) ? $altValue['IdAuthy'] : $altValue['Authy_Username'])))." ", "   i='" . $__pkJsonEsc . "' c='IdAuthy' class=''  j='editApiLog'") . span(htmlspecialchars((string)((($altValue['Time'] !== null ) ? $altValue['Time'] : $data->getTime())))." ", "   i='" . $__pkJsonEsc . "' c='Time' class=''  j='editApiLog'") . span(htmlspecialchars((string)((($altValue['RawParameters'] !== null ) ? $altValue['RawParameters'] : substr(strip_tags((string)($data->getRawParameters() ?? '')), 0, 100))))." ", "   i='" . $__pkJsonEsc . "' c='RawParameters' class=''  j='editApiLog'") . span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." ", "   i='" . $__pkJsonEsc . "' c='Count' class=''  j='editApiLog'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='ApiLogRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['IdApiRbac'] !== null ) ? $altValue['IdApiRbac'] : $altValue['ApiRbac_Model'])))." "), "  i='" . $__pkJsonEsc . "' c='IdApiRbac' class=''  j='editApiLog'") . td(span(htmlspecialchars((string)($altValue['ApiRbac_Action'])).''), " c='ApiRbac__Action' j='editApiLog' i='".$__pkJsonEsc."'").
                            td(span(htmlspecialchars((string)($altValue['ApiRbac_Query'])).''), " c='ApiRbac__Query' j='editApiLog' i='".$__pkJsonEsc."'").

                td(span(htmlspecialchars((string)((($altValue['IdAuthy'] !== null ) ? $altValue['IdAuthy'] : $altValue['Authy_Username'])))." "), "  i='" . $__pkJsonEsc . "' c='IdAuthy' class=''  j='editApiLog'") .
                td(span(htmlspecialchars((string)((($altValue['Time'] !== null ) ? $altValue['Time'] : $data->getTime())))." "), "  i='" . $__pkJsonEsc . "' c='Time' class=''  j='editApiLog'") .
                td(span(htmlspecialchars((string)((($altValue['RawParameters'] !== null ) ? $altValue['RawParameters'] : substr(strip_tags((string)($data->getRawParameters() ?? '')), 0, 100))))." "), "  i='" . $__pkJsonEsc . "' c='RawParameters' class=''  j='editApiLog'") .
                td(span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." "), "  i='" . $__pkJsonEsc . "' c='Count' class=''  j='editApiLog'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='ApiLogDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountApiLog', $i);
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
                    . select('prune_action_ApiLog', array (
  0 =>
  array (
    0 => 'Prune',
    1 => 'Prune',
    2 => 'placeholder',
  ),
  1 =>
  array (
    0 => 'Older than 1 month',
    1 => '1 month',
  ),
  2 =>
  array (
    0 => 'Older than 1 week',
    1 => '1 week',
  ),
  3 =>
  array (
    0 => 'Older than 1 day',
    1 => '1 day',
  ),
), "class='mass-action-sel'")
                ,'','class="default-controls"')
                .div($controlsContent,'ApiLogControlsList', "class='custom-controls'")
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
                        .span(_('API log'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('ApiLog', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("API ACL model"), " type='button' th='sorted' c='ApiRbac.Model' class='va-mob-sortrow' ") . button(_("User username"), " type='button' th='sorted' c='Authy.Username' class='va-mob-sortrow' ") . button(_("Time"), " type='button' th='sorted' c='Time' class='va-mob-sortrow' ") . button(_("Raw parameters"), " type='button' th='sorted' c='RawParameters' class='va-mob-sortrow' ") . button(_("Count"), " type='button' th='sorted' c='Count' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='ApiLogTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'ApiLogListForm', " class='va-mob proto-app' data-model='ApiLog' data-table='ApiLog' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='ApiLog' data-parent='ApiRbac'" : ''));

        



        $return['onReadyJs'] =
            $HelpDivJs

    ."
    (function(){
        var sel = document.getElementById('prune_action_ApiLog');
        if (!sel) { return; }

        /* Same shared confirm shell add_mass_action emits — whichever
           parameter's ready-block runs first defines it. */
        if (typeof window.gcMassActionConfirm !== 'function') {
            window.gcMassActionConfirm = function (bodyEl, onOk, onCancel) {
                var dim = document.createElement('div');
                dim.className = 'gc-confirm-dim';
                dim.style.cssText = 'position:fixed;left:0;top:0;right:0;bottom:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);';
                var boxEl = document.createElement('div');
                boxEl.className = 'gc-confirm-box';
                boxEl.style.cssText = 'background:#fff;border-radius:8px;padding:20px;max-width:420px;width:90%;box-shadow:0 8px 30px rgba(0,0,0,0.25);';
                var msgEl = document.createElement('div');
                msgEl.className = 'gc-confirm-msg';
                msgEl.appendChild(bodyEl);
                var btns = document.createElement('div');
                btns.className = 'gc-confirm-btns';
                btns.style.cssText = 'display:flex;gap:10px;justify-content:flex-end;margin-top:16px;';
                var cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'gc-confirm-btn';
                cancelBtn.textContent = 'Cancel';
                var okBtn = document.createElement('button');
                okBtn.type = 'button';
                okBtn.className = 'gc-confirm-btn primary';
                okBtn.textContent = 'Proceed';
                btns.appendChild(cancelBtn);
                btns.appendChild(okBtn);
                boxEl.appendChild(msgEl);
                boxEl.appendChild(btns);
                dim.appendChild(boxEl);
                document.body.appendChild(dim);
                requestAnimationFrame(function () { dim.classList.add('show'); });
                var closeDlg = function () {
                    dim.classList.remove('show');
                    setTimeout(function () { if (dim.parentNode) { dim.parentNode.removeChild(dim); } }, 180);
                };
                cancelBtn.addEventListener('click', function () { closeDlg(); if (onCancel) { onCancel(); } });
                okBtn.addEventListener('click', function () { closeDlg(); if (onOk) { onOk(); } });
                dim.addEventListener('click', function (e) { if (e.target === dim) { closeDlg(); if (onCancel) { onCancel(); } } });
            };
        }

        var placeholder = \"Prune\";
        sel.addEventListener('change', function () {
            var win = sel.value;
            if (win === placeholder) { return; }

            var div = document.createElement('div');
            var line = document.createElement('div');
            line.textContent = 'You are about to permanently delete every entry older than ' + win + '. This cannot be undone.';
            div.appendChild(line);
            var proceed = document.createElement('div');
            proceed.textContent = 'Proceed?';
            div.appendChild(proceed);

            window.gcMassActionConfirm(div, function () {
                var qpm = new URLSearchParams();
                qpm.set('ui', 'editDialog');
                qpm.set('older_than', win);
                fetch(_SITE_URL + 'ApiLog/prune', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: qpm.toString()
                }).then(function (r) { return r.text(); }).then(function (resp) {
                    var out = resp;
                    try { var j = JSON.parse(resp); if (j && typeof j === 'object' && j.html != null) { out = j.html; } } catch (e) {}
                    if (/<[a-z!\/]/i.test(out)) {
                        out = new DOMParser().parseFromString(out, 'text/html').body.textContent.trim();
                    }
                    alertb('Result', out, function () { location.reload(); });
                    sel.value = placeholder;
                });
            }, function () {
                sel.value = placeholder;
            });
        });
    })();"
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addApiLogAutoc');
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
    public function setCreateDefaultsApiLog(array $data): ApiLog
    {

        unset($data['IdApiLog']);
        $e = new ApiLog();


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //foreign
        $e->setIdAuthy(( $data['IdAuthy'] == '' ) ? null : $data['IdAuthy']);
        $e->setTime( ($data['Time'] == '' || $data['Time'] == 'null' || substr($data['Time'], 0, 10) == '-0001-11-30')?date('Y-m-d'):$data['Time'] );
        //integer not required
        $e->setRawParameters( ($data['RawParameters'] == '' ) ? null : $data['RawParameters']);
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsApiLog(array $data): ?ApiLog
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(ApiLogQuery::class, json_decode($data['i']), 'ApiLog', 'w');
        if ($e === null) { return null; }


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if( isset($data['IdAuthy']) ){
            $e->setIdAuthy(( $data['IdAuthy'] == '' ) ? null : $data['IdAuthy']);
        }
        if(isset($data['Time'])){
            $e->setTime( ($data['Time'] == '' || $data['Time'] == 'null' || substr($data['Time'], 0, 10) == '-0001-11-30') ? null : $data['Time'] );
        }
        if(isset($data['RawParameters'])){
            $e->setRawParameters( ($data['RawParameters'] == '' ) ? null : $data['RawParameters']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of ApiLog
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

        $je = "ApiLogTable";

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

                case 'ApiRbac':
                    $data['IdApiRbac'] = $data['ip'];
                    break;
                case 'Authy':
                    $data['IdAuthy'] = $data['ip'];
                    break;
            }
            $IdParent = $data['ip'];
        }

        if(!$IdParent && isset($data['ip']) && $data['ip'] !== '' && $data['ip'] != 0) {
            $IdParent = $data['ip'];
            $data['IdApiRbac'] = $data['ip'];
        }

        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('ApiLog', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addApiLog' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = ApiLogQuery::create()

                #required api_log
                ->leftJoinWith('ApiRbac')
                #default
                ->leftJoinWith('Authy')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('ApiLog', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->ApiLog['isNew'] = 'yes';
            $dataObj = new ApiLog();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdApiRbac";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->ApiLog['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getApiRbac())?'':$dataObj->setApiRbac( new ApiRbac() );
                                    ($dataObj->getAuthy())?'':$dataObj->setAuthy( new Authy() );


        if($this->setReadOnly !== 'all'){ $this->arrayIdApiRbacOptions = $this->selectBoxApiLog_IdApiRbac($this, $dataObj, $data); } else { $this->arrayIdApiRbacOptions = []; }






$this->fields['ApiLog']['IdApiRbac']['html'] = stdFieldRow(_("Rule"), selectboxCustomArray('IdApiRbac', $this->arrayIdApiRbacOptions, "", "v='ID_API_RBAC'  s='d'  val='".$dataObj->getIdApiRbac()."'", $dataObj->getIdApiRbac()), 'IdApiRbac', "", $this->commentsIdApiRbac, $this->commentsIdApiRbac_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiLog']['IdAuthy']['html'] = stdFieldRow(_("User"),
    input('text', 'IdAuthyAutoc', $dataObj->getAuthy()?->getFullname(), " title='".str_replace("'","", (string)($dataObj->getAuthy()?->getFullname()))."' v='ID_AUTHY' rid='IdAuthy' placeholder='"._('User')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdAuthy&quot;,&quot;table&quot;:&quot;ApiLog&quot;,&quot;childTable&quot;:&quot;Authy&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;Authy&quot;,&quot;show&quot;:[&quot;Fullname&quot;],&quot;id&quot;:&quot;IdAuthy&quot;,&quot;filter&quot;:&quot;Fullname&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdAuthy', $dataObj->getIdAuthy(), "s='d'"), 'IdAuthy', "", $this->commentsIdAuthy, $this->commentsIdAuthy_css, '', ' ', 'no', 'v2');
$this->fields['ApiLog']['Time']['html'] = stdFieldRow(_("Time"), input('datetime-local', 'Time', $dataObj->getTime(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='req' title='Time'"), 'Time', "", $this->commentsTime, $this->commentsTime_css, ' half', ' ', 'no', 'v2');
$this->fields['ApiLog']['RawParameters']['html'] = stdFieldRow(_("Raw parameters"), textarea('RawParameters', htmlentities((string)($dataObj->getRawParameters() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Raw parameters'))."' cols='71' v='RAW_PARAMETERS' s='d'  class=' ' style='' spellcheck='false'"), 'RawParameters', "", $this->commentsRawParameters, $this->commentsRawParameters_css, '', ' ', 'no', 'v2');
$this->fields['ApiLog']['Count']['html'] = stdFieldRow(_("Count"), input('number', 'Count', $dataObj->getCount(), " step='1' placeholder='".str_replace("'","&#39;",_('Count'))."' v='COUNT' size='0' s='d' class='req'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['ApiLog']['IdApiRbac']['html'] = input('hidden', 'IdApiRbac', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('ApiLog','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('ApiLog','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveApiLog', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedApiLog','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdApiLog', $dataObj->getIdApiLog(), " s='d' pk")
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
        $_gcCreatedSpan = '';
        $_gcModifiedSpan = '';
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('API log'), _SITE_URL.'ApiLog', "class='nav-btn'")
                        .div(
                            span(_('API log'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'ApiLog', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['ApiLog']['IdApiRbac']['html']
.$this->fields['ApiLog']['IdAuthy']['html']
.$this->fields['ApiLog']['Time']['html']
.$this->fields['ApiLog']['RawParameters']['html']
.$this->fields['ApiLog']['Count']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntApiLog", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formApiLog' class='mainForm formContent' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['ApiLog']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['ApiLog']['ixmemautocapp'];
            unset($_SESSION['mem']['ApiLog']['ixmemautocapp']);
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

        $this->fieldsRo['ApiLog']['IdApiRbac']['html'] = stdFieldRow(_("Rule"), div( htmlspecialchars((string)(($dataObj->getApiRbac())?($dataObj->getApiRbac()->getModel().' '.$dataObj->getApiRbac()->getAction().' '.$dataObj->getApiRbac()->getQuery()):''), ENT_QUOTES), 'IdApiRbac_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdApiRbac', $dataObj->getIdApiRbac(), "s='d'"), 'IdApiRbac', "", $this->commentsIdApiRbac, $this->commentsIdApiRbac_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiLog']['IdAuthy']['html'] = stdFieldRow(_("User"), div( htmlspecialchars((string)(($dataObj->getAuthy())?($dataObj->getAuthy()->getUsername()):''), ENT_QUOTES), 'IdAuthy_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdAuthy', $dataObj->getIdAuthy(), "s='d'"), 'IdAuthy', "", $this->commentsIdAuthy, $this->commentsIdAuthy_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiLog']['Time']['html'] = stdFieldRow(_("Time"), div( htmlspecialchars((string)($dataObj->getTime()), ENT_QUOTES), 'Time_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Time', $dataObj->getTime(), "s='d'"), 'Time', "", $this->commentsTime, $this->commentsTime_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['ApiLog']['RawParameters']['html'] = stdFieldRow(_("Raw parameters"), div( htmlspecialchars((string)($dataObj->getRawParameters()), ENT_QUOTES), 'RawParameters_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RawParameters', $dataObj->getRawParameters(), "s='d'"), 'RawParameters', "", $this->commentsRawParameters, $this->commentsRawParameters_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['ApiLog']['Count']['html'] = stdFieldRow(_("Count"), div( htmlspecialchars((string)($dataObj->getCount()), ENT_QUOTES), 'Count_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Count', $dataObj->getCount(), "s='d'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['ApiLog'] as $field => $ar) {
                $this->fields['ApiLog'][$field]['html'] = $this->fieldsRo['ApiLog'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['ApiLog'][$field]['html'] = $this->fieldsRo['ApiLog'][$field]['html'];
            }
        }
    }

    /**
     * Query for ApiLog_IdApiRbac selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxApiLog_IdApiRbac(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && !method_exists($gcSbHost, 'beginSelectboxApiLog_IdApiRbac')
        && !method_exists($gcSbHost, 'selectboxDataApiLog_IdApiRbac');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('api_rbac', 'ApiLog_IdApiRbac', false);
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = ApiRbacQuery::create();

    $gcSbHost = is_object($obj) ? $obj : $this;
    if(method_exists($gcSbHost, 'beginSelectboxApiLog_IdApiRbac') and $array)
        $ret = $gcSbHost->beginSelectboxApiLog_IdApiRbac($q, $dataObj, $data, $obj);
    if($ret !== false)
            $q->addAsColumn('selDisplay', 'CONCAT_WS ( ", ", '.ApiRbacPeer::MODEL.', '.ApiRbacPeer::ACTION.', '.ApiRbacPeer::QUERY.' )');
            $q->select(['selDisplay', 'IdApiRbac']);
            $q->orderBy('selDisplay', 'ASC');
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataApiLog_IdApiRbac')){
                $gcSbHost->selectboxDataApiLog_IdApiRbac($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('api_rbac', 'ApiLog_IdApiRbac', false, $gcSbResult);
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}

    /**
     * Query for ApiLog_IdAuthy selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxApiLog_IdAuthy(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && !method_exists($gcSbHost, 'beginSelectboxApiLog_IdAuthy')
        && !method_exists($gcSbHost, 'selectboxDataApiLog_IdAuthy');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('authy', 'ApiLog_IdAuthy', true);
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = AuthyQuery::create();

    $gcSbHost = is_object($obj) ? $obj : $this;
    if(method_exists($gcSbHost, 'beginSelectboxApiLog_IdAuthy') and $array)
        $ret = $gcSbHost->beginSelectboxApiLog_IdAuthy($q, $dataObj, $data, $obj);
    if($ret !== false)
            $q->addAsColumn('selDisplay', ''.AuthyPeer::USERNAME.'');
            $q->select(['selDisplay', 'IdAuthy']);
            $q->orderBy('selDisplay', 'ASC');
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataApiLog_IdAuthy')){
                $gcSbHost->selectboxDataApiLog_IdAuthy($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('authy', 'ApiLog_IdAuthy', true, $gcSbResult);
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
