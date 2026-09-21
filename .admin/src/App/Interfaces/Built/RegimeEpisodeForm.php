<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'RegimeEpisode' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class RegimeEpisodeForm extends RegimeEpisode
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

        public $arrayIdFleetSlotOptions;
    public $arrayIdGridRunOptions;
    public $commentsIdRegimeEpisode;
    public $commentsIdRegimeEpisode_css;
    public $commentsSymbol;
    public $commentsSymbol_css;
    public $commentsAlgo;
    public $commentsAlgo_css;
    public $commentsIdFleetSlot;
    public $commentsIdFleetSlot_css;
    public $commentsIdGridRun;
    public $commentsIdGridRun_css;
    public $commentsVerdict;
    public $commentsVerdict_css;
    public $commentsOpenedAt;
    public $commentsOpenedAt_css;
    public $commentsClosedAt;
    public $commentsClosedAt_css;
    public $commentsPriceOpen;
    public $commentsPriceOpen_css;
    public $commentsPriceClose;
    public $commentsPriceClose_css;
    public $commentsEngagedPctTw;
    public $commentsEngagedPctTw_css;
    public $commentsSamples;
    public $commentsSamples_css;
    public $commentsRealized;
    public $commentsRealized_css;
    public $commentsMtmClose;
    public $commentsMtmClose_css;
    public $commentsHodlPct;
    public $commentsHodlPct_css;
    public $commentsCapturedPct;
    public $commentsCapturedPct_css;
    public $commentsIdleSamples;
    public $commentsIdleSamples_css;
    public $commentsIdleAlertedAt;
    public $commentsIdleAlertedAt_css;
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
    public $RegimeEpisode;


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
        $this->model_name = 'RegimeEpisode';
        $this->virtualClassName = 'RegimeEpisode';
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

        $q = new RegimeEpisodeQuery();
        $q = $this->setAclFilter($q);


        $q

                #default
                ->leftJoinWith('FleetSlot')
                #default
                ->leftJoinWith('GridRun');
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['Symbol']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Symbol'], $criteria);

            $q->filterBySymbol($value, $criteria);
        }
        if( isset($this->searchMs['Algo']) ) {
            $criteria = \Criteria::IN;
            $value = array_values(array_intersect((array)$this->searchMs['Algo'], RegimeEpisodePeer::getValueSet(RegimeEpisodePeer::ALGO)));

            if (!empty($value)) { $q->filterByAlgo($value, $criteria); }
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
                                    . ' on RegimeEpisode — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['RegimeEpisode/'],
                                    $_SESSION['mem']['order']['RegimeEpisode/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#RegimeEpisodeListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
.th(_("Fleet slot symbol"), " th='sorted' c='FleetSlot.Symbol' title='"._('FleetSlot.Symbol')."' ")
.th(_('Fleet slot algorithm'), "t='mc' th='sorted' c='FleetSlot.Algo'").th(_("Grid Run label"), " th='sorted' c='GridRun.Label' title='"._('GridRun.Label')."' ")
.th(_("Verdict"), " th='sorted' c='Verdict' title='" . _('Verdict')."' ")
.th(_("Opened at"), " th='sorted' c='OpenedAt' title='" . _('Opened at')."' ")
.th(_("Closed at"), " th='sorted' c='ClosedAt' title='" . _('Closed at')."' ")
.th(_("Price at open"), " th='sorted' c='PriceOpen' title='" . _('Price at open')."' ")
.th(_("Price at close"), " th='sorted' c='PriceClose' title='" . _('Price at close')."' ")
.th(_("Engaged % (time-weighted)"), " th='sorted' c='EngagedPctTw' title='" . _('Engaged % (time-weighted)')."' ")
.th(_("Samples"), " th='sorted' c='Samples' title='" . _('Samples')."' ")
.th(_("Realized (USDT)"), " th='sorted' c='Realized' title='" . _('Realized (USDT)')."' ")
.th(_("Mark-to-market at close"), " th='sorted' c='MtmClose' title='" . _('Mark-to-market at close')."' ")
.th(_("HODL move %"), " th='sorted' c='HodlPct' title='" . _('HODL move %')."' ")
.th(_("Captured % of HODL"), " th='sorted' c='CapturedPct' title='" . _('Captured % of HODL')."' ")
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
                .form(div(div(input('text', 'Symbol', $this->searchMs['Symbol'] ?? '', '  title="'._('Symbol').'" placeholder="'._('Search').' '._('Symbol').', '._('Verdict').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('RegimeEpisode')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('RegimeEpisode')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(
                        div(_('Search in'),''," class='va-fblock-lbl' ")
                        .div(''.button(span(_('Symbol'))," type='button' class='va-fchip active' data-msfield='Symbol' ").button(span(_('Verdict'))," type='button' class='va-fchip active' data-msfield='Verdict' "),''," class='va-fchips' ")
                        .div(input('text', 'Verdict', $this->searchMs['Verdict'] ?? '', '  title="'._('Verdict').'" placeholder="'._('Verdict').'"',''),'','class="ac-search-item"')
                    ,''," class='va-fblock' data-block='searchin' ").div(
                        div(_('Algorithm'),''," class='va-fblock-lbl' ")
                        .div((function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fchip' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })(),''," class='va-fchips va-fchips-multi' data-msfield='Algo[]' ")
                        .div(selectboxCustomArray('Algo[]', [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ], _('Algorithm'), '  size="1" t="1"   multiple  ', $this->searchMs['Algo'] ?? ''), '', 'class="multiple-select ac-search-item"  title="'._('Algorithm').'"')
                    ,''," class='va-fblock va-fblock-multi' data-block='multi' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('RegimeEpisode')."' hidden ").div(
                           button(span(_("Search")),'id="msRegimeEpisodeBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msRegimeEpisodeBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsRegimeEpisode' class='va-mob-searchform' data-entity='RegimeEpisode'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addRegimeEpisode' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'RegimeEpisode';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdRegimeEpisode' => NULL,
  'Symbol' => NULL,
  'Algo' => NULL,
  'IdFleetSlot' => NULL,
  'IdGridRun' => NULL,
  'Verdict' => NULL,
  'OpenedAt' => NULL,
  'ClosedAt' => NULL,
  'PriceOpen' => NULL,
  'PriceClose' => NULL,
  'EngagedPctTw' => NULL,
  'Samples' => NULL,
  'Realized' => NULL,
  'MtmClose' => NULL,
  'HodlPct' => NULL,
  'CapturedPct' => NULL,
  'IdleSamples' => NULL,
  'IdleAlertedAt' => NULL,
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
            $this->isChild = 'RegimeEpisode';
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
        $gcListKey = 'RegimeEpisode/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'RegimeEpisode/child';
            if (($_SESSION['mem']['ip'][$gcListKey] ?? null) !== (string) $IdParent) {
                $_SESSION['mem']['ip'][$gcListKey] = (string) $IdParent;
                unset($_SESSION['mem']['page'][$gcListKey]);
            }
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', $gcListKey);

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';

            $_gcSel = (array)($this->searchMs['Algo'] ?? []);
            $_gcChips = button(span(_('All')), " type='button' class='va-mob-chip".(empty($_gcSel)?' active':'')."' data-msfield='Algo' data-msval='' ");
            foreach( [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ] as $_gcCo ) {
                $_gcCv = is_array($_gcCo) ? (string)$_gcCo[1] : (string)$_gcCo;
                $_gcCl = is_array($_gcCo) ? (string)$_gcCo[0] : (string)$_gcCo;
                $_gcChips .= button(span(htmlspecialchars($_gcCl)), " type='button' class='va-mob-chip".(in_array($_gcCv, $_gcSel, true)?' active':'')."' data-msfield='Algo' data-msval='".htmlspecialchars($_gcCv)."' ");
            }
            $trChips = div($_gcChips, '', " class='va-mob-chips' ");

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



        $default_order[]['OpenedAt']='DESC';
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
                if($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteRegimeEpisode' ");
                }
            }

            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                # hoist the row PK encodings once — reused by the mobile + desktop row wrappers below
                $__pkJsonEsc = htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES);
                $__pkEsc = htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES);
                $this->listActionCell = '';



        $altValue['FleetSlot_Symbol'] = "";
        if($data->getFleetSlot()){
            $altValue['FleetSlot_Symbol'] = $data->getFleetSlot()->getSymbol();
        }
        $altValue['FleetSlot_Algo'] = "";
        if($data->getFleetSlot()){
            $altValue['FleetSlot_Algo'] = $data->getFleetSlot()->getAlgo();
        }
        $altValue['GridRun_Label'] = "";
        if($data->getGridRun()){
            $altValue['GridRun_Label'] = $data->getGridRun()->getLabel();
        }


                $actionInner = '' . $this->canDelete . $this->listActionCell;
                $actionCell =  td($actionInner, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." ", "   i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editRegimeEpisode'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['IdFleetSlot'] !== null ) ? $altValue['IdFleetSlot'] : $altValue['FleetSlot_Symbol'])))." ", "   i='" . $__pkJsonEsc . "' c='IdFleetSlot' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." ", "   i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : $data->getVerdict())))." ", "   i='" . $__pkJsonEsc . "' c='Verdict' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['OpenedAt'] !== null ) ? $altValue['OpenedAt'] : $data->getOpenedAt())))." ", "   i='" . $__pkJsonEsc . "' c='OpenedAt' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['ClosedAt'] !== null ) ? $altValue['ClosedAt'] : $data->getClosedAt())))." ", "   i='" . $__pkJsonEsc . "' c='ClosedAt' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['PriceOpen'] !== null ) ? $altValue['PriceOpen'] : str_replace(',', '.', (string)($data->getPriceOpen() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PriceOpen' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['PriceClose'] !== null ) ? $altValue['PriceClose'] : str_replace(',', '.', (string)($data->getPriceClose() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PriceClose' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['EngagedPctTw'] !== null ) ? $altValue['EngagedPctTw'] : str_replace(',', '.', (string)($data->getEngagedPctTw() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='EngagedPctTw' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['Samples'] !== null ) ? $altValue['Samples'] : $data->getSamples())))." ", "   i='" . $__pkJsonEsc . "' c='Samples' class=''  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['Realized'] !== null ) ? $altValue['Realized'] : str_replace(',', '.', (string)($data->getRealized() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Realized' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['MtmClose'] !== null ) ? $altValue['MtmClose'] : str_replace(',', '.', (string)($data->getMtmClose() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='MtmClose' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['HodlPct'] !== null ) ? $altValue['HodlPct'] : str_replace(',', '.', (string)($data->getHodlPct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='HodlPct' class='right'  j='editRegimeEpisode'") . span(htmlspecialchars((string)((($altValue['CapturedPct'] !== null ) ? $altValue['CapturedPct'] : str_replace(',', '.', (string)($data->getCapturedPct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='CapturedPct' class='right'  j='editRegimeEpisode'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='RegimeEpisodeRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." "), "  i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['IdFleetSlot'] !== null ) ? $altValue['IdFleetSlot'] : $altValue['FleetSlot_Symbol'])))." "), "  i='" . $__pkJsonEsc . "' c='IdFleetSlot' class=''  j='editRegimeEpisode'") . td(span(htmlspecialchars((string)($altValue['FleetSlot_Algo'])).''), " c='FleetSlot__Algo' j='editRegimeEpisode' i='".$__pkJsonEsc."'").

                td(span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." "), "  i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : $data->getVerdict())))." "), "  i='" . $__pkJsonEsc . "' c='Verdict' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['OpenedAt'] !== null ) ? $altValue['OpenedAt'] : $data->getOpenedAt())))." "), "  i='" . $__pkJsonEsc . "' c='OpenedAt' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['ClosedAt'] !== null ) ? $altValue['ClosedAt'] : $data->getClosedAt())))." "), "  i='" . $__pkJsonEsc . "' c='ClosedAt' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['PriceOpen'] !== null ) ? $altValue['PriceOpen'] : str_replace(',', '.', (string)($data->getPriceOpen() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PriceOpen' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['PriceClose'] !== null ) ? $altValue['PriceClose'] : str_replace(',', '.', (string)($data->getPriceClose() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PriceClose' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['EngagedPctTw'] !== null ) ? $altValue['EngagedPctTw'] : str_replace(',', '.', (string)($data->getEngagedPctTw() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='EngagedPctTw' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['Samples'] !== null ) ? $altValue['Samples'] : $data->getSamples())))." "), "  i='" . $__pkJsonEsc . "' c='Samples' class=''  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['Realized'] !== null ) ? $altValue['Realized'] : str_replace(',', '.', (string)($data->getRealized() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Realized' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['MtmClose'] !== null ) ? $altValue['MtmClose'] : str_replace(',', '.', (string)($data->getMtmClose() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='MtmClose' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['HodlPct'] !== null ) ? $altValue['HodlPct'] : str_replace(',', '.', (string)($data->getHodlPct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='HodlPct' class='right'  j='editRegimeEpisode'") .
                td(span(htmlspecialchars((string)((($altValue['CapturedPct'] !== null ) ? $altValue['CapturedPct'] : str_replace(',', '.', (string)($data->getCapturedPct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='CapturedPct' class='right'  j='editRegimeEpisode'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='RegimeEpisodeDtRow".$__pkEsc."'");

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
            $tr .= input('hidden', 'rowCountRegimeEpisode', $i);

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
                .div($controlsContent,'RegimeEpisodeControlsList', "class='custom-controls'")
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
                        .span(_('Regime episode'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Symbol"), " type='button' th='sorted' c='Symbol' class='va-mob-sortrow' ") . button(_("Fleet slot symbol"), " type='button' th='sorted' c='FleetSlot.Symbol' class='va-mob-sortrow' ") . button(_("Grid Run label"), " type='button' th='sorted' c='GridRun.Label' class='va-mob-sortrow' ") . button(_("Verdict"), " type='button' th='sorted' c='Verdict' class='va-mob-sortrow' ") . button(_("Opened at"), " type='button' th='sorted' c='OpenedAt' class='va-mob-sortrow' ") . button(_("Closed at"), " type='button' th='sorted' c='ClosedAt' class='va-mob-sortrow' ") . button(_("Price at open"), " type='button' th='sorted' c='PriceOpen' class='va-mob-sortrow' ") . button(_("Price at close"), " type='button' th='sorted' c='PriceClose' class='va-mob-sortrow' ") . button(_("Engaged % (time-weighted)"), " type='button' th='sorted' c='EngagedPctTw' class='va-mob-sortrow' ") . button(_("Samples"), " type='button' th='sorted' c='Samples' class='va-mob-sortrow' ") . button(_("Realized (USDT)"), " type='button' th='sorted' c='Realized' class='va-mob-sortrow' ") . button(_("Mark-to-market at close"), " type='button' th='sorted' c='MtmClose' class='va-mob-sortrow' ") . button(_("HODL move %"), " type='button' th='sorted' c='HodlPct' class='va-mob-sortrow' ") . button(_("Captured % of HODL"), " type='button' th='sorted' c='CapturedPct' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='RegimeEpisodeTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'RegimeEpisodeListForm', " class='va-mob proto-app' data-model='RegimeEpisode' data-table='RegimeEpisode' data-gc-db='regime_episode' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addRegimeEpisodeAutoc');
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
    public function setCreateDefaultsRegimeEpisode(array $data): RegimeEpisode
    {

        unset($data['IdRegimeEpisode']);
        $e = new RegimeEpisode();


        if(!$data['Algo']){
            $data['Algo'] = 'Trend';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //foreign
        $e->setIdFleetSlot(( $data['IdFleetSlot'] == '' ) ? null : $data['IdFleetSlot']);
        //foreign
        $e->setIdGridRun(( $data['IdGridRun'] == '' ) ? null : $data['IdGridRun']);
        $e->setOpenedAt( ($data['OpenedAt'] == '' || $data['OpenedAt'] == 'null' || substr($data['OpenedAt'], 0, 10) == '-0001-11-30')?date('Y-m-d'):$data['OpenedAt'] );
        $e->setClosedAt( ($data['ClosedAt'] == '' || $data['ClosedAt'] == 'null' || substr($data['ClosedAt'],0,10) == '-0001-11-30') ? null : $data['ClosedAt'] );
        //decimal not required
        $e->setPriceClose( ($data['PriceClose'] == '' ) ? null : $data['PriceClose']);
        //decimal not required
        $e->setEngagedPctTw( ($data['EngagedPctTw'] == '' ) ? null : $data['EngagedPctTw']);
        //decimal not required
        $e->setRealized( ($data['Realized'] == '' ) ? null : $data['Realized']);
        //decimal not required
        $e->setMtmClose( ($data['MtmClose'] == '' ) ? null : $data['MtmClose']);
        //decimal not required
        $e->setHodlPct( ($data['HodlPct'] == '' ) ? null : $data['HodlPct']);
        //decimal not required
        $e->setCapturedPct( ($data['CapturedPct'] == '' ) ? null : $data['CapturedPct']);
        $e->setIdleAlertedAt( ($data['IdleAlertedAt'] == '' || $data['IdleAlertedAt'] == 'null' || substr($data['IdleAlertedAt'],0,10) == '-0001-11-30') ? null : $data['IdleAlertedAt'] );
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsRegimeEpisode(array $data): ?RegimeEpisode
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(RegimeEpisodeQuery::class, json_decode($data['i']), 'RegimeEpisode', 'w');
        if ($e === null) { return null; }


        if(!$data['Algo']){
            $data['Algo'] = 'Trend';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if( isset($data['IdFleetSlot']) ){
            $e->setIdFleetSlot(( $data['IdFleetSlot'] == '' ) ? null : $data['IdFleetSlot']);
        }
        if( isset($data['IdGridRun']) ){
            $e->setIdGridRun(( $data['IdGridRun'] == '' ) ? null : $data['IdGridRun']);
        }
        if(isset($data['OpenedAt'])){
            $e->setOpenedAt( ($data['OpenedAt'] == '' || $data['OpenedAt'] == 'null' || substr($data['OpenedAt'], 0, 10) == '-0001-11-30') ? null : $data['OpenedAt'] );
        }
        if(isset($data['ClosedAt'])){
            $e->setClosedAt( ($data['ClosedAt'] == '' || $data['ClosedAt'] == 'null' || substr($data['ClosedAt'],0,10) == '-0001-11-30') ? null : $data['ClosedAt'] );
        }
        if(isset($data['PriceClose'])){
            $e->setPriceClose( ($data['PriceClose'] == '' ) ? null : $data['PriceClose']);
        }
        if(isset($data['EngagedPctTw'])){
            $e->setEngagedPctTw( ($data['EngagedPctTw'] == '' ) ? null : $data['EngagedPctTw']);
        }
        if(isset($data['Realized'])){
            $e->setRealized( ($data['Realized'] == '' ) ? null : $data['Realized']);
        }
        if(isset($data['MtmClose'])){
            $e->setMtmClose( ($data['MtmClose'] == '' ) ? null : $data['MtmClose']);
        }
        if(isset($data['HodlPct'])){
            $e->setHodlPct( ($data['HodlPct'] == '' ) ? null : $data['HodlPct']);
        }
        if(isset($data['CapturedPct'])){
            $e->setCapturedPct( ($data['CapturedPct'] == '' ) ? null : $data['CapturedPct']);
        }
        if(isset($data['IdleAlertedAt'])){
            $e->setIdleAlertedAt( ($data['IdleAlertedAt'] == '' || $data['IdleAlertedAt'] == 'null' || substr($data['IdleAlertedAt'],0,10) == '-0001-11-30') ? null : $data['IdleAlertedAt'] );
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of RegimeEpisode
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

        $je = "RegimeEpisodeTable";

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

                case 'FleetSlot':
                    $data['IdFleetSlot'] = $data['ip'];
                    break;
                case 'GridRun':
                    $data['IdGridRun'] = $data['ip'];
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


        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addRegimeEpisodeForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = RegimeEpisodeQuery::create()

                #default
                ->leftJoinWith('FleetSlot')
                #default
                ->leftJoinWith('GridRun')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->RegimeEpisode['isNew'] = 'yes';
            $dataObj = new RegimeEpisode();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->RegimeEpisode['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getFleetSlot())?'':$dataObj->setFleetSlot( new FleetSlot() );
                                    ($dataObj->getGridRun())?'':$dataObj->setGridRun( new GridRun() );








$this->fields['RegimeEpisode']['Symbol']['html'] = stdFieldRow(_("Symbol"), input('text', 'Symbol', htmlentities((string)($dataObj->getSymbol() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Symbol'))."' size='35'  v='SYMBOL' s='d' class='req'  ")."", 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['Algo']['html'] = stdFieldRow(_("Algorithm"), selectboxCustomArray('Algo', [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ], "", "s='d'  ", $dataObj->getAlgo(), '', false), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['IdFleetSlot']['html'] = stdFieldRow(_("Slot"),
    input('text', 'IdFleetSlotAutoc', $dataObj->getFleetSlot()?->getSymbol() . " " . $dataObj->getFleetSlot()?->getAlgo(), " title='".str_replace("'","", (string)($dataObj->getFleetSlot()?->getSymbol() . " " . $dataObj->getFleetSlot()?->getAlgo()))."' v='ID_FLEET_SLOT' rid='IdFleetSlot' placeholder='"._('Slot')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdFleetSlot&quot;,&quot;table&quot;:&quot;RegimeEpisode&quot;,&quot;childTable&quot;:&quot;FleetSlot&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;FleetSlot&quot;,&quot;show&quot;:[&quot;Symbol&quot;,&quot;Algo&quot;],&quot;id&quot;:&quot;IdFleetSlot&quot;,&quot;filter&quot;:&quot;Symbol&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdFleetSlot', $dataObj->getIdFleetSlot(), "s='d'"), 'IdFleetSlot', "", $this->commentsIdFleetSlot, $this->commentsIdFleetSlot_css, '', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['IdGridRun']['html'] = stdFieldRow(_("Run"),
    input('text', 'IdGridRunAutoc', $dataObj->getGridRun()?->getLabel(), " title='".str_replace("'","", (string)($dataObj->getGridRun()?->getLabel()))."' v='ID_GRID_RUN' rid='IdGridRun' placeholder='"._('Run')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdGridRun&quot;,&quot;table&quot;:&quot;RegimeEpisode&quot;,&quot;childTable&quot;:&quot;GridRun&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;GridRun&quot;,&quot;show&quot;:[&quot;Label&quot;],&quot;id&quot;:&quot;IdGridRun&quot;,&quot;filter&quot;:&quot;Label&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, '', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['Verdict']['html'] = stdFieldRow(_("Verdict"), input('text', 'Verdict', htmlentities((string)($dataObj->getVerdict() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Verdict'))."' size='15'  v='VERDICT' s='d' class='req'  ")."", 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['OpenedAt']['html'] = stdFieldRow(_("Opened at"), input('datetime-local', 'OpenedAt', $dataObj->getOpenedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='req' title='Opened at'"), 'OpenedAt', "", $this->commentsOpenedAt, $this->commentsOpenedAt_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['ClosedAt']['html'] = stdFieldRow(_("Closed at"), input('datetime-local', 'ClosedAt', $dataObj->getClosedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Closed at'"), 'ClosedAt', "", $this->commentsClosedAt, $this->commentsClosedAt_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['PriceOpen']['html'] = stdFieldRow(_("Price at open"), input('number', 'PriceOpen', $dataObj->getPriceOpen(), "  placeholder='".str_replace("'","&#39;",_('Price at open'))."'  v='PRICE_OPEN' size='10' s='d' class='req'"), 'PriceOpen', "", $this->commentsPriceOpen, $this->commentsPriceOpen_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['PriceClose']['html'] = stdFieldRow(_("Price at close"), input('number', 'PriceClose', $dataObj->getPriceClose(), "  placeholder='".str_replace("'","&#39;",_('Price at close'))."'  v='PRICE_CLOSE' size='10' s='d' class=''"), 'PriceClose', "", $this->commentsPriceClose, $this->commentsPriceClose_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['EngagedPctTw']['html'] = stdFieldRow(_("Engaged % (time-weighted)"), input('number', 'EngagedPctTw', $dataObj->getEngagedPctTw(), "  placeholder='".str_replace("'","&#39;",_('Engaged % (time-weighted)'))."'  v='ENGAGED_PCT_TW' size='5' s='d' class=''"), 'EngagedPctTw', "", $this->commentsEngagedPctTw, $this->commentsEngagedPctTw_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['Samples']['html'] = stdFieldRow(_("Samples"), input('number', 'Samples', $dataObj->getSamples(), " step='1' placeholder='".str_replace("'","&#39;",_('Samples'))."' v='SAMPLES' size='5' s='d' class=''"), 'Samples', "", $this->commentsSamples, $this->commentsSamples_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['Realized']['html'] = stdFieldRow(_("Realized (USDT)"), input('number', 'Realized', $dataObj->getRealized(), "  placeholder='".str_replace("'","&#39;",_('Realized (USDT)'))."'  v='REALIZED' size='10' s='d' class=''"), 'Realized', "", $this->commentsRealized, $this->commentsRealized_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['MtmClose']['html'] = stdFieldRow(_("Mark-to-market at close"), input('number', 'MtmClose', $dataObj->getMtmClose(), "  placeholder='".str_replace("'","&#39;",_('Mark-to-market at close'))."'  v='MTM_CLOSE' size='10' s='d' class=''"), 'MtmClose', "", $this->commentsMtmClose, $this->commentsMtmClose_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['HodlPct']['html'] = stdFieldRow(_("HODL move %"), input('number', 'HodlPct', $dataObj->getHodlPct(), "  placeholder='".str_replace("'","&#39;",_('HODL move %'))."'  v='HODL_PCT' size='5' s='d' class=''"), 'HodlPct', "", $this->commentsHodlPct, $this->commentsHodlPct_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['CapturedPct']['html'] = stdFieldRow(_("Captured % of HODL"), input('number', 'CapturedPct', $dataObj->getCapturedPct(), "  placeholder='".str_replace("'","&#39;",_('Captured % of HODL'))."'  v='CAPTURED_PCT' size='5' s='d' class=''"), 'CapturedPct', "", $this->commentsCapturedPct, $this->commentsCapturedPct_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['IdleSamples']['html'] = stdFieldRow(_("Consecutive sub-floor samples"), input('number', 'IdleSamples', $dataObj->getIdleSamples(), " step='1' placeholder='".str_replace("'","&#39;",_('Consecutive sub-floor samples'))."' v='IDLE_SAMPLES' size='5' s='d' class=''"), 'IdleSamples', "", $this->commentsIdleSamples, $this->commentsIdleSamples_css, ' half', ' ', 'no', 'v2');
$this->fields['RegimeEpisode']['IdleAlertedAt']['html'] = stdFieldRow(_("Idle alerted at"), input('datetime-local', 'IdleAlertedAt', $dataObj->getIdleAlertedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Idle alerted at'"), 'IdleAlertedAt', "", $this->commentsIdleAlertedAt, $this->commentsIdleAlertedAt_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'Symbol',1=>'Algo',2=>'Verdict',3=>'OpenedAt',4=>'ClosedAt',5=>'PriceOpen',6=>'PriceClose',7=>'EngagedPctTw',8=>'Samples',9=>'Realized',10=>'MtmClose',11=>'HodlPct',12=>'CapturedPct',13=>'IdleSamples',14=>'IdleAlertedAt',15=>'IdCreation',16=>'IdModification',17=>'IdGroupCreation',), $dataObj);

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
            if( ($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('RegimeEpisode','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveRegimeEpisode', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedRegimeEpisode','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdRegimeEpisode', $dataObj->getIdRegimeEpisode(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('RegimeEpisode: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Regime episode'), _SITE_URL.'RegimeEpisode', "class='nav-btn'")
                        .div(
                            span(_('Regime episode'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'RegimeEpisode', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['RegimeEpisode']['Symbol']['html']
.$this->fields['RegimeEpisode']['Algo']['html']
.$this->fields['RegimeEpisode']['IdFleetSlot']['html']
.$this->fields['RegimeEpisode']['IdGridRun']['html']
.$this->fields['RegimeEpisode']['Verdict']['html']
.$this->fields['RegimeEpisode']['OpenedAt']['html']
.$this->fields['RegimeEpisode']['ClosedAt']['html']
.$this->fields['RegimeEpisode']['PriceOpen']['html']
.$this->fields['RegimeEpisode']['PriceClose']['html']
.$this->fields['RegimeEpisode']['EngagedPctTw']['html']
.$this->fields['RegimeEpisode']['Samples']['html']
.$this->fields['RegimeEpisode']['Realized']['html']
.$this->fields['RegimeEpisode']['MtmClose']['html']
.$this->fields['RegimeEpisode']['HodlPct']['html']
.$this->fields['RegimeEpisode']['CapturedPct']['html']
.$this->fields['RegimeEpisode']['IdleSamples']['html']
.$this->fields['RegimeEpisode']['IdleAlertedAt']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntRegimeEpisode", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formRegimeEpisode' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['RegimeEpisode']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['RegimeEpisode'][$field]['html'] = $this->fieldsRo['RegimeEpisode'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Symbol':
        $this->fieldsRo['RegimeEpisode']['Symbol']['html'] = stdFieldRow(_("Symbol"), div( htmlspecialchars((string)($dataObj->getSymbol()), ENT_QUOTES), 'Symbol_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Symbol', $dataObj->getSymbol(), "s='d'"), 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Algo':
        $this->fieldsRo['RegimeEpisode']['Algo']['html'] = stdFieldRow(_("Algorithm"), div( htmlspecialchars((string)($dataObj->getAlgo()), ENT_QUOTES), 'Algo_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Algo', $dataObj->getAlgo(), "s='d'"), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IdFleetSlot':
        $this->fieldsRo['RegimeEpisode']['IdFleetSlot']['html'] = stdFieldRow(_("Slot"), div( htmlspecialchars((string)(($dataObj->getFleetSlot())?($dataObj->getFleetSlot()->getSymbol().' '.$dataObj->getFleetSlot()->getAlgo()):''), ENT_QUOTES), 'IdFleetSlot_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdFleetSlot', $dataObj->getIdFleetSlot(), "s='d'"), 'IdFleetSlot', "", $this->commentsIdFleetSlot, $this->commentsIdFleetSlot_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'IdGridRun':
        $this->fieldsRo['RegimeEpisode']['IdGridRun']['html'] = stdFieldRow(_("Run"), div( htmlspecialchars((string)(($dataObj->getGridRun())?($dataObj->getGridRun()->getLabel()):''), ENT_QUOTES), 'IdGridRun_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'Verdict':
        $this->fieldsRo['RegimeEpisode']['Verdict']['html'] = stdFieldRow(_("Verdict"), div( htmlspecialchars((string)($dataObj->getVerdict()), ENT_QUOTES), 'Verdict_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Verdict', $dataObj->getVerdict(), "s='d'"), 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'OpenedAt':
        $this->fieldsRo['RegimeEpisode']['OpenedAt']['html'] = stdFieldRow(_("Opened at"), div( htmlspecialchars((string)($dataObj->getOpenedAt()), ENT_QUOTES), 'OpenedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'OpenedAt', $dataObj->getOpenedAt(), "s='d'"), 'OpenedAt', "", $this->commentsOpenedAt, $this->commentsOpenedAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ClosedAt':
        $this->fieldsRo['RegimeEpisode']['ClosedAt']['html'] = stdFieldRow(_("Closed at"), div( htmlspecialchars((string)($dataObj->getClosedAt()), ENT_QUOTES), 'ClosedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ClosedAt', $dataObj->getClosedAt(), "s='d'"), 'ClosedAt', "", $this->commentsClosedAt, $this->commentsClosedAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PriceOpen':
        $this->fieldsRo['RegimeEpisode']['PriceOpen']['html'] = stdFieldRow(_("Price at open"), div( htmlspecialchars((string)($dataObj->getPriceOpen()), ENT_QUOTES), 'PriceOpen_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PriceOpen', $dataObj->getPriceOpen(), "s='d'"), 'PriceOpen', "", $this->commentsPriceOpen, $this->commentsPriceOpen_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PriceClose':
        $this->fieldsRo['RegimeEpisode']['PriceClose']['html'] = stdFieldRow(_("Price at close"), div( htmlspecialchars((string)($dataObj->getPriceClose()), ENT_QUOTES), 'PriceClose_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PriceClose', $dataObj->getPriceClose(), "s='d'"), 'PriceClose', "", $this->commentsPriceClose, $this->commentsPriceClose_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'EngagedPctTw':
        $this->fieldsRo['RegimeEpisode']['EngagedPctTw']['html'] = stdFieldRow(_("Engaged % (time-weighted)"), div( htmlspecialchars((string)($dataObj->getEngagedPctTw()), ENT_QUOTES), 'EngagedPctTw_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EngagedPctTw', $dataObj->getEngagedPctTw(), "s='d'"), 'EngagedPctTw', "", $this->commentsEngagedPctTw, $this->commentsEngagedPctTw_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Samples':
        $this->fieldsRo['RegimeEpisode']['Samples']['html'] = stdFieldRow(_("Samples"), div( htmlspecialchars((string)($dataObj->getSamples()), ENT_QUOTES), 'Samples_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Samples', $dataObj->getSamples(), "s='d'"), 'Samples', "", $this->commentsSamples, $this->commentsSamples_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Realized':
        $this->fieldsRo['RegimeEpisode']['Realized']['html'] = stdFieldRow(_("Realized (USDT)"), div( htmlspecialchars((string)($dataObj->getRealized()), ENT_QUOTES), 'Realized_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Realized', $dataObj->getRealized(), "s='d'"), 'Realized', "", $this->commentsRealized, $this->commentsRealized_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MtmClose':
        $this->fieldsRo['RegimeEpisode']['MtmClose']['html'] = stdFieldRow(_("Mark-to-market at close"), div( htmlspecialchars((string)($dataObj->getMtmClose()), ENT_QUOTES), 'MtmClose_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MtmClose', $dataObj->getMtmClose(), "s='d'"), 'MtmClose', "", $this->commentsMtmClose, $this->commentsMtmClose_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'HodlPct':
        $this->fieldsRo['RegimeEpisode']['HodlPct']['html'] = stdFieldRow(_("HODL move %"), div( htmlspecialchars((string)($dataObj->getHodlPct()), ENT_QUOTES), 'HodlPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'HodlPct', $dataObj->getHodlPct(), "s='d'"), 'HodlPct', "", $this->commentsHodlPct, $this->commentsHodlPct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'CapturedPct':
        $this->fieldsRo['RegimeEpisode']['CapturedPct']['html'] = stdFieldRow(_("Captured % of HODL"), div( htmlspecialchars((string)($dataObj->getCapturedPct()), ENT_QUOTES), 'CapturedPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'CapturedPct', $dataObj->getCapturedPct(), "s='d'"), 'CapturedPct', "", $this->commentsCapturedPct, $this->commentsCapturedPct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IdleSamples':
        $this->fieldsRo['RegimeEpisode']['IdleSamples']['html'] = stdFieldRow(_("Consecutive sub-floor samples"), div( htmlspecialchars((string)($dataObj->getIdleSamples()), ENT_QUOTES), 'IdleSamples_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdleSamples', $dataObj->getIdleSamples(), "s='d'"), 'IdleSamples', "", $this->commentsIdleSamples, $this->commentsIdleSamples_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IdleAlertedAt':
        $this->fieldsRo['RegimeEpisode']['IdleAlertedAt']['html'] = stdFieldRow(_("Idle alerted at"), div( htmlspecialchars((string)($dataObj->getIdleAlertedAt()), ENT_QUOTES), 'IdleAlertedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdleAlertedAt', $dataObj->getIdleAlertedAt(), "s='d'"), 'IdleAlertedAt', "", $this->commentsIdleAlertedAt, $this->commentsIdleAlertedAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }

    /**
     * Query for RegimeEpisode_IdFleetSlot selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxRegimeEpisode_IdFleetSlot(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxRegimeEpisode_IdFleetSlot')
        && !method_exists($gcSbHost, 'selectboxDataRegimeEpisode_IdFleetSlot');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('fleet_slot', 'RegimeEpisode_IdFleetSlot', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('FleetSlot'));
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = FleetSlotQuery::create();

    $gcSbSess = $_SESSION[_AUTH_VAR] ?? null;
    if (is_object($gcSbSess) && method_exists($gcSbSess, 'applyOwnerGroupScope')) {
        $gcSbSess->applyOwnerGroupScope($q, $gcSbSess->hasRights('FleetSlot', 'r'));
    }

    $gcSbHost = is_object($obj) ? $obj : $this;
    $ret = null;
    if(method_exists($gcSbHost, 'beginSelectboxRegimeEpisode_IdFleetSlot') and $array)
        $ret = $gcSbHost->beginSelectboxRegimeEpisode_IdFleetSlot($q, $dataObj, $data, $obj);
    if($ret !== false) {
            $q->addAsColumn('selDisplay', 'CONCAT_WS ( ", ", '.FleetSlotPeer::SYMBOL.', '.FleetSlotPeer::ALGO.' )');
            $q->select(['selDisplay', 'IdFleetSlot']);
            $q->orderBy('selDisplay', 'ASC');

    }
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataRegimeEpisode_IdFleetSlot')){
                $gcSbHost->selectboxDataRegimeEpisode_IdFleetSlot($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('fleet_slot', 'RegimeEpisode_IdFleetSlot', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('FleetSlot'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}

    /**
     * Query for RegimeEpisode_IdGridRun selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxRegimeEpisode_IdGridRun(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxRegimeEpisode_IdGridRun')
        && !method_exists($gcSbHost, 'selectboxDataRegimeEpisode_IdGridRun');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('grid_run', 'RegimeEpisode_IdGridRun', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = GridRunQuery::create();

    $gcSbSess = $_SESSION[_AUTH_VAR] ?? null;
    if (is_object($gcSbSess) && method_exists($gcSbSess, 'applyOwnerGroupScope')) {
        $gcSbSess->applyOwnerGroupScope($q, $gcSbSess->hasRights('GridRun', 'r'));
    }

    $gcSbHost = is_object($obj) ? $obj : $this;
    $ret = null;
    if(method_exists($gcSbHost, 'beginSelectboxRegimeEpisode_IdGridRun') and $array)
        $ret = $gcSbHost->beginSelectboxRegimeEpisode_IdGridRun($q, $dataObj, $data, $obj);
    if($ret !== false) {
            $q->addAsColumn('selDisplay', ''.GridRunPeer::LABEL.'');
            $q->select(['selDisplay', 'IdGridRun']);
            $q->orderBy('selDisplay', 'ASC');

    }
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataRegimeEpisode_IdGridRun')){
                $gcSbHost->selectboxDataRegimeEpisode_IdGridRun($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('grid_run', 'RegimeEpisode_IdGridRun', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
