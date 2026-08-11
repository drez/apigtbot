<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'AuthyLog' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class AuthyLogForm extends AuthyLog
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
        $this->model_name = 'AuthyLog';
        $this->virtualClassName = 'AuthyLog';
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

        $q = new AuthyLogQuery();
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
            $q->filterByIdAuthy($hasParent);
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
                                var __se=document.querySelector(\"#AuthyLogListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
                $trHead = th(_("Date"), " th='sorted' c='Timestamp' title='" . _('Date')."' ")
.th(_("Username"), " th='sorted' c='Login' title='" . _('Username')."' ")
.th(_("Event"), " th='sorted' c='Event' title='" . _('Event')."' ")
.th(_("Ip"), " th='sorted' c='Ip' title='" . _('Ip')."' ")
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
                 if($_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addAuthyLog' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'AuthyLog';
        $altValue = array (
  'IdAuthyLog' => NULL,
  'IdAuthy' => NULL,
  'Timestamp' => NULL,
  'Login' => NULL,
  'Userid' => NULL,
  'Result' => NULL,
  'Event' => NULL,
  'Ip' => NULL,
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
            $this->isChild = 'AuthyLog';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'AuthyLog/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'AuthyLog/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['AuthyLog/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'AuthyLog/');

        
        
        $default_order[]['Timestamp']='DESC';
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
            $gcGroupCol = 'Timestamp';
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
                if($_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteAuthyLog' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Timestamp'] !== null ) ? $altValue['Timestamp'] : $data->getTimestamp()));
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
   div('' . span(htmlspecialchars((string)((($altValue['Timestamp'] !== null ) ? $altValue['Timestamp'] : $data->getTimestamp())))." ", "   i='" . $__pkJsonEsc . "' c='Timestamp' class=''  j='editAuthyLog'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Login'] !== null ) ? $altValue['Login'] : $data->getLogin())))." ", "   i='" . $__pkJsonEsc . "' c='Login' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Event'] !== null ) ? $altValue['Event'] : $data->getEvent())))." ", "   i='" . $__pkJsonEsc . "' c='Event' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Ip'] !== null ) ? $altValue['Ip'] : $data->getIp())))." ", "   i='" . $__pkJsonEsc . "' c='Ip' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." ", "   i='" . $__pkJsonEsc . "' c='Count' class=''  j='editAuthyLog'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='AuthyLogRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Timestamp'] !== null ) ? $altValue['Timestamp'] : $data->getTimestamp())))." "), "  i='" . $__pkJsonEsc . "' c='Timestamp' class=''  j='editAuthyLog'") .
                td(span(htmlspecialchars((string)((($altValue['Login'] !== null ) ? $altValue['Login'] : $data->getLogin())))." "), "  i='" . $__pkJsonEsc . "' c='Login' class=''  j='editAuthyLog'") .
                td(span(htmlspecialchars((string)((($altValue['Event'] !== null ) ? $altValue['Event'] : $data->getEvent())))." "), "  i='" . $__pkJsonEsc . "' c='Event' class=''  j='editAuthyLog'") .
                td(span(htmlspecialchars((string)((($altValue['Ip'] !== null ) ? $altValue['Ip'] : $data->getIp())))." "), "  i='" . $__pkJsonEsc . "' c='Ip' class=''  j='editAuthyLog'") .
                td(span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." "), "  i='" . $__pkJsonEsc . "' c='Count' class=''  j='editAuthyLog'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='AuthyLogDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountAuthyLog', $i);
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
                .div($controlsContent,'AuthyLogControlsList', "class='custom-controls'")
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
                        .span(_('Login log'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Date"), " type='button' th='sorted' c='Timestamp' class='va-mob-sortrow' ") . button(_("Username"), " type='button' th='sorted' c='Login' class='va-mob-sortrow' ") . button(_("Event"), " type='button' th='sorted' c='Event' class='va-mob-sortrow' ") . button(_("Ip"), " type='button' th='sorted' c='Ip' class='va-mob-sortrow' ") . button(_("Count"), " type='button' th='sorted' c='Count' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='AuthyLogTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'AuthyLogListForm', " class='va-mob proto-app' data-model='AuthyLog' data-table='AuthyLog' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='AuthyLog' data-parent='Authy'" : ''));

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addAuthyLogAutoc');
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
    public function setCreateDefaultsAuthyLog(array $data): AuthyLog
    {

        unset($data['IdAuthyLog']);
        $e = new AuthyLog();


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //foreign
        $e->setIdAuthy(( $data['IdAuthy'] == '' ) ? null : $data['IdAuthy']);
        $e->setTimestamp( ($data['Timestamp'] == '' || $data['Timestamp'] == 'null' || substr($data['Timestamp'],0,10) == '-0001-11-30') ? null : $data['Timestamp'] );
        //integer not required
        $e->setUserid( ($data['Userid'] == '' ) ? null : $data['Userid']);
        //integer not required
        $e->setEvent( ($data['Event'] == '' ) ? null : $data['Event']);
        //integer not required
        $e->setCount( ($data['Count'] == '' ) ? null : $data['Count']);
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsAuthyLog(array $data): ?AuthyLog
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyLogQuery::class, json_decode($data['i']), 'AuthyLog', 'w');
        if ($e === null) { return null; }


        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if( isset($data['IdAuthy']) ){
            $e->setIdAuthy(( $data['IdAuthy'] == '' ) ? null : $data['IdAuthy']);
        }
        if(isset($data['Timestamp'])){
            $e->setTimestamp( ($data['Timestamp'] == '' || $data['Timestamp'] == 'null' || substr($data['Timestamp'],0,10) == '-0001-11-30') ? null : $data['Timestamp'] );
        }
        if(isset($data['Userid'])){
            $e->setUserid( ($data['Userid'] == '' ) ? null : $data['Userid']);
        }
        if(isset($data['Event'])){
            $e->setEvent( ($data['Event'] == '' ) ? null : $data['Event']);
        }
        if(isset($data['Count'])){
            $e->setCount( ($data['Count'] == '' ) ? null : $data['Count']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of AuthyLog
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

        $je = "AuthyLogTable";

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

                case 'Authy':
                    $data['IdAuthy'] = $data['ip'];
                    break;
            }
            $IdParent = $data['ip'];
        }

        if(!$IdParent && isset($data['ip']) && $data['ip'] !== '' && $data['ip'] != 0) {
            $IdParent = $data['ip'];
            $data['IdAuthy'] = $data['ip'];
        }

        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addAuthyLog' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = AuthyLogQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->AuthyLog['isNew'] = 'yes';
            $dataObj = new AuthyLog();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdAuthy";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->AuthyLog['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;












$this->fields['AuthyLog']['Timestamp']['html'] = stdFieldRow(_("Date"), input('datetime-local', 'Timestamp', $dataObj->getTimestamp(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Date'"), 'Timestamp', "", $this->commentsTimestamp, $this->commentsTimestamp_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyLog']['Login']['html'] = stdFieldRow(_("Username"), input('text', 'Login', htmlentities((string)($dataObj->getLogin() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Username'))."' size='35'  v='LOGIN' s='d' class='req'  ")."", 'Login', "", $this->commentsLogin, $this->commentsLogin_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyLog']['Event']['html'] = stdFieldRow(_("Event"), input('text', 'Event', htmlentities((string)($dataObj->getEvent() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Event'))."' size='35'  v='EVENT' s='d' class=''  ")."", 'Event', "", $this->commentsEvent, $this->commentsEvent_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyLog']['Ip']['html'] = stdFieldRow(_("Ip"), input('text', 'Ip', htmlentities((string)($dataObj->getIp() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Ip'))."' size='15'  v='IP' s='d' class='req'  ")."", 'Ip', "", $this->commentsIp, $this->commentsIp_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyLog']['Count']['html'] = stdFieldRow(_("Count"), input('number', 'Count', $dataObj->getCount(), " step='1' placeholder='".str_replace("'","&#39;",_('Count'))."' v='COUNT' size='0' s='d' class=''"), 'Count', "", $this->commentsCount, $this->commentsCount_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['AuthyLog']['IdAuthy']['html'] = input('hidden', 'IdAuthy', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('AuthyLog','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('AuthyLog','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveAuthyLog', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedAuthyLog','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdAuthyLog', $dataObj->getIdAuthyLog(), " s='d' pk").input('hidden', 'IdAuthy', $dataObj->getIdAuthy(), " s='d' nodesc").input('hidden', 'Userid', $dataObj->getUserid(), "   v='USERID' size='0' s='d' class=''").input('hidden', 'Result', htmlentities((string)($dataObj->getResult() ?? '')), "   placeholder='".str_replace("'","&#39;",_(''))."' size='35'  v='RESULT' s='d' class='req'  ").""
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Login log'), _SITE_URL.'AuthyLog', "class='nav-btn'")
                        .div(
                            span(_('Login log'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'AuthyLog', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['AuthyLog']['Timestamp']['html']
.$this->fields['AuthyLog']['Login']['html']
.$this->fields['AuthyLog']['Event']['html']
.$this->fields['AuthyLog']['Ip']['html']
.$this->fields['AuthyLog']['Count']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntAuthyLog", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formAuthyLog' class='mainForm formContent' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['AuthyLog']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['AuthyLog']['ixmemautocapp'];
            unset($_SESSION['mem']['AuthyLog']['ixmemautocapp']);
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

        $this->fieldsRo['AuthyLog']['Timestamp']['html'] = stdFieldRow(_("Date"), div( htmlspecialchars((string)($dataObj->getTimestamp()), ENT_QUOTES), 'Timestamp_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Timestamp', $dataObj->getTimestamp(), "s='d'"), 'Timestamp', "", $this->commentsTimestamp, $this->commentsTimestamp_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['AuthyLog']['Login']['html'] = stdFieldRow(_("Username"), div( htmlspecialchars((string)($dataObj->getLogin()), ENT_QUOTES), 'Login_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Login', $dataObj->getLogin(), "s='d'"), 'Login', "", $this->commentsLogin, $this->commentsLogin_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['AuthyLog']['Event']['html'] = stdFieldRow(_("Event"), div( htmlspecialchars((string)($dataObj->getEvent()), ENT_QUOTES), 'Event_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Event', $dataObj->getEvent(), "s='d'"), 'Event', "", $this->commentsEvent, $this->commentsEvent_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['AuthyLog']['Ip']['html'] = stdFieldRow(_("Ip"), div( htmlspecialchars((string)($dataObj->getIp()), ENT_QUOTES), 'Ip_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ip', $dataObj->getIp(), "s='d'"), 'Ip', "", $this->commentsIp, $this->commentsIp_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['AuthyLog']['Count']['html'] = stdFieldRow(_("Count"), div( htmlspecialchars((string)($dataObj->getCount()), ENT_QUOTES), 'Count_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Count', $dataObj->getCount(), "s='d'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['AuthyLog'] as $field => $ar) {
                $this->fields['AuthyLog'][$field]['html'] = $this->fieldsRo['AuthyLog'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['AuthyLog'][$field]['html'] = $this->fieldsRo['AuthyLog'][$field]['html'];
            }
        }
    }
}
