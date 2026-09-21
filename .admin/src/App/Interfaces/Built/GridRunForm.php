<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'GridRun' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class GridRunForm extends GridRun
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

        public $queryObjBotOrder;
    public $listActionCellBotOrder;
    public $arrayIdGridRunOptions;
    public $queryObjTradeCycle;
    public $listActionCellTradeCycle;
    public $queryObjBotEvent;
    public $listActionCellBotEvent;
    public $queryObjBotCommand;
    public $listActionCellBotCommand;
    public $queryObjGridRunAudit;
    public $listActionCellGridRunAudit;
    public $commentsIdGridRun;
    public $commentsIdGridRun_css;
    public $commentsLabel;
    public $commentsLabel_css;
    public $commentsSymbol;
    public $commentsSymbol_css;
    public $commentsStatus;
    public $commentsStatus_css;
    public $commentsKillSwitch;
    public $commentsKillSwitch_css;
    public $commentsProfile;
    public $commentsProfile_css;
    public $commentsAlgo;
    public $commentsAlgo_css;
    public $commentsSimulated;
    public $commentsSimulated_css;
    public $commentsPLow;
    public $commentsPLow_css;
    public $commentsPHigh;
    public $commentsPHigh_css;
    public $commentsNLevels;
    public $commentsNLevels_css;
    public $commentsSpacing;
    public $commentsSpacing_css;
    public $commentsAllocation;
    public $commentsAllocation_css;
    public $commentsBudgetQuote;
    public $commentsBudgetQuote_css;
    public $commentsDeployPct;
    public $commentsDeployPct_css;
    public $commentsAllocMode;
    public $commentsAllocMode_css;
    public $commentsFeePct;
    public $commentsFeePct_css;
    public $commentsMaxPositionQuote;
    public $commentsMaxPositionQuote_css;
    public $commentsMaxOrderQuote;
    public $commentsMaxOrderQuote_css;
    public $commentsDailyLossLimitQuote;
    public $commentsDailyLossLimitQuote_css;
    public $commentsMaxUnrealizedLossQuote;
    public $commentsMaxUnrealizedLossQuote_css;
    public $commentsSellAtLoss;
    public $commentsSellAtLoss_css;
    public $commentsSellWhenStarved;
    public $commentsSellWhenStarved_css;
    public $commentsBreakoutBufferPct;
    public $commentsBreakoutBufferPct_css;
    public $commentsBreakoutPolicy;
    public $commentsBreakoutPolicy_css;
    public $commentsMaxOpenOrders;
    public $commentsMaxOpenOrders_css;
    public $commentsMaxBuyLevelsBelow;
    public $commentsMaxBuyLevelsBelow_css;
    public $commentsTrendTf;
    public $commentsTrendTf_css;
    public $commentsDonchianPeriod;
    public $commentsDonchianPeriod_css;
    public $commentsTrendEmaFast;
    public $commentsTrendEmaFast_css;
    public $commentsTrendEmaSlow;
    public $commentsTrendEmaSlow_css;
    public $commentsAtrPeriod;
    public $commentsAtrPeriod_css;
    public $commentsAtrStopMult;
    public $commentsAtrStopMult_css;
    public $commentsAtrInitialMult;
    public $commentsAtrInitialMult_css;
    public $commentsTrendStopFloorPct;
    public $commentsTrendStopFloorPct_css;
    public $commentsTrendSignal;
    public $commentsTrendSignal_css;
    public $commentsReentryCooldown;
    public $commentsReentryCooldown_css;
    public $commentsEngineState;
    public $commentsEngineState_css;
    public $commentsLastTickAt;
    public $commentsLastTickAt_css;
    public $commentsLastPrice;
    public $commentsLastPrice_css;
    public $commentsBalBase;
    public $commentsBalBase_css;
    public $commentsBalQuote;
    public $commentsBalQuote_css;
    public $commentsSimBalBase;
    public $commentsSimBalBase_css;
    public $commentsSimBalQuote;
    public $commentsSimBalQuote_css;
    public $commentsRunUid;
    public $commentsRunUid_css;
    public $commentsAppliedGeometry;
    public $commentsAppliedGeometry_css;
    public $commentsLedgerResetAt;
    public $commentsLedgerResetAt_css;
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
    public $GridRun;
    public $BotOrder;
    public $hookBotOrderListTop;
    public $hookBotOrderListBottom;
    public $hookBotOrderTableFooter;
    public $hookListReadyJsBotOrder;
    public $hookListReadyJsFirstBotOrder;
    public $commentsIdBotOrder;
    public $commentsIdBotOrder_css;
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
    public $TradeCycle;
    public $hookTradeCycleListTop;
    public $hookTradeCycleListBottom;
    public $hookTradeCycleTableFooter;
    public $hookListReadyJsTradeCycle;
    public $hookListReadyJsFirstTradeCycle;
    public $commentsIdTradeCycle;
    public $commentsIdTradeCycle_css;
    public $commentsBuyPrice;
    public $commentsBuyPrice_css;
    public $commentsSellPrice;
    public $commentsSellPrice_css;
    public $commentsRealizedPnl;
    public $commentsRealizedPnl_css;
    public $commentsFeesTotal;
    public $commentsFeesTotal_css;
    public $BotEvent;
    public $hookBotEventListTop;
    public $hookBotEventListBottom;
    public $hookBotEventTableFooter;
    public $hookListReadyJsBotEvent;
    public $hookListReadyJsFirstBotEvent;
    public $commentsIdBotEvent;
    public $commentsIdBotEvent_css;
    public $commentsLevel;
    public $commentsLevel_css;
    public $commentsKind;
    public $commentsKind_css;
    public $commentsMessage;
    public $commentsMessage_css;
    public $commentsPayload;
    public $commentsPayload_css;
    public $BotCommand;
    public $hookBotCommandListTop;
    public $hookBotCommandListBottom;
    public $hookBotCommandTableFooter;
    public $hookListReadyJsBotCommand;
    public $hookListReadyJsFirstBotCommand;
    public $commentsIdBotCommand;
    public $commentsIdBotCommand_css;
    public $commentsCommand;
    public $commentsCommand_css;
    public $commentsCmdStatus;
    public $commentsCmdStatus_css;
    public $commentsNote;
    public $commentsNote_css;
    public $GridRunAudit;
    public $hookGridRunAuditListTop;
    public $hookGridRunAuditListBottom;
    public $hookGridRunAuditTableFooter;
    public $hookListReadyJsGridRunAudit;
    public $hookListReadyJsFirstGridRunAudit;
    public $commentsIdGridRunAudit;
    public $commentsIdGridRunAudit_css;
    public $commentsField;
    public $commentsField_css;
    public $commentsValueFrom;
    public $commentsValueFrom_css;
    public $commentsValueTo;
    public $commentsValueTo_css;
    public $commentsActor;
    public $commentsActor_css;
    public $commentsSource;
    public $commentsSource_css;


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
        $this->model_name = 'GridRun';
        $this->virtualClassName = 'GridRun';
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

        $q = new GridRunQuery();
        $q = $this->setAclFilter($q);


        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form


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
                                    . ' on GridRun — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['GridRun/'],
                                    $_SESSION['mem']['order']['GridRun/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#GridRunListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
                $trHead = th(_("Label"), " th='sorted' c='Label' title='" . _('Label')."' ")
.th(_("Symbol"), " th='sorted' c='Symbol' title='" . _('Symbol')."' ")
.th(_("Status"), " th='sorted' c='Status' title='" . _('Status')."' ")
.th(_("Kill switch"), " th='sorted' c='KillSwitch' title='" . _('Kill switch')."' ")
.th(_("Risk profile"), " th='sorted' c='Profile' title='" . _('Risk profile')."' ")
.th(_("Algorithm"), " th='sorted' c='Algo' title='" . _('Algorithm')."' ")
.th(_("Simulated"), " th='sorted' c='Simulated' title='" . _('Simulated')."' ")
.th(_("Range low"), " th='sorted' c='PLow' title='" . _('Range low')."' ")
.th(_("Range high"), " th='sorted' c='PHigh' title='" . _('Range high')."' ")
.th(_("Grid lines"), " th='sorted' c='NLevels' title='" . _('Grid lines')."' ")
.th(_("Spacing"), " th='sorted' c='Spacing' title='" . _('Spacing')."' ")
.th(_("Allocation"), " th='sorted' c='Allocation' title='" . _('Allocation')."' ")
.th(_("Budget (USDT)"), " th='sorted' c='BudgetQuote' title='" . _('Budget (USDT)')."' ")
.th(_("Deployed budget %"), " th='sorted' c='DeployPct' title='" . _('Deployed budget %')."' ")
.th(_("Allocation mode"), " th='sorted' c='AllocMode' title='" . _('Allocation mode')."' ")
.th(_("Fee per side"), " th='sorted' c='FeePct' title='" . _('Fee per side')."' ")
.th(_("Max position (USDT)"), " th='sorted' c='MaxPositionQuote' title='" . _('Max position (USDT)')."' ")
.th(_("Max per-order (USDT)"), " th='sorted' c='MaxOrderQuote' title='" . _('Max per-order (USDT)')."' ")
.th(_("Daily loss limit"), " th='sorted' c='DailyLossLimitQuote' title='" . _('Daily loss limit')."' ")
.th(_("Max unrealized loss"), " th='sorted' c='MaxUnrealizedLossQuote' title='" . _('Max unrealized loss')."' ")
.th(_("Sell at loss"), " th='sorted' c='SellAtLoss' title='" . _('Sell at loss')."' ")
.th(_("Sell at loss when starved"), " th='sorted' c='SellWhenStarved' title='" . _('Sell at loss when starved')."' ")
.th(_("On breakout"), " th='sorted' c='BreakoutPolicy' title='" . _('On breakout')."' ")
.th(_("Active buy levels below price (0 = all)"), " th='sorted' c='MaxBuyLevelsBelow' title='" . _('Active buy levels below price (0 = all)')."' ")
.th(_("Signal timeframe"), " th='sorted' c='TrendTf' title='" . _('Signal timeframe')."' ")
.th(_("Breakout period"), " th='sorted' c='DonchianPeriod' title='" . _('Breakout period')."' ")
.th(_("Trend EMA fast"), " th='sorted' c='TrendEmaFast' title='" . _('Trend EMA fast')."' ")
.th(_("Trend EMA slow"), " th='sorted' c='TrendEmaSlow' title='" . _('Trend EMA slow')."' ")
.th(_("ATR period"), " th='sorted' c='AtrPeriod' title='" . _('ATR period')."' ")
.th(_("Trail stop x ATR"), " th='sorted' c='AtrStopMult' title='" . _('Trail stop x ATR')."' ")
.th(_("Initial stop x ATR"), " th='sorted' c='AtrInitialMult' title='" . _('Initial stop x ATR')."' ")
.th(_("Trail stop floor (fraction of HWM)"), " th='sorted' c='TrendStopFloorPct' title='" . _('Trail stop floor (fraction of HWM)')."' ")
.th(_("Trend entry signal"), " th='sorted' c='TrendSignal' title='" . _('Trend entry signal')."' ")
.th(_("Re-entry cooldown (bars)"), " th='sorted' c='ReentryCooldown' title='" . _('Re-entry cooldown (bars)')."' ")
.th(_("Last price"), " th='sorted' c='LastPrice' title='" . _('Last price')."' ")
.th(_("Wallet base"), " th='sorted' c='BalBase' title='" . _('Wallet base')."' ")
.th(_("Wallet quote"), " th='sorted' c='BalQuote' title='" . _('Wallet quote')."' ")
.th(_("Applied geometry (daemon-managed)"), " th='sorted' c='AppliedGeometry' title='" . _('Applied geometry (daemon-managed)')."' ")
.th(_("Ledger rebased at (daemon-managed)"), " th='sorted' c='LedgerResetAt' title='" . _('Ledger rebased at (daemon-managed)')."' ")
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
                if($_SESSION[_AUTH_VAR]->hasRights('GridRun', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addGridRun' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'GridRun';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdGridRun' => NULL,
  'Label' => NULL,
  'Symbol' => NULL,
  'Status' => NULL,
  'KillSwitch' => NULL,
  'Profile' => NULL,
  'Algo' => NULL,
  'Simulated' => NULL,
  'PLow' => NULL,
  'PHigh' => NULL,
  'NLevels' => NULL,
  'Spacing' => NULL,
  'Allocation' => NULL,
  'BudgetQuote' => NULL,
  'DeployPct' => NULL,
  'AllocMode' => NULL,
  'FeePct' => NULL,
  'MaxPositionQuote' => NULL,
  'MaxOrderQuote' => NULL,
  'DailyLossLimitQuote' => NULL,
  'MaxUnrealizedLossQuote' => NULL,
  'SellAtLoss' => NULL,
  'SellWhenStarved' => NULL,
  'BreakoutBufferPct' => NULL,
  'BreakoutPolicy' => NULL,
  'MaxOpenOrders' => NULL,
  'MaxBuyLevelsBelow' => NULL,
  'TrendTf' => NULL,
  'DonchianPeriod' => NULL,
  'TrendEmaFast' => NULL,
  'TrendEmaSlow' => NULL,
  'AtrPeriod' => NULL,
  'AtrStopMult' => NULL,
  'AtrInitialMult' => NULL,
  'TrendStopFloorPct' => NULL,
  'TrendSignal' => NULL,
  'ReentryCooldown' => NULL,
  'EngineState' => NULL,
  'LastTickAt' => NULL,
  'LastPrice' => NULL,
  'BalBase' => NULL,
  'BalQuote' => NULL,
  'SimBalBase' => NULL,
  'SimBalQuote' => NULL,
  'RunUid' => NULL,
  'AppliedGeometry' => NULL,
  'LedgerResetAt' => NULL,
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
            $this->isChild = 'GridRun';
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
        $gcListKey = 'GridRun/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'GridRun/child';
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
            $gcGroupCol = 'Label';
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
                if($_SESSION[_AUTH_VAR]->hasRights('GridRun', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteGridRun' ");
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
   div('' . span(htmlspecialchars((string)((($altValue['Label'] !== null ) ? $altValue['Label'] : $data->getLabel())))." ", "   i='" . $__pkJsonEsc . "' c='Label' class=''  j='editGridRun'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." ", "   i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Status'] !== null ) ? $altValue['Status'] : isntPo($data->getStatus()))))." ", "   i='" . $__pkJsonEsc . "' c='Status' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['KillSwitch'] !== null ) ? $altValue['KillSwitch'] : ($data->getKillSwitch() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='KillSwitch' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Profile'] !== null ) ? $altValue['Profile'] : isntPo($data->getProfile()))))." ", "   i='" . $__pkJsonEsc . "' c='Profile' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Algo'] !== null ) ? $altValue['Algo'] : isntPo($data->getAlgo()))))." ", "   i='" . $__pkJsonEsc . "' c='Algo' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['PLow'] !== null ) ? $altValue['PLow'] : str_replace(',', '.', (string)($data->getPLow() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PLow' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['PHigh'] !== null ) ? $altValue['PHigh'] : str_replace(',', '.', (string)($data->getPHigh() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PHigh' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['NLevels'] !== null ) ? $altValue['NLevels'] : $data->getNLevels())))." ", "   i='" . $__pkJsonEsc . "' c='NLevels' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Spacing'] !== null ) ? $altValue['Spacing'] : isntPo($data->getSpacing()))))." ", "   i='" . $__pkJsonEsc . "' c='Spacing' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['Allocation'] !== null ) ? $altValue['Allocation'] : isntPo($data->getAllocation()))))." ", "   i='" . $__pkJsonEsc . "' c='Allocation' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['BudgetQuote'] !== null ) ? $altValue['BudgetQuote'] : str_replace(',', '.', (string)($data->getBudgetQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='BudgetQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['DeployPct'] !== null ) ? $altValue['DeployPct'] : $data->getDeployPct())))." ", "   i='" . $__pkJsonEsc . "' c='DeployPct' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['AllocMode'] !== null ) ? $altValue['AllocMode'] : isntPo($data->getAllocMode()))))." ", "   i='" . $__pkJsonEsc . "' c='AllocMode' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['FeePct'] !== null ) ? $altValue['FeePct'] : str_replace(',', '.', (string)($data->getFeePct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FeePct' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['MaxPositionQuote'] !== null ) ? $altValue['MaxPositionQuote'] : str_replace(',', '.', (string)($data->getMaxPositionQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='MaxPositionQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['MaxOrderQuote'] !== null ) ? $altValue['MaxOrderQuote'] : str_replace(',', '.', (string)($data->getMaxOrderQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='MaxOrderQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['DailyLossLimitQuote'] !== null ) ? $altValue['DailyLossLimitQuote'] : str_replace(',', '.', (string)($data->getDailyLossLimitQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='DailyLossLimitQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['MaxUnrealizedLossQuote'] !== null ) ? $altValue['MaxUnrealizedLossQuote'] : str_replace(',', '.', (string)($data->getMaxUnrealizedLossQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='MaxUnrealizedLossQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['SellAtLoss'] !== null ) ? $altValue['SellAtLoss'] : ($data->getSellAtLoss() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='SellAtLoss' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['SellWhenStarved'] !== null ) ? $altValue['SellWhenStarved'] : ($data->getSellWhenStarved() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='SellWhenStarved' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['BreakoutPolicy'] !== null ) ? $altValue['BreakoutPolicy'] : isntPo($data->getBreakoutPolicy()))))." ", "   i='" . $__pkJsonEsc . "' c='BreakoutPolicy' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['MaxBuyLevelsBelow'] !== null ) ? $altValue['MaxBuyLevelsBelow'] : $data->getMaxBuyLevelsBelow())))." ", "   i='" . $__pkJsonEsc . "' c='MaxBuyLevelsBelow' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['TrendTf'] !== null ) ? $altValue['TrendTf'] : isntPo($data->getTrendTf()))))." ", "   i='" . $__pkJsonEsc . "' c='TrendTf' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['DonchianPeriod'] !== null ) ? $altValue['DonchianPeriod'] : $data->getDonchianPeriod())))." ", "   i='" . $__pkJsonEsc . "' c='DonchianPeriod' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['TrendEmaFast'] !== null ) ? $altValue['TrendEmaFast'] : $data->getTrendEmaFast())))." ", "   i='" . $__pkJsonEsc . "' c='TrendEmaFast' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['TrendEmaSlow'] !== null ) ? $altValue['TrendEmaSlow'] : $data->getTrendEmaSlow())))." ", "   i='" . $__pkJsonEsc . "' c='TrendEmaSlow' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['AtrPeriod'] !== null ) ? $altValue['AtrPeriod'] : $data->getAtrPeriod())))." ", "   i='" . $__pkJsonEsc . "' c='AtrPeriod' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['AtrStopMult'] !== null ) ? $altValue['AtrStopMult'] : str_replace(',', '.', (string)($data->getAtrStopMult() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='AtrStopMult' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['AtrInitialMult'] !== null ) ? $altValue['AtrInitialMult'] : str_replace(',', '.', (string)($data->getAtrInitialMult() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='AtrInitialMult' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['TrendStopFloorPct'] !== null ) ? $altValue['TrendStopFloorPct'] : str_replace(',', '.', (string)($data->getTrendStopFloorPct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='TrendStopFloorPct' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['TrendSignal'] !== null ) ? $altValue['TrendSignal'] : isntPo($data->getTrendSignal()))))." ", "   i='" . $__pkJsonEsc . "' c='TrendSignal' class='center'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['ReentryCooldown'] !== null ) ? $altValue['ReentryCooldown'] : $data->getReentryCooldown())))." ", "   i='" . $__pkJsonEsc . "' c='ReentryCooldown' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['LastPrice'] !== null ) ? $altValue['LastPrice'] : str_replace(',', '.', (string)($data->getLastPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='LastPrice' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['BalBase'] !== null ) ? $altValue['BalBase'] : str_replace(',', '.', (string)($data->getBalBase() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='BalBase' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['BalQuote'] !== null ) ? $altValue['BalQuote'] : str_replace(',', '.', (string)($data->getBalQuote() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='BalQuote' class='right'  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['AppliedGeometry'] !== null ) ? $altValue['AppliedGeometry'] : substr(strip_tags((string)($data->getAppliedGeometry() ?? '')), 0, 100))))." ", "   i='" . $__pkJsonEsc . "' c='AppliedGeometry' class=''  j='editGridRun'") . span(htmlspecialchars((string)((($altValue['LedgerResetAt'] !== null ) ? $altValue['LedgerResetAt'] : $data->getLedgerResetAt())))." ", "   i='" . $__pkJsonEsc . "' c='LedgerResetAt' class=''  j='editGridRun'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='GridRunRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Label'] !== null ) ? $altValue['Label'] : $data->getLabel())))." "), "  i='" . $__pkJsonEsc . "' c='Label' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." "), "  i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Status'] !== null ) ? $altValue['Status'] : isntPo($data->getStatus()))))." "), "  i='" . $__pkJsonEsc . "' c='Status' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['KillSwitch'] !== null ) ? $altValue['KillSwitch'] : ($data->getKillSwitch() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='KillSwitch' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Profile'] !== null ) ? $altValue['Profile'] : isntPo($data->getProfile()))))." "), "  i='" . $__pkJsonEsc . "' c='Profile' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Algo'] !== null ) ? $altValue['Algo'] : isntPo($data->getAlgo()))))." "), "  i='" . $__pkJsonEsc . "' c='Algo' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['PLow'] !== null ) ? $altValue['PLow'] : str_replace(',', '.', (string)($data->getPLow() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PLow' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['PHigh'] !== null ) ? $altValue['PHigh'] : str_replace(',', '.', (string)($data->getPHigh() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PHigh' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['NLevels'] !== null ) ? $altValue['NLevels'] : $data->getNLevels())))." "), "  i='" . $__pkJsonEsc . "' c='NLevels' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Spacing'] !== null ) ? $altValue['Spacing'] : isntPo($data->getSpacing()))))." "), "  i='" . $__pkJsonEsc . "' c='Spacing' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['Allocation'] !== null ) ? $altValue['Allocation'] : isntPo($data->getAllocation()))))." "), "  i='" . $__pkJsonEsc . "' c='Allocation' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['BudgetQuote'] !== null ) ? $altValue['BudgetQuote'] : str_replace(',', '.', (string)($data->getBudgetQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='BudgetQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['DeployPct'] !== null ) ? $altValue['DeployPct'] : $data->getDeployPct())))." "), "  i='" . $__pkJsonEsc . "' c='DeployPct' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['AllocMode'] !== null ) ? $altValue['AllocMode'] : isntPo($data->getAllocMode()))))." "), "  i='" . $__pkJsonEsc . "' c='AllocMode' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['FeePct'] !== null ) ? $altValue['FeePct'] : str_replace(',', '.', (string)($data->getFeePct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FeePct' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['MaxPositionQuote'] !== null ) ? $altValue['MaxPositionQuote'] : str_replace(',', '.', (string)($data->getMaxPositionQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='MaxPositionQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['MaxOrderQuote'] !== null ) ? $altValue['MaxOrderQuote'] : str_replace(',', '.', (string)($data->getMaxOrderQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='MaxOrderQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['DailyLossLimitQuote'] !== null ) ? $altValue['DailyLossLimitQuote'] : str_replace(',', '.', (string)($data->getDailyLossLimitQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='DailyLossLimitQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['MaxUnrealizedLossQuote'] !== null ) ? $altValue['MaxUnrealizedLossQuote'] : str_replace(',', '.', (string)($data->getMaxUnrealizedLossQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='MaxUnrealizedLossQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['SellAtLoss'] !== null ) ? $altValue['SellAtLoss'] : ($data->getSellAtLoss() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='SellAtLoss' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['SellWhenStarved'] !== null ) ? $altValue['SellWhenStarved'] : ($data->getSellWhenStarved() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='SellWhenStarved' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['BreakoutPolicy'] !== null ) ? $altValue['BreakoutPolicy'] : isntPo($data->getBreakoutPolicy()))))." "), "  i='" . $__pkJsonEsc . "' c='BreakoutPolicy' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['MaxBuyLevelsBelow'] !== null ) ? $altValue['MaxBuyLevelsBelow'] : $data->getMaxBuyLevelsBelow())))." "), "  i='" . $__pkJsonEsc . "' c='MaxBuyLevelsBelow' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['TrendTf'] !== null ) ? $altValue['TrendTf'] : isntPo($data->getTrendTf()))))." "), "  i='" . $__pkJsonEsc . "' c='TrendTf' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['DonchianPeriod'] !== null ) ? $altValue['DonchianPeriod'] : $data->getDonchianPeriod())))." "), "  i='" . $__pkJsonEsc . "' c='DonchianPeriod' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['TrendEmaFast'] !== null ) ? $altValue['TrendEmaFast'] : $data->getTrendEmaFast())))." "), "  i='" . $__pkJsonEsc . "' c='TrendEmaFast' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['TrendEmaSlow'] !== null ) ? $altValue['TrendEmaSlow'] : $data->getTrendEmaSlow())))." "), "  i='" . $__pkJsonEsc . "' c='TrendEmaSlow' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['AtrPeriod'] !== null ) ? $altValue['AtrPeriod'] : $data->getAtrPeriod())))." "), "  i='" . $__pkJsonEsc . "' c='AtrPeriod' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['AtrStopMult'] !== null ) ? $altValue['AtrStopMult'] : str_replace(',', '.', (string)($data->getAtrStopMult() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='AtrStopMult' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['AtrInitialMult'] !== null ) ? $altValue['AtrInitialMult'] : str_replace(',', '.', (string)($data->getAtrInitialMult() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='AtrInitialMult' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['TrendStopFloorPct'] !== null ) ? $altValue['TrendStopFloorPct'] : str_replace(',', '.', (string)($data->getTrendStopFloorPct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='TrendStopFloorPct' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['TrendSignal'] !== null ) ? $altValue['TrendSignal'] : isntPo($data->getTrendSignal()))))." "), "  i='" . $__pkJsonEsc . "' c='TrendSignal' class='center'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['ReentryCooldown'] !== null ) ? $altValue['ReentryCooldown'] : $data->getReentryCooldown())))." "), "  i='" . $__pkJsonEsc . "' c='ReentryCooldown' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['LastPrice'] !== null ) ? $altValue['LastPrice'] : str_replace(',', '.', (string)($data->getLastPrice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='LastPrice' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['BalBase'] !== null ) ? $altValue['BalBase'] : str_replace(',', '.', (string)($data->getBalBase() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='BalBase' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['BalQuote'] !== null ) ? $altValue['BalQuote'] : str_replace(',', '.', (string)($data->getBalQuote() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='BalQuote' class='right'  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['AppliedGeometry'] !== null ) ? $altValue['AppliedGeometry'] : substr(strip_tags((string)($data->getAppliedGeometry() ?? '')), 0, 100))))." "), "  i='" . $__pkJsonEsc . "' c='AppliedGeometry' class=''  j='editGridRun'") .
                td(span(htmlspecialchars((string)((($altValue['LedgerResetAt'] !== null ) ? $altValue['LedgerResetAt'] : $data->getLedgerResetAt())))." "), "  i='" . $__pkJsonEsc . "' c='LedgerResetAt' class=''  j='editGridRun'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='GridRunDtRow".$__pkEsc."'");

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Label'] !== null ) ? $altValue['Label'] : $data->getLabel()));
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
            $tr .= input('hidden', 'rowCountGridRun', $i);

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
                .div($controlsContent,'GridRunControlsList', "class='custom-controls'")
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
                        .span(_('Grid Run'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('GridRun', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Label"), " type='button' th='sorted' c='Label' class='va-mob-sortrow' ") . button(_("Symbol"), " type='button' th='sorted' c='Symbol' class='va-mob-sortrow' ") . button(_("Status"), " type='button' th='sorted' c='Status' class='va-mob-sortrow' ") . button(_("Kill switch"), " type='button' th='sorted' c='KillSwitch' class='va-mob-sortrow' ") . button(_("Risk profile"), " type='button' th='sorted' c='Profile' class='va-mob-sortrow' ") . button(_("Algorithm"), " type='button' th='sorted' c='Algo' class='va-mob-sortrow' ") . button(_("Simulated"), " type='button' th='sorted' c='Simulated' class='va-mob-sortrow' ") . button(_("Range low"), " type='button' th='sorted' c='PLow' class='va-mob-sortrow' ") . button(_("Range high"), " type='button' th='sorted' c='PHigh' class='va-mob-sortrow' ") . button(_("Grid lines"), " type='button' th='sorted' c='NLevels' class='va-mob-sortrow' ") . button(_("Spacing"), " type='button' th='sorted' c='Spacing' class='va-mob-sortrow' ") . button(_("Allocation"), " type='button' th='sorted' c='Allocation' class='va-mob-sortrow' ") . button(_("Budget (USDT)"), " type='button' th='sorted' c='BudgetQuote' class='va-mob-sortrow' ") . button(_("Deployed budget %"), " type='button' th='sorted' c='DeployPct' class='va-mob-sortrow' ") . button(_("Allocation mode"), " type='button' th='sorted' c='AllocMode' class='va-mob-sortrow' ") . button(_("Fee per side"), " type='button' th='sorted' c='FeePct' class='va-mob-sortrow' ") . button(_("Max position (USDT)"), " type='button' th='sorted' c='MaxPositionQuote' class='va-mob-sortrow' ") . button(_("Max per-order (USDT)"), " type='button' th='sorted' c='MaxOrderQuote' class='va-mob-sortrow' ") . button(_("Daily loss limit"), " type='button' th='sorted' c='DailyLossLimitQuote' class='va-mob-sortrow' ") . button(_("Max unrealized loss"), " type='button' th='sorted' c='MaxUnrealizedLossQuote' class='va-mob-sortrow' ") . button(_("Sell at loss"), " type='button' th='sorted' c='SellAtLoss' class='va-mob-sortrow' ") . button(_("Sell at loss when starved"), " type='button' th='sorted' c='SellWhenStarved' class='va-mob-sortrow' ") . button(_("On breakout"), " type='button' th='sorted' c='BreakoutPolicy' class='va-mob-sortrow' ") . button(_("Active buy levels below price (0 = all)"), " type='button' th='sorted' c='MaxBuyLevelsBelow' class='va-mob-sortrow' ") . button(_("Signal timeframe"), " type='button' th='sorted' c='TrendTf' class='va-mob-sortrow' ") . button(_("Breakout period"), " type='button' th='sorted' c='DonchianPeriod' class='va-mob-sortrow' ") . button(_("Trend EMA fast"), " type='button' th='sorted' c='TrendEmaFast' class='va-mob-sortrow' ") . button(_("Trend EMA slow"), " type='button' th='sorted' c='TrendEmaSlow' class='va-mob-sortrow' ") . button(_("ATR period"), " type='button' th='sorted' c='AtrPeriod' class='va-mob-sortrow' ") . button(_("Trail stop x ATR"), " type='button' th='sorted' c='AtrStopMult' class='va-mob-sortrow' ") . button(_("Initial stop x ATR"), " type='button' th='sorted' c='AtrInitialMult' class='va-mob-sortrow' ") . button(_("Trail stop floor (fraction of HWM)"), " type='button' th='sorted' c='TrendStopFloorPct' class='va-mob-sortrow' ") . button(_("Trend entry signal"), " type='button' th='sorted' c='TrendSignal' class='va-mob-sortrow' ") . button(_("Re-entry cooldown (bars)"), " type='button' th='sorted' c='ReentryCooldown' class='va-mob-sortrow' ") . button(_("Last price"), " type='button' th='sorted' c='LastPrice' class='va-mob-sortrow' ") . button(_("Wallet base"), " type='button' th='sorted' c='BalBase' class='va-mob-sortrow' ") . button(_("Wallet quote"), " type='button' th='sorted' c='BalQuote' class='va-mob-sortrow' ") . button(_("Applied geometry (daemon-managed)"), " type='button' th='sorted' c='AppliedGeometry' class='va-mob-sortrow' ") . button(_("Ledger rebased at (daemon-managed)"), " type='button' th='sorted' c='LedgerResetAt' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='GridRunTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'GridRunListForm', " class='va-mob proto-app' data-model='GridRun' data-table='GridRun' data-gc-db='grid_run' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addGridRunAutoc');
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
    public function setCreateDefaultsGridRun(array $data): GridRun
    {

        unset($data['IdGridRun']);
        $e = new GridRun();


        if(!$data['Status']){
            $data['Status'] = 'Draft';
        }
        $data['KillSwitch'] = ($data['KillSwitch'] == '')?false:$data['KillSwitch'];
        if(!$data['Profile']){
            $data['Profile'] = 'Balanced';
        }
        if(!$data['Algo']){
            $data['Algo'] = 'Grid';
        }
        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        if(!$data['Spacing']){
            $data['Spacing'] = 'Geometric';
        }
        if(!$data['Allocation']){
            $data['Allocation'] = 'EqualQuote';
        }
        if(!$data['AllocMode']){
            $data['AllocMode'] = 'Auto';
        }
        $data['SellAtLoss'] = ($data['SellAtLoss'] == '')?false:$data['SellAtLoss'];
        $data['SellWhenStarved'] = ($data['SellWhenStarved'] == '')?false:$data['SellWhenStarved'];
        if(!$data['BreakoutPolicy']){
            $data['BreakoutPolicy'] = 'HaltAndHold';
        }
        if(!$data['TrendTf']){
            $data['TrendTf'] = '1h';
        }
        if(!$data['TrendSignal']){
            $data['TrendSignal'] = 'Donchian';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        if(isset($data['KillSwitch'])){
            $e->setKillSwitch( (!isset($data['KillSwitch']) || $data['KillSwitch'] == '') ? false : $data['KillSwitch']);
        }
        if(isset($data['Simulated'])){
            $e->setSimulated( (!isset($data['Simulated']) || $data['Simulated'] == '') ? false : $data['Simulated']);
        }
        //decimal not required
        $e->setMaxUnrealizedLossQuote( ($data['MaxUnrealizedLossQuote'] == '' ) ? null : $data['MaxUnrealizedLossQuote']);
        if(isset($data['SellAtLoss'])){
            $e->setSellAtLoss( (!isset($data['SellAtLoss']) || $data['SellAtLoss'] == '') ? false : $data['SellAtLoss']);
        }
        if(isset($data['SellWhenStarved'])){
            $e->setSellWhenStarved( (!isset($data['SellWhenStarved']) || $data['SellWhenStarved'] == '') ? false : $data['SellWhenStarved']);
        }
        //decimal not required
        $e->setBreakoutBufferPct( ($data['BreakoutBufferPct'] == '' ) ? null : $data['BreakoutBufferPct']);
        //integer not required
        $e->setMaxBuyLevelsBelow( ($data['MaxBuyLevelsBelow'] == '' ) ? null : $data['MaxBuyLevelsBelow']);
        //decimal not required
        $e->setAtrStopMult( ($data['AtrStopMult'] == '' ) ? null : $data['AtrStopMult']);
        //decimal not required
        $e->setAtrInitialMult( ($data['AtrInitialMult'] == '' ) ? null : $data['AtrInitialMult']);
        //decimal not required
        $e->setTrendStopFloorPct( ($data['TrendStopFloorPct'] == '' ) ? null : $data['TrendStopFloorPct']);
        //integer not required
        $e->setReentryCooldown( ($data['ReentryCooldown'] == '' ) ? null : $data['ReentryCooldown']);
        //longvarchar not required
        $e->setEngineState( ($data['EngineState'] == '' ) ? null : $data['EngineState']);
        $e->setLastTickAt( ($data['LastTickAt'] == '' || $data['LastTickAt'] == 'null' || substr($data['LastTickAt'],0,10) == '-0001-11-30') ? null : $data['LastTickAt'] );
        //decimal not required
        $e->setLastPrice( ($data['LastPrice'] == '' ) ? null : $data['LastPrice']);
        //decimal not required
        $e->setBalBase( ($data['BalBase'] == '' ) ? null : $data['BalBase']);
        //decimal not required
        $e->setBalQuote( ($data['BalQuote'] == '' ) ? null : $data['BalQuote']);
        //decimal not required
        $e->setSimBalBase( ($data['SimBalBase'] == '' ) ? null : $data['SimBalBase']);
        //decimal not required
        $e->setSimBalQuote( ($data['SimBalQuote'] == '' ) ? null : $data['SimBalQuote']);
        //varchar not required
        $e->setRunUid( ($data['RunUid'] == '' ) ? null : $data['RunUid']);
        //longvarchar not required
        $e->setAppliedGeometry( ($data['AppliedGeometry'] == '' ) ? null : $data['AppliedGeometry']);
        $e->setLedgerResetAt( ($data['LedgerResetAt'] == '' || $data['LedgerResetAt'] == 'null' || substr($data['LedgerResetAt'],0,10) == '-0001-11-30') ? null : $data['LedgerResetAt'] );
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsGridRun(array $data): ?GridRun
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(GridRunQuery::class, json_decode($data['i']), 'GridRun', 'w');
        if ($e === null) { return null; }


        if(!$data['Status']){
            $data['Status'] = 'Draft';
        }
        $data['KillSwitch'] = ($data['KillSwitch'] == '')?false:$data['KillSwitch'];
        if(!$data['Profile']){
            $data['Profile'] = 'Balanced';
        }
        if(!$data['Algo']){
            $data['Algo'] = 'Grid';
        }
        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        if(!$data['Spacing']){
            $data['Spacing'] = 'Geometric';
        }
        if(!$data['Allocation']){
            $data['Allocation'] = 'EqualQuote';
        }
        if(!$data['AllocMode']){
            $data['AllocMode'] = 'Auto';
        }
        $data['SellAtLoss'] = ($data['SellAtLoss'] == '')?false:$data['SellAtLoss'];
        $data['SellWhenStarved'] = ($data['SellWhenStarved'] == '')?false:$data['SellWhenStarved'];
        if(!$data['BreakoutPolicy']){
            $data['BreakoutPolicy'] = 'HaltAndHold';
        }
        if(!$data['TrendTf']){
            $data['TrendTf'] = '1h';
        }
        if(!$data['TrendSignal']){
            $data['TrendSignal'] = 'Donchian';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['MaxUnrealizedLossQuote'])){
            $e->setMaxUnrealizedLossQuote( ($data['MaxUnrealizedLossQuote'] == '' ) ? null : $data['MaxUnrealizedLossQuote']);
        }
        if(isset($data['BreakoutBufferPct'])){
            $e->setBreakoutBufferPct( ($data['BreakoutBufferPct'] == '' ) ? null : $data['BreakoutBufferPct']);
        }
        if(isset($data['MaxBuyLevelsBelow'])){
            $e->setMaxBuyLevelsBelow( ($data['MaxBuyLevelsBelow'] == '' ) ? null : $data['MaxBuyLevelsBelow']);
        }
        if(isset($data['AtrStopMult'])){
            $e->setAtrStopMult( ($data['AtrStopMult'] == '' ) ? null : $data['AtrStopMult']);
        }
        if(isset($data['AtrInitialMult'])){
            $e->setAtrInitialMult( ($data['AtrInitialMult'] == '' ) ? null : $data['AtrInitialMult']);
        }
        if(isset($data['TrendStopFloorPct'])){
            $e->setTrendStopFloorPct( ($data['TrendStopFloorPct'] == '' ) ? null : $data['TrendStopFloorPct']);
        }
        if(isset($data['ReentryCooldown'])){
            $e->setReentryCooldown( ($data['ReentryCooldown'] == '' ) ? null : $data['ReentryCooldown']);
        }
        if(isset($data['EngineState'])){
            $e->setEngineState( ($data['EngineState'] == '' ) ? null : $data['EngineState']);
        }
        if(isset($data['LastTickAt'])){
            $e->setLastTickAt( ($data['LastTickAt'] == '' || $data['LastTickAt'] == 'null' || substr($data['LastTickAt'],0,10) == '-0001-11-30') ? null : $data['LastTickAt'] );
        }
        if(isset($data['LastPrice'])){
            $e->setLastPrice( ($data['LastPrice'] == '' ) ? null : $data['LastPrice']);
        }
        if(isset($data['BalBase'])){
            $e->setBalBase( ($data['BalBase'] == '' ) ? null : $data['BalBase']);
        }
        if(isset($data['BalQuote'])){
            $e->setBalQuote( ($data['BalQuote'] == '' ) ? null : $data['BalQuote']);
        }
        if(isset($data['SimBalBase'])){
            $e->setSimBalBase( ($data['SimBalBase'] == '' ) ? null : $data['SimBalBase']);
        }
        if(isset($data['SimBalQuote'])){
            $e->setSimBalQuote( ($data['SimBalQuote'] == '' ) ? null : $data['SimBalQuote']);
        }
        if(isset($data['RunUid'])){
            $e->setRunUid( ($data['RunUid'] == '' ) ? null : $data['RunUid']);
        }
        if(isset($data['AppliedGeometry'])){
            $e->setAppliedGeometry( ($data['AppliedGeometry'] == '' ) ? null : $data['AppliedGeometry']);
        }
        if(isset($data['LedgerResetAt'])){
            $e->setLedgerResetAt( ($data['LedgerResetAt'] == '' || $data['LedgerResetAt'] == 'null' || substr($data['LedgerResetAt'],0,10) == '-0001-11-30') ? null : $data['LedgerResetAt'] );
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of GridRun
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

        $je = "GridRunTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('GridRun', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addGridRunForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = GridRunQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('GridRun', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->GridRun['isNew'] = 'yes';
            $dataObj = new GridRun();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->GridRun['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;









        $KillSwitchChecked = ($dataObj->getKillSwitch())?"checked='checked'":'';
                $KillSwitch = ($dataObj->getKillSwitch())?"true":"true";
                    $SimulatedChecked = ($dataObj->getSimulated())?"checked='checked'":'';
                $Simulated = ($dataObj->getSimulated())?"true":"true";
                    $SellAtLossChecked = ($dataObj->getSellAtLoss())?"checked='checked'":'';
                $SellAtLoss = ($dataObj->getSellAtLoss())?"true":"true";
                    $SellWhenStarvedChecked = ($dataObj->getSellWhenStarved())?"checked='checked'":'';
                $SellWhenStarved = ($dataObj->getSellWhenStarved())?"true":"true";



$this->fields['GridRun']['Label']['html'] = stdFieldRow(_("Label"), input('text', 'Label', htmlentities((string)($dataObj->getLabel() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Label'))."' size='35'  v='LABEL' s='d' class='req'  ")."", 'Label', "", $this->commentsLabel, $this->commentsLabel_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Symbol']['html'] = stdFieldRow(_("Symbol"), input('text', 'Symbol', htmlentities((string)($dataObj->getSymbol() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Symbol'))."' size='35'  v='SYMBOL' s='d' class='req'  ")."", 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Status']['html'] = stdFieldRow(_("Status"), selectboxCustomArray('Status', [ '0' => ['0'=>_("Draft"), '1'=>"Draft"],'1' => ['0'=>_("DryRun"), '1'=>"DryRun"],'2' => ['0'=>_("Testnet"), '1'=>"Testnet"],'3' => ['0'=>_("Live"), '1'=>"Live"],'4' => ['0'=>_("Halted"), '1'=>"Halted"],'5' => ['0'=>_("Done"), '1'=>"Done"],'6' => ['0'=>_("Retiring"), '1'=>"Retiring"], ], "", "s='d'  ", $dataObj->getStatus(), '', false), 'Status', "", $this->commentsStatus, $this->commentsStatus_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['KillSwitch']['html'] = stdFieldRow(_("Kill switch"), input('checkbox', 'KillSwitch', $KillSwitch, "$KillSwitchChecked  size='10' s='d'"), 'KillSwitch', "", $this->commentsKillSwitch, $this->commentsKillSwitch_css, ' half', ' ', 'yes', 'v2');
$this->fields['GridRun']['Profile']['html'] = stdFieldRow(_("Risk profile"), selectboxCustomArray('Profile', [ '0' => ['0'=>_("NoLoss"), '1'=>"NoLoss"],'1' => ['0'=>_("Cautious"), '1'=>"Cautious"],'2' => ['0'=>_("Balanced"), '1'=>"Balanced"],'3' => ['0'=>_("Aggressive"), '1'=>"Aggressive"],'4' => ['0'=>_("Max"), '1'=>"Max"], ], "", "s='d'  ", $dataObj->getProfile(), '', false), 'Profile', "", $this->commentsProfile, $this->commentsProfile_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Algo']['html'] = stdFieldRow(_("Algorithm"), selectboxCustomArray('Algo', [ '0' => ['0'=>_("Grid"), '1'=>"Grid"],'1' => ['0'=>_("Trend"), '1'=>"Trend"], ], "", "s='d'  ", $dataObj->getAlgo(), '', false), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Simulated']['html'] = stdFieldRow(_("Simulated"), input('checkbox', 'Simulated', $Simulated, "$SimulatedChecked  size='10' s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, ' half', ' ', 'yes', 'v2');
$this->fields['GridRun']['PLow']['html'] = stdFieldRow(_("Range low"), input('number', 'PLow', $dataObj->getPLow(), "  placeholder='".str_replace("'","&#39;",_('Range low'))."'  v='P_LOW' size='10' s='d' class='req'"), 'PLow', "", $this->commentsPLow, $this->commentsPLow_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['PHigh']['html'] = stdFieldRow(_("Range high"), input('number', 'PHigh', $dataObj->getPHigh(), "  placeholder='".str_replace("'","&#39;",_('Range high'))."'  v='P_HIGH' size='10' s='d' class='req'"), 'PHigh', "", $this->commentsPHigh, $this->commentsPHigh_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['NLevels']['html'] = stdFieldRow(_("Grid lines"), input('number', 'NLevels', $dataObj->getNLevels(), " step='1' placeholder='".str_replace("'","&#39;",_('Grid lines'))."' v='N_LEVELS' size='5' s='d' class='req'"), 'NLevels', "", $this->commentsNLevels, $this->commentsNLevels_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Spacing']['html'] = stdFieldRow(_("Spacing"), selectboxCustomArray('Spacing', [ '0' => ['0'=>_("Geometric"), '1'=>"Geometric"],'1' => ['0'=>_("Arithmetic"), '1'=>"Arithmetic"], ], "", "s='d'  ", $dataObj->getSpacing(), '', false), 'Spacing', "", $this->commentsSpacing, $this->commentsSpacing_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['Allocation']['html'] = stdFieldRow(_("Allocation"), selectboxCustomArray('Allocation', [ '0' => ['0'=>_("EqualQuote"), '1'=>"EqualQuote"],'1' => ['0'=>_("EqualBase"), '1'=>"EqualBase"],'2' => ['0'=>_("BottomWeighted"), '1'=>"BottomWeighted"], ], "", "s='d'  ", $dataObj->getAllocation(), '', false), 'Allocation', "", $this->commentsAllocation, $this->commentsAllocation_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['BudgetQuote']['html'] = stdFieldRow(_("Budget (USDT)"), input('number', 'BudgetQuote', $dataObj->getBudgetQuote(), "  placeholder='".str_replace("'","&#39;",_('Budget (USDT)'))."'  v='BUDGET_QUOTE' size='10' s='d' class='req'"), 'BudgetQuote', "", $this->commentsBudgetQuote, $this->commentsBudgetQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['DeployPct']['html'] = stdFieldRow(_("Deployed budget %"), input('number', 'DeployPct', $dataObj->getDeployPct(), " step='1' placeholder='".str_replace("'","&#39;",_('Deployed budget %'))."' v='DEPLOY_PCT' size='5' s='d' class=''"), 'DeployPct', "", $this->commentsDeployPct, $this->commentsDeployPct_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['AllocMode']['html'] = stdFieldRow(_("Allocation mode"), selectboxCustomArray('AllocMode', [ '0' => ['0'=>_("Auto"), '1'=>"Auto"],'1' => ['0'=>_("Fixed"), '1'=>"Fixed"], ], "", "s='d'  ", $dataObj->getAllocMode(), '', false), 'AllocMode', "", $this->commentsAllocMode, $this->commentsAllocMode_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['FeePct']['html'] = stdFieldRow(_("Fee per side"), input('number', 'FeePct', $dataObj->getFeePct(), "  placeholder='".str_replace("'","&#39;",_('Fee per side'))."'  v='FEE_PCT' size='5' s='d' class='req'"), 'FeePct', "", $this->commentsFeePct, $this->commentsFeePct_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['MaxPositionQuote']['html'] = stdFieldRow(_("Max position (USDT)"), input('number', 'MaxPositionQuote', $dataObj->getMaxPositionQuote(), "  placeholder='".str_replace("'","&#39;",_('Max position (USDT)'))."'  v='MAX_POSITION_QUOTE' size='10' s='d' class='req'"), 'MaxPositionQuote', "", $this->commentsMaxPositionQuote, $this->commentsMaxPositionQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['MaxOrderQuote']['html'] = stdFieldRow(_("Max per-order (USDT)"), input('number', 'MaxOrderQuote', $dataObj->getMaxOrderQuote(), "  placeholder='".str_replace("'","&#39;",_('Max per-order (USDT)'))."'  v='MAX_ORDER_QUOTE' size='10' s='d' class='req'"), 'MaxOrderQuote', "", $this->commentsMaxOrderQuote, $this->commentsMaxOrderQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['DailyLossLimitQuote']['html'] = stdFieldRow(_("Daily loss limit"), input('number', 'DailyLossLimitQuote', $dataObj->getDailyLossLimitQuote(), "  placeholder='".str_replace("'","&#39;",_('Daily loss limit'))."'  v='DAILY_LOSS_LIMIT_QUOTE' size='10' s='d' class='req'"), 'DailyLossLimitQuote', "", $this->commentsDailyLossLimitQuote, $this->commentsDailyLossLimitQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['MaxUnrealizedLossQuote']['html'] = stdFieldRow(_("Max unrealized loss"), input('number', 'MaxUnrealizedLossQuote', $dataObj->getMaxUnrealizedLossQuote(), "  placeholder='".str_replace("'","&#39;",_('Max unrealized loss'))."'  v='MAX_UNREALIZED_LOSS_QUOTE' size='10' s='d' class=''"), 'MaxUnrealizedLossQuote', "", $this->commentsMaxUnrealizedLossQuote, $this->commentsMaxUnrealizedLossQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['SellAtLoss']['html'] = stdFieldRow(_("Sell at loss"), input('checkbox', 'SellAtLoss', $SellAtLoss, "$SellAtLossChecked  size='10' s='d'"), 'SellAtLoss', "", $this->commentsSellAtLoss, $this->commentsSellAtLoss_css, ' half', ' ', 'yes', 'v2');
$this->fields['GridRun']['SellWhenStarved']['html'] = stdFieldRow(_("Sell at loss when starved"), input('checkbox', 'SellWhenStarved', $SellWhenStarved, "$SellWhenStarvedChecked  size='10' s='d'"), 'SellWhenStarved', "", $this->commentsSellWhenStarved, $this->commentsSellWhenStarved_css, ' half', ' ', 'yes', 'v2');
$this->fields['GridRun']['BreakoutBufferPct']['html'] = stdFieldRow(_("Breakout buffer"), input('number', 'BreakoutBufferPct', $dataObj->getBreakoutBufferPct(), "  placeholder='".str_replace("'","&#39;",_('Breakout buffer'))."'  v='BREAKOUT_BUFFER_PCT' size='5' s='d' class=''"), 'BreakoutBufferPct', "", $this->commentsBreakoutBufferPct, $this->commentsBreakoutBufferPct_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['BreakoutPolicy']['html'] = stdFieldRow(_("On breakout"), selectboxCustomArray('BreakoutPolicy', [ '0' => ['0'=>_("HaltAndHold"), '1'=>"HaltAndHold"],'1' => ['0'=>_("Flatten"), '1'=>"Flatten"], ], "", "s='d'  ", $dataObj->getBreakoutPolicy(), '', false), 'BreakoutPolicy', "", $this->commentsBreakoutPolicy, $this->commentsBreakoutPolicy_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['MaxOpenOrders']['html'] = stdFieldRow(_("Max open orders"), input('number', 'MaxOpenOrders', $dataObj->getMaxOpenOrders(), " step='1' placeholder='".str_replace("'","&#39;",_('Max open orders'))."' v='MAX_OPEN_ORDERS' size='5' s='d' class=''"), 'MaxOpenOrders', "", $this->commentsMaxOpenOrders, $this->commentsMaxOpenOrders_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['MaxBuyLevelsBelow']['html'] = stdFieldRow(_("Active buy levels below price (0 = all)"), input('number', 'MaxBuyLevelsBelow', $dataObj->getMaxBuyLevelsBelow(), " step='1' placeholder='".str_replace("'","&#39;",_('Active buy levels below price (0 = all)'))."' v='MAX_BUY_LEVELS_BELOW' size='5' s='d' class=''"), 'MaxBuyLevelsBelow', "", $this->commentsMaxBuyLevelsBelow, $this->commentsMaxBuyLevelsBelow_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['TrendTf']['html'] = stdFieldRow(_("Signal timeframe"), selectboxCustomArray('TrendTf', [ '0' => ['0'=>_("1h"), '1'=>"1h"],'1' => ['0'=>_("4h"), '1'=>"4h"], ], "", "s='d'  ", $dataObj->getTrendTf(), '', false), 'TrendTf', "", $this->commentsTrendTf, $this->commentsTrendTf_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['DonchianPeriod']['html'] = stdFieldRow(_("Breakout period"), input('number', 'DonchianPeriod', $dataObj->getDonchianPeriod(), " step='1' placeholder='".str_replace("'","&#39;",_('Breakout period'))."' v='DONCHIAN_PERIOD' size='5' s='d' class=''"), 'DonchianPeriod', "", $this->commentsDonchianPeriod, $this->commentsDonchianPeriod_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['TrendEmaFast']['html'] = stdFieldRow(_("Trend EMA fast"), input('number', 'TrendEmaFast', $dataObj->getTrendEmaFast(), " step='1' placeholder='".str_replace("'","&#39;",_('Trend EMA fast'))."' v='TREND_EMA_FAST' size='5' s='d' class=''"), 'TrendEmaFast', "", $this->commentsTrendEmaFast, $this->commentsTrendEmaFast_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['TrendEmaSlow']['html'] = stdFieldRow(_("Trend EMA slow"), input('number', 'TrendEmaSlow', $dataObj->getTrendEmaSlow(), " step='1' placeholder='".str_replace("'","&#39;",_('Trend EMA slow'))."' v='TREND_EMA_SLOW' size='5' s='d' class=''"), 'TrendEmaSlow', "", $this->commentsTrendEmaSlow, $this->commentsTrendEmaSlow_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['AtrPeriod']['html'] = stdFieldRow(_("ATR period"), input('number', 'AtrPeriod', $dataObj->getAtrPeriod(), " step='1' placeholder='".str_replace("'","&#39;",_('ATR period'))."' v='ATR_PERIOD' size='5' s='d' class=''"), 'AtrPeriod', "", $this->commentsAtrPeriod, $this->commentsAtrPeriod_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['AtrStopMult']['html'] = stdFieldRow(_("Trail stop x ATR"), input('number', 'AtrStopMult', $dataObj->getAtrStopMult(), "  placeholder='".str_replace("'","&#39;",_('Trail stop x ATR'))."'  v='ATR_STOP_MULT' size='5' s='d' class=''"), 'AtrStopMult', "", $this->commentsAtrStopMult, $this->commentsAtrStopMult_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['AtrInitialMult']['html'] = stdFieldRow(_("Initial stop x ATR"), input('number', 'AtrInitialMult', $dataObj->getAtrInitialMult(), "  placeholder='".str_replace("'","&#39;",_('Initial stop x ATR'))."'  v='ATR_INITIAL_MULT' size='5' s='d' class=''"), 'AtrInitialMult', "", $this->commentsAtrInitialMult, $this->commentsAtrInitialMult_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['TrendStopFloorPct']['html'] = stdFieldRow(_("Trail stop floor (fraction of HWM)"), input('number', 'TrendStopFloorPct', $dataObj->getTrendStopFloorPct(), "  placeholder='".str_replace("'","&#39;",_('Trail stop floor (fraction of HWM)'))."'  v='TREND_STOP_FLOOR_PCT' size='5' s='d' class=''"), 'TrendStopFloorPct', "", $this->commentsTrendStopFloorPct, $this->commentsTrendStopFloorPct_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['TrendSignal']['html'] = stdFieldRow(_("Trend entry signal"), selectboxCustomArray('TrendSignal', [ '0' => ['0'=>_("Donchian"), '1'=>"Donchian"],'1' => ['0'=>_("EmaCross1d"), '1'=>"EmaCross1d"], ], "", "s='d'  ", $dataObj->getTrendSignal(), '', false), 'TrendSignal', "", $this->commentsTrendSignal, $this->commentsTrendSignal_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['ReentryCooldown']['html'] = stdFieldRow(_("Re-entry cooldown (bars)"), input('number', 'ReentryCooldown', $dataObj->getReentryCooldown(), " step='1' placeholder='".str_replace("'","&#39;",_('Re-entry cooldown (bars)'))."' v='REENTRY_COOLDOWN' size='5' s='d' class=''"), 'ReentryCooldown', "", $this->commentsReentryCooldown, $this->commentsReentryCooldown_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['EngineState']['html'] = stdFieldRow(_("Engine state (daemon-managed)"), textarea('EngineState', htmlentities((string)($dataObj->getEngineState() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Engine state (daemon-managed)'))."' cols='71' v='ENGINE_STATE' s='d'  class=' ' style='' spellcheck='false'"), 'EngineState', "", $this->commentsEngineState, $this->commentsEngineState_css, '', ' ', 'no', 'v2');
$this->fields['GridRun']['LastTickAt']['html'] = stdFieldRow(_("Last tick"), input('datetime-local', 'LastTickAt', $dataObj->getLastTickAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Last tick'"), 'LastTickAt', "", $this->commentsLastTickAt, $this->commentsLastTickAt_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['LastPrice']['html'] = stdFieldRow(_("Last price"), input('number', 'LastPrice', $dataObj->getLastPrice(), "  placeholder='".str_replace("'","&#39;",_('Last price'))."'  v='LAST_PRICE' size='10' s='d' class=''"), 'LastPrice', "", $this->commentsLastPrice, $this->commentsLastPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['BalBase']['html'] = stdFieldRow(_("Wallet base"), input('number', 'BalBase', $dataObj->getBalBase(), "  placeholder='".str_replace("'","&#39;",_('Wallet base'))."'  v='BAL_BASE' size='10' s='d' class=''"), 'BalBase', "", $this->commentsBalBase, $this->commentsBalBase_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['BalQuote']['html'] = stdFieldRow(_("Wallet quote"), input('number', 'BalQuote', $dataObj->getBalQuote(), "  placeholder='".str_replace("'","&#39;",_('Wallet quote'))."'  v='BAL_QUOTE' size='10' s='d' class=''"), 'BalQuote', "", $this->commentsBalQuote, $this->commentsBalQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['SimBalBase']['html'] = stdFieldRow(_("Paper wallet base (daemon-managed)"), input('number', 'SimBalBase', $dataObj->getSimBalBase(), "  placeholder='".str_replace("'","&#39;",_('Paper wallet base (daemon-managed)'))."'  v='SIM_BAL_BASE' size='10' s='d' class=''"), 'SimBalBase', "", $this->commentsSimBalBase, $this->commentsSimBalBase_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['SimBalQuote']['html'] = stdFieldRow(_("Paper wallet quote (daemon-managed)"), input('number', 'SimBalQuote', $dataObj->getSimBalQuote(), "  placeholder='".str_replace("'","&#39;",_('Paper wallet quote (daemon-managed)'))."'  v='SIM_BAL_QUOTE' size='10' s='d' class=''"), 'SimBalQuote', "", $this->commentsSimBalQuote, $this->commentsSimBalQuote_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['RunUid']['html'] = stdFieldRow(_("Run UID"), input('text', 'RunUid', htmlentities((string)($dataObj->getRunUid() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Run UID'))."' size='35'  v='RUN_UID' s='d' class=''  ")."", 'RunUid', "", $this->commentsRunUid, $this->commentsRunUid_css, ' half', ' ', 'no', 'v2');
$this->fields['GridRun']['AppliedGeometry']['html'] = stdFieldRow(_("Applied geometry (daemon-managed)"), textarea('AppliedGeometry', htmlentities((string)($dataObj->getAppliedGeometry() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Applied geometry (daemon-managed)'))."' cols='71' v='APPLIED_GEOMETRY' s='d'  class=' ' style='' spellcheck='false'"), 'AppliedGeometry', "", $this->commentsAppliedGeometry, $this->commentsAppliedGeometry_css, '', ' ', 'no', 'v2');
$this->fields['GridRun']['LedgerResetAt']['html'] = stdFieldRow(_("Ledger rebased at (daemon-managed)"), input('datetime-local', 'LedgerResetAt', $dataObj->getLedgerResetAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Ledger rebased at (daemon-managed)'"), 'LedgerResetAt', "", $this->commentsLedgerResetAt, $this->commentsLedgerResetAt_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'DailyLossLimitQuote',1=>'MaxUnrealizedLossQuote',2=>'MaxPositionQuote',3=>'MaxOrderQuote',4=>'BreakoutPolicy',5=>'AtrStopMult',6=>'AtrInitialMult',7=>'ReentryCooldown',8=>'EngineState',9=>'IdCreation',10=>'IdModification',11=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }



        $ChildOnglet = '';
        if( !isset($this->GridRun['request']['ChildHide']) ) {

            # define child lists 'Order'
            $ongletTab['0']['t'] = _('Order');
            $ongletTab['0']['p'] = 'BotOrder';
            $ongletTab['0']['lkey'] = 'IdGridRun';
            $ongletTab['0']['fkey'] = 'IdGridRun';
            $ongletTab['0']['icon'] = 'ri-shopping-bag-line';
            # define child lists 'Trade Cycle'
            $ongletTab['1']['t'] = _('Trade Cycle');
            $ongletTab['1']['p'] = 'TradeCycle';
            $ongletTab['1']['lkey'] = 'IdGridRun';
            $ongletTab['1']['fkey'] = 'IdGridRun';
            $ongletTab['1']['icon'] = 'ri-folder-line';
            # define child lists 'Event'
            $ongletTab['2']['t'] = _('Event');
            $ongletTab['2']['p'] = 'BotEvent';
            $ongletTab['2']['lkey'] = 'IdGridRun';
            $ongletTab['2']['fkey'] = 'IdGridRun';
            $ongletTab['2']['icon'] = 'ri-calendar-event-line';
            # define child lists 'Command'
            $ongletTab['3']['t'] = _('Command');
            $ongletTab['3']['p'] = 'BotCommand';
            $ongletTab['3']['lkey'] = 'IdGridRun';
            $ongletTab['3']['fkey'] = 'IdGridRun';
            $ongletTab['3']['icon'] = 'ri-folder-line';
            # define child lists 'Change history'
            $ongletTab['4']['t'] = _('Change history');
            $ongletTab['4']['p'] = 'GridRunAudit';
            $ongletTab['4']['lkey'] = 'IdGridRun';
            $ongletTab['4']['fkey'] = 'IdGridRun';
            $ongletTab['4']['icon'] = 'ri-folder-line';
        if(!empty($ongletTab) and $dataObj->getIdGridRun()){
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
                                    , ' type="button" class="child-tab" role="tab" j="conglet_GridRun" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" ')
                                    . htmlLink(
                                        "<i class='ri-add-line'></i>"
                                    , 'Javascript:;', ' class="child-tab-add header-controls" j="childadd_GridRun" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" title="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" aria-label="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" style="display:inline-flex;align-items:center;padding:0 7px;margin-right:4px;" ');
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
            if( ($_SESSION[_AUTH_VAR]->hasRights('GridRun','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('GridRun','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveGridRun', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedGridRun','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        // Custom drawer tabs registered by the wrapper (FormHelper::addFormTab, e.g. from afterFormObj).
        [$gcTabNavFirst, $gcTabNavLast, $gcPanesFirst, $gcPanesLast, $gcFirstTabActive] = $this->renderFormCustomTabs();
        $ongletf =
            div(
                div($gcTabNavFirst . button(_('Grid Run'), ' type="button" class="tab-btn' . ($gcFirstTabActive ? ' is-active' : '') . '" role="tab" data-tab="tab_GridRun" aria-selected="' . ($gcFirstTabActive ? 'true' : 'false') . '" aria-controls="tab_GridRun" ')
                    .button(_('Grid + budget'), ' type="button" class="tab-btn" role="tab" data-tab="tab_p_low" aria-selected="false" aria-controls="tab_p_low" ')
                    .button(_('Risk limits'), ' type="button" class="tab-btn" role="tab" data-tab="tab_max_position_quote" aria-selected="false" aria-controls="tab_max_position_quote" ')
                    .button(_('Trend settings'), ' type="button" class="tab-btn" role="tab" data-tab="tab_trend_tf" aria-selected="false" aria-controls="tab_trend_tf" ')
                    .button(_('Telemetry'), ' type="button" class="tab-btn" role="tab" data-tab="tab_last_tick_at" aria-selected="false" aria-controls="tab_last_tick_at" ') . $gcTabNavLast,'',"class='sw-tabnav' role='tablist'")
            ,'cntOngletGridRun',' class="cntOnglet"')
        ;



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
        if($dataObj->getIdGridRun()) {
            if($ChildOnglet) {
                $childTabsHtml = div($ChildOnglet, '', " class='child-tabs' role='tablist' ");
                $childPannelHtml = div('', 'cntGridRunChild', ' class="child-pannel" ');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Grid Run'), _SITE_URL.'GridRun', "class='nav-btn'")
                        .div(
                            span(_('Grid Run'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'GridRun', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div('' . $gcPanesFirst .
                    '<div id="tab_GridRun" class="tab-pane' . (($gcFirstTabActive ?? true) ? ' is-active' : '') . '" role="tabpanel" data-tab="tab_GridRun"' . (($gcFirstTabActive ?? true) ? '' : ' hidden') . '><div class="sw-grid">'.
$this->fields['GridRun']['Label']['html']
.$this->fields['GridRun']['Symbol']['html']
.$this->fields['GridRun']['Status']['html']
.$this->fields['GridRun']['KillSwitch']['html']
.$this->fields['GridRun']['Profile']['html']
.$this->fields['GridRun']['Algo']['html']
.$this->fields['GridRun']['Simulated']['html']
.'</div></div><div id="tab_p_low" class="tab-pane" role="tabpanel" data-tab="tab_p_low" hidden><div class="sw-grid">'
.$this->fields['GridRun']['PLow']['html']
.$this->fields['GridRun']['PHigh']['html']
.$this->fields['GridRun']['NLevels']['html']
.$this->fields['GridRun']['Spacing']['html']
.$this->fields['GridRun']['Allocation']['html']
.$this->fields['GridRun']['BudgetQuote']['html']
.$this->fields['GridRun']['DeployPct']['html']
.$this->fields['GridRun']['AllocMode']['html']
.$this->fields['GridRun']['FeePct']['html']
.'</div></div><div id="tab_max_position_quote" class="tab-pane" role="tabpanel" data-tab="tab_max_position_quote" hidden><div class="sw-grid">'
.$this->fields['GridRun']['MaxPositionQuote']['html']
.$this->fields['GridRun']['MaxOrderQuote']['html']
.$this->fields['GridRun']['DailyLossLimitQuote']['html']
.$this->fields['GridRun']['MaxUnrealizedLossQuote']['html']
.$this->fields['GridRun']['SellAtLoss']['html']
.$this->fields['GridRun']['SellWhenStarved']['html']
.$this->fields['GridRun']['BreakoutBufferPct']['html']
.$this->fields['GridRun']['BreakoutPolicy']['html']
.$this->fields['GridRun']['MaxOpenOrders']['html']
.$this->fields['GridRun']['MaxBuyLevelsBelow']['html']
.'</div></div><div id="tab_trend_tf" class="tab-pane" role="tabpanel" data-tab="tab_trend_tf" hidden><div class="sw-grid">'
.$this->fields['GridRun']['TrendTf']['html']
.$this->fields['GridRun']['DonchianPeriod']['html']
.$this->fields['GridRun']['TrendEmaFast']['html']
.$this->fields['GridRun']['TrendEmaSlow']['html']
.$this->fields['GridRun']['AtrPeriod']['html']
.$this->fields['GridRun']['AtrStopMult']['html']
.$this->fields['GridRun']['AtrInitialMult']['html']
.$this->fields['GridRun']['TrendStopFloorPct']['html']
.$this->fields['GridRun']['TrendSignal']['html']
.$this->fields['GridRun']['ReentryCooldown']['html']
.$this->fields['GridRun']['EngineState']['html']
.'</div></div><div id="tab_last_tick_at" class="tab-pane" role="tabpanel" data-tab="tab_last_tick_at" hidden><div class="sw-grid">'
.$this->fields['GridRun']['LastTickAt']['html']
.$this->fields['GridRun']['LastPrice']['html']
.$this->fields['GridRun']['BalBase']['html']
.$this->fields['GridRun']['BalQuote']['html']
.$this->fields['GridRun']['SimBalBase']['html']
.$this->fields['GridRun']['SimBalQuote']['html']
.$this->fields['GridRun']['RunUid']['html']
.$this->fields['GridRun']['AppliedGeometry']['html']
.$this->fields['GridRun']['LedgerResetAt']['html'].'</div></div>' . $gcPanesLast ,'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntGridRun", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formGridRun' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['GridRun']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['GridRun'][$field]['html'] = $this->fieldsRo['GridRun'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Label':
        $this->fieldsRo['GridRun']['Label']['html'] = stdFieldRow(_("Label"), div( htmlspecialchars((string)($dataObj->getLabel()), ENT_QUOTES), 'Label_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Label', $dataObj->getLabel(), "s='d'"), 'Label', "", $this->commentsLabel, $this->commentsLabel_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Symbol':
        $this->fieldsRo['GridRun']['Symbol']['html'] = stdFieldRow(_("Symbol"), div( htmlspecialchars((string)($dataObj->getSymbol()), ENT_QUOTES), 'Symbol_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Symbol', $dataObj->getSymbol(), "s='d'"), 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Status':
        $this->fieldsRo['GridRun']['Status']['html'] = stdFieldRow(_("Status"), div( htmlspecialchars((string)($dataObj->getStatus()), ENT_QUOTES), 'Status_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Status', $dataObj->getStatus(), "s='d'"), 'Status', "", $this->commentsStatus, $this->commentsStatus_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'KillSwitch':
        $this->fieldsRo['GridRun']['KillSwitch']['html'] = stdFieldRow(_("Kill switch"), div( htmlspecialchars((string)($dataObj->getKillSwitch()), ENT_QUOTES), 'KillSwitch_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'KillSwitch', $dataObj->getKillSwitch(), "s='d'"), 'KillSwitch', "", $this->commentsKillSwitch, $this->commentsKillSwitch_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Profile':
        $this->fieldsRo['GridRun']['Profile']['html'] = stdFieldRow(_("Risk profile"), div( htmlspecialchars((string)($dataObj->getProfile()), ENT_QUOTES), 'Profile_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Profile', $dataObj->getProfile(), "s='d'"), 'Profile', "", $this->commentsProfile, $this->commentsProfile_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Algo':
        $this->fieldsRo['GridRun']['Algo']['html'] = stdFieldRow(_("Algorithm"), div( htmlspecialchars((string)($dataObj->getAlgo()), ENT_QUOTES), 'Algo_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Algo', $dataObj->getAlgo(), "s='d'"), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Simulated':
        $this->fieldsRo['GridRun']['Simulated']['html'] = stdFieldRow(_("Simulated"), div( htmlspecialchars((string)($dataObj->getSimulated()), ENT_QUOTES), 'Simulated_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Simulated', $dataObj->getSimulated(), "s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PLow':
        $this->fieldsRo['GridRun']['PLow']['html'] = stdFieldRow(_("Range low"), div( htmlspecialchars((string)($dataObj->getPLow()), ENT_QUOTES), 'PLow_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PLow', $dataObj->getPLow(), "s='d'"), 'PLow', "", $this->commentsPLow, $this->commentsPLow_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PHigh':
        $this->fieldsRo['GridRun']['PHigh']['html'] = stdFieldRow(_("Range high"), div( htmlspecialchars((string)($dataObj->getPHigh()), ENT_QUOTES), 'PHigh_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PHigh', $dataObj->getPHigh(), "s='d'"), 'PHigh', "", $this->commentsPHigh, $this->commentsPHigh_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'NLevels':
        $this->fieldsRo['GridRun']['NLevels']['html'] = stdFieldRow(_("Grid lines"), div( htmlspecialchars((string)($dataObj->getNLevels()), ENT_QUOTES), 'NLevels_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'NLevels', $dataObj->getNLevels(), "s='d'"), 'NLevels', "", $this->commentsNLevels, $this->commentsNLevels_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Spacing':
        $this->fieldsRo['GridRun']['Spacing']['html'] = stdFieldRow(_("Spacing"), div( htmlspecialchars((string)($dataObj->getSpacing()), ENT_QUOTES), 'Spacing_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Spacing', $dataObj->getSpacing(), "s='d'"), 'Spacing', "", $this->commentsSpacing, $this->commentsSpacing_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Allocation':
        $this->fieldsRo['GridRun']['Allocation']['html'] = stdFieldRow(_("Allocation"), div( htmlspecialchars((string)($dataObj->getAllocation()), ENT_QUOTES), 'Allocation_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Allocation', $dataObj->getAllocation(), "s='d'"), 'Allocation', "", $this->commentsAllocation, $this->commentsAllocation_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BudgetQuote':
        $this->fieldsRo['GridRun']['BudgetQuote']['html'] = stdFieldRow(_("Budget (USDT)"), div( htmlspecialchars((string)($dataObj->getBudgetQuote()), ENT_QUOTES), 'BudgetQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BudgetQuote', $dataObj->getBudgetQuote(), "s='d'"), 'BudgetQuote', "", $this->commentsBudgetQuote, $this->commentsBudgetQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'DeployPct':
        $this->fieldsRo['GridRun']['DeployPct']['html'] = stdFieldRow(_("Deployed budget %"), div( htmlspecialchars((string)($dataObj->getDeployPct()), ENT_QUOTES), 'DeployPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DeployPct', $dataObj->getDeployPct(), "s='d'"), 'DeployPct', "", $this->commentsDeployPct, $this->commentsDeployPct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'AllocMode':
        $this->fieldsRo['GridRun']['AllocMode']['html'] = stdFieldRow(_("Allocation mode"), div( htmlspecialchars((string)($dataObj->getAllocMode()), ENT_QUOTES), 'AllocMode_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AllocMode', $dataObj->getAllocMode(), "s='d'"), 'AllocMode', "", $this->commentsAllocMode, $this->commentsAllocMode_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'FeePct':
        $this->fieldsRo['GridRun']['FeePct']['html'] = stdFieldRow(_("Fee per side"), div( htmlspecialchars((string)($dataObj->getFeePct()), ENT_QUOTES), 'FeePct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FeePct', $dataObj->getFeePct(), "s='d'"), 'FeePct', "", $this->commentsFeePct, $this->commentsFeePct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxPositionQuote':
        $this->fieldsRo['GridRun']['MaxPositionQuote']['html'] = stdFieldRow(_("Max position (USDT)"), div( htmlspecialchars((string)($dataObj->getMaxPositionQuote()), ENT_QUOTES), 'MaxPositionQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxPositionQuote', $dataObj->getMaxPositionQuote(), "s='d'"), 'MaxPositionQuote', "", $this->commentsMaxPositionQuote, $this->commentsMaxPositionQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxOrderQuote':
        $this->fieldsRo['GridRun']['MaxOrderQuote']['html'] = stdFieldRow(_("Max per-order (USDT)"), div( htmlspecialchars((string)($dataObj->getMaxOrderQuote()), ENT_QUOTES), 'MaxOrderQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxOrderQuote', $dataObj->getMaxOrderQuote(), "s='d'"), 'MaxOrderQuote', "", $this->commentsMaxOrderQuote, $this->commentsMaxOrderQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'DailyLossLimitQuote':
        $this->fieldsRo['GridRun']['DailyLossLimitQuote']['html'] = stdFieldRow(_("Daily loss limit"), div( htmlspecialchars((string)($dataObj->getDailyLossLimitQuote()), ENT_QUOTES), 'DailyLossLimitQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DailyLossLimitQuote', $dataObj->getDailyLossLimitQuote(), "s='d'"), 'DailyLossLimitQuote', "", $this->commentsDailyLossLimitQuote, $this->commentsDailyLossLimitQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxUnrealizedLossQuote':
        $this->fieldsRo['GridRun']['MaxUnrealizedLossQuote']['html'] = stdFieldRow(_("Max unrealized loss"), div( htmlspecialchars((string)($dataObj->getMaxUnrealizedLossQuote()), ENT_QUOTES), 'MaxUnrealizedLossQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxUnrealizedLossQuote', $dataObj->getMaxUnrealizedLossQuote(), "s='d'"), 'MaxUnrealizedLossQuote', "", $this->commentsMaxUnrealizedLossQuote, $this->commentsMaxUnrealizedLossQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'SellAtLoss':
        $this->fieldsRo['GridRun']['SellAtLoss']['html'] = stdFieldRow(_("Sell at loss"), div( htmlspecialchars((string)($dataObj->getSellAtLoss()), ENT_QUOTES), 'SellAtLoss_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SellAtLoss', $dataObj->getSellAtLoss(), "s='d'"), 'SellAtLoss', "", $this->commentsSellAtLoss, $this->commentsSellAtLoss_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'SellWhenStarved':
        $this->fieldsRo['GridRun']['SellWhenStarved']['html'] = stdFieldRow(_("Sell at loss when starved"), div( htmlspecialchars((string)($dataObj->getSellWhenStarved()), ENT_QUOTES), 'SellWhenStarved_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SellWhenStarved', $dataObj->getSellWhenStarved(), "s='d'"), 'SellWhenStarved', "", $this->commentsSellWhenStarved, $this->commentsSellWhenStarved_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BreakoutBufferPct':
        $this->fieldsRo['GridRun']['BreakoutBufferPct']['html'] = stdFieldRow(_("Breakout buffer"), div( htmlspecialchars((string)($dataObj->getBreakoutBufferPct()), ENT_QUOTES), 'BreakoutBufferPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BreakoutBufferPct', $dataObj->getBreakoutBufferPct(), "s='d'"), 'BreakoutBufferPct', "", $this->commentsBreakoutBufferPct, $this->commentsBreakoutBufferPct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BreakoutPolicy':
        $this->fieldsRo['GridRun']['BreakoutPolicy']['html'] = stdFieldRow(_("On breakout"), div( htmlspecialchars((string)($dataObj->getBreakoutPolicy()), ENT_QUOTES), 'BreakoutPolicy_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BreakoutPolicy', $dataObj->getBreakoutPolicy(), "s='d'"), 'BreakoutPolicy', "", $this->commentsBreakoutPolicy, $this->commentsBreakoutPolicy_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxOpenOrders':
        $this->fieldsRo['GridRun']['MaxOpenOrders']['html'] = stdFieldRow(_("Max open orders"), div( htmlspecialchars((string)($dataObj->getMaxOpenOrders()), ENT_QUOTES), 'MaxOpenOrders_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxOpenOrders', $dataObj->getMaxOpenOrders(), "s='d'"), 'MaxOpenOrders', "", $this->commentsMaxOpenOrders, $this->commentsMaxOpenOrders_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'MaxBuyLevelsBelow':
        $this->fieldsRo['GridRun']['MaxBuyLevelsBelow']['html'] = stdFieldRow(_("Active buy levels below price (0 = all)"), div( htmlspecialchars((string)($dataObj->getMaxBuyLevelsBelow()), ENT_QUOTES), 'MaxBuyLevelsBelow_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'MaxBuyLevelsBelow', $dataObj->getMaxBuyLevelsBelow(), "s='d'"), 'MaxBuyLevelsBelow', "", $this->commentsMaxBuyLevelsBelow, $this->commentsMaxBuyLevelsBelow_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TrendTf':
        $this->fieldsRo['GridRun']['TrendTf']['html'] = stdFieldRow(_("Signal timeframe"), div( htmlspecialchars((string)($dataObj->getTrendTf()), ENT_QUOTES), 'TrendTf_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TrendTf', $dataObj->getTrendTf(), "s='d'"), 'TrendTf', "", $this->commentsTrendTf, $this->commentsTrendTf_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'DonchianPeriod':
        $this->fieldsRo['GridRun']['DonchianPeriod']['html'] = stdFieldRow(_("Breakout period"), div( htmlspecialchars((string)($dataObj->getDonchianPeriod()), ENT_QUOTES), 'DonchianPeriod_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DonchianPeriod', $dataObj->getDonchianPeriod(), "s='d'"), 'DonchianPeriod', "", $this->commentsDonchianPeriod, $this->commentsDonchianPeriod_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TrendEmaFast':
        $this->fieldsRo['GridRun']['TrendEmaFast']['html'] = stdFieldRow(_("Trend EMA fast"), div( htmlspecialchars((string)($dataObj->getTrendEmaFast()), ENT_QUOTES), 'TrendEmaFast_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TrendEmaFast', $dataObj->getTrendEmaFast(), "s='d'"), 'TrendEmaFast', "", $this->commentsTrendEmaFast, $this->commentsTrendEmaFast_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TrendEmaSlow':
        $this->fieldsRo['GridRun']['TrendEmaSlow']['html'] = stdFieldRow(_("Trend EMA slow"), div( htmlspecialchars((string)($dataObj->getTrendEmaSlow()), ENT_QUOTES), 'TrendEmaSlow_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TrendEmaSlow', $dataObj->getTrendEmaSlow(), "s='d'"), 'TrendEmaSlow', "", $this->commentsTrendEmaSlow, $this->commentsTrendEmaSlow_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'AtrPeriod':
        $this->fieldsRo['GridRun']['AtrPeriod']['html'] = stdFieldRow(_("ATR period"), div( htmlspecialchars((string)($dataObj->getAtrPeriod()), ENT_QUOTES), 'AtrPeriod_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AtrPeriod', $dataObj->getAtrPeriod(), "s='d'"), 'AtrPeriod', "", $this->commentsAtrPeriod, $this->commentsAtrPeriod_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'AtrStopMult':
        $this->fieldsRo['GridRun']['AtrStopMult']['html'] = stdFieldRow(_("Trail stop x ATR"), div( htmlspecialchars((string)($dataObj->getAtrStopMult()), ENT_QUOTES), 'AtrStopMult_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AtrStopMult', $dataObj->getAtrStopMult(), "s='d'"), 'AtrStopMult', "", $this->commentsAtrStopMult, $this->commentsAtrStopMult_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'AtrInitialMult':
        $this->fieldsRo['GridRun']['AtrInitialMult']['html'] = stdFieldRow(_("Initial stop x ATR"), div( htmlspecialchars((string)($dataObj->getAtrInitialMult()), ENT_QUOTES), 'AtrInitialMult_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AtrInitialMult', $dataObj->getAtrInitialMult(), "s='d'"), 'AtrInitialMult', "", $this->commentsAtrInitialMult, $this->commentsAtrInitialMult_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TrendStopFloorPct':
        $this->fieldsRo['GridRun']['TrendStopFloorPct']['html'] = stdFieldRow(_("Trail stop floor (fraction of HWM)"), div( htmlspecialchars((string)($dataObj->getTrendStopFloorPct()), ENT_QUOTES), 'TrendStopFloorPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TrendStopFloorPct', $dataObj->getTrendStopFloorPct(), "s='d'"), 'TrendStopFloorPct', "", $this->commentsTrendStopFloorPct, $this->commentsTrendStopFloorPct_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TrendSignal':
        $this->fieldsRo['GridRun']['TrendSignal']['html'] = stdFieldRow(_("Trend entry signal"), div( htmlspecialchars((string)($dataObj->getTrendSignal()), ENT_QUOTES), 'TrendSignal_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TrendSignal', $dataObj->getTrendSignal(), "s='d'"), 'TrendSignal', "", $this->commentsTrendSignal, $this->commentsTrendSignal_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ReentryCooldown':
        $this->fieldsRo['GridRun']['ReentryCooldown']['html'] = stdFieldRow(_("Re-entry cooldown (bars)"), div( htmlspecialchars((string)($dataObj->getReentryCooldown()), ENT_QUOTES), 'ReentryCooldown_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ReentryCooldown', $dataObj->getReentryCooldown(), "s='d'"), 'ReentryCooldown', "", $this->commentsReentryCooldown, $this->commentsReentryCooldown_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'EngineState':
        $this->fieldsRo['GridRun']['EngineState']['html'] = stdFieldRow(_("Engine state (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getEngineState()), ENT_QUOTES), 'EngineState_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EngineState', $dataObj->getEngineState(), "s='d'"), 'EngineState', "", $this->commentsEngineState, $this->commentsEngineState_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'LastTickAt':
        $this->fieldsRo['GridRun']['LastTickAt']['html'] = stdFieldRow(_("Last tick"), div( htmlspecialchars((string)($dataObj->getLastTickAt()), ENT_QUOTES), 'LastTickAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LastTickAt', $dataObj->getLastTickAt(), "s='d'"), 'LastTickAt', "", $this->commentsLastTickAt, $this->commentsLastTickAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LastPrice':
        $this->fieldsRo['GridRun']['LastPrice']['html'] = stdFieldRow(_("Last price"), div( htmlspecialchars((string)($dataObj->getLastPrice()), ENT_QUOTES), 'LastPrice_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LastPrice', $dataObj->getLastPrice(), "s='d'"), 'LastPrice', "", $this->commentsLastPrice, $this->commentsLastPrice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BalBase':
        $this->fieldsRo['GridRun']['BalBase']['html'] = stdFieldRow(_("Wallet base"), div( htmlspecialchars((string)($dataObj->getBalBase()), ENT_QUOTES), 'BalBase_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BalBase', $dataObj->getBalBase(), "s='d'"), 'BalBase', "", $this->commentsBalBase, $this->commentsBalBase_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BalQuote':
        $this->fieldsRo['GridRun']['BalQuote']['html'] = stdFieldRow(_("Wallet quote"), div( htmlspecialchars((string)($dataObj->getBalQuote()), ENT_QUOTES), 'BalQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BalQuote', $dataObj->getBalQuote(), "s='d'"), 'BalQuote', "", $this->commentsBalQuote, $this->commentsBalQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'SimBalBase':
        $this->fieldsRo['GridRun']['SimBalBase']['html'] = stdFieldRow(_("Paper wallet base (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getSimBalBase()), ENT_QUOTES), 'SimBalBase_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SimBalBase', $dataObj->getSimBalBase(), "s='d'"), 'SimBalBase', "", $this->commentsSimBalBase, $this->commentsSimBalBase_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'SimBalQuote':
        $this->fieldsRo['GridRun']['SimBalQuote']['html'] = stdFieldRow(_("Paper wallet quote (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getSimBalQuote()), ENT_QUOTES), 'SimBalQuote_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SimBalQuote', $dataObj->getSimBalQuote(), "s='d'"), 'SimBalQuote', "", $this->commentsSimBalQuote, $this->commentsSimBalQuote_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'RunUid':
        $this->fieldsRo['GridRun']['RunUid']['html'] = stdFieldRow(_("Run UID"), div( htmlspecialchars((string)($dataObj->getRunUid()), ENT_QUOTES), 'RunUid_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RunUid', $dataObj->getRunUid(), "s='d'"), 'RunUid', "", $this->commentsRunUid, $this->commentsRunUid_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'AppliedGeometry':
        $this->fieldsRo['GridRun']['AppliedGeometry']['html'] = stdFieldRow(_("Applied geometry (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getAppliedGeometry()), ENT_QUOTES), 'AppliedGeometry_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AppliedGeometry', $dataObj->getAppliedGeometry(), "s='d'"), 'AppliedGeometry', "", $this->commentsAppliedGeometry, $this->commentsAppliedGeometry_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'LedgerResetAt':
        $this->fieldsRo['GridRun']['LedgerResetAt']['html'] = stdFieldRow(_("Ledger rebased at (daemon-managed)"), div( htmlspecialchars((string)($dataObj->getLedgerResetAt()), ENT_QUOTES), 'LedgerResetAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LedgerResetAt', $dataObj->getLedgerResetAt(), "s='d'"), 'LedgerResetAt', "", $this->commentsLedgerResetAt, $this->commentsLedgerResetAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }
    /**
     * function getBotOrderList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getBotOrderList(String $IdGridRun, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntGridRunChild' : $request['cui'];
        $_SESSION['mem']['GridRun']['child']['list']['active'] = 'BotOrder';

        if (method_exists($this, 'beforeChildListBotOrder')) {
            $this->beforeChildListBotOrder();
        }

        $svcCls = class_exists('\\App\\BotOrderServiceWrapper')
            ? '\\App\\BotOrderServiceWrapper'
            : '\\App\\BotOrderService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdGridRun);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

    /**
     * function getTradeCycleList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getTradeCycleList(String $IdGridRun, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntGridRunChild' : $request['cui'];
        $_SESSION['mem']['GridRun']['child']['list']['active'] = 'TradeCycle';

        if (method_exists($this, 'beforeChildListTradeCycle')) {
            $this->beforeChildListTradeCycle();
        }

        $svcCls = class_exists('\\App\\TradeCycleServiceWrapper')
            ? '\\App\\TradeCycleServiceWrapper'
            : '\\App\\TradeCycleService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdGridRun);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

    /**
     * function getBotEventList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getBotEventList(String $IdGridRun, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntGridRunChild' : $request['cui'];
        $_SESSION['mem']['GridRun']['child']['list']['active'] = 'BotEvent';

        if (method_exists($this, 'beforeChildListBotEvent')) {
            $this->beforeChildListBotEvent();
        }

        $svcCls = class_exists('\\App\\BotEventServiceWrapper')
            ? '\\App\\BotEventServiceWrapper'
            : '\\App\\BotEventService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdGridRun);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

    /**
     * function getBotCommandList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getBotCommandList(String $IdGridRun, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntGridRunChild' : $request['cui'];
        $_SESSION['mem']['GridRun']['child']['list']['active'] = 'BotCommand';

        if (method_exists($this, 'beforeChildListBotCommand')) {
            $this->beforeChildListBotCommand();
        }

        $svcCls = class_exists('\\App\\BotCommandServiceWrapper')
            ? '\\App\\BotCommandServiceWrapper'
            : '\\App\\BotCommandService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdGridRun);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

    /**
     * function getGridRunAuditList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getGridRunAuditList(String $IdGridRun, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntGridRunChild' : $request['cui'];
        $_SESSION['mem']['GridRun']['child']['list']['active'] = 'GridRunAudit';

        if (method_exists($this, 'beforeChildListGridRunAudit')) {
            $this->beforeChildListGridRunAudit();
        }

        $svcCls = class_exists('\\App\\GridRunAuditServiceWrapper')
            ? '\\App\\GridRunAuditServiceWrapper'
            : '\\App\\GridRunAuditService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdGridRun);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

}
