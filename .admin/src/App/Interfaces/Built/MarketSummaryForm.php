<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'MarketSummary' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class MarketSummaryForm extends MarketSummary
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
        $this->model_name = 'MarketSummary';
        $this->virtualClassName = 'MarketSummary';
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

        $q = new MarketSummaryQuery();
        $q = $this->setAclFilter($q);
        

        $q
            ;
        if(is_array( $this->searchMs )){
            # main search form
            
            
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
                                var __se=document.querySelector(\"#MarketSummaryListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
                $trHead = th(_("Symbol"), " th='sorted' c='Symbol' title='" . _('Symbol')."' ")
.th(_("Timeframe"), " th='sorted' c='Tf' title='" . _('Timeframe')."' ")
.th(_("Price"), " th='sorted' c='Price' title='" . _('Price')."' ")
.th(_("EMA20"), " th='sorted' c='Ema20' title='" . _('EMA20')."' ")
.th(_("EMA50"), " th='sorted' c='Ema50' title='" . _('EMA50')."' ")
.th(_("EMA200"), " th='sorted' c='Ema200' title='" . _('EMA200')."' ")
.th(_("RSI14"), " th='sorted' c='Rsi14' title='" . _('RSI14')."' ")
.th(_("ATR14"), " th='sorted' c='Atr14' title='" . _('ATR14')."' ")
.th(_("ATR %"), " th='sorted' c='AtrPct' title='" . _('ATR %')."' ")
.th(_("Trend"), " th='sorted' c='Trend' title='" . _('Trend')."' ")
.th(_("Swing high"), " th='sorted' c='SwingHigh' title='" . _('Swing high')."' ")
.th(_("Swing low"), " th='sorted' c='SwingLow' title='" . _('Swing low')."' ")
.th(_("Candles"), " th='sorted' c='CandlesUsed' title='" . _('Candles')."' ")
.th(_("Funding rate"), " th='sorted' c='FundingRate' title='" . _('Funding rate')."' ")
.th(_("Depth imbalance"), " th='sorted' c='DepthImbalance' title='" . _('Depth imbalance')."' ")
.th(_("Depth imbalance (smoothed)"), " th='sorted' c='DepthImbalanceAvg' title='" . _('Depth imbalance (smoothed)')."' ")
.th(_("ADX14"), " th='sorted' c='Adx14' title='" . _('ADX14')."' ")
.th(_("ATR% percentile"), " th='sorted' c='AtrPctRank' title='" . _('ATR% percentile')."' ")
.th(_("Taker buy ratio"), " th='sorted' c='TakerBuyRatio' title='" . _('Taker buy ratio')."' ")
.th(_("Volume z-score"), " th='sorted' c='VolZscore' title='" . _('Volume z-score')."' ")
.th(_("Efficiency ratio"), " th='sorted' c='Er20' title='" . _('Efficiency ratio')."' ")
.th(_("Choppiness"), " th='sorted' c='Chop14' title='" . _('Choppiness')."' ")
.th(_("Funding 30d percentile"), " th='sorted' c='FundingPct' title='" . _('Funding 30d percentile')."' ")
.th(_("Computed at"), " th='sorted' c='ComputedAt' title='" . _('Computed at')."' ")
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
                 if($_SESSION[_AUTH_VAR]->hasRights('MarketSummary', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addMarketSummary' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'MarketSummary';
        $altValue = array (
  'IdMarketSummary' => NULL,
  'Symbol' => NULL,
  'Tf' => NULL,
  'Price' => NULL,
  'Ema20' => NULL,
  'Ema50' => NULL,
  'Ema200' => NULL,
  'Rsi14' => NULL,
  'Atr14' => NULL,
  'AtrPct' => NULL,
  'Trend' => NULL,
  'SwingHigh' => NULL,
  'SwingLow' => NULL,
  'CandlesUsed' => NULL,
  'RecentCandles' => NULL,
  'FundingRate' => NULL,
  'DepthImbalance' => NULL,
  'DepthImbalanceAvg' => NULL,
  'Adx14' => NULL,
  'AtrPctRank' => NULL,
  'TakerBuyRatio' => NULL,
  'VolZscore' => NULL,
  'Er20' => NULL,
  'Chop14' => NULL,
  'FundingPct' => NULL,
  'ComputedAt' => NULL,
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
            $this->isChild = 'MarketSummary';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'MarketSummary/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'MarketSummary/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['MarketSummary/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'MarketSummary/');

        
        
        $default_order[]['ComputedAt']='DESC';
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
                if($_SESSION[_AUTH_VAR]->hasRights('MarketSummary', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteMarketSummary' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol()));
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
   div('' . span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." ", "   i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editMarketSummary'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Tf'] !== null ) ? $altValue['Tf'] : $data->getTf())))." ", "   i='" . $__pkJsonEsc . "' c='Tf' class=''  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Price'] !== null ) ? $altValue['Price'] : str_replace(',', '.', (string)($data->getPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Price' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Ema20'] !== null ) ? $altValue['Ema20'] : str_replace(',', '.', (string)($data->getEma20() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Ema20' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Ema50'] !== null ) ? $altValue['Ema50'] : str_replace(',', '.', (string)($data->getEma50() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Ema50' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Ema200'] !== null ) ? $altValue['Ema200'] : str_replace(',', '.', (string)($data->getEma200() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Ema200' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Rsi14'] !== null ) ? $altValue['Rsi14'] : str_replace(',', '.', (string)($data->getRsi14() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Rsi14' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Atr14'] !== null ) ? $altValue['Atr14'] : str_replace(',', '.', (string)($data->getAtr14() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Atr14' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['AtrPct'] !== null ) ? $altValue['AtrPct'] : str_replace(',', '.', (string)($data->getAtrPct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='AtrPct' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Trend'] !== null ) ? $altValue['Trend'] : isntPo($data->getTrend()))))." ", "   i='" . $__pkJsonEsc . "' c='Trend' class='center'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['SwingHigh'] !== null ) ? $altValue['SwingHigh'] : str_replace(',', '.', (string)($data->getSwingHigh() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='SwingHigh' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['SwingLow'] !== null ) ? $altValue['SwingLow'] : str_replace(',', '.', (string)($data->getSwingLow() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='SwingLow' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['CandlesUsed'] !== null ) ? $altValue['CandlesUsed'] : $data->getCandlesUsed())))." ", "   i='" . $__pkJsonEsc . "' c='CandlesUsed' class=''  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['FundingRate'] !== null ) ? $altValue['FundingRate'] : str_replace(',', '.', (string)($data->getFundingRate() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FundingRate' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['DepthImbalance'] !== null ) ? $altValue['DepthImbalance'] : str_replace(',', '.', (string)($data->getDepthImbalance() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='DepthImbalance' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['DepthImbalanceAvg'] !== null ) ? $altValue['DepthImbalanceAvg'] : str_replace(',', '.', (string)($data->getDepthImbalanceAvg() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='DepthImbalanceAvg' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Adx14'] !== null ) ? $altValue['Adx14'] : str_replace(',', '.', (string)($data->getAdx14() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Adx14' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['AtrPctRank'] !== null ) ? $altValue['AtrPctRank'] : str_replace(',', '.', (string)($data->getAtrPctRank() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='AtrPctRank' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['TakerBuyRatio'] !== null ) ? $altValue['TakerBuyRatio'] : str_replace(',', '.', (string)($data->getTakerBuyRatio() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='TakerBuyRatio' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['VolZscore'] !== null ) ? $altValue['VolZscore'] : str_replace(',', '.', (string)($data->getVolZscore() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='VolZscore' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Er20'] !== null ) ? $altValue['Er20'] : str_replace(',', '.', (string)($data->getEr20() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Er20' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['Chop14'] !== null ) ? $altValue['Chop14'] : str_replace(',', '.', (string)($data->getChop14() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Chop14' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['FundingPct'] !== null ) ? $altValue['FundingPct'] : str_replace(',', '.', (string)($data->getFundingPct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FundingPct' class='right'  j='editMarketSummary'") . span(htmlspecialchars((string)((($altValue['ComputedAt'] !== null ) ? $altValue['ComputedAt'] : $data->getComputedAt())))." ", "   i='" . $__pkJsonEsc . "' c='ComputedAt' class=''  j='editMarketSummary'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='MarketSummaryRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." "), "  i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Tf'] !== null ) ? $altValue['Tf'] : $data->getTf())))." "), "  i='" . $__pkJsonEsc . "' c='Tf' class=''  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Price'] !== null ) ? $altValue['Price'] : str_replace(',', '.', (string)($data->getPrice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Price' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Ema20'] !== null ) ? $altValue['Ema20'] : str_replace(',', '.', (string)($data->getEma20() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Ema20' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Ema50'] !== null ) ? $altValue['Ema50'] : str_replace(',', '.', (string)($data->getEma50() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Ema50' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Ema200'] !== null ) ? $altValue['Ema200'] : str_replace(',', '.', (string)($data->getEma200() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Ema200' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Rsi14'] !== null ) ? $altValue['Rsi14'] : str_replace(',', '.', (string)($data->getRsi14() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Rsi14' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Atr14'] !== null ) ? $altValue['Atr14'] : str_replace(',', '.', (string)($data->getAtr14() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Atr14' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['AtrPct'] !== null ) ? $altValue['AtrPct'] : str_replace(',', '.', (string)($data->getAtrPct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='AtrPct' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Trend'] !== null ) ? $altValue['Trend'] : isntPo($data->getTrend()))))." "), "  i='" . $__pkJsonEsc . "' c='Trend' class='center'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['SwingHigh'] !== null ) ? $altValue['SwingHigh'] : str_replace(',', '.', (string)($data->getSwingHigh() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='SwingHigh' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['SwingLow'] !== null ) ? $altValue['SwingLow'] : str_replace(',', '.', (string)($data->getSwingLow() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='SwingLow' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['CandlesUsed'] !== null ) ? $altValue['CandlesUsed'] : $data->getCandlesUsed())))." "), "  i='" . $__pkJsonEsc . "' c='CandlesUsed' class=''  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['FundingRate'] !== null ) ? $altValue['FundingRate'] : str_replace(',', '.', (string)($data->getFundingRate() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FundingRate' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['DepthImbalance'] !== null ) ? $altValue['DepthImbalance'] : str_replace(',', '.', (string)($data->getDepthImbalance() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='DepthImbalance' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['DepthImbalanceAvg'] !== null ) ? $altValue['DepthImbalanceAvg'] : str_replace(',', '.', (string)($data->getDepthImbalanceAvg() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='DepthImbalanceAvg' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Adx14'] !== null ) ? $altValue['Adx14'] : str_replace(',', '.', (string)($data->getAdx14() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Adx14' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['AtrPctRank'] !== null ) ? $altValue['AtrPctRank'] : str_replace(',', '.', (string)($data->getAtrPctRank() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='AtrPctRank' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['TakerBuyRatio'] !== null ) ? $altValue['TakerBuyRatio'] : str_replace(',', '.', (string)($data->getTakerBuyRatio() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='TakerBuyRatio' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['VolZscore'] !== null ) ? $altValue['VolZscore'] : str_replace(',', '.', (string)($data->getVolZscore() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='VolZscore' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Er20'] !== null ) ? $altValue['Er20'] : str_replace(',', '.', (string)($data->getEr20() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Er20' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['Chop14'] !== null ) ? $altValue['Chop14'] : str_replace(',', '.', (string)($data->getChop14() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Chop14' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['FundingPct'] !== null ) ? $altValue['FundingPct'] : str_replace(',', '.', (string)($data->getFundingPct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FundingPct' class='right'  j='editMarketSummary'") .
                td(span(htmlspecialchars((string)((($altValue['ComputedAt'] !== null ) ? $altValue['ComputedAt'] : $data->getComputedAt())))." "), "  i='" . $__pkJsonEsc . "' c='ComputedAt' class=''  j='editMarketSummary'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='MarketSummaryDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountMarketSummary', $i);
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
                .div($controlsContent,'MarketSummaryControlsList', "class='custom-controls'")
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
                        .span(_('Market Data'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('MarketSummary', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Symbol"), " type='button' th='sorted' c='Symbol' class='va-mob-sortrow' ") . button(_("Timeframe"), " type='button' th='sorted' c='Tf' class='va-mob-sortrow' ") . button(_("Price"), " type='button' th='sorted' c='Price' class='va-mob-sortrow' ") . button(_("EMA20"), " type='button' th='sorted' c='Ema20' class='va-mob-sortrow' ") . button(_("EMA50"), " type='button' th='sorted' c='Ema50' class='va-mob-sortrow' ") . button(_("EMA200"), " type='button' th='sorted' c='Ema200' class='va-mob-sortrow' ") . button(_("RSI14"), " type='button' th='sorted' c='Rsi14' class='va-mob-sortrow' ") . button(_("ATR14"), " type='button' th='sorted' c='Atr14' class='va-mob-sortrow' ") . button(_("ATR %"), " type='button' th='sorted' c='AtrPct' class='va-mob-sortrow' ") . button(_("Trend"), " type='button' th='sorted' c='Trend' class='va-mob-sortrow' ") . button(_("Swing high"), " type='button' th='sorted' c='SwingHigh' class='va-mob-sortrow' ") . button(_("Swing low"), " type='button' th='sorted' c='SwingLow' class='va-mob-sortrow' ") . button(_("Candles"), " type='button' th='sorted' c='CandlesUsed' class='va-mob-sortrow' ") . button(_("Funding rate"), " type='button' th='sorted' c='FundingRate' class='va-mob-sortrow' ") . button(_("Depth imbalance"), " type='button' th='sorted' c='DepthImbalance' class='va-mob-sortrow' ") . button(_("Depth imbalance (smoothed)"), " type='button' th='sorted' c='DepthImbalanceAvg' class='va-mob-sortrow' ") . button(_("ADX14"), " type='button' th='sorted' c='Adx14' class='va-mob-sortrow' ") . button(_("ATR% percentile"), " type='button' th='sorted' c='AtrPctRank' class='va-mob-sortrow' ") . button(_("Taker buy ratio"), " type='button' th='sorted' c='TakerBuyRatio' class='va-mob-sortrow' ") . button(_("Volume z-score"), " type='button' th='sorted' c='VolZscore' class='va-mob-sortrow' ") . button(_("Efficiency ratio"), " type='button' th='sorted' c='Er20' class='va-mob-sortrow' ") . button(_("Choppiness"), " type='button' th='sorted' c='Chop14' class='va-mob-sortrow' ") . button(_("Funding 30d percentile"), " type='button' th='sorted' c='FundingPct' class='va-mob-sortrow' ") . button(_("Computed at"), " type='button' th='sorted' c='ComputedAt' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='MarketSummaryTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'MarketSummaryListForm', " class='va-mob proto-app' data-model='MarketSummary' data-table='MarketSummary' data-ui='".$this->uiTabsId."' ");

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addMarketSummaryAutoc');
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
    public function setCreateDefaultsMarketSummary(array $data): MarketSummary
    {

        unset($data['IdMarketSummary']);
        $e = new MarketSummary();


        if( $data['Trend'] == '' )unset($data['Trend']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //integer not required
        $e->setPrice( ($data['Price'] == '' ) ? null : $data['Price']);
        //integer not required
        $e->setEma20( ($data['Ema20'] == '' ) ? null : $data['Ema20']);
        //integer not required
        $e->setEma50( ($data['Ema50'] == '' ) ? null : $data['Ema50']);
        //integer not required
        $e->setEma200( ($data['Ema200'] == '' ) ? null : $data['Ema200']);
        //integer not required
        $e->setRsi14( ($data['Rsi14'] == '' ) ? null : $data['Rsi14']);
        //integer not required
        $e->setAtr14( ($data['Atr14'] == '' ) ? null : $data['Atr14']);
        //integer not required
        $e->setAtrPct( ($data['AtrPct'] == '' ) ? null : $data['AtrPct']);
        $e->setTrend(($data['Trend'] == '' ) ? null : $data['Trend']);
        //integer not required
        $e->setSwingHigh( ($data['SwingHigh'] == '' ) ? null : $data['SwingHigh']);
        //integer not required
        $e->setSwingLow( ($data['SwingLow'] == '' ) ? null : $data['SwingLow']);
        //integer not required
        $e->setRecentCandles( ($data['RecentCandles'] == '' ) ? null : $data['RecentCandles']);
        //integer not required
        $e->setFundingRate( ($data['FundingRate'] == '' ) ? null : $data['FundingRate']);
        //integer not required
        $e->setDepthImbalance( ($data['DepthImbalance'] == '' ) ? null : $data['DepthImbalance']);
        //integer not required
        $e->setDepthImbalanceAvg( ($data['DepthImbalanceAvg'] == '' ) ? null : $data['DepthImbalanceAvg']);
        //integer not required
        $e->setAdx14( ($data['Adx14'] == '' ) ? null : $data['Adx14']);
        //integer not required
        $e->setAtrPctRank( ($data['AtrPctRank'] == '' ) ? null : $data['AtrPctRank']);
        //integer not required
        $e->setTakerBuyRatio( ($data['TakerBuyRatio'] == '' ) ? null : $data['TakerBuyRatio']);
        //integer not required
        $e->setVolZscore( ($data['VolZscore'] == '' ) ? null : $data['VolZscore']);
        //integer not required
        $e->setEr20( ($data['Er20'] == '' ) ? null : $data['Er20']);
        //integer not required
        $e->setChop14( ($data['Chop14'] == '' ) ? null : $data['Chop14']);
        //integer not required
        $e->setFundingPct( ($data['FundingPct'] == '' ) ? null : $data['FundingPct']);
        $e->setComputedAt( ($data['ComputedAt'] == '' || $data['ComputedAt'] == 'null' || substr($data['ComputedAt'],0,10) == '-0001-11-30') ? null : $data['ComputedAt'] );
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
    public function setUpdateDefaultsMarketSummary(array $data): ?MarketSummary
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(MarketSummaryQuery::class, json_decode($data['i']), 'MarketSummary', 'w');
        if ($e === null) { return null; }


        if( $data['Trend'] == '' )unset($data['Trend']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant','IdAuthy'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['Price'])){
            $e->setPrice( ($data['Price'] == '' ) ? null : $data['Price']);
        }
        if(isset($data['Ema20'])){
            $e->setEma20( ($data['Ema20'] == '' ) ? null : $data['Ema20']);
        }
        if(isset($data['Ema50'])){
            $e->setEma50( ($data['Ema50'] == '' ) ? null : $data['Ema50']);
        }
        if(isset($data['Ema200'])){
            $e->setEma200( ($data['Ema200'] == '' ) ? null : $data['Ema200']);
        }
        if(isset($data['Rsi14'])){
            $e->setRsi14( ($data['Rsi14'] == '' ) ? null : $data['Rsi14']);
        }
        if(isset($data['Atr14'])){
            $e->setAtr14( ($data['Atr14'] == '' ) ? null : $data['Atr14']);
        }
        if(isset($data['AtrPct'])){
            $e->setAtrPct( ($data['AtrPct'] == '' ) ? null : $data['AtrPct']);
        }
        if(isset($data['Trend'])){
            $e->setTrend(($data['Trend'] == '' ) ? null : $data['Trend']);
        }
        if(isset($data['SwingHigh'])){
            $e->setSwingHigh( ($data['SwingHigh'] == '' ) ? null : $data['SwingHigh']);
        }
        if(isset($data['SwingLow'])){
            $e->setSwingLow( ($data['SwingLow'] == '' ) ? null : $data['SwingLow']);
        }
        if(isset($data['RecentCandles'])){
            $e->setRecentCandles( ($data['RecentCandles'] == '' ) ? null : $data['RecentCandles']);
        }
        if(isset($data['FundingRate'])){
            $e->setFundingRate( ($data['FundingRate'] == '' ) ? null : $data['FundingRate']);
        }
        if(isset($data['DepthImbalance'])){
            $e->setDepthImbalance( ($data['DepthImbalance'] == '' ) ? null : $data['DepthImbalance']);
        }
        if(isset($data['DepthImbalanceAvg'])){
            $e->setDepthImbalanceAvg( ($data['DepthImbalanceAvg'] == '' ) ? null : $data['DepthImbalanceAvg']);
        }
        if(isset($data['Adx14'])){
            $e->setAdx14( ($data['Adx14'] == '' ) ? null : $data['Adx14']);
        }
        if(isset($data['AtrPctRank'])){
            $e->setAtrPctRank( ($data['AtrPctRank'] == '' ) ? null : $data['AtrPctRank']);
        }
        if(isset($data['TakerBuyRatio'])){
            $e->setTakerBuyRatio( ($data['TakerBuyRatio'] == '' ) ? null : $data['TakerBuyRatio']);
        }
        if(isset($data['VolZscore'])){
            $e->setVolZscore( ($data['VolZscore'] == '' ) ? null : $data['VolZscore']);
        }
        if(isset($data['Er20'])){
            $e->setEr20( ($data['Er20'] == '' ) ? null : $data['Er20']);
        }
        if(isset($data['Chop14'])){
            $e->setChop14( ($data['Chop14'] == '' ) ? null : $data['Chop14']);
        }
        if(isset($data['FundingPct'])){
            $e->setFundingPct( ($data['FundingPct'] == '' ) ? null : $data['FundingPct']);
        }
        if(isset($data['ComputedAt'])){
            $e->setComputedAt( ($data['ComputedAt'] == '' || $data['ComputedAt'] == 'null' || substr($data['ComputedAt'],0,10) == '-0001-11-30') ? null : $data['ComputedAt'] );
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
     * Produce a formated form of MarketSummary
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

        $je = "MarketSummaryTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('MarketSummary', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addMarketSummary' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = MarketSummaryQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('MarketSummary', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->MarketSummary['isNew'] = 'yes';
            $dataObj = new MarketSummary();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->MarketSummary['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;












$this->fields['MarketSummary']['Symbol']['html'] = stdFieldRow(_("Symbol"), input('text', 'Symbol', htmlentities((string)($dataObj->getSymbol() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Symbol'))."' size='35'  v='SYMBOL' s='d' class='req'  ")."", 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Tf']['html'] = stdFieldRow(_("Timeframe"), input('text', 'Tf', htmlentities((string)($dataObj->getTf() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Timeframe'))."' size='15'  v='TF' s='d' class='req'  ")."", 'Tf', "", $this->commentsTf, $this->commentsTf_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Price']['html'] = stdFieldRow(_("Price"), input('number', 'Price', $dataObj->getPrice(), "  placeholder='".str_replace("'","&#39;",_('Price'))."'  v='PRICE' size='10' s='d' class=''"), 'Price', "", $this->commentsPrice, $this->commentsPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Ema20']['html'] = stdFieldRow(_("EMA20"), input('number', 'Ema20', $dataObj->getEma20(), "  placeholder='".str_replace("'","&#39;",_('EMA20'))."'  v='EMA20' size='10' s='d' class=''"), 'Ema20', "", $this->commentsEma20, $this->commentsEma20_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Ema50']['html'] = stdFieldRow(_("EMA50"), input('number', 'Ema50', $dataObj->getEma50(), "  placeholder='".str_replace("'","&#39;",_('EMA50'))."'  v='EMA50' size='10' s='d' class=''"), 'Ema50', "", $this->commentsEma50, $this->commentsEma50_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Ema200']['html'] = stdFieldRow(_("EMA200"), input('number', 'Ema200', $dataObj->getEma200(), "  placeholder='".str_replace("'","&#39;",_('EMA200'))."'  v='EMA200' size='10' s='d' class=''"), 'Ema200', "", $this->commentsEma200, $this->commentsEma200_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Rsi14']['html'] = stdFieldRow(_("RSI14"), input('number', 'Rsi14', $dataObj->getRsi14(), "  placeholder='".str_replace("'","&#39;",_('RSI14'))."'  v='RSI14' size='5' s='d' class=''"), 'Rsi14', "", $this->commentsRsi14, $this->commentsRsi14_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Atr14']['html'] = stdFieldRow(_("ATR14"), input('number', 'Atr14', $dataObj->getAtr14(), "  placeholder='".str_replace("'","&#39;",_('ATR14'))."'  v='ATR14' size='10' s='d' class=''"), 'Atr14', "", $this->commentsAtr14, $this->commentsAtr14_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['AtrPct']['html'] = stdFieldRow(_("ATR %"), input('number', 'AtrPct', $dataObj->getAtrPct(), "  placeholder='".str_replace("'","&#39;",_('ATR %'))."'  v='ATR_PCT' size='5' s='d' class=''"), 'AtrPct', "", $this->commentsAtrPct, $this->commentsAtrPct_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Trend']['html'] = stdFieldRow(_("Trend"), selectboxCustomArray('Trend', [ '0' => ['0'=>_("strong_up"), '1'=>"strong_up"],'1' => ['0'=>_("up"), '1'=>"up"],'2' => ['0'=>_("sideways"), '1'=>"sideways"],'3' => ['0'=>_("down"), '1'=>"down"],'4' => ['0'=>_("strong_down"), '1'=>"strong_down"], ], _('Trend'), "s='d'  ", $dataObj->getTrend(), '', true), 'Trend', "", $this->commentsTrend, $this->commentsTrend_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['SwingHigh']['html'] = stdFieldRow(_("Swing high"), input('number', 'SwingHigh', $dataObj->getSwingHigh(), "  placeholder='".str_replace("'","&#39;",_('Swing high'))."'  v='SWING_HIGH' size='10' s='d' class=''"), 'SwingHigh', "", $this->commentsSwingHigh, $this->commentsSwingHigh_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['SwingLow']['html'] = stdFieldRow(_("Swing low"), input('number', 'SwingLow', $dataObj->getSwingLow(), "  placeholder='".str_replace("'","&#39;",_('Swing low'))."'  v='SWING_LOW' size='10' s='d' class=''"), 'SwingLow', "", $this->commentsSwingLow, $this->commentsSwingLow_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['CandlesUsed']['html'] = stdFieldRow(_("Candles"), input('number', 'CandlesUsed', $dataObj->getCandlesUsed(), " step='1' placeholder='".str_replace("'","&#39;",_('Candles'))."' v='CANDLES_USED' size='5' s='d' class=''"), 'CandlesUsed', "", $this->commentsCandlesUsed, $this->commentsCandlesUsed_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['RecentCandles']['html'] = stdFieldRow(_("Candles JSON"), textarea('RecentCandles', htmlentities((string)($dataObj->getRecentCandles() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Candles JSON'))."' cols='71' v='RECENT_CANDLES' s='d'  class=' ' style='' spellcheck='false'"), 'RecentCandles', "", $this->commentsRecentCandles, $this->commentsRecentCandles_css, '', ' ', 'no', 'v2');
$this->fields['MarketSummary']['FundingRate']['html'] = stdFieldRow(_("Funding rate"), input('number', 'FundingRate', $dataObj->getFundingRate(), "  placeholder='".str_replace("'","&#39;",_('Funding rate'))."'  v='FUNDING_RATE' size='10' s='d' class=''"), 'FundingRate', "", $this->commentsFundingRate, $this->commentsFundingRate_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['DepthImbalance']['html'] = stdFieldRow(_("Depth imbalance"), input('number', 'DepthImbalance', $dataObj->getDepthImbalance(), "  placeholder='".str_replace("'","&#39;",_('Depth imbalance'))."'  v='DEPTH_IMBALANCE' size='5' s='d' class=''"), 'DepthImbalance', "", $this->commentsDepthImbalance, $this->commentsDepthImbalance_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['DepthImbalanceAvg']['html'] = stdFieldRow(_("Depth imbalance (smoothed)"), input('number', 'DepthImbalanceAvg', $dataObj->getDepthImbalanceAvg(), "  placeholder='".str_replace("'","&#39;",_('Depth imbalance (smoothed)'))."'  v='DEPTH_IMBALANCE_AVG' size='5' s='d' class=''"), 'DepthImbalanceAvg', "", $this->commentsDepthImbalanceAvg, $this->commentsDepthImbalanceAvg_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Adx14']['html'] = stdFieldRow(_("ADX14"), input('number', 'Adx14', $dataObj->getAdx14(), "  placeholder='".str_replace("'","&#39;",_('ADX14'))."'  v='ADX14' size='5' s='d' class=''"), 'Adx14', "", $this->commentsAdx14, $this->commentsAdx14_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['AtrPctRank']['html'] = stdFieldRow(_("ATR% percentile"), input('number', 'AtrPctRank', $dataObj->getAtrPctRank(), "  placeholder='".str_replace("'","&#39;",_('ATR% percentile'))."'  v='ATR_PCT_RANK' size='5' s='d' class=''"), 'AtrPctRank', "", $this->commentsAtrPctRank, $this->commentsAtrPctRank_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['TakerBuyRatio']['html'] = stdFieldRow(_("Taker buy ratio"), input('number', 'TakerBuyRatio', $dataObj->getTakerBuyRatio(), "  placeholder='".str_replace("'","&#39;",_('Taker buy ratio'))."'  v='TAKER_BUY_RATIO' size='5' s='d' class=''"), 'TakerBuyRatio', "", $this->commentsTakerBuyRatio, $this->commentsTakerBuyRatio_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['VolZscore']['html'] = stdFieldRow(_("Volume z-score"), input('number', 'VolZscore', $dataObj->getVolZscore(), "  placeholder='".str_replace("'","&#39;",_('Volume z-score'))."'  v='VOL_ZSCORE' size='5' s='d' class=''"), 'VolZscore', "", $this->commentsVolZscore, $this->commentsVolZscore_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Er20']['html'] = stdFieldRow(_("Efficiency ratio"), input('number', 'Er20', $dataObj->getEr20(), "  placeholder='".str_replace("'","&#39;",_('Efficiency ratio'))."'  v='ER20' size='5' s='d' class=''"), 'Er20', "", $this->commentsEr20, $this->commentsEr20_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['Chop14']['html'] = stdFieldRow(_("Choppiness"), input('number', 'Chop14', $dataObj->getChop14(), "  placeholder='".str_replace("'","&#39;",_('Choppiness'))."'  v='CHOP14' size='5' s='d' class=''"), 'Chop14', "", $this->commentsChop14, $this->commentsChop14_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['FundingPct']['html'] = stdFieldRow(_("Funding 30d percentile"), input('number', 'FundingPct', $dataObj->getFundingPct(), "  placeholder='".str_replace("'","&#39;",_('Funding 30d percentile'))."'  v='FUNDING_PCT' size='5' s='d' class=''"), 'FundingPct', "", $this->commentsFundingPct, $this->commentsFundingPct_css, ' half', ' ', 'no', 'v2');
$this->fields['MarketSummary']['ComputedAt']['html'] = stdFieldRow(_("Computed at"), input('datetime-local', 'ComputedAt', $dataObj->getComputedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Computed at'"), 'ComputedAt', "", $this->commentsComputedAt, $this->commentsComputedAt_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'Symbol',1=>'Tf',2=>'Price',3=>'Ema20',4=>'Ema50',5=>'Ema200',6=>'Rsi14',7=>'Atr14',8=>'AtrPct',9=>'Trend',10=>'SwingHigh',11=>'SwingLow',12=>'CandlesUsed',13=>'RecentCandles',14=>'ComputedAt',15=>'DepthImbalanceAvg',16=>'Adx14',17=>'AtrPctRank',18=>'TakerBuyRatio',19=>'VolZscore',20=>'Er20',21=>'Chop14',22=>'FundingPct',23=>'IdCreation',24=>'IdModification',25=>'IdGroupCreation',), $dataObj);

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
            if( ($_SESSION[_AUTH_VAR]->hasRights('MarketSummary','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('MarketSummary','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveMarketSummary', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedMarketSummary','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdMarketSummary', $dataObj->getIdMarketSummary(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Market Data'), _SITE_URL.'MarketSummary', "class='nav-btn'")
                        .div(
                            span(_('Market Data'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'MarketSummary', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['MarketSummary']['Symbol']['html']
.$this->fields['MarketSummary']['Tf']['html']
.$this->fields['MarketSummary']['Price']['html']
.$this->fields['MarketSummary']['Ema20']['html']
.$this->fields['MarketSummary']['Ema50']['html']
.$this->fields['MarketSummary']['Ema200']['html']
.$this->fields['MarketSummary']['Rsi14']['html']
.$this->fields['MarketSummary']['Atr14']['html']
.$this->fields['MarketSummary']['AtrPct']['html']
.$this->fields['MarketSummary']['Trend']['html']
.$this->fields['MarketSummary']['SwingHigh']['html']
.$this->fields['MarketSummary']['SwingLow']['html']
.$this->fields['MarketSummary']['CandlesUsed']['html']
.$this->fields['MarketSummary']['RecentCandles']['html']
.$this->fields['MarketSummary']['FundingRate']['html']
.$this->fields['MarketSummary']['DepthImbalance']['html']
.$this->fields['MarketSummary']['DepthImbalanceAvg']['html']
.$this->fields['MarketSummary']['Adx14']['html']
.$this->fields['MarketSummary']['AtrPctRank']['html']
.$this->fields['MarketSummary']['TakerBuyRatio']['html']
.$this->fields['MarketSummary']['VolZscore']['html']
.$this->fields['MarketSummary']['Er20']['html']
.$this->fields['MarketSummary']['Chop14']['html']
.$this->fields['MarketSummary']['FundingPct']['html']
.$this->fields['MarketSummary']['ComputedAt']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntMarketSummary", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formMarketSummary' class='mainForm formContent' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['MarketSummary']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['MarketSummary']['ixmemautocapp'];
            unset($_SESSION['mem']['MarketSummary']['ixmemautocapp']);
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

        $this->fieldsRo['MarketSummary']['Symbol']['html'] = stdFieldRow(_("Symbol"), div( htmlspecialchars((string)($dataObj->getSymbol()), ENT_QUOTES), 'Symbol_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Symbol', $dataObj->getSymbol(), "s='d'"), 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Tf']['html'] = stdFieldRow(_("Timeframe"), div( htmlspecialchars((string)($dataObj->getTf()), ENT_QUOTES), 'Tf_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Tf', $dataObj->getTf(), "s='d'"), 'Tf', "", $this->commentsTf, $this->commentsTf_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Price']['html'] = stdFieldRow(_("Price"), div( htmlspecialchars((string)($dataObj->getPrice()), ENT_QUOTES), 'Price_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Price', $dataObj->getPrice(), "s='d'"), 'Price', "", $this->commentsPrice, $this->commentsPrice_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Ema20']['html'] = stdFieldRow(_("EMA20"), div( htmlspecialchars((string)($dataObj->getEma20()), ENT_QUOTES), 'Ema20_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ema20', $dataObj->getEma20(), "s='d'"), 'Ema20', "", $this->commentsEma20, $this->commentsEma20_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Ema50']['html'] = stdFieldRow(_("EMA50"), div( htmlspecialchars((string)($dataObj->getEma50()), ENT_QUOTES), 'Ema50_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ema50', $dataObj->getEma50(), "s='d'"), 'Ema50', "", $this->commentsEma50, $this->commentsEma50_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Ema200']['html'] = stdFieldRow(_("EMA200"), div( htmlspecialchars((string)($dataObj->getEma200()), ENT_QUOTES), 'Ema200_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Ema200', $dataObj->getEma200(), "s='d'"), 'Ema200', "", $this->commentsEma200, $this->commentsEma200_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Rsi14']['html'] = stdFieldRow(_("RSI14"), div( htmlspecialchars((string)($dataObj->getRsi14()), ENT_QUOTES), 'Rsi14_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Rsi14', $dataObj->getRsi14(), "s='d'"), 'Rsi14', "", $this->commentsRsi14, $this->commentsRsi14_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Atr14']['html'] = stdFieldRow(_("ATR14"), div( htmlspecialchars((string)($dataObj->getAtr14()), ENT_QUOTES), 'Atr14_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Atr14', $dataObj->getAtr14(), "s='d'"), 'Atr14', "", $this->commentsAtr14, $this->commentsAtr14_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['AtrPct']['html'] = stdFieldRow(_("ATR %"), div( htmlspecialchars((string)($dataObj->getAtrPct()), ENT_QUOTES), 'AtrPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AtrPct', $dataObj->getAtrPct(), "s='d'"), 'AtrPct', "", $this->commentsAtrPct, $this->commentsAtrPct_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Trend']['html'] = stdFieldRow(_("Trend"), div( htmlspecialchars((string)($dataObj->getTrend()), ENT_QUOTES), 'Trend_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Trend', $dataObj->getTrend(), "s='d'"), 'Trend', "", $this->commentsTrend, $this->commentsTrend_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['SwingHigh']['html'] = stdFieldRow(_("Swing high"), div( htmlspecialchars((string)($dataObj->getSwingHigh()), ENT_QUOTES), 'SwingHigh_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SwingHigh', $dataObj->getSwingHigh(), "s='d'"), 'SwingHigh', "", $this->commentsSwingHigh, $this->commentsSwingHigh_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['SwingLow']['html'] = stdFieldRow(_("Swing low"), div( htmlspecialchars((string)($dataObj->getSwingLow()), ENT_QUOTES), 'SwingLow_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SwingLow', $dataObj->getSwingLow(), "s='d'"), 'SwingLow', "", $this->commentsSwingLow, $this->commentsSwingLow_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['CandlesUsed']['html'] = stdFieldRow(_("Candles"), div( htmlspecialchars((string)($dataObj->getCandlesUsed()), ENT_QUOTES), 'CandlesUsed_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'CandlesUsed', $dataObj->getCandlesUsed(), "s='d'"), 'CandlesUsed', "", $this->commentsCandlesUsed, $this->commentsCandlesUsed_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['RecentCandles']['html'] = stdFieldRow(_("Candles JSON"), div( htmlspecialchars((string)($dataObj->getRecentCandles()), ENT_QUOTES), 'RecentCandles_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RecentCandles', $dataObj->getRecentCandles(), "s='d'"), 'RecentCandles', "", $this->commentsRecentCandles, $this->commentsRecentCandles_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['FundingRate']['html'] = stdFieldRow(_("Funding rate"), div( htmlspecialchars((string)($dataObj->getFundingRate()), ENT_QUOTES), 'FundingRate_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FundingRate', $dataObj->getFundingRate(), "s='d'"), 'FundingRate', "", $this->commentsFundingRate, $this->commentsFundingRate_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['DepthImbalance']['html'] = stdFieldRow(_("Depth imbalance"), div( htmlspecialchars((string)($dataObj->getDepthImbalance()), ENT_QUOTES), 'DepthImbalance_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DepthImbalance', $dataObj->getDepthImbalance(), "s='d'"), 'DepthImbalance', "", $this->commentsDepthImbalance, $this->commentsDepthImbalance_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['DepthImbalanceAvg']['html'] = stdFieldRow(_("Depth imbalance (smoothed)"), div( htmlspecialchars((string)($dataObj->getDepthImbalanceAvg()), ENT_QUOTES), 'DepthImbalanceAvg_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DepthImbalanceAvg', $dataObj->getDepthImbalanceAvg(), "s='d'"), 'DepthImbalanceAvg', "", $this->commentsDepthImbalanceAvg, $this->commentsDepthImbalanceAvg_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Adx14']['html'] = stdFieldRow(_("ADX14"), div( htmlspecialchars((string)($dataObj->getAdx14()), ENT_QUOTES), 'Adx14_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Adx14', $dataObj->getAdx14(), "s='d'"), 'Adx14', "", $this->commentsAdx14, $this->commentsAdx14_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['AtrPctRank']['html'] = stdFieldRow(_("ATR% percentile"), div( htmlspecialchars((string)($dataObj->getAtrPctRank()), ENT_QUOTES), 'AtrPctRank_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AtrPctRank', $dataObj->getAtrPctRank(), "s='d'"), 'AtrPctRank', "", $this->commentsAtrPctRank, $this->commentsAtrPctRank_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['TakerBuyRatio']['html'] = stdFieldRow(_("Taker buy ratio"), div( htmlspecialchars((string)($dataObj->getTakerBuyRatio()), ENT_QUOTES), 'TakerBuyRatio_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TakerBuyRatio', $dataObj->getTakerBuyRatio(), "s='d'"), 'TakerBuyRatio', "", $this->commentsTakerBuyRatio, $this->commentsTakerBuyRatio_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['VolZscore']['html'] = stdFieldRow(_("Volume z-score"), div( htmlspecialchars((string)($dataObj->getVolZscore()), ENT_QUOTES), 'VolZscore_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'VolZscore', $dataObj->getVolZscore(), "s='d'"), 'VolZscore', "", $this->commentsVolZscore, $this->commentsVolZscore_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Er20']['html'] = stdFieldRow(_("Efficiency ratio"), div( htmlspecialchars((string)($dataObj->getEr20()), ENT_QUOTES), 'Er20_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Er20', $dataObj->getEr20(), "s='d'"), 'Er20', "", $this->commentsEr20, $this->commentsEr20_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['Chop14']['html'] = stdFieldRow(_("Choppiness"), div( htmlspecialchars((string)($dataObj->getChop14()), ENT_QUOTES), 'Chop14_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Chop14', $dataObj->getChop14(), "s='d'"), 'Chop14', "", $this->commentsChop14, $this->commentsChop14_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['FundingPct']['html'] = stdFieldRow(_("Funding 30d percentile"), div( htmlspecialchars((string)($dataObj->getFundingPct()), ENT_QUOTES), 'FundingPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FundingPct', $dataObj->getFundingPct(), "s='d'"), 'FundingPct', "", $this->commentsFundingPct, $this->commentsFundingPct_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['MarketSummary']['ComputedAt']['html'] = stdFieldRow(_("Computed at"), div( htmlspecialchars((string)($dataObj->getComputedAt()), ENT_QUOTES), 'ComputedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ComputedAt', $dataObj->getComputedAt(), "s='d'"), 'ComputedAt', "", $this->commentsComputedAt, $this->commentsComputedAt_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['MarketSummary'] as $field => $ar) {
                $this->fields['MarketSummary'][$field]['html'] = $this->fieldsRo['MarketSummary'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['MarketSummary'][$field]['html'] = $this->fieldsRo['MarketSummary'][$field]['html'];
            }
        }
    }
}
