<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'BotOrder' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class BotOrderForm extends BotOrder
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

        public $arrayIdGridRunOptions;
    public $commentsIdBotOrder;
    public $commentsIdBotOrder_css;
    public $commentsIdGridRun;
    public $commentsIdGridRun_css;
    public $commentsClientOrderId;
    public $commentsClientOrderId_css;
    public $commentsExchangeOrderId;
    public $commentsExchangeOrderId_css;
    public $commentsLevelIdx;
    public $commentsLevelIdx_css;
    public $commentsSide;
    public $commentsSide_css;
    public $commentsState;
    public $commentsState_css;
    public $commentsPrice;
    public $commentsPrice_css;
    public $commentsQty;
    public $commentsQty_css;
    public $commentsFilledQty;
    public $commentsFilledQty_css;
    public $commentsFeePaid;
    public $commentsFeePaid_css;
    public $commentsFeeAsset;
    public $commentsFeeAsset_css;
    public $commentsIsLegacy;
    public $commentsIsLegacy_css;
    public $commentsLegacyBuyPrice;
    public $commentsLegacyBuyPrice_css;
    public $commentsLegacyBuyFee;
    public $commentsLegacyBuyFee_css;
    public $commentsSimulated;
    public $commentsSimulated_css;
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
    public $BotOrder;


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
        $this->model_name = 'BotOrder';
        $this->virtualClassName = 'BotOrder';
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

        $q = new BotOrderQuery();
        $q = $this->setAclFilter($q);


        $q

                #required bot_order
                ->leftJoinWith('GridRun');
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['ClientOrderId']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['ClientOrderId'], $criteria);

            $q->filterByClientOrderId($value, $criteria);
        }
        if( isset($this->searchMs['Side']) ) {
            $criteria = \Criteria::IN;
            $value = array_values(array_intersect((array)$this->searchMs['Side'], BotOrderPeer::getValueSet(BotOrderPeer::SIDE)));

            if (!empty($value)) { $q->filterBySide($value, $criteria); }
        }
        if( isset($this->searchMs['State']) ) {
            $criteria = \Criteria::IN;
            $value = array_values(array_intersect((array)$this->searchMs['State'], BotOrderPeer::getValueSet(BotOrderPeer::STATE)));

            if (!empty($value)) { $q->filterByState($value, $criteria); }
        }
        if( isset($this->searchMs['Simulated']) ) {
            $criteria = \Criteria::EQUAL;
            $value = $this->searchMs['Simulated'];

            $q->filterBySimulated($value);
        }

        }else{
            ## standard list

        }

        $hasParent = json_decode((string) $IdParent);
        if (!empty($hasParent)) {
            $q->filterByIdGridRun($hasParent);
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
                                    . ' on BotOrder — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['BotOrder/'],
                                    $_SESSION['mem']['order']['BotOrder/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#BotOrderListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
                $trHead = (empty($this->IdParent) ? th(_("Grid Run label"), " th='sorted' c='GridRun.Label' title='"._('GridRun.Label')."' ") : '')
.th(_("Client order id"), " th='sorted' c='ClientOrderId' title='" . _('Client order id')."' ")
.th(_("Exchange id"), " th='sorted' c='ExchangeOrderId' title='" . _('Exchange id')."' ")
.th(_("Level"), " th='sorted' c='LevelIdx' title='" . _('Level')."' ")
.th(_("Side"), " th='sorted' c='Side' title='" . _('Side')."' ")
.th(_("State"), " th='sorted' c='State' title='" . _('State')."' ")
.th(_("Price"), " th='sorted' c='Price' title='" . _('Price')."' ")
.th(_("Qty"), " th='sorted' c='Qty' title='" . _('Qty')."' ")
.th(_("Filled qty"), " th='sorted' c='FilledQty' title='" . _('Filled qty')."' ")
.th(_("Fee paid"), " th='sorted' c='FeePaid' title='" . _('Fee paid')."' ")
.th(_("Fee asset"), " th='sorted' c='FeeAsset' title='" . _('Fee asset')."' ")
.th(_("Legacy exit"), " th='sorted' c='IsLegacy' title='" . _('Legacy exit')."' ")
.th(_("Simulated"), " th='sorted' c='Simulated' title='" . _('Simulated')."' ")
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
                .form(div(div(input('text', 'ClientOrderId', $this->searchMs['ClientOrderId'] ?? '', '  title="'._('Client order id').'" placeholder="'._('Search').' '._('Client order id').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('BotOrder')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('BotOrder')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(
                        div(_('Side'),''," class='va-fblock-lbl' ")
                        .div((function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("Buy"), '1'=>"Buy"],'1' => ['0'=>_("Sell"), '1'=>"Sell"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fchip' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })(),''," class='va-fchips va-fchips-multi' data-msfield='Side[]' ")
                        .div(selectboxCustomArray('Side[]', [ '0' => ['0'=>_("Buy"), '1'=>"Buy"],'1' => ['0'=>_("Sell"), '1'=>"Sell"], ], _('Side'), '  size="1" t="1"   multiple  ', $this->searchMs['Side'] ?? ''), '', 'class="multiple-select ac-search-item"  title="'._('Side').'"')
                    ,''," class='va-fblock va-fblock-multi' data-block='multi' ").div(
                        div(_('State'),''," class='va-fblock-lbl' ")
                        .div((function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("Intended"), '1'=>"Intended"],'1' => ['0'=>_("Vetoed"), '1'=>"Vetoed"],'2' => ['0'=>_("BUY_OPEN"), '1'=>"BUY_OPEN"],'3' => ['0'=>_("SELL_OPEN"), '1'=>"SELL_OPEN"],'4' => ['0'=>_("Filled"), '1'=>"Filled"],'5' => ['0'=>_("PartFilled"), '1'=>"PartFilled"],'6' => ['0'=>_("Canceled"), '1'=>"Canceled"],'7' => ['0'=>_("Rejected"), '1'=>"Rejected"],'8' => ['0'=>_("Lost"), '1'=>"Lost"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fchip' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })(),''," class='va-fchips va-fchips-multi' data-msfield='State[]' ")
                        .div(selectboxCustomArray('State[]', [ '0' => ['0'=>_("Intended"), '1'=>"Intended"],'1' => ['0'=>_("Vetoed"), '1'=>"Vetoed"],'2' => ['0'=>_("BUY_OPEN"), '1'=>"BUY_OPEN"],'3' => ['0'=>_("SELL_OPEN"), '1'=>"SELL_OPEN"],'4' => ['0'=>_("Filled"), '1'=>"Filled"],'5' => ['0'=>_("PartFilled"), '1'=>"PartFilled"],'6' => ['0'=>_("Canceled"), '1'=>"Canceled"],'7' => ['0'=>_("Rejected"), '1'=>"Rejected"],'8' => ['0'=>_("Lost"), '1'=>"Lost"], ], _('State'), '  size="1" t="1"   multiple  ', $this->searchMs['State'] ?? ''), '', 'class="multiple-select ac-search-item"  title="'._('State').'"')
                    ,''," class='va-fblock va-fblock-multi' data-block='multi' ").div(_('More filters'),''," class='va-fgroup-cap' ").div(
                        div(_('Simulated'),''," class='va-fblock-lbl' ")
                        .div(div(selectboxCustomArray('Simulated', [ '0' => ['0' => _('Yes'), '1' => 'yes'], '1' => ['0' => _('No'), '1' => 'no'] ], _('Simulated'), '  size="1" t="1"  ', $this->searchMs['Simulated'] ?? ''), '', 'class="ac-search-item" title="'._('Simulated').'"'),''," class='va-fselect' ")
                    ,''," class='va-fblock va-fblock-select' data-block='select' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('BotOrder')."' hidden ").div(
                           button(span(_("Search")),'id="msBotOrderBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msBotOrderBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsBotOrder' class='va-mob-searchform' data-entity='BotOrder'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('BotOrder', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addBotOrder' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'BotOrder';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdBotOrder' => NULL,
  'IdGridRun' => NULL,
  'ClientOrderId' => NULL,
  'ExchangeOrderId' => NULL,
  'LevelIdx' => NULL,
  'Side' => NULL,
  'State' => NULL,
  'Price' => NULL,
  'Qty' => NULL,
  'FilledQty' => NULL,
  'FeePaid' => NULL,
  'FeeAsset' => NULL,
  'IsLegacy' => NULL,
  'LegacyBuyPrice' => NULL,
  'LegacyBuyFee' => NULL,
  'Simulated' => NULL,
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
            $this->isChild = 'BotOrder';
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
        $gcListKey = 'BotOrder/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'BotOrder/child';
            if (($_SESSION['mem']['ip'][$gcListKey] ?? null) !== (string) $IdParent) {
                $_SESSION['mem']['ip'][$gcListKey] = (string) $IdParent;
                unset($_SESSION['mem']['page'][$gcListKey]);
            }
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', $gcListKey);

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';

            $_gcSel = (array)($this->searchMs['Side'] ?? []);
            $_gcChips = button(span(_('All')), " type='button' class='va-mob-chip".(empty($_gcSel)?' active':'')."' data-msfield='Side' data-msval='' ");
            foreach( [ '0' => ['0'=>_("Buy"), '1'=>"Buy"],'1' => ['0'=>_("Sell"), '1'=>"Sell"], ] as $_gcCo ) {
                $_gcCv = is_array($_gcCo) ? (string)$_gcCo[1] : (string)$_gcCo;
                $_gcCl = is_array($_gcCo) ? (string)$_gcCo[0] : (string)$_gcCo;
                $_gcChips .= button(span(htmlspecialchars($_gcCl)), " type='button' class='va-mob-chip".(in_array($_gcCv, $_gcSel, true)?' active':'')."' data-msfield='Side' data-msval='".htmlspecialchars($_gcCv)."' ");
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
            $gcGroupCol = 'GridRun.Label';
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
                if($_SESSION[_AUTH_VAR]->hasRights('BotOrder', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteBotOrder' ");
                }
            }

            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                # hoist the row PK encodings once — reused by the mobile + desktop row wrappers below
                $__pkJsonEsc = htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES);
                $__pkEsc = htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES);
                $this->listActionCell = '';



        $altValue['GridRun_Label'] = "";
        if($data->getGridRun()){
            $altValue['GridRun_Label'] = $data->getGridRun()->getLabel();
        }


                $actionInner = '' . $this->canDelete . $this->listActionCell;
                $actionCell =  td($actionInner, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . (empty($this->IdParent) ? (span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." ", "   i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editBotOrder'")) : (span(htmlspecialchars((string)((($altValue['ClientOrderId'] !== null ) ? $altValue['ClientOrderId'] : $data->getClientOrderId())))." ", "   i='" . $__pkJsonEsc . "' c='ClientOrderId' class=''  j='editBotOrder'"))) ,''," class='name' ")
   . div(''  . (empty($this->IdParent) ? (''  . span(htmlspecialchars((string)((($altValue['ClientOrderId'] !== null ) ? $altValue['ClientOrderId'] : $data->getClientOrderId())))." ", "   i='" . $__pkJsonEsc . "' c='ClientOrderId' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['ExchangeOrderId'] !== null ) ? $altValue['ExchangeOrderId'] : $data->getExchangeOrderId())))." ", "   i='" . $__pkJsonEsc . "' c='ExchangeOrderId' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." ", "   i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Side'] !== null ) ? $altValue['Side'] : isntPo($data->getSide()))))." ", "   i='" . $__pkJsonEsc . "' c='Side' class='center'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['State'] !== null ) ? $altValue['State'] : isntPo($data->getState()))))." ", "   i='" . $__pkJsonEsc . "' c='State' class='center'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Price'] !== null ) ? $altValue['Price'] : str_replace(',', '.', (string)($data->getPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Price' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FilledQty'] !== null ) ? $altValue['FilledQty'] : str_replace(',', '.', (string)($data->getFilledQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FilledQty' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FeePaid'] !== null ) ? $altValue['FeePaid'] : str_replace(',', '.', (string)($data->getFeePaid() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FeePaid' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FeeAsset'] !== null ) ? $altValue['FeeAsset'] : $data->getFeeAsset())))." ", "   i='" . $__pkJsonEsc . "' c='FeeAsset' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['IsLegacy'] !== null ) ? $altValue['IsLegacy'] : ($data->getIsLegacy() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='IsLegacy' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editBotOrder'")) : (''  . span(htmlspecialchars((string)((($altValue['ExchangeOrderId'] !== null ) ? $altValue['ExchangeOrderId'] : $data->getExchangeOrderId())))." ", "   i='" . $__pkJsonEsc . "' c='ExchangeOrderId' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." ", "   i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Side'] !== null ) ? $altValue['Side'] : isntPo($data->getSide()))))." ", "   i='" . $__pkJsonEsc . "' c='Side' class='center'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['State'] !== null ) ? $altValue['State'] : isntPo($data->getState()))))." ", "   i='" . $__pkJsonEsc . "' c='State' class='center'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Price'] !== null ) ? $altValue['Price'] : str_replace(',', '.', (string)($data->getPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Price' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FilledQty'] !== null ) ? $altValue['FilledQty'] : str_replace(',', '.', (string)($data->getFilledQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FilledQty' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FeePaid'] !== null ) ? $altValue['FeePaid'] : str_replace(',', '.', (string)($data->getFeePaid() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FeePaid' class='right'  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['FeeAsset'] !== null ) ? $altValue['FeeAsset'] : $data->getFeeAsset())))." ", "   i='" . $__pkJsonEsc . "' c='FeeAsset' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['IsLegacy'] !== null ) ? $altValue['IsLegacy'] : ($data->getIsLegacy() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='IsLegacy' class=''  j='editBotOrder'") . span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editBotOrder'"))) . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='BotOrderRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                    (empty($this->IdParent) ?
                td(span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." "), "  i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editBotOrder'") : '') .
                td(span(htmlspecialchars((string)((($altValue['ClientOrderId'] !== null ) ? $altValue['ClientOrderId'] : $data->getClientOrderId())))." "), "  i='" . $__pkJsonEsc . "' c='ClientOrderId' class=''  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['ExchangeOrderId'] !== null ) ? $altValue['ExchangeOrderId'] : $data->getExchangeOrderId())))." "), "  i='" . $__pkJsonEsc . "' c='ExchangeOrderId' class=''  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." "), "  i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['Side'] !== null ) ? $altValue['Side'] : isntPo($data->getSide()))))." "), "  i='" . $__pkJsonEsc . "' c='Side' class='center'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['State'] !== null ) ? $altValue['State'] : isntPo($data->getState()))))." "), "  i='" . $__pkJsonEsc . "' c='State' class='center'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['Price'] !== null ) ? $altValue['Price'] : str_replace(',', '.', (string)($data->getPrice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Price' class='right'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['FilledQty'] !== null ) ? $altValue['FilledQty'] : str_replace(',', '.', (string)($data->getFilledQty() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FilledQty' class='right'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['FeePaid'] !== null ) ? $altValue['FeePaid'] : str_replace(',', '.', (string)($data->getFeePaid() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FeePaid' class='right'  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['FeeAsset'] !== null ) ? $altValue['FeeAsset'] : $data->getFeeAsset())))." "), "  i='" . $__pkJsonEsc . "' c='FeeAsset' class=''  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['IsLegacy'] !== null ) ? $altValue['IsLegacy'] : ($data->getIsLegacy() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='IsLegacy' class=''  j='editBotOrder'") .
                td(span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editBotOrder'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='BotOrderDtRow".$__pkEsc."'");

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label']));
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
            $tr .= input('hidden', 'rowCountBotOrder', $i);

        }

        $gcParentRef = '';
        $gcParentPk = json_decode((string) $IdParent);
        if (!empty($gcParentPk) && $_SESSION[_AUTH_VAR]->hasRights('GridRun', 'r')) {
            $gcParentObj = $_SESSION[_AUTH_VAR]->loadPkScoped(GridRunQuery::class, $gcParentPk, 'GridRun', 'r');
            if ($gcParentObj) {
                $gcParentRef = trim((string) ($gcParentObj->getLabel() ?? ''));
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
                .div($controlsContent,'BotOrderControlsList', "class='custom-controls'")
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
                        .span(_('Order'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('BotOrder', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
                    ,''," class='va-mob-row1' ")
                    .($gcParentRef !== '' ? div(span(_('Grid Run'), " class='va-mob-parent-type' ") . span(htmlspecialchars($gcParentRef), " class='va-mob-parent-name' "), '', " class='va-mob-parent' ") : '')
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
                        .div("".$gcSortSheetClear . (empty($this->IdParent) ? button(_("Grid Run label"), " type='button' th='sorted' c='GridRun.Label' class='va-mob-sortrow' ") : '') . button(_("Client order id"), " type='button' th='sorted' c='ClientOrderId' class='va-mob-sortrow' ") . button(_("Exchange id"), " type='button' th='sorted' c='ExchangeOrderId' class='va-mob-sortrow' ") . button(_("Level"), " type='button' th='sorted' c='LevelIdx' class='va-mob-sortrow' ") . button(_("Side"), " type='button' th='sorted' c='Side' class='va-mob-sortrow' ") . button(_("State"), " type='button' th='sorted' c='State' class='va-mob-sortrow' ") . button(_("Price"), " type='button' th='sorted' c='Price' class='va-mob-sortrow' ") . button(_("Qty"), " type='button' th='sorted' c='Qty' class='va-mob-sortrow' ") . button(_("Filled qty"), " type='button' th='sorted' c='FilledQty' class='va-mob-sortrow' ") . button(_("Fee paid"), " type='button' th='sorted' c='FeePaid' class='va-mob-sortrow' ") . button(_("Fee asset"), " type='button' th='sorted' c='FeeAsset' class='va-mob-sortrow' ") . button(_("Legacy exit"), " type='button' th='sorted' c='IsLegacy' class='va-mob-sortrow' ") . button(_("Simulated"), " type='button' th='sorted' c='Simulated' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='BotOrderTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'BotOrderListForm', " class='va-mob proto-app' data-model='BotOrder' data-table='BotOrder' data-gc-db='bot_order' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='BotOrder' data-parent='GridRun'" : ''));





        $return['onReadyJs'] =
            $HelpDivJs

            ."





        (function(){
            function gcRenderSummary(cards){
                if (!cards || !cards.length) return;
                var host = document.querySelector('.va-mob.proto-app') || document.querySelector('.ac-list') || document.querySelector('table');
                if (!host || !host.parentNode) return;
                var wrap = document.getElementById('gcSummaryCards');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.id = 'gcSummaryCards';
                    wrap.style.cssText = 'display:flex;gap:12px;flex-wrap:wrap;margin:0 0 14px;';
                    host.parentNode.insertBefore(wrap, host);
                }
                wrap.innerHTML = '';
                cards.forEach(function(c){
                    var v = c.value;
                    if (c.format === 'money'   && window.gcListFmt) { v = window.gcListFmt.money(v); }
                    else if (c.format === 'percent' && window.gcListFmt) { v = window.gcListFmt.percent(v); }
                    if (v === null || v === undefined) { v = c.value; }
                    var card = document.createElement('div');
                    card.style.cssText = 'flex:1;min-width:120px;background:#f0f6ff;border:1px solid #d6e6ff;border-radius:8px;padding:10px 14px;';
                    var lbl = document.createElement('div');
                    lbl.style.cssText = 'font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#5b7a9b;';
                    lbl.textContent = c.label;
                    var val = document.createElement('div');
                    val.style.cssText = 'font-size:1.35em;font-weight:700;color:#1f4e79;line-height:1.2;';
                    val.textContent = String(v);
                    card.appendChild(lbl); card.appendChild(val);
                    wrap.appendChild(card);
                });
            }
            /* GET on purpose: a read. The middleware demands the WRITE right for
               any unmapped POST, which hid the cards from read-only users;
               summarycards is not a mutating action, so a GET passes on 'r'
               (the handler re-checks it and scopes the aggregates). */
            fetch(_SITE_URL + 'BotOrder/summarycards', {
                method: 'GET', credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r){ return r.json(); }).then(function(res){
                if (res && res.status === 'success') { gcRenderSummary(res.data); }
            }).catch(function(){});
        })();



        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addBotOrderAutoc');
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
    public function setCreateDefaultsBotOrder(array $data): BotOrder
    {

        unset($data['IdBotOrder']);
        $e = new BotOrder();


        if(!$data['Side']){
            $data['Side'] = 'Buy';
        }
        if(!$data['State']){
            $data['State'] = 'Intended';
        }
        $data['IsLegacy'] = ($data['IsLegacy'] == '')?false:$data['IsLegacy'];
        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //varchar not required
        $e->setExchangeOrderId( ($data['ExchangeOrderId'] == '' ) ? null : $data['ExchangeOrderId']);
        //decimal not required
        $e->setFilledQty( ($data['FilledQty'] == '' ) ? null : $data['FilledQty']);
        //decimal not required
        $e->setFeePaid( ($data['FeePaid'] == '' ) ? null : $data['FeePaid']);
        //varchar not required
        $e->setFeeAsset( ($data['FeeAsset'] == '' ) ? null : $data['FeeAsset']);
        if(isset($data['IsLegacy'])){
            $e->setIsLegacy( (!isset($data['IsLegacy']) || $data['IsLegacy'] == '') ? false : $data['IsLegacy']);
        }
        //decimal not required
        $e->setLegacyBuyPrice( ($data['LegacyBuyPrice'] == '' ) ? null : $data['LegacyBuyPrice']);
        //decimal not required
        $e->setLegacyBuyFee( ($data['LegacyBuyFee'] == '' ) ? null : $data['LegacyBuyFee']);
        if(isset($data['Simulated'])){
            $e->setSimulated( (!isset($data['Simulated']) || $data['Simulated'] == '') ? false : $data['Simulated']);
        }
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsBotOrder(array $data): ?BotOrder
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(BotOrderQuery::class, json_decode($data['i']), 'BotOrder', 'w');
        if ($e === null) { return null; }


        if(!$data['Side']){
            $data['Side'] = 'Buy';
        }
        if(!$data['State']){
            $data['State'] = 'Intended';
        }
        $data['IsLegacy'] = ($data['IsLegacy'] == '')?false:$data['IsLegacy'];
        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['ExchangeOrderId'])){
            $e->setExchangeOrderId( ($data['ExchangeOrderId'] == '' ) ? null : $data['ExchangeOrderId']);
        }
        if(isset($data['FilledQty'])){
            $e->setFilledQty( ($data['FilledQty'] == '' ) ? null : $data['FilledQty']);
        }
        if(isset($data['FeePaid'])){
            $e->setFeePaid( ($data['FeePaid'] == '' ) ? null : $data['FeePaid']);
        }
        if(isset($data['FeeAsset'])){
            $e->setFeeAsset( ($data['FeeAsset'] == '' ) ? null : $data['FeeAsset']);
        }
        if(isset($data['LegacyBuyPrice'])){
            $e->setLegacyBuyPrice( ($data['LegacyBuyPrice'] == '' ) ? null : $data['LegacyBuyPrice']);
        }
        if(isset($data['LegacyBuyFee'])){
            $e->setLegacyBuyFee( ($data['LegacyBuyFee'] == '' ) ? null : $data['LegacyBuyFee']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of BotOrder
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

        $je = "BotOrderTable";

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

        if(!$IdParent && isset($data['ip']) && $data['ip'] !== '' && $data['ip'] != 0) {
            $IdParent = $data['ip'];
            $data['IdGridRun'] = $data['ip'];
        }

        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('BotOrder', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addBotOrderForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = BotOrderQuery::create()

                #required bot_order
                ->leftJoinWith('GridRun')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('BotOrder', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->BotOrder['isNew'] = 'yes';
            $dataObj = new BotOrder();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdGridRun";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->BotOrder['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getGridRun())?'':$dataObj->setGridRun( new GridRun() );





        $IsLegacyChecked = ($dataObj->getIsLegacy())?"checked='checked'":'';
                $IsLegacy = ($dataObj->getIsLegacy())?"true":"true";
                    $SimulatedChecked = ($dataObj->getSimulated())?"checked='checked'":'';
                $Simulated = ($dataObj->getSimulated())?"true":"true";



$this->fields['BotOrder']['IdGridRun']['html'] = stdFieldRow(_("Run"),
    input('text', 'IdGridRunAutoc', $dataObj->getGridRun()?->getLabel(), " title='".str_replace("'","", (string)($dataObj->getGridRun()?->getLabel()))."' v='ID_GRID_RUN' rid='IdGridRun' placeholder='"._('Run')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdGridRun&quot;,&quot;table&quot;:&quot;BotOrder&quot;,&quot;childTable&quot;:&quot;GridRun&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;GridRun&quot;,&quot;show&quot;:[&quot;Label&quot;],&quot;id&quot;:&quot;IdGridRun&quot;,&quot;filter&quot;:&quot;Label&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, '', ' ', 'no', 'v2');
$this->fields['BotOrder']['ClientOrderId']['html'] = stdFieldRow(_("Client order id"), input('text', 'ClientOrderId', htmlentities((string)($dataObj->getClientOrderId() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Client order id'))."' size='35'  v='CLIENT_ORDER_ID' s='d' class='req'  ")."", 'ClientOrderId', "", $this->commentsClientOrderId, $this->commentsClientOrderId_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['ExchangeOrderId']['html'] = stdFieldRow(_("Exchange id"), input('text', 'ExchangeOrderId', htmlentities((string)($dataObj->getExchangeOrderId() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Exchange id'))."' size='35'  v='EXCHANGE_ORDER_ID' s='d' class=''  ")."", 'ExchangeOrderId', "", $this->commentsExchangeOrderId, $this->commentsExchangeOrderId_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['LevelIdx']['html'] = stdFieldRow(_("Level"), input('number', 'LevelIdx', $dataObj->getLevelIdx(), " step='1' placeholder='".str_replace("'","&#39;",_('Level'))."' v='LEVEL_IDX' size='5' s='d' class='req'"), 'LevelIdx', "", $this->commentsLevelIdx, $this->commentsLevelIdx_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['Side']['html'] = stdFieldRow(_("Side"), selectboxCustomArray('Side', [ '0' => ['0'=>_("Buy"), '1'=>"Buy"],'1' => ['0'=>_("Sell"), '1'=>"Sell"], ], "", "s='d'  ", $dataObj->getSide(), '', false), 'Side', "", $this->commentsSide, $this->commentsSide_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['State']['html'] = stdFieldRow(_("State"), selectboxCustomArray('State', [ '0' => ['0'=>_("Intended"), '1'=>"Intended"],'1' => ['0'=>_("Vetoed"), '1'=>"Vetoed"],'2' => ['0'=>_("BUY_OPEN"), '1'=>"BUY_OPEN"],'3' => ['0'=>_("SELL_OPEN"), '1'=>"SELL_OPEN"],'4' => ['0'=>_("Filled"), '1'=>"Filled"],'5' => ['0'=>_("PartFilled"), '1'=>"PartFilled"],'6' => ['0'=>_("Canceled"), '1'=>"Canceled"],'7' => ['0'=>_("Rejected"), '1'=>"Rejected"],'8' => ['0'=>_("Lost"), '1'=>"Lost"], ], "", "s='d'  ", $dataObj->getState(), '', false), 'State', "", $this->commentsState, $this->commentsState_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['Price']['html'] = stdFieldRow(_("Price"), input('number', 'Price', $dataObj->getPrice(), "  placeholder='".str_replace("'","&#39;",_('Price'))."'  v='PRICE' size='10' s='d' class='req'"), 'Price', "", $this->commentsPrice, $this->commentsPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['Qty']['html'] = stdFieldRow(_("Qty"), input('number', 'Qty', $dataObj->getQty(), "  placeholder='".str_replace("'","&#39;",_('Qty'))."'  v='QTY' size='10' s='d' class='req'"), 'Qty', "", $this->commentsQty, $this->commentsQty_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['FilledQty']['html'] = stdFieldRow(_("Filled qty"), input('number', 'FilledQty', $dataObj->getFilledQty(), "  placeholder='".str_replace("'","&#39;",_('Filled qty'))."'  v='FILLED_QTY' size='10' s='d' class=''"), 'FilledQty', "", $this->commentsFilledQty, $this->commentsFilledQty_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['FeePaid']['html'] = stdFieldRow(_("Fee paid"), input('number', 'FeePaid', $dataObj->getFeePaid(), "  placeholder='".str_replace("'","&#39;",_('Fee paid'))."'  v='FEE_PAID' size='10' s='d' class=''"), 'FeePaid', "", $this->commentsFeePaid, $this->commentsFeePaid_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['FeeAsset']['html'] = stdFieldRow(_("Fee asset"), input('text', 'FeeAsset', htmlentities((string)($dataObj->getFeeAsset() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Fee asset'))."' size='15'  v='FEE_ASSET' s='d' class=''  ")."", 'FeeAsset', "", $this->commentsFeeAsset, $this->commentsFeeAsset_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['IsLegacy']['html'] = stdFieldRow(_("Legacy exit"), input('checkbox', 'IsLegacy', $IsLegacy, "$IsLegacyChecked  size='10' s='d'"), 'IsLegacy', "", $this->commentsIsLegacy, $this->commentsIsLegacy_css, ' half', ' ', 'yes', 'v2');
$this->fields['BotOrder']['LegacyBuyPrice']['html'] = stdFieldRow(_("Legacy buy price (daemon-managed)"), input('number', 'LegacyBuyPrice', $dataObj->getLegacyBuyPrice(), "  placeholder='".str_replace("'","&#39;",_('Legacy buy price (daemon-managed)'))."'  v='LEGACY_BUY_PRICE' size='10' s='d' class=''"), 'LegacyBuyPrice', "", $this->commentsLegacyBuyPrice, $this->commentsLegacyBuyPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['LegacyBuyFee']['html'] = stdFieldRow(_("Legacy buy fee (daemon-managed)"), input('number', 'LegacyBuyFee', $dataObj->getLegacyBuyFee(), "  placeholder='".str_replace("'","&#39;",_('Legacy buy fee (daemon-managed)'))."'  v='LEGACY_BUY_FEE' size='10' s='d' class=''"), 'LegacyBuyFee', "", $this->commentsLegacyBuyFee, $this->commentsLegacyBuyFee_css, ' half', ' ', 'no', 'v2');
$this->fields['BotOrder']['Simulated']['html'] = stdFieldRow(_("Simulated"), input('checkbox', 'Simulated', $Simulated, "$SimulatedChecked  size='10' s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, ' half', ' ', 'yes', 'v2');


        $this->lockFormField(array(0=>'ClientOrderId',1=>'ExchangeOrderId',2=>'FilledQty',3=>'FeePaid',4=>'Simulated',5=>'LegacyBuyPrice',6=>'LegacyBuyFee',7=>'IdCreation',8=>'IdModification',9=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['BotOrder']['IdGridRun']['html'] = input('hidden', 'IdGridRun', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('BotOrder','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('BotOrder','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveBotOrder', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedBotOrder','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdBotOrder', $dataObj->getIdBotOrder(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('BotOrder: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Order'), _SITE_URL.'BotOrder', "class='nav-btn'")
                        .div(
                            span(_('Order'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'BotOrder', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['BotOrder']['IdGridRun']['html']
.$this->fields['BotOrder']['ClientOrderId']['html']
.$this->fields['BotOrder']['ExchangeOrderId']['html']
.$this->fields['BotOrder']['LevelIdx']['html']
.$this->fields['BotOrder']['Side']['html']
.$this->fields['BotOrder']['State']['html']
.$this->fields['BotOrder']['Price']['html']
.$this->fields['BotOrder']['Qty']['html']
.$this->fields['BotOrder']['FilledQty']['html']
.$this->fields['BotOrder']['FeePaid']['html']
.$this->fields['BotOrder']['FeeAsset']['html']
.$this->fields['BotOrder']['IsLegacy']['html']
.$this->fields['BotOrder']['LegacyBuyPrice']['html']
.$this->fields['BotOrder']['LegacyBuyFee']['html']
.$this->fields['BotOrder']['Simulated']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntBotOrder", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formBotOrder' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['BotOrder']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['BotOrder'][$field]['html'] = $this->fieldsRo['BotOrder'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'IdGridRun':
        $this->fieldsRo['BotOrder']['IdGridRun']['html'] = stdFieldRow(_("Run"), div( htmlspecialchars((string)(($dataObj->getGridRun())?($dataObj->getGridRun()->getLabel()):''), ENT_QUOTES), 'IdGridRun_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'ClientOrderId':
        $this->fieldsRo['BotOrder']['ClientOrderId']['html'] = stdFieldRow(_("Client order id"), div( htmlspecialchars((string)($dataObj->getClientOrderId()), ENT_QUOTES), 'ClientOrderId_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ClientOrderId', $dataObj->getClientOrderId(), "s='d'"), 'ClientOrderId', "", $this->commentsClientOrderId, $this->commentsClientOrderId_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ExchangeOrderId':
        $this->fieldsRo['BotOrder']['ExchangeOrderId']['html'] = stdFieldRow(_("Exchange id"), div( htmlspecialchars((string)($dataObj->getExchangeOrderId()), ENT_QUOTES), 'ExchangeOrderId_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ExchangeOrderId', $dataObj->getExchangeOrderId(), "s='d'"), 'ExchangeOrderId', "", $this->commentsExchangeOrderId, $this->commentsExchangeOrderId_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LevelIdx':
        $this->fieldsRo['BotOrder']['LevelIdx']['html'] = stdFieldRow(_("Level"), div( htmlspecialchars((string)($dataObj->getLevelIdx()), ENT_QUOTES), 'LevelIdx_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LevelIdx', $dataObj->getLevelIdx(), "s='d'"), 'LevelIdx', "", $this->commentsLevelIdx, $this->commentsLevelIdx_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Side':
        $this->fieldsRo['BotOrder']['Side']['html'] = stdFieldRow(_("Side"), div( htmlspecialchars((string)($dataObj->getSide()), ENT_QUOTES), 'Side_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Side', $dataObj->getSide(), "s='d'"), 'Side', "", $this->commentsSide, $this->commentsSide_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'State':
        $this->fieldsRo['BotOrder']['State']['html'] = stdFieldRow(_("State"), div( htmlspecialchars((string)($dataObj->getState()), ENT_QUOTES), 'State_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'State', $dataObj->getState(), "s='d'"), 'State', "", $this->commentsState, $this->commentsState_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Price':
        $this->fieldsRo['BotOrder']['Price']['html'] = stdFieldRow(_("Price"), div( htmlspecialchars((string)($dataObj->getPrice()), ENT_QUOTES), 'Price_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Price', $dataObj->getPrice(), "s='d'"), 'Price', "", $this->commentsPrice, $this->commentsPrice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Qty':
        $this->fieldsRo['BotOrder']['Qty']['html'] = stdFieldRow(_("Qty"), div( htmlspecialchars((string)($dataObj->getQty()), ENT_QUOTES), 'Qty_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Qty', $dataObj->getQty(), "s='d'"), 'Qty', "", $this->commentsQty, $this->commentsQty_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'FilledQty':
        $this->fieldsRo['BotOrder']['FilledQty']['html'] = stdFieldRow(_("Filled qty"), div( htmlspecialchars((string)($dataObj->getFilledQty()), ENT_QUOTES), 'FilledQty_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FilledQty', $dataObj->getFilledQty(), "s='d'"), 'FilledQty', "", $this->commentsFilledQty, $this->commentsFilledQty_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'FeePaid':
        $this->fieldsRo['BotOrder']['FeePaid']['html'] = stdFieldRow(_("Fee paid"), div( htmlspecialchars((string)($dataObj->getFeePaid()), ENT_QUOTES), 'FeePaid_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FeePaid', $dataObj->getFeePaid(), "s='d'"), 'FeePaid', "", $this->commentsFeePaid, $this->commentsFeePaid_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'FeeAsset':
        $this->fieldsRo['BotOrder']['FeeAsset']['html'] = stdFieldRow(_("Fee asset"), div( htmlspecialchars((string)($dataObj->getFeeAsset()), ENT_QUOTES), 'FeeAsset_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FeeAsset', $dataObj->getFeeAsset(), "s='d'"), 'FeeAsset', "", $this->commentsFeeAsset, $this->commentsFeeAsset_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IsLegacy':
        $this->fieldsRo['BotOrder']['IsLegacy']['html'] = stdFieldRow(_("Legacy exit"), div( htmlspecialchars((string)($dataObj->getIsLegacy()), ENT_QUOTES), 'IsLegacy_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IsLegacy', $dataObj->getIsLegacy(), "s='d'"), 'IsLegacy', "", $this->commentsIsLegacy, $this->commentsIsLegacy_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LegacyBuyPrice':
        $this->fieldsRo['BotOrder']['LegacyBuyPrice']['html'] = stdFieldRow(_("Legacy buy price (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getLegacyBuyPrice()), ENT_QUOTES), 'LegacyBuyPrice_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LegacyBuyPrice', $dataObj->getLegacyBuyPrice(), "s='d'"), 'LegacyBuyPrice', "", $this->commentsLegacyBuyPrice, $this->commentsLegacyBuyPrice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LegacyBuyFee':
        $this->fieldsRo['BotOrder']['LegacyBuyFee']['html'] = stdFieldRow(_("Legacy buy fee (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getLegacyBuyFee()), ENT_QUOTES), 'LegacyBuyFee_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LegacyBuyFee', $dataObj->getLegacyBuyFee(), "s='d'"), 'LegacyBuyFee', "", $this->commentsLegacyBuyFee, $this->commentsLegacyBuyFee_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Simulated':
        $this->fieldsRo['BotOrder']['Simulated']['html'] = stdFieldRow(_("Simulated"), div( htmlspecialchars((string)($dataObj->getSimulated()), ENT_QUOTES), 'Simulated_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Simulated', $dataObj->getSimulated(), "s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }

    /**
     * Query for BotOrder_IdGridRun selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxBotOrder_IdGridRun(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxBotOrder_IdGridRun')
        && !method_exists($gcSbHost, 'selectboxDataBotOrder_IdGridRun');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('grid_run', 'BotOrder_IdGridRun', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
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
    if(method_exists($gcSbHost, 'beginSelectboxBotOrder_IdGridRun') and $array)
        $ret = $gcSbHost->beginSelectboxBotOrder_IdGridRun($q, $dataObj, $data, $obj);
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
            if(method_exists($gcSbHost, 'selectboxDataBotOrder_IdGridRun')){
                $gcSbHost->selectboxDataBotOrder_IdGridRun($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt );
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('grid_run', 'BotOrder_IdGridRun', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
