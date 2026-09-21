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
    /** getEditForm()/getList() field slots, keyed [Model][Column]['html']. */
    public $fields = [];
    public $fieldsRo = [];
    /** Columns whose read-only markup gcBuildFieldRo() has already built (A43). */
    public $gcFieldRoBuilt = [];
    /** Sort-header restore JS, rebuilt per list query. */
    public $orderReadyJsOrder = '';

        public $commentsIdAuthyLog;
    public $commentsIdAuthyLog_css;
    public $commentsIdAuthy;
    public $commentsIdAuthy_css;
    public $commentsTimestamp;
    public $commentsTimestamp_css;
    public $commentsLogin;
    public $commentsLogin_css;
    public $commentsUserid;
    public $commentsUserid_css;
    public $commentsResult;
    public $commentsResult_css;
    public $commentsEvent;
    public $commentsEvent_css;
    public $commentsIp;
    public $commentsIp_css;
    public $commentsCount;
    public $commentsCount_css;
    public $AuthyLog;


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


            $this->orderReadyJsOrder = '';
            if(!empty($this->searchOrder)){
                $f=0;
                foreach($this->searchOrder as $order){
                    foreach($order as $col => $sens){
                        if($sens){
                            $tOrd = explode('.',$col);
                            # The ordering comes from the session (setOrderVar keeps
                            # whatever the client last clicked, and a session can outlive
                            # a renamed/removed column or be seeded by another list).
                            # Propel throws on a column it cannot resolve, which turned a
                            # stale sort key into a 500 on the whole list — fall back to
                            # the model's default order instead, and forget the key so the
                            # next request is clean.
                            $gcOrdApplied = true;
                            try {
                            if(!empty($tOrd[1])){
                                $q->join($tOrd[0]." order".$f);
                                $orderBy = "use".$tOrd[0]."Query";
                                $q->$orderBy("order".$f, 'left join')->orderBy($tOrd[1], $sens)->endUse();
                            }else{
                                $q->orderBy($col,$sens);
                            }
                            } catch (\Exception $gcOrdEx) {
                                $gcOrdApplied = false;
                                error_log('list order: dropping unresolvable column ' . (string) $col
                                    . ' on AuthyLog — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['AuthyLog/'],
                                    $_SESSION['mem']['order']['AuthyLog/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#AuthyLogListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
                            ";
                            }
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
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
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
        $altValue = $__altValueInit;
        $tr = '';
        $trDt = '';
        $hook = ['class' => ''];
        $this->orderReadyJsOrder = '';
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

        // A22: list session key for search / order / page. $childTableName is
        // always empty in the unified getList(), so the standalone list and every
        // parent-scoped (child-tab) render used to share ONE key and therefore one
        // page/sort/search state. Standalone keeps the historic '<Table>/' key;
        // parent-scoped renders get '<Table>/child'. NOT keyed per parent id:
        // FormHelper stores these keys unbounded, so one entry per visited parent
        // would grow the session forever — instead the stored page is dropped when
        // the parent id changes (search/sort intentionally carry over, matching the
        // pre-existing '<Parent>/<Child>' desktop child-list behaviour).
        $gcListKey = 'AuthyLog/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'AuthyLog/child';
            if (($_SESSION['mem']['ip'][$gcListKey] ?? null) !== (string) $IdParent) {
                $_SESSION['mem']['ip'][$gcListKey] = (string) $IdParent;
                unset($_SESSION['mem']['page'][$gcListKey]);
            }
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', $gcListKey);

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';


        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', $gcListKey);

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order'][$gcListKey])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', $gcListKey);



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
            // $this->searchOrder is the ordering this list actually runs with: the
            // session ordering for this list, or the schema default ($default_order,
            // resolved just above) when the session carries none. A table that
            // declares NO default order leaves it empty — the query emits no ORDER BY,
            // so the list is NOT name-ordered and gets no headers. Only the first
            // entry with a truthy sens decides (that is the primary sort column that
            // getListSearch() applies); direction-agnostic, since a Z→A sort groups
            // just as well as A→Z. Compared on the NORMALISED FULL column name so a
            // dotted FK label ('Product.Name') matches its own sort key.
            // Child-context lists (IdParent set) never letter-group: their order is
            // the child ranking/FK order, not the name column.
            $gcGroupOn = false;
            if (empty($IdParent) && is_array($this->searchOrder)) {
                foreach ($this->searchOrder as $gcOrdEntry) {
                    if (!is_array($gcOrdEntry)) { continue; }
                    foreach ($gcOrdEntry as $gcOrdCol => $gcOrdSens) {
                        if (!$gcOrdSens) { continue; }
                        $gcGroupOn = ($gcGroupNorm($gcOrdCol) === $gcGroupKey);
                        break 2;
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
                # hoist the row PK encodings once — reused by the mobile + desktop row wrappers below
                $__pkJsonEsc = htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES);
                $__pkEsc = htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES);
                $this->listActionCell = '';





                $actionInner = '' . $this->canDelete . $this->listActionCell;
                $actionCell =  td($actionInner, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['Timestamp'] !== null ) ? $altValue['Timestamp'] : $data->getTimestamp())))." ", "   i='" . $__pkJsonEsc . "' c='Timestamp' class=''  j='editAuthyLog'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Login'] !== null ) ? $altValue['Login'] : $data->getLogin())))." ", "   i='" . $__pkJsonEsc . "' c='Login' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Event'] !== null ) ? $altValue['Event'] : $data->getEvent())))." ", "   i='" . $__pkJsonEsc . "' c='Event' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Ip'] !== null ) ? $altValue['Ip'] : $data->getIp())))." ", "   i='" . $__pkJsonEsc . "' c='Ip' class=''  j='editAuthyLog'") . span(htmlspecialchars((string)((($altValue['Count'] !== null ) ? $altValue['Count'] : $data->getCount())))." ", "   i='" . $__pkJsonEsc . "' c='Count' class=''  j='editAuthyLog'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
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

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Timestamp'] !== null ) ? $altValue['Timestamp'] : $data->getTimestamp()));
                    $gcL = mb_strtoupper(mb_substr(trim($gcVal), 0, 1));
                    if ($gcL !== '' && $gcL !== $gcGroupLetter) {
                        $gcGroupLetter = $gcL;
                        $gcListRows[] = div(htmlspecialchars($gcL), '', " class='va-mob-sect-head' ");
                    }
                }
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = $__altValueInit;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountAuthyLog', $i);

        }

        $gcParentRef = '';
        $gcParentPk = json_decode((string) $IdParent);
        if (!empty($gcParentPk) && $_SESSION[_AUTH_VAR]->hasRights('Authy', 'r')) {
            $gcParentObj = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyQuery::class, $gcParentPk, 'Authy', 'r');
            if ($gcParentObj) {
                if ($gcParentRef === '') {
                    $gcParentRef = is_scalar($gcParentPk) ? (string) $gcParentPk : (string) json_encode($gcParentPk);
                }
            }
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
                    .($gcParentRef !== '' ? div(span(_('User'), " class='va-mob-parent-type' ") . span(htmlspecialchars($gcParentRef), " class='va-mob-parent-name' "), '', " class='va-mob-parent' ") : '')
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
            , 'AuthyLogListForm', " class='va-mob proto-app' data-model='AuthyLog' data-table='AuthyLog' data-gc-db='authy_log' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='AuthyLog' data-parent='Authy'" : ''));





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

        $return['js'] .= script($this->hookListJs);
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
        //varchar not required
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
        $childTable = ['html' => '', 'js' => '', 'onReadyJs' => ''];
        $script_autoc_one = '';
        $ongletf = '';
        $mceInclude = '';
        $IdParent = 0;
        $uiTabsId = ( $uiTabsId === null ) ? 'tabsContain' : $uiTabsId;
        $jet = 'tr';

        $je = "AuthyLogTable";

        if($jsElement)	{
            $je = $jsElement;
        }

        if($jsElementType)	{
            $jet = $jsElementType;
        }

        if(!empty($data['data']['ip'])){
            $data['ip'] = $data['data']['ip'];
            $data['pc'] = $data['data']['pc'] ?? '';
            $data['tp'] = $data['data']['tp'] ?? '';
        }

        if(!empty($data['pc'])) {
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
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addAuthyLogForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


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
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('AuthyLog: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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

        // The ['ixmemautocapp'] restore block is gone: nothing in the emitter,
        // the runtime or the template ever writes that session key, so the
        // condition was dead — and with it an unguarded $_GET['Autocapp'] read
        // (a warning on every form render) and an $Autocapp local nothing read.

        $return['js'] .= $childTable['js']
        . script($this->hookFormIncludeJs) ."
        ";

        $return['onReadyJs'] =
        $this->hookFormReadyJsFirst.
        "

        ".$this->bindEditJs."
        ".$this->SaveButtonJs."

        ".$childTable['onReadyJs']."
        ".($error['onReadyJs'] ?? '')."
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
        if($fields === 'all') {
            $fields = array_keys($this->fields['AuthyLog']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['AuthyLog'][$field]['html'] = $this->fieldsRo['AuthyLog'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Timestamp':
        $this->fieldsRo['AuthyLog']['Timestamp']['html'] = stdFieldRow(_("Date"), div( htmlspecialchars((string)($dataObj->getTimestamp()), ENT_QUOTES), 'Timestamp_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Timestamp', $dataObj->getTimestamp(), "s='d'"), 'Timestamp', "", $this->commentsTimestamp, $this->commentsTimestamp_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Login':
        $this->fieldsRo['AuthyLog']['Login']['html'] = stdFieldRow(_("Username"), div( htmlspecialchars((string)($dataObj->getLogin()), ENT_QUOTES), 'Login_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Login', $dataObj->getLogin(), "s='d'"), 'Login', "", $this->commentsLogin, $this->commentsLogin_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Event':
        $this->fieldsRo['AuthyLog']['Event']['html'] = stdFieldRow(_("Event"), div( htmlspecialchars((string)($dataObj->getEvent()), ENT_QUOTES), 'Event_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Event', $dataObj->getEvent(), "s='d'"), 'Event', "", $this->commentsEvent, $this->commentsEvent_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Ip':
        $this->fieldsRo['AuthyLog']['Ip']['html'] = stdFieldRow(_("Ip"), div( htmlspecialchars((string)($dataObj->getIp()), ENT_QUOTES), 'Ip_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ip', $dataObj->getIp(), "s='d'"), 'Ip', "", $this->commentsIp, $this->commentsIp_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Count':
        $this->fieldsRo['AuthyLog']['Count']['html'] = stdFieldRow(_("Count"), div( htmlspecialchars((string)($dataObj->getCount()), ENT_QUOTES), 'Count_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Count', $dataObj->getCount(), "s='d'"), 'Count', "", $this->commentsCount, $this->commentsCount_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }
}
