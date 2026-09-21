<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'MarketOutlook' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class MarketOutlookForm extends MarketOutlook
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

        public $commentsIdMarketOutlook;
    public $commentsIdMarketOutlook_css;
    public $commentsSymbol;
    public $commentsSymbol_css;
    public $commentsKind;
    public $commentsKind_css;
    public $commentsVerdict;
    public $commentsVerdict_css;
    public $commentsPrevVerdict;
    public $commentsPrevVerdict_css;
    public $commentsPriceAt;
    public $commentsPriceAt_css;
    public $commentsCalledAt;
    public $commentsCalledAt_css;
    public $commentsDetail;
    public $commentsDetail_css;
    public $commentsEvalStatus;
    public $commentsEvalStatus_css;
    public $commentsPrice7d;
    public $commentsPrice7d_css;
    public $commentsPrice30d;
    public $commentsPrice30d_css;
    public $commentsRet7d;
    public $commentsRet7d_css;
    public $commentsRet30d;
    public $commentsRet30d_css;
    public $commentsMaxAdversePct;
    public $commentsMaxAdversePct_css;
    public $commentsHit7d;
    public $commentsHit7d_css;
    public $commentsHit30d;
    public $commentsHit30d_css;
    public $commentsScoredAt;
    public $commentsScoredAt_css;
    public $commentsDateCreation;
    public $commentsDateCreation_css;
    public $commentsDateModification;
    public $commentsDateModification_css;
    public $commentsIdGroupCreation;
    public $commentsIdGroupCreation_css;
    public $commentsIdCreation;
    public $commentsIdCreation_css;
    public $commentsIdModification;
    public $commentsIdModification_css;
    public $MarketOutlook;


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
        $this->model_name = 'MarketOutlook';
        $this->virtualClassName = 'MarketOutlook';
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

        $q = new MarketOutlookQuery();
        $q = $this->setAclFilter($q);


        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['Symbol']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Symbol'], $criteria);

            $q->filterBySymbol($value, $criteria);
        }
        if( isset($this->searchMs['Kind']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Kind'], $criteria);

            $q->filterByKind($value, $criteria);
        }
        if( isset($this->searchMs['Verdict']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Verdict'], $criteria);

            $q->filterByVerdict($value, $criteria);
        }

        }else{
            ## standard list

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
                                    . ' on MarketOutlook — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['MarketOutlook/'],
                                    $_SESSION['mem']['order']['MarketOutlook/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#MarketOutlookListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
                $trHead = th(_("Symbol"), " th='sorted' c='Symbol' title='" . _('Symbol')."' ")
.th(_("Kind"), " th='sorted' c='Kind' title='" . _('Kind')."' ")
.th(_("Verdict"), " th='sorted' c='Verdict' title='" . _('Verdict')."' ")
.th(_("Previous"), " th='sorted' c='PrevVerdict' title='" . _('Previous')."' ")
.th(_("Price at call"), " th='sorted' c='PriceAt' title='" . _('Price at call')."' ")
.th(_("Called at"), " th='sorted' c='CalledAt' title='" . _('Called at')."' ")
.th(_("Eval"), " th='sorted' c='EvalStatus' title='" . _('Eval')."' ")
.th(_("Price +7d"), " th='sorted' c='Price7d' title='" . _('Price +7d')."' ")
.th(_("Price +30d"), " th='sorted' c='Price30d' title='" . _('Price +30d')."' ")
.th(_("Return 7d %"), " th='sorted' c='Ret7d' title='" . _('Return 7d %')."' ")
.th(_("Return 30d %"), " th='sorted' c='Ret30d' title='" . _('Return 30d %')."' ")
.th(_("Max adverse 30d %"), " th='sorted' c='MaxAdversePct' title='" . _('Max adverse 30d %')."' ")
.th(_("Hit 7d"), " th='sorted' c='Hit7d' title='" . _('Hit 7d')."' ")
.th(_("Hit 30d"), " th='sorted' c='Hit30d' title='" . _('Hit 30d')."' ")
.th(_("Scored at"), " th='sorted' c='ScoredAt' title='" . _('Scored at')."' ")
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



                $trSearch = ''
                .form(div(div(input('text', 'Symbol', $this->searchMs['Symbol'] ?? '', '  title="'._('Symbol').'" placeholder="'._('Search').' '._('Symbol').', '._('Kind').', '._('Verdict').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('MarketOutlook')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('MarketOutlook')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(
                        div(_('Search in'),''," class='va-fblock-lbl' ")
                        .div(''.button(span(_('Symbol'))," type='button' class='va-fchip active' data-msfield='Symbol' ").button(span(_('Kind'))," type='button' class='va-fchip active' data-msfield='Kind' ").button(span(_('Verdict'))," type='button' class='va-fchip active' data-msfield='Verdict' "),''," class='va-fchips' ")
                        .div(input('text', 'Kind', $this->searchMs['Kind'] ?? '', '  title="'._('Kind').'" placeholder="'._('Kind').'"',''),'','class="ac-search-item"').div(input('text', 'Verdict', $this->searchMs['Verdict'] ?? '', '  title="'._('Verdict').'" placeholder="'._('Verdict').'"',''),'','class="ac-search-item"')
                    ,''," class='va-fblock' data-block='searchin' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('MarketOutlook')."' hidden ").div(
                           button(span(_("Search")),'id="msMarketOutlookBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msMarketOutlookBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsMarketOutlook' class='va-mob-searchform' data-entity='MarketOutlook'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addMarketOutlook' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'MarketOutlook';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdMarketOutlook' => NULL,
  'Symbol' => NULL,
  'Kind' => NULL,
  'Verdict' => NULL,
  'PrevVerdict' => NULL,
  'PriceAt' => NULL,
  'CalledAt' => NULL,
  'Detail' => NULL,
  'EvalStatus' => NULL,
  'Price7d' => NULL,
  'Price30d' => NULL,
  'Ret7d' => NULL,
  'Ret30d' => NULL,
  'MaxAdversePct' => NULL,
  'Hit7d' => NULL,
  'Hit30d' => NULL,
  'ScoredAt' => NULL,
  'DateCreation' => NULL,
  'DateModification' => NULL,
  'IdGroupCreation' => NULL,
  'IdCreation' => NULL,
  'IdModification' => NULL,
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
            $this->isChild = 'MarketOutlook';
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
        $gcListKey = 'MarketOutlook/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'MarketOutlook/child';
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



        $default_order[]['CalledAt']='DESC';
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
            $gcGroupCol = 'Symbol';
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
                if($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteMarketOutlook' ");
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
   div('' . span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." ", "   i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editMarketOutlook'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Kind'] !== null ) ? $altValue['Kind'] : $data->getKind())))." ", "   i='" . $__pkJsonEsc . "' c='Kind' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : $data->getVerdict())))." ", "   i='" . $__pkJsonEsc . "' c='Verdict' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['PrevVerdict'] !== null ) ? $altValue['PrevVerdict'] : $data->getPrevVerdict())))." ", "   i='" . $__pkJsonEsc . "' c='PrevVerdict' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['PriceAt'] !== null ) ? $altValue['PriceAt'] : str_replace(',', '.', (string)($data->getPriceAt() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PriceAt' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['CalledAt'] !== null ) ? $altValue['CalledAt'] : $data->getCalledAt())))." ", "   i='" . $__pkJsonEsc . "' c='CalledAt' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['EvalStatus'] !== null ) ? $altValue['EvalStatus'] : $data->getEvalStatus())))." ", "   i='" . $__pkJsonEsc . "' c='EvalStatus' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Price7d'] !== null ) ? $altValue['Price7d'] : str_replace(',', '.', (string)($data->getPrice7d() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Price7d' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Price30d'] !== null ) ? $altValue['Price30d'] : str_replace(',', '.', (string)($data->getPrice30d() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Price30d' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Ret7d'] !== null ) ? $altValue['Ret7d'] : str_replace(',', '.', (string)($data->getRet7d() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Ret7d' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Ret30d'] !== null ) ? $altValue['Ret30d'] : str_replace(',', '.', (string)($data->getRet30d() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Ret30d' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['MaxAdversePct'] !== null ) ? $altValue['MaxAdversePct'] : str_replace(',', '.', (string)($data->getMaxAdversePct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='MaxAdversePct' class='right'  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Hit7d'] !== null ) ? $altValue['Hit7d'] : ($data->getHit7d() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Hit7d' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['Hit30d'] !== null ) ? $altValue['Hit30d'] : ($data->getHit30d() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Hit30d' class=''  j='editMarketOutlook'") . span(htmlspecialchars((string)((($altValue['ScoredAt'] !== null ) ? $altValue['ScoredAt'] : $data->getScoredAt())))." ", "   i='" . $__pkJsonEsc . "' c='ScoredAt' class=''  j='editMarketOutlook'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='MarketOutlookRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." "), "  i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Kind'] !== null ) ? $altValue['Kind'] : $data->getKind())))." "), "  i='" . $__pkJsonEsc . "' c='Kind' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : $data->getVerdict())))." "), "  i='" . $__pkJsonEsc . "' c='Verdict' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['PrevVerdict'] !== null ) ? $altValue['PrevVerdict'] : $data->getPrevVerdict())))." "), "  i='" . $__pkJsonEsc . "' c='PrevVerdict' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['PriceAt'] !== null ) ? $altValue['PriceAt'] : str_replace(',', '.', (string)($data->getPriceAt() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PriceAt' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['CalledAt'] !== null ) ? $altValue['CalledAt'] : $data->getCalledAt())))." "), "  i='" . $__pkJsonEsc . "' c='CalledAt' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['EvalStatus'] !== null ) ? $altValue['EvalStatus'] : $data->getEvalStatus())))." "), "  i='" . $__pkJsonEsc . "' c='EvalStatus' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Price7d'] !== null ) ? $altValue['Price7d'] : str_replace(',', '.', (string)($data->getPrice7d() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Price7d' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Price30d'] !== null ) ? $altValue['Price30d'] : str_replace(',', '.', (string)($data->getPrice30d() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Price30d' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Ret7d'] !== null ) ? $altValue['Ret7d'] : str_replace(',', '.', (string)($data->getRet7d() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Ret7d' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Ret30d'] !== null ) ? $altValue['Ret30d'] : str_replace(',', '.', (string)($data->getRet30d() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Ret30d' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['MaxAdversePct'] !== null ) ? $altValue['MaxAdversePct'] : str_replace(',', '.', (string)($data->getMaxAdversePct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='MaxAdversePct' class='right'  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Hit7d'] !== null ) ? $altValue['Hit7d'] : ($data->getHit7d() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Hit7d' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['Hit30d'] !== null ) ? $altValue['Hit30d'] : ($data->getHit30d() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Hit30d' class=''  j='editMarketOutlook'") .
                td(span(htmlspecialchars((string)((($altValue['ScoredAt'] !== null ) ? $altValue['ScoredAt'] : $data->getScoredAt())))." "), "  i='" . $__pkJsonEsc . "' c='ScoredAt' class=''  j='editMarketOutlook'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='MarketOutlookDtRow".$__pkEsc."'");

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol()));
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
            $tr .= input('hidden', 'rowCountMarketOutlook', $i);

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
                .div($controlsContent,'MarketOutlookControlsList', "class='custom-controls'")
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
                        .span(_('Market Outlook'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Symbol"), " type='button' th='sorted' c='Symbol' class='va-mob-sortrow' ") . button(_("Kind"), " type='button' th='sorted' c='Kind' class='va-mob-sortrow' ") . button(_("Verdict"), " type='button' th='sorted' c='Verdict' class='va-mob-sortrow' ") . button(_("Previous"), " type='button' th='sorted' c='PrevVerdict' class='va-mob-sortrow' ") . button(_("Price at call"), " type='button' th='sorted' c='PriceAt' class='va-mob-sortrow' ") . button(_("Called at"), " type='button' th='sorted' c='CalledAt' class='va-mob-sortrow' ") . button(_("Eval"), " type='button' th='sorted' c='EvalStatus' class='va-mob-sortrow' ") . button(_("Price +7d"), " type='button' th='sorted' c='Price7d' class='va-mob-sortrow' ") . button(_("Price +30d"), " type='button' th='sorted' c='Price30d' class='va-mob-sortrow' ") . button(_("Return 7d %"), " type='button' th='sorted' c='Ret7d' class='va-mob-sortrow' ") . button(_("Return 30d %"), " type='button' th='sorted' c='Ret30d' class='va-mob-sortrow' ") . button(_("Max adverse 30d %"), " type='button' th='sorted' c='MaxAdversePct' class='va-mob-sortrow' ") . button(_("Hit 7d"), " type='button' th='sorted' c='Hit7d' class='va-mob-sortrow' ") . button(_("Hit 30d"), " type='button' th='sorted' c='Hit30d' class='va-mob-sortrow' ") . button(_("Scored at"), " type='button' th='sorted' c='ScoredAt' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='MarketOutlookTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'MarketOutlookListForm', " class='va-mob proto-app' data-model='MarketOutlook' data-table='MarketOutlook' data-gc-db='market_outlook' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addMarketOutlookAutoc');
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
    public function setCreateDefaultsMarketOutlook(array $data): MarketOutlook
    {

        unset($data['IdMarketOutlook']);
        $e = new MarketOutlook();


        $data['Hit7d'] = ($data['Hit7d'] == '')?false:$data['Hit7d'];
        $data['Hit30d'] = ($data['Hit30d'] == '')?false:$data['Hit30d'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //varchar not required
        $e->setPrevVerdict( ($data['PrevVerdict'] == '' ) ? null : $data['PrevVerdict']);
        $e->setCalledAt( ($data['CalledAt'] == '' || $data['CalledAt'] == 'null' || substr($data['CalledAt'], 0, 10) == '-0001-11-30')?date('Y-m-d'):$data['CalledAt'] );
        //longvarchar not required
        $e->setDetail( ($data['Detail'] == '' ) ? null : $data['Detail']);
        //decimal not required
        $e->setPrice7d( ($data['Price7d'] == '' ) ? null : $data['Price7d']);
        //decimal not required
        $e->setPrice30d( ($data['Price30d'] == '' ) ? null : $data['Price30d']);
        //decimal not required
        $e->setRet7d( ($data['Ret7d'] == '' ) ? null : $data['Ret7d']);
        //decimal not required
        $e->setRet30d( ($data['Ret30d'] == '' ) ? null : $data['Ret30d']);
        //decimal not required
        $e->setMaxAdversePct( ($data['MaxAdversePct'] == '' ) ? null : $data['MaxAdversePct']);
        if(isset($data['Hit7d'])){
            $e->setHit7d( (!isset($data['Hit7d']) || $data['Hit7d'] == '') ? false : $data['Hit7d']);
        }
        if(isset($data['Hit30d'])){
            $e->setHit30d( (!isset($data['Hit30d']) || $data['Hit30d'] == '') ? false : $data['Hit30d']);
        }
        $e->setScoredAt( ($data['ScoredAt'] == '' || $data['ScoredAt'] == 'null' || substr($data['ScoredAt'],0,10) == '-0001-11-30') ? null : $data['ScoredAt'] );
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsMarketOutlook(array $data): ?MarketOutlook
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(MarketOutlookQuery::class, json_decode($data['i']), 'MarketOutlook', 'w');
        if ($e === null) { return null; }


        $data['Hit7d'] = ($data['Hit7d'] == '')?false:$data['Hit7d'];
        $data['Hit30d'] = ($data['Hit30d'] == '')?false:$data['Hit30d'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['PrevVerdict'])){
            $e->setPrevVerdict( ($data['PrevVerdict'] == '' ) ? null : $data['PrevVerdict']);
        }
        if(isset($data['CalledAt'])){
            $e->setCalledAt( ($data['CalledAt'] == '' || $data['CalledAt'] == 'null' || substr($data['CalledAt'], 0, 10) == '-0001-11-30') ? null : $data['CalledAt'] );
        }
        if(isset($data['Detail'])){
            $e->setDetail( ($data['Detail'] == '' ) ? null : $data['Detail']);
        }
        if(isset($data['Price7d'])){
            $e->setPrice7d( ($data['Price7d'] == '' ) ? null : $data['Price7d']);
        }
        if(isset($data['Price30d'])){
            $e->setPrice30d( ($data['Price30d'] == '' ) ? null : $data['Price30d']);
        }
        if(isset($data['Ret7d'])){
            $e->setRet7d( ($data['Ret7d'] == '' ) ? null : $data['Ret7d']);
        }
        if(isset($data['Ret30d'])){
            $e->setRet30d( ($data['Ret30d'] == '' ) ? null : $data['Ret30d']);
        }
        if(isset($data['MaxAdversePct'])){
            $e->setMaxAdversePct( ($data['MaxAdversePct'] == '' ) ? null : $data['MaxAdversePct']);
        }
        if(isset($data['ScoredAt'])){
            $e->setScoredAt( ($data['ScoredAt'] == '' || $data['ScoredAt'] == 'null' || substr($data['ScoredAt'],0,10) == '-0001-11-30') ? null : $data['ScoredAt'] );
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of MarketOutlook
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

        $je = "MarketOutlookTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addMarketOutlookForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = MarketOutlookQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('MarketOutlook', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->MarketOutlook['isNew'] = 'yes';
            $dataObj = new MarketOutlook();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->MarketOutlook['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;









        $Hit7dChecked = ($dataObj->getHit7d())?"checked='checked'":'';
                $Hit7d = ($dataObj->getHit7d())?"true":"true";
                    $Hit30dChecked = ($dataObj->getHit30d())?"checked='checked'":'';
                $Hit30d = ($dataObj->getHit30d())?"true":"true";



$this->fields['MarketOutlook']['Symbol']['html'] = stdFieldRow(_("Symbol"), input('text', 'Symbol', htmlentities((string)($dataObj->getSymbol() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Symbol'))."' size='35'  v='SYMBOL' s='d' class='req'  ")."", 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Kind']['html'] = stdFieldRow(_("Kind"), input('text', 'Kind', htmlentities((string)($dataObj->getKind() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Kind'))."' size='15'  v='KIND' s='d' class='req'  ")."", 'Kind', "", $this->commentsKind, $this->commentsKind_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Verdict']['html'] = stdFieldRow(_("Verdict"), input('text', 'Verdict', htmlentities((string)($dataObj->getVerdict() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Verdict'))."' size='15'  v='VERDICT' s='d' class='req'  ")."", 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['PrevVerdict']['html'] = stdFieldRow(_("Previous"), input('text', 'PrevVerdict', htmlentities((string)($dataObj->getPrevVerdict() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Previous'))."' size='15'  v='PREV_VERDICT' s='d' class=''  ")."", 'PrevVerdict', "", $this->commentsPrevVerdict, $this->commentsPrevVerdict_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['PriceAt']['html'] = stdFieldRow(_("Price at call"), input('number', 'PriceAt', $dataObj->getPriceAt(), "  placeholder='".str_replace("'","&#39;",_('Price at call'))."'  v='PRICE_AT' size='10' s='d' class='req'"), 'PriceAt', "", $this->commentsPriceAt, $this->commentsPriceAt_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['CalledAt']['html'] = stdFieldRow(_("Called at"), input('datetime-local', 'CalledAt', $dataObj->getCalledAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='req' title='Called at'"), 'CalledAt', "", $this->commentsCalledAt, $this->commentsCalledAt_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Detail']['html'] = stdFieldRow(_("Detail"), textarea('Detail', htmlentities((string)($dataObj->getDetail() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Detail'))."' cols='71' v='DETAIL' s='d'  class=' ' style='' spellcheck='false'"), 'Detail', "", $this->commentsDetail, $this->commentsDetail_css, '', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['EvalStatus']['html'] = stdFieldRow(_("Eval"), input('text', 'EvalStatus', htmlentities((string)($dataObj->getEvalStatus() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Eval'))."' size='15'  v='EVAL_STATUS' s='d' class='req'  ")."", 'EvalStatus', "", $this->commentsEvalStatus, $this->commentsEvalStatus_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Price7d']['html'] = stdFieldRow(_("Price +7d"), input('number', 'Price7d', $dataObj->getPrice7d(), "  placeholder='".str_replace("'","&#39;",_('Price +7d'))."'  v='PRICE_7D' size='10' s='d' class=''"), 'Price7d', "", $this->commentsPrice7d, $this->commentsPrice7d_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Price30d']['html'] = stdFieldRow(_("Price +30d"), input('number', 'Price30d', $dataObj->getPrice30d(), "  placeholder='".str_replace("'","&#39;",_('Price +30d'))."'  v='PRICE_30D' size='10' s='d' class=''"), 'Price30d', "", $this->commentsPrice30d, $this->commentsPrice30d_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Ret7d']['html'] = stdFieldRow(_("Return 7d %"), input('number', 'Ret7d', $dataObj->getRet7d(), "  placeholder='".str_replace("'","&#39;",_('Return 7d %'))."'  v='RET_7D' size='5' s='d' class=''"), 'Ret7d', "", $this->commentsRet7d, $this->commentsRet7d_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Ret30d']['html'] = stdFieldRow(_("Return 30d %"), input('number', 'Ret30d', $dataObj->getRet30d(), "  placeholder='".str_replace("'","&#39;",_('Return 30d %'))."'  v='RET_30D' size='5' s='d' class=''"), 'Ret30d', "", $this->commentsRet30d, $this->commentsRet30d_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['MaxAdversePct']['html'] = stdFieldRow(_("Max adverse 30d %"), input('number', 'MaxAdversePct', $dataObj->getMaxAdversePct(), "  placeholder='".str_replace("'","&#39;",_('Max adverse 30d %'))."'  v='MAX_ADVERSE_PCT' size='5' s='d' class=''"), 'MaxAdversePct', "", $this->commentsMaxAdversePct, $this->commentsMaxAdversePct_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketOutlook']['Hit7d']['html'] = stdFieldRow(_("Hit 7d"), input('checkbox', 'Hit7d', $Hit7d, "$Hit7dChecked  size='10' s='d'"), 'Hit7d', "", $this->commentsHit7d, $this->commentsHit7d_css, ' half', ' ', 'yes', 'v2');
$this->fields['MarketOutlook']['Hit30d']['html'] = stdFieldRow(_("Hit 30d"), input('checkbox', 'Hit30d', $Hit30d, "$Hit30dChecked  size='10' s='d'"), 'Hit30d', "", $this->commentsHit30d, $this->commentsHit30d_css, ' half', ' ', 'yes', 'v2');
$this->fields['MarketOutlook']['ScoredAt']['html'] = stdFieldRow(_("Scored at"), input('datetime-local', 'ScoredAt', $dataObj->getScoredAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Scored at'"), 'ScoredAt', "", $this->commentsScoredAt, $this->commentsScoredAt_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'Symbol',1=>'Kind',2=>'Verdict',3=>'PrevVerdict',4=>'PriceAt',5=>'CalledAt',6=>'Detail',7=>'EvalStatus',8=>'Price7d',9=>'Price30d',10=>'Ret7d',11=>'Ret30d',12=>'MaxAdversePct',13=>'Hit7d',14=>'Hit30d',15=>'ScoredAt',16=>'IdCreation',17=>'IdModification',18=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }





        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('MarketOutlook','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveMarketOutlook', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedMarketOutlook','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdMarketOutlook', $dataObj->getIdMarketOutlook(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('MarketOutlook: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Market Outlook'), _SITE_URL.'MarketOutlook', "class='nav-btn'")
                        .div(
                            span(_('Market Outlook'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'MarketOutlook', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['MarketOutlook']['Symbol']['html']
.$this->fields['MarketOutlook']['Kind']['html']
.$this->fields['MarketOutlook']['Verdict']['html']
.$this->fields['MarketOutlook']['PrevVerdict']['html']
.$this->fields['MarketOutlook']['PriceAt']['html']
.$this->fields['MarketOutlook']['CalledAt']['html']
.$this->fields['MarketOutlook']['Detail']['html']
.$this->fields['MarketOutlook']['EvalStatus']['html']
.$this->fields['MarketOutlook']['Price7d']['html']
.$this->fields['MarketOutlook']['Price30d']['html']
.$this->fields['MarketOutlook']['Ret7d']['html']
.$this->fields['MarketOutlook']['Ret30d']['html']
.$this->fields['MarketOutlook']['MaxAdversePct']['html']
.$this->fields['MarketOutlook']['Hit7d']['html']
.$this->fields['MarketOutlook']['Hit30d']['html']
.$this->fields['MarketOutlook']['ScoredAt']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntMarketOutlook", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formMarketOutlook' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['MarketOutlook']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['MarketOutlook'][$field]['html'] = $this->fieldsRo['MarketOutlook'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Symbol':
        $this->fieldsRo['MarketOutlook']['Symbol']['html'] = stdFieldRow(_("Symbol"), div( htmlspecialchars((string)($dataObj->getSymbol()), ENT_QUOTES), 'Symbol_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Symbol', $dataObj->getSymbol(), "s='d'"), 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Kind':
        $this->fieldsRo['MarketOutlook']['Kind']['html'] = stdFieldRow(_("Kind"), div( htmlspecialchars((string)($dataObj->getKind()), ENT_QUOTES), 'Kind_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Kind', $dataObj->getKind(), "s='d'"), 'Kind', "", $this->commentsKind, $this->commentsKind_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Verdict':
        $this->fieldsRo['MarketOutlook']['Verdict']['html'] = stdFieldRow(_("Verdict"), div( htmlspecialchars((string)($dataObj->getVerdict()), ENT_QUOTES), 'Verdict_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Verdict', $dataObj->getVerdict(), "s='d'"), 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PrevVerdict':
        $this->fieldsRo['MarketOutlook']['PrevVerdict']['html'] = stdFieldRow(_("Previous"), div( htmlspecialchars((string)($dataObj->getPrevVerdict()), ENT_QUOTES), 'PrevVerdict_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PrevVerdict', $dataObj->getPrevVerdict(), "s='d'"), 'PrevVerdict', "", $this->commentsPrevVerdict, $this->commentsPrevVerdict_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PriceAt':
        $this->fieldsRo['MarketOutlook']['PriceAt']['html'] = stdFieldRow(_("Price at call"), div( htmlspecialchars((string)($dataObj->getPriceAt()), ENT_QUOTES), 'PriceAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PriceAt', $dataObj->getPriceAt(), "s='d'"), 'PriceAt', "", $this->commentsPriceAt, $this->commentsPriceAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'CalledAt':
        $this->fieldsRo['MarketOutlook']['CalledAt']['html'] = stdFieldRow(_("Called at"), div( htmlspecialchars((string)($dataObj->getCalledAt()), ENT_QUOTES), 'CalledAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'CalledAt', $dataObj->getCalledAt(), "s='d'"), 'CalledAt', "", $this->commentsCalledAt, $this->commentsCalledAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Detail':
        $this->fieldsRo['MarketOutlook']['Detail']['html'] = stdFieldRow(_("Detail"), div( htmlspecialchars((string)($dataObj->getDetail()), ENT_QUOTES), 'Detail_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Detail', $dataObj->getDetail(), "s='d'"), 'Detail', "", $this->commentsDetail, $this->commentsDetail_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'EvalStatus':
        $this->fieldsRo['MarketOutlook']['EvalStatus']['html'] = stdFieldRow(_("Eval"), div( htmlspecialchars((string)($dataObj->getEvalStatus()), ENT_QUOTES), 'EvalStatus_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EvalStatus', $dataObj->getEvalStatus(), "s='d'"), 'EvalStatus', "", $this->commentsEvalStatus, $this->commentsEvalStatus_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Price7d':
        $this->fieldsRo['MarketOutlook']['Price7d']['html'] = stdFieldRow(_("Price +7d"), div( htmlspecialchars((string)($dataObj->getPrice7d()), ENT_QUOTES), 'Price7d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Price7d', $dataObj->getPrice7d(), "s='d'"), 'Price7d', "", $this->commentsPrice7d, $this->commentsPrice7d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Price30d':
        $this->fieldsRo['MarketOutlook']['Price30d']['html'] = stdFieldRow(_("Price +30d"), div( htmlspecialchars((string)($dataObj->getPrice30d()), ENT_QUOTES), 'Price30d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Price30d', $dataObj->getPrice30d(), "s='d'"), 'Price30d', "", $this->commentsPrice30d, $this->commentsPrice30d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Ret7d':
        $this->fieldsRo['MarketOutlook']['Ret7d']['html'] = stdFieldRow(_("Return 7d %"), div( htmlspecialchars((string)($dataObj->getRet7d()), ENT_QUOTES), 'Ret7d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ret7d', $dataObj->getRet7d(), "s='d'"), 'Ret7d', "", $this->commentsRet7d, $this->commentsRet7d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Ret30d':
        $this->fieldsRo['MarketOutlook']['Ret30d']['html'] = stdFieldRow(_("Return 30d %"), div( htmlspecialchars((string)($dataObj->getRet30d()), ENT_QUOTES), 'Ret30d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ret30d', $dataObj->getRet30d(), "s='d'"), 'Ret30d', "", $this->commentsRet30d, $this->commentsRet30d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxAdversePct':
        $this->fieldsRo['MarketOutlook']['MaxAdversePct']['html'] = stdFieldRow(_("Max adverse 30d %"), div( htmlspecialchars((string)($dataObj->getMaxAdversePct()), ENT_QUOTES), 'MaxAdversePct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxAdversePct', $dataObj->getMaxAdversePct(), "s='d'"), 'MaxAdversePct', "", $this->commentsMaxAdversePct, $this->commentsMaxAdversePct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Hit7d':
        $this->fieldsRo['MarketOutlook']['Hit7d']['html'] = stdFieldRow(_("Hit 7d"), div( htmlspecialchars((string)($dataObj->getHit7d()), ENT_QUOTES), 'Hit7d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Hit7d', $dataObj->getHit7d(), "s='d'"), 'Hit7d', "", $this->commentsHit7d, $this->commentsHit7d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Hit30d':
        $this->fieldsRo['MarketOutlook']['Hit30d']['html'] = stdFieldRow(_("Hit 30d"), div( htmlspecialchars((string)($dataObj->getHit30d()), ENT_QUOTES), 'Hit30d_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Hit30d', $dataObj->getHit30d(), "s='d'"), 'Hit30d', "", $this->commentsHit30d, $this->commentsHit30d_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ScoredAt':
        $this->fieldsRo['MarketOutlook']['ScoredAt']['html'] = stdFieldRow(_("Scored at"), div( htmlspecialchars((string)($dataObj->getScoredAt()), ENT_QUOTES), 'ScoredAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ScoredAt', $dataObj->getScoredAt(), "s='d'"), 'ScoredAt', "", $this->commentsScoredAt, $this->commentsScoredAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }
}
