<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'TradeCycle' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class TradeCycleForm extends TradeCycle
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
    public $commentsIdTradeCycle;
    public $commentsIdTradeCycle_css;
    public $commentsIdGridRun;
    public $commentsIdGridRun_css;
    public $commentsLevelIdx;
    public $commentsLevelIdx_css;
    public $commentsBuyPrice;
    public $commentsBuyPrice_css;
    public $commentsSellPrice;
    public $commentsSellPrice_css;
    public $commentsQty;
    public $commentsQty_css;
    public $commentsRealizedPnl;
    public $commentsRealizedPnl_css;
    public $commentsFeesTotal;
    public $commentsFeesTotal_css;
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
    public $TradeCycle;


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
        $this->model_name = 'TradeCycle';
        $this->virtualClassName = 'TradeCycle';
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

        $q = new TradeCycleQuery();
        $q = $this->setAclFilter($q);


        $q

                #required trade_cycle
                ->leftJoinWith('GridRun');
        if(is_array( $this->searchMs )){
            # main search form

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
                                    . ' on TradeCycle — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['TradeCycle/'],
                                    $_SESSION['mem']['order']['TradeCycle/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#TradeCycleListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
.th(_("Level"), " th='sorted' c='LevelIdx' title='" . _('Level')."' ")
.th(_("Buy price"), " th='sorted' c='BuyPrice' title='" . _('Buy price')."' ")
.th(_("Sell price"), " th='sorted' c='SellPrice' title='" . _('Sell price')."' ")
.th(_("Qty"), " th='sorted' c='Qty' title='" . _('Qty')."' ")
.th(_("Realized PnL"), " th='sorted' c='RealizedPnl' title='" . _('Realized PnL')."' ")
.th(_("Fees"), " th='sorted' c='FeesTotal' title='" . _('Fees')."' ")
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
                .form($this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('TradeCycle')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(_('More filters'),''," class='va-fgroup-cap' ").div(
                        div(_('Simulated'),''," class='va-fblock-lbl' ")
                        .div(div(selectboxCustomArray('Simulated', [ '0' => ['0' => _('Yes'), '1' => 'yes'], '1' => ['0' => _('No'), '1' => 'no'] ], _('Simulated'), '  size="1" t="1"  ', $this->searchMs['Simulated'] ?? ''), '', 'class="ac-search-item" title="'._('Simulated').'"'),''," class='va-fselect' ")
                    ,''," class='va-fblock va-fblock-select' data-block='select' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('TradeCycle')."' hidden ").div(
                           button(span(_("Search")),'id="msTradeCycleBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msTradeCycleBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsTradeCycle' class='va-mob-searchform' data-entity='TradeCycle'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('TradeCycle', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addTradeCycle' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'TradeCycle';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdTradeCycle' => NULL,
  'IdGridRun' => NULL,
  'LevelIdx' => NULL,
  'BuyPrice' => NULL,
  'SellPrice' => NULL,
  'Qty' => NULL,
  'RealizedPnl' => NULL,
  'FeesTotal' => NULL,
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
            $this->isChild = 'TradeCycle';
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
        $gcListKey = 'TradeCycle/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'TradeCycle/child';
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
                if($_SESSION[_AUTH_VAR]->hasRights('TradeCycle', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteTradeCycle' ");
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
   div('' . (empty($this->IdParent) ? (span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." ", "   i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editTradeCycle'")) : (span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." ", "   i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editTradeCycle'"))) ,''," class='name' ")
   . div(''  . (empty($this->IdParent) ? (''  . span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." ", "   i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['BuyPrice'] !== null ) ? $altValue['BuyPrice'] : str_replace(',', '.', (string)($data->getBuyPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='BuyPrice' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['SellPrice'] !== null ) ? $altValue['SellPrice'] : str_replace(',', '.', (string)($data->getSellPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='SellPrice' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['RealizedPnl'] !== null ) ? $altValue['RealizedPnl'] : str_replace(',', '.', (string)($data->getRealizedPnl() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='RealizedPnl' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['FeesTotal'] !== null ) ? $altValue['FeesTotal'] : str_replace(',', '.', (string)($data->getFeesTotal() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FeesTotal' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editTradeCycle'")) : (''  . span(htmlspecialchars((string)((($altValue['BuyPrice'] !== null ) ? $altValue['BuyPrice'] : str_replace(',', '.', (string)($data->getBuyPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='BuyPrice' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['SellPrice'] !== null ) ? $altValue['SellPrice'] : str_replace(',', '.', (string)($data->getSellPrice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='SellPrice' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['RealizedPnl'] !== null ) ? $altValue['RealizedPnl'] : str_replace(',', '.', (string)($data->getRealizedPnl() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='RealizedPnl' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['FeesTotal'] !== null ) ? $altValue['FeesTotal'] : str_replace(',', '.', (string)($data->getFeesTotal() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='FeesTotal' class='right'  j='editTradeCycle'") . span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editTradeCycle'"))) . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='TradeCycleRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                    (empty($this->IdParent) ?
                td(span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." "), "  i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editTradeCycle'") : '') .
                td(span(htmlspecialchars((string)((($altValue['LevelIdx'] !== null ) ? $altValue['LevelIdx'] : $data->getLevelIdx())))." "), "  i='" . $__pkJsonEsc . "' c='LevelIdx' class=''  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['BuyPrice'] !== null ) ? $altValue['BuyPrice'] : str_replace(',', '.', (string)($data->getBuyPrice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='BuyPrice' class='right'  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['SellPrice'] !== null ) ? $altValue['SellPrice'] : str_replace(',', '.', (string)($data->getSellPrice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='SellPrice' class='right'  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['Qty'] !== null ) ? $altValue['Qty'] : str_replace(',', '.', (string)($data->getQty() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='Qty' class='right'  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['RealizedPnl'] !== null ) ? $altValue['RealizedPnl'] : str_replace(',', '.', (string)($data->getRealizedPnl() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='RealizedPnl' class='right'  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['FeesTotal'] !== null ) ? $altValue['FeesTotal'] : str_replace(',', '.', (string)($data->getFeesTotal() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='FeesTotal' class='right'  j='editTradeCycle'") .
                td(span(htmlspecialchars((string)((($altValue['Simulated'] !== null ) ? $altValue['Simulated'] : ($data->getSimulated() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Simulated' class=''  j='editTradeCycle'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='TradeCycleDtRow".$__pkEsc."'");

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
            $tr .= input('hidden', 'rowCountTradeCycle', $i);

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
                .div($controlsContent,'TradeCycleControlsList', "class='custom-controls'")
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
                        .span(_('Trade Cycle'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('TradeCycle', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . (empty($this->IdParent) ? button(_("Grid Run label"), " type='button' th='sorted' c='GridRun.Label' class='va-mob-sortrow' ") : '') . button(_("Level"), " type='button' th='sorted' c='LevelIdx' class='va-mob-sortrow' ") . button(_("Buy price"), " type='button' th='sorted' c='BuyPrice' class='va-mob-sortrow' ") . button(_("Sell price"), " type='button' th='sorted' c='SellPrice' class='va-mob-sortrow' ") . button(_("Qty"), " type='button' th='sorted' c='Qty' class='va-mob-sortrow' ") . button(_("Realized PnL"), " type='button' th='sorted' c='RealizedPnl' class='va-mob-sortrow' ") . button(_("Fees"), " type='button' th='sorted' c='FeesTotal' class='va-mob-sortrow' ") . button(_("Simulated"), " type='button' th='sorted' c='Simulated' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='TradeCycleTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'TradeCycleListForm', " class='va-mob proto-app' data-model='TradeCycle' data-table='TradeCycle' data-gc-db='trade_cycle' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='TradeCycle' data-parent='GridRun'" : ''));





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
            fetch(_SITE_URL + 'TradeCycle/summarycards', {
                method: 'GET', credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r){ return r.json(); }).then(function(res){
                if (res && res.status === 'success') { gcRenderSummary(res.data); }
            }).catch(function(){});
        })();



        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addTradeCycleAutoc');
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
    public function setCreateDefaultsTradeCycle(array $data): TradeCycle
    {

        unset($data['IdTradeCycle']);
        $e = new TradeCycle();


        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        if(isset($data['Simulated'])){
            $e->setSimulated( (!isset($data['Simulated']) || $data['Simulated'] == '') ? false : $data['Simulated']);
        }
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsTradeCycle(array $data): ?TradeCycle
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(TradeCycleQuery::class, json_decode($data['i']), 'TradeCycle', 'w');
        if ($e === null) { return null; }


        $data['Simulated'] = ($data['Simulated'] == '')?false:$data['Simulated'];
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of TradeCycle
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

        $je = "TradeCycleTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('TradeCycle', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addTradeCycleForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = TradeCycleQuery::create()

                #required trade_cycle
                ->leftJoinWith('GridRun')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('TradeCycle', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->TradeCycle['isNew'] = 'yes';
            $dataObj = new TradeCycle();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdGridRun";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->TradeCycle['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getGridRun())?'':$dataObj->setGridRun( new GridRun() );





        $SimulatedChecked = ($dataObj->getSimulated())?"checked='checked'":'';
                $Simulated = ($dataObj->getSimulated())?"true":"true";



$this->fields['TradeCycle']['IdGridRun']['html'] = stdFieldRow(_("Run"),
    input('text', 'IdGridRunAutoc', $dataObj->getGridRun()?->getLabel(), " title='".str_replace("'","", (string)($dataObj->getGridRun()?->getLabel()))."' v='ID_GRID_RUN' rid='IdGridRun' placeholder='"._('Run')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdGridRun&quot;,&quot;table&quot;:&quot;TradeCycle&quot;,&quot;childTable&quot;:&quot;GridRun&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;GridRun&quot;,&quot;show&quot;:[&quot;Label&quot;],&quot;id&quot;:&quot;IdGridRun&quot;,&quot;filter&quot;:&quot;Label&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, '', ' ', 'no', 'v2');
$this->fields['TradeCycle']['LevelIdx']['html'] = stdFieldRow(_("Level"), input('number', 'LevelIdx', $dataObj->getLevelIdx(), " step='1' placeholder='".str_replace("'","&#39;",_('Level'))."' v='LEVEL_IDX' size='5' s='d' class='req'"), 'LevelIdx', "", $this->commentsLevelIdx, $this->commentsLevelIdx_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['BuyPrice']['html'] = stdFieldRow(_("Buy price"), input('number', 'BuyPrice', $dataObj->getBuyPrice(), "  placeholder='".str_replace("'","&#39;",_('Buy price'))."'  v='BUY_PRICE' size='10' s='d' class='req'"), 'BuyPrice', "", $this->commentsBuyPrice, $this->commentsBuyPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['SellPrice']['html'] = stdFieldRow(_("Sell price"), input('number', 'SellPrice', $dataObj->getSellPrice(), "  placeholder='".str_replace("'","&#39;",_('Sell price'))."'  v='SELL_PRICE' size='10' s='d' class='req'"), 'SellPrice', "", $this->commentsSellPrice, $this->commentsSellPrice_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['Qty']['html'] = stdFieldRow(_("Qty"), input('number', 'Qty', $dataObj->getQty(), "  placeholder='".str_replace("'","&#39;",_('Qty'))."'  v='QTY' size='10' s='d' class='req'"), 'Qty', "", $this->commentsQty, $this->commentsQty_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['RealizedPnl']['html'] = stdFieldRow(_("Realized PnL"), input('number', 'RealizedPnl', $dataObj->getRealizedPnl(), "  placeholder='".str_replace("'","&#39;",_('Realized PnL'))."'  v='REALIZED_PNL' size='10' s='d' class='req'"), 'RealizedPnl', "", $this->commentsRealizedPnl, $this->commentsRealizedPnl_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['FeesTotal']['html'] = stdFieldRow(_("Fees"), input('number', 'FeesTotal', $dataObj->getFeesTotal(), "  placeholder='".str_replace("'","&#39;",_('Fees'))."'  v='FEES_TOTAL' size='10' s='d' class='req'"), 'FeesTotal', "", $this->commentsFeesTotal, $this->commentsFeesTotal_css, ' half', ' ', 'no', 'v2');
$this->fields['TradeCycle']['Simulated']['html'] = stdFieldRow(_("Simulated"), input('checkbox', 'Simulated', $Simulated, "$SimulatedChecked  size='10' s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, ' half', ' ', 'yes', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['TradeCycle']['IdGridRun']['html'] = input('hidden', 'IdGridRun', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('TradeCycle','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('TradeCycle','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveTradeCycle', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedTradeCycle','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdTradeCycle', $dataObj->getIdTradeCycle(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('TradeCycle: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Trade Cycle'), _SITE_URL.'TradeCycle', "class='nav-btn'")
                        .div(
                            span(_('Trade Cycle'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'TradeCycle', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['TradeCycle']['IdGridRun']['html']
.$this->fields['TradeCycle']['LevelIdx']['html']
.$this->fields['TradeCycle']['BuyPrice']['html']
.$this->fields['TradeCycle']['SellPrice']['html']
.$this->fields['TradeCycle']['Qty']['html']
.$this->fields['TradeCycle']['RealizedPnl']['html']
.$this->fields['TradeCycle']['FeesTotal']['html']
.$this->fields['TradeCycle']['Simulated']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntTradeCycle", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formTradeCycle' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['TradeCycle']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['TradeCycle'][$field]['html'] = $this->fieldsRo['TradeCycle'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'IdGridRun':
        $this->fieldsRo['TradeCycle']['IdGridRun']['html'] = stdFieldRow(_("Run"), div( htmlspecialchars((string)(($dataObj->getGridRun())?($dataObj->getGridRun()->getLabel()):''), ENT_QUOTES), 'IdGridRun_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'LevelIdx':
        $this->fieldsRo['TradeCycle']['LevelIdx']['html'] = stdFieldRow(_("Level"), div( htmlspecialchars((string)($dataObj->getLevelIdx()), ENT_QUOTES), 'LevelIdx_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LevelIdx', $dataObj->getLevelIdx(), "s='d'"), 'LevelIdx', "", $this->commentsLevelIdx, $this->commentsLevelIdx_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'BuyPrice':
        $this->fieldsRo['TradeCycle']['BuyPrice']['html'] = stdFieldRow(_("Buy price"), div( htmlspecialchars((string)($dataObj->getBuyPrice()), ENT_QUOTES), 'BuyPrice_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'BuyPrice', $dataObj->getBuyPrice(), "s='d'"), 'BuyPrice', "", $this->commentsBuyPrice, $this->commentsBuyPrice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'SellPrice':
        $this->fieldsRo['TradeCycle']['SellPrice']['html'] = stdFieldRow(_("Sell price"), div( htmlspecialchars((string)($dataObj->getSellPrice()), ENT_QUOTES), 'SellPrice_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'SellPrice', $dataObj->getSellPrice(), "s='d'"), 'SellPrice', "", $this->commentsSellPrice, $this->commentsSellPrice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Qty':
        $this->fieldsRo['TradeCycle']['Qty']['html'] = stdFieldRow(_("Qty"), div( htmlspecialchars((string)($dataObj->getQty()), ENT_QUOTES), 'Qty_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Qty', $dataObj->getQty(), "s='d'"), 'Qty', "", $this->commentsQty, $this->commentsQty_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'RealizedPnl':
        $this->fieldsRo['TradeCycle']['RealizedPnl']['html'] = stdFieldRow(_("Realized PnL"), div( htmlspecialchars((string)($dataObj->getRealizedPnl()), ENT_QUOTES), 'RealizedPnl_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RealizedPnl', $dataObj->getRealizedPnl(), "s='d'"), 'RealizedPnl', "", $this->commentsRealizedPnl, $this->commentsRealizedPnl_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'FeesTotal':
        $this->fieldsRo['TradeCycle']['FeesTotal']['html'] = stdFieldRow(_("Fees"), div( htmlspecialchars((string)($dataObj->getFeesTotal()), ENT_QUOTES), 'FeesTotal_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'FeesTotal', $dataObj->getFeesTotal(), "s='d'"), 'FeesTotal', "", $this->commentsFeesTotal, $this->commentsFeesTotal_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Simulated':
        $this->fieldsRo['TradeCycle']['Simulated']['html'] = stdFieldRow(_("Simulated"), div( htmlspecialchars((string)($dataObj->getSimulated()), ENT_QUOTES), 'Simulated_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Simulated', $dataObj->getSimulated(), "s='d'"), 'Simulated', "", $this->commentsSimulated, $this->commentsSimulated_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }

    /**
     * Query for TradeCycle_IdGridRun selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxTradeCycle_IdGridRun(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxTradeCycle_IdGridRun')
        && !method_exists($gcSbHost, 'selectboxDataTradeCycle_IdGridRun');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('grid_run', 'TradeCycle_IdGridRun', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
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
    if(method_exists($gcSbHost, 'beginSelectboxTradeCycle_IdGridRun') and $array)
        $ret = $gcSbHost->beginSelectboxTradeCycle_IdGridRun($q, $dataObj, $data, $obj);
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
            if(method_exists($gcSbHost, 'selectboxDataTradeCycle_IdGridRun')){
                $gcSbHost->selectboxDataTradeCycle_IdGridRun($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt );
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('grid_run', 'TradeCycle_IdGridRun', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
