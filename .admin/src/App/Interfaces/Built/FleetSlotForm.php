<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'FleetSlot' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class FleetSlotForm extends FleetSlot
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
    public $commentsIdFleetSlot;
    public $commentsIdFleetSlot_css;
    public $commentsSymbol;
    public $commentsSymbol_css;
    public $commentsAlgo;
    public $commentsAlgo_css;
    public $commentsTargetSlice;
    public $commentsTargetSlice_css;
    public $commentsEnabled;
    public $commentsEnabled_css;
    public $commentsState;
    public $commentsState_css;
    public $commentsConfirmUp;
    public $commentsConfirmUp_css;
    public $commentsConfirmDown;
    public $commentsConfirmDown_css;
    public $commentsLastVerdict;
    public $commentsLastVerdict_css;
    public $commentsVerdictAt;
    public $commentsVerdictAt_css;
    public $commentsEpisodeStartedAt;
    public $commentsEpisodeStartedAt_css;
    public $commentsActivation;
    public $commentsActivation_css;
    public $commentsIdGridRun;
    public $commentsIdGridRun_css;
    public $commentsLastEmptyAlertAt;
    public $commentsLastEmptyAlertAt_css;
    public $commentsLastParkedAlertAt;
    public $commentsLastParkedAlertAt_css;
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
    public $FleetSlot;


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
        $this->model_name = 'FleetSlot';
        $this->virtualClassName = 'FleetSlot';
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

        $q = new FleetSlotQuery();
        $q = $this->setAclFilter($q);


        $q

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
            $value = array_values(array_intersect((array)$this->searchMs['Algo'], FleetSlotPeer::getValueSet(FleetSlotPeer::ALGO)));

            if (!empty($value)) { $q->filterByAlgo($value, $criteria); }
        }
        if( isset($this->searchMs['State']) ) {
            $criteria = \Criteria::IN;
            $value = array_values(array_intersect((array)$this->searchMs['State'], FleetSlotPeer::getValueSet(FleetSlotPeer::STATE)));

            if (!empty($value)) { $q->filterByState($value, $criteria); }
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
                                    . ' on FleetSlot — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['FleetSlot/'],
                                    $_SESSION['mem']['order']['FleetSlot/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#FleetSlotListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
.th(_("Algorithm"), " th='sorted' c='Algo' title='" . _('Algorithm')."' ")
.th(_("Target slice (USDT)"), " th='sorted' c='TargetSlice' title='" . _('Target slice (USDT)')."' ")
.th(_("Enabled"), " th='sorted' c='Enabled' title='" . _('Enabled')."' ")
.th(_("Arm state"), " th='sorted' c='State' title='" . _('Arm state')."' ")
.th(_("Last verdict"), " th='sorted' c='LastVerdict' title='" . _('Last verdict')."' ")
.th(_("Verdict at"), " th='sorted' c='VerdictAt' title='" . _('Verdict at')."' ")
.th(_("TREND_UP episode started"), " th='sorted' c='EpisodeStartedAt' title='" . _('TREND_UP episode started')."' ")
.th(_("Grid Run label"), " th='sorted' c='GridRun.Label' title='"._('GridRun.Label')."' ")
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
                .form(div(div(input('text', 'Symbol', $this->searchMs['Symbol'] ?? '', '  title="'._('Symbol').'" placeholder="'._('Search').' '._('Symbol').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('FleetSlot')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('FleetSlot')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(
                        div(_('Algorithm'),''," class='va-fblock-lbl' ")
                        .div((function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fchip' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })(),''," class='va-fchips va-fchips-multi' data-msfield='Algo[]' ")
                        .div(selectboxCustomArray('Algo[]', [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ], _('Algorithm'), '  size="1" t="1"   multiple  ', $this->searchMs['Algo'] ?? ''), '', 'class="multiple-select ac-search-item"  title="'._('Algorithm').'"')
                    ,''," class='va-fblock va-fblock-multi' data-block='multi' ").div(
                        div(_('State'),''," class='va-fblock-lbl' ")
                        .div((function(){ $_gcOpt=''; foreach( [ '0' => ['0'=>_("idle"), '1'=>"idle"],'1' => ['0'=>_("active"), '1'=>"active"],'2' => ['0'=>_("winding_down"), '1'=>"winding_down"], ] as $_gcO ){ $_gcV = is_array($_gcO)?(string)$_gcO[1]:(string)$_gcO; $_gcL = is_array($_gcO)?(string)$_gcO[0]:(string)$_gcO; $_gcOpt .= button(span(htmlspecialchars($_gcL))," type='button' class='va-fchip' data-msval='".htmlspecialchars($_gcV)."' "); } return $_gcOpt; })(),''," class='va-fchips va-fchips-multi' data-msfield='State[]' ")
                        .div(selectboxCustomArray('State[]', [ '0' => ['0'=>_("idle"), '1'=>"idle"],'1' => ['0'=>_("active"), '1'=>"active"],'2' => ['0'=>_("winding_down"), '1'=>"winding_down"], ], _('State'), '  size="1" t="1"   multiple  ', $this->searchMs['State'] ?? ''), '', 'class="multiple-select ac-search-item"  title="'._('State').'"')
                    ,''," class='va-fblock va-fblock-multi' data-block='multi' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('FleetSlot')."' hidden ").div(
                           button(span(_("Search")),'id="msFleetSlotBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msFleetSlotBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsFleetSlot' class='va-mob-searchform' data-entity='FleetSlot'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('FleetSlot', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addFleetSlot' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'FleetSlot';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdFleetSlot' => NULL,
  'Symbol' => NULL,
  'Algo' => NULL,
  'TargetSlice' => NULL,
  'Enabled' => NULL,
  'State' => NULL,
  'ConfirmUp' => NULL,
  'ConfirmDown' => NULL,
  'LastVerdict' => NULL,
  'VerdictAt' => NULL,
  'EpisodeStartedAt' => NULL,
  'Activation' => NULL,
  'IdGridRun' => NULL,
  'LastEmptyAlertAt' => NULL,
  'LastParkedAlertAt' => NULL,
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
            $this->isChild = 'FleetSlot';
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
        $gcListKey = 'FleetSlot/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'FleetSlot/child';
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



        $default_order[]['Symbol']='ASC';
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
                if($_SESSION[_AUTH_VAR]->hasRights('FleetSlot', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteFleetSlot' ");
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
   div('' . span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." ", "   i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editFleetSlot'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Algo'] !== null ) ? $altValue['Algo'] : isntPo($data->getAlgo()))))." ", "   i='" . $__pkJsonEsc . "' c='Algo' class='center'  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['TargetSlice'] !== null ) ? $altValue['TargetSlice'] : str_replace(',', '.', (string)($data->getTargetSlice() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='TargetSlice' class='right'  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['Enabled'] !== null ) ? $altValue['Enabled'] : ($data->getEnabled() ? 'Yes' : 'No'))))." ", "   i='" . $__pkJsonEsc . "' c='Enabled' class=''  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['State'] !== null ) ? $altValue['State'] : isntPo($data->getState()))))." ", "   i='" . $__pkJsonEsc . "' c='State' class='center'  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['LastVerdict'] !== null ) ? $altValue['LastVerdict'] : $data->getLastVerdict())))." ", "   i='" . $__pkJsonEsc . "' c='LastVerdict' class=''  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['VerdictAt'] !== null ) ? $altValue['VerdictAt'] : $data->getVerdictAt())))." ", "   i='" . $__pkJsonEsc . "' c='VerdictAt' class=''  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['EpisodeStartedAt'] !== null ) ? $altValue['EpisodeStartedAt'] : $data->getEpisodeStartedAt())))." ", "   i='" . $__pkJsonEsc . "' c='EpisodeStartedAt' class=''  j='editFleetSlot'") . span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." ", "   i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editFleetSlot'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='FleetSlotRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Symbol'] !== null ) ? $altValue['Symbol'] : $data->getSymbol())))." "), "  i='" . $__pkJsonEsc . "' c='Symbol' class=''  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['Algo'] !== null ) ? $altValue['Algo'] : isntPo($data->getAlgo()))))." "), "  i='" . $__pkJsonEsc . "' c='Algo' class='center'  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['TargetSlice'] !== null ) ? $altValue['TargetSlice'] : str_replace(',', '.', (string)($data->getTargetSlice() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='TargetSlice' class='right'  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['Enabled'] !== null ) ? $altValue['Enabled'] : ($data->getEnabled() ? 'Yes' : 'No'))))." "), "  i='" . $__pkJsonEsc . "' c='Enabled' class=''  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['State'] !== null ) ? $altValue['State'] : isntPo($data->getState()))))." "), "  i='" . $__pkJsonEsc . "' c='State' class='center'  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['LastVerdict'] !== null ) ? $altValue['LastVerdict'] : $data->getLastVerdict())))." "), "  i='" . $__pkJsonEsc . "' c='LastVerdict' class=''  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['VerdictAt'] !== null ) ? $altValue['VerdictAt'] : $data->getVerdictAt())))." "), "  i='" . $__pkJsonEsc . "' c='VerdictAt' class=''  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['EpisodeStartedAt'] !== null ) ? $altValue['EpisodeStartedAt'] : $data->getEpisodeStartedAt())))." "), "  i='" . $__pkJsonEsc . "' c='EpisodeStartedAt' class=''  j='editFleetSlot'") .
                td(span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." "), "  i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editFleetSlot'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='FleetSlotDtRow".$__pkEsc."'");

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
            $tr .= input('hidden', 'rowCountFleetSlot', $i);

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
                .div($controlsContent,'FleetSlotControlsList', "class='custom-controls'")
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
                        .span(_('Fleet slot'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('FleetSlot', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Symbol"), " type='button' th='sorted' c='Symbol' class='va-mob-sortrow' ") . button(_("Algorithm"), " type='button' th='sorted' c='Algo' class='va-mob-sortrow' ") . button(_("Target slice (USDT)"), " type='button' th='sorted' c='TargetSlice' class='va-mob-sortrow' ") . button(_("Enabled"), " type='button' th='sorted' c='Enabled' class='va-mob-sortrow' ") . button(_("Arm state"), " type='button' th='sorted' c='State' class='va-mob-sortrow' ") . button(_("Last verdict"), " type='button' th='sorted' c='LastVerdict' class='va-mob-sortrow' ") . button(_("Verdict at"), " type='button' th='sorted' c='VerdictAt' class='va-mob-sortrow' ") . button(_("TREND_UP episode started"), " type='button' th='sorted' c='EpisodeStartedAt' class='va-mob-sortrow' ") . button(_("Grid Run label"), " type='button' th='sorted' c='GridRun.Label' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='FleetSlotTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'FleetSlotListForm', " class='va-mob proto-app' data-model='FleetSlot' data-table='FleetSlot' data-gc-db='fleet_slot' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addFleetSlotAutoc');
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
    public function setCreateDefaultsFleetSlot(array $data): FleetSlot
    {

        unset($data['IdFleetSlot']);
        $e = new FleetSlot();


        if(!$data['Algo']){
            $data['Algo'] = 'Trend';
        }
        $data['Enabled'] = ($data['Enabled'] == '')?false:$data['Enabled'];
        if(!$data['State']){
            $data['State'] = 'idle';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        if(isset($data['Enabled'])){
            $e->setEnabled( (!isset($data['Enabled']) || $data['Enabled'] == '') ? false : $data['Enabled']);
        }
        //varchar not required
        $e->setLastVerdict( ($data['LastVerdict'] == '' ) ? null : $data['LastVerdict']);
        $e->setVerdictAt( ($data['VerdictAt'] == '' || $data['VerdictAt'] == 'null' || substr($data['VerdictAt'],0,10) == '-0001-11-30') ? null : $data['VerdictAt'] );
        $e->setEpisodeStartedAt( ($data['EpisodeStartedAt'] == '' || $data['EpisodeStartedAt'] == 'null' || substr($data['EpisodeStartedAt'],0,10) == '-0001-11-30') ? null : $data['EpisodeStartedAt'] );
        //longvarchar not required
        $e->setActivation( ($data['Activation'] == '' ) ? null : $data['Activation']);
        //foreign
        $e->setIdGridRun(( $data['IdGridRun'] == '' ) ? null : $data['IdGridRun']);
        $e->setLastEmptyAlertAt( ($data['LastEmptyAlertAt'] == '' || $data['LastEmptyAlertAt'] == 'null' || substr($data['LastEmptyAlertAt'],0,10) == '-0001-11-30') ? null : $data['LastEmptyAlertAt'] );
        $e->setLastParkedAlertAt( ($data['LastParkedAlertAt'] == '' || $data['LastParkedAlertAt'] == 'null' || substr($data['LastParkedAlertAt'],0,10) == '-0001-11-30') ? null : $data['LastParkedAlertAt'] );
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsFleetSlot(array $data): ?FleetSlot
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(FleetSlotQuery::class, json_decode($data['i']), 'FleetSlot', 'w');
        if ($e === null) { return null; }


        if(!$data['Algo']){
            $data['Algo'] = 'Trend';
        }
        $data['Enabled'] = ($data['Enabled'] == '')?false:$data['Enabled'];
        if(!$data['State']){
            $data['State'] = 'idle';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['LastVerdict'])){
            $e->setLastVerdict( ($data['LastVerdict'] == '' ) ? null : $data['LastVerdict']);
        }
        if(isset($data['VerdictAt'])){
            $e->setVerdictAt( ($data['VerdictAt'] == '' || $data['VerdictAt'] == 'null' || substr($data['VerdictAt'],0,10) == '-0001-11-30') ? null : $data['VerdictAt'] );
        }
        if(isset($data['EpisodeStartedAt'])){
            $e->setEpisodeStartedAt( ($data['EpisodeStartedAt'] == '' || $data['EpisodeStartedAt'] == 'null' || substr($data['EpisodeStartedAt'],0,10) == '-0001-11-30') ? null : $data['EpisodeStartedAt'] );
        }
        if(isset($data['Activation'])){
            $e->setActivation( ($data['Activation'] == '' ) ? null : $data['Activation']);
        }
        if( isset($data['IdGridRun']) ){
            $e->setIdGridRun(( $data['IdGridRun'] == '' ) ? null : $data['IdGridRun']);
        }
        if(isset($data['LastEmptyAlertAt'])){
            $e->setLastEmptyAlertAt( ($data['LastEmptyAlertAt'] == '' || $data['LastEmptyAlertAt'] == 'null' || substr($data['LastEmptyAlertAt'],0,10) == '-0001-11-30') ? null : $data['LastEmptyAlertAt'] );
        }
        if(isset($data['LastParkedAlertAt'])){
            $e->setLastParkedAlertAt( ($data['LastParkedAlertAt'] == '' || $data['LastParkedAlertAt'] == 'null' || substr($data['LastParkedAlertAt'],0,10) == '-0001-11-30') ? null : $data['LastParkedAlertAt'] );
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of FleetSlot
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

        $je = "FleetSlotTable";

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


        if($error == ''){
            unset($error);
        }



        // #23 S5: SaveButtonJs (the inline read-only #save<T>.remove()) is no longer
        // emitted — the Save button is now rendered server-side ONLY when the user
        // has save rights (see formSaveBarSet above), so there is nothing to remove.
        // One less generated block on the screens.js push() re-exec arm.
        $this->SaveButtonJs = "";

        if($_SESSION[_AUTH_VAR]->hasRights('FleetSlot', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addFleetSlotForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = FleetSlotQuery::create()

                #default
                ->leftJoinWith('GridRun')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('FleetSlot', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->FleetSlot['isNew'] = 'yes';
            $dataObj = new FleetSlot();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->FleetSlot['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getGridRun())?'':$dataObj->setGridRun( new GridRun() );





        $EnabledChecked = ($dataObj->getEnabled())?"checked='checked'":'';
                $Enabled = ($dataObj->getEnabled())?"true":"true";



$this->fields['FleetSlot']['Symbol']['html'] = stdFieldRow(_("Symbol"), input('text', 'Symbol', htmlentities((string)($dataObj->getSymbol() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Symbol'))."' size='35'  v='SYMBOL' s='d' class='req'  ")."", 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['Algo']['html'] = stdFieldRow(_("Algorithm"), selectboxCustomArray('Algo', [ '0' => ['0'=>_("Trend"), '1'=>"Trend"],'1' => ['0'=>_("Grid"), '1'=>"Grid"], ], "", "s='d'  ", $dataObj->getAlgo(), '', false), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['TargetSlice']['html'] = stdFieldRow(_("Target slice (USDT)"), input('number', 'TargetSlice', $dataObj->getTargetSlice(), "  placeholder='".str_replace("'","&#39;",_('Target slice (USDT)'))."'  v='TARGET_SLICE' size='10' s='d' class='req'"), 'TargetSlice', "", $this->commentsTargetSlice, $this->commentsTargetSlice_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['Enabled']['html'] = stdFieldRow(_("Enabled"), input('checkbox', 'Enabled', $Enabled, "$EnabledChecked  size='10' s='d'"), 'Enabled', "", $this->commentsEnabled, $this->commentsEnabled_css, ' half', ' ', 'yes', 'v2');
$this->fields['FleetSlot']['State']['html'] = stdFieldRow(_("Arm state"), selectboxCustomArray('State', [ '0' => ['0'=>_("idle"), '1'=>"idle"],'1' => ['0'=>_("active"), '1'=>"active"],'2' => ['0'=>_("winding_down"), '1'=>"winding_down"], ], "", "s='d'  ", $dataObj->getState(), '', false), 'State', "", $this->commentsState, $this->commentsState_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['ConfirmUp']['html'] = stdFieldRow(_("Consecutive TREND_UP passes"), input('number', 'ConfirmUp', $dataObj->getConfirmUp(), " step='1' placeholder='".str_replace("'","&#39;",_('Consecutive TREND_UP passes'))."' v='CONFIRM_UP' size='5' s='d' class=''"), 'ConfirmUp', "", $this->commentsConfirmUp, $this->commentsConfirmUp_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['ConfirmDown']['html'] = stdFieldRow(_("Consecutive non-TREND_UP passes"), input('number', 'ConfirmDown', $dataObj->getConfirmDown(), " step='1' placeholder='".str_replace("'","&#39;",_('Consecutive non-TREND_UP passes'))."' v='CONFIRM_DOWN' size='5' s='d' class=''"), 'ConfirmDown', "", $this->commentsConfirmDown, $this->commentsConfirmDown_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['LastVerdict']['html'] = stdFieldRow(_("Last verdict"), input('text', 'LastVerdict', htmlentities((string)($dataObj->getLastVerdict() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Last verdict'))."' size='15'  v='LAST_VERDICT' s='d' class=''  ")."", 'LastVerdict', "", $this->commentsLastVerdict, $this->commentsLastVerdict_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['VerdictAt']['html'] = stdFieldRow(_("Verdict at"), input('datetime-local', 'VerdictAt', $dataObj->getVerdictAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Verdict at'"), 'VerdictAt', "", $this->commentsVerdictAt, $this->commentsVerdictAt_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['EpisodeStartedAt']['html'] = stdFieldRow(_("TREND_UP episode started"), input('datetime-local', 'EpisodeStartedAt', $dataObj->getEpisodeStartedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='TREND_UP episode started'"), 'EpisodeStartedAt', "", $this->commentsEpisodeStartedAt, $this->commentsEpisodeStartedAt_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['Activation']['html'] = stdFieldRow(_("Activation payload"), textarea('Activation', htmlentities((string)($dataObj->getActivation() ?? '')) ,"placeholder='".str_replace("'","&#39;",_('Activation payload'))."' cols='71' v='ACTIVATION' s='d'  class=' ' style='' spellcheck='false'"), 'Activation', "", $this->commentsActivation, $this->commentsActivation_css, '', ' ', 'no', 'v2');
$this->fields['FleetSlot']['IdGridRun']['html'] = stdFieldRow(_("Run"),
    input('text', 'IdGridRunAutoc', $dataObj->getGridRun()?->getLabel(), " title='".str_replace("'","", (string)($dataObj->getGridRun()?->getLabel()))."' v='ID_GRID_RUN' rid='IdGridRun' placeholder='"._('Run')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdGridRun&quot;,&quot;table&quot;:&quot;FleetSlot&quot;,&quot;childTable&quot;:&quot;GridRun&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;GridRun&quot;,&quot;show&quot;:[&quot;Label&quot;],&quot;id&quot;:&quot;IdGridRun&quot;,&quot;filter&quot;:&quot;Label&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, '', ' ', 'no', 'v2');
$this->fields['FleetSlot']['LastEmptyAlertAt']['html'] = stdFieldRow(_("Empty alerted at"), input('datetime-local', 'LastEmptyAlertAt', $dataObj->getLastEmptyAlertAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Empty alerted at'"), 'LastEmptyAlertAt', "", $this->commentsLastEmptyAlertAt, $this->commentsLastEmptyAlertAt_css, ' half', ' ', 'no', 'v2');
$this->fields['FleetSlot']['LastParkedAlertAt']['html'] = stdFieldRow(_("Parked alerted at"), input('datetime-local', 'LastParkedAlertAt', $dataObj->getLastParkedAlertAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Parked alerted at'"), 'LastParkedAlertAt', "", $this->commentsLastParkedAlertAt, $this->commentsLastParkedAlertAt_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'State',1=>'ConfirmUp',2=>'ConfirmDown',3=>'LastVerdict',4=>'VerdictAt',5=>'EpisodeStartedAt',6=>'Activation',7=>'LastEmptyAlertAt',8=>'LastParkedAlertAt',9=>'IdCreation',10=>'IdModification',11=>'IdGroupCreation',), $dataObj);

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
            if( ($_SESSION[_AUTH_VAR]->hasRights('FleetSlot','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('FleetSlot','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveFleetSlot', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedFleetSlot','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdFleetSlot', $dataObj->getIdFleetSlot(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        $gcFirstTabActive = true;
        if (!empty($this->formCustomTabs)) {
            throw new \LogicException('FleetSlot: addFormTab() needs add_tab_columns (without add_field_groups) on the table — there is no tab strip to put the tab in');
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Fleet slot'), _SITE_URL.'FleetSlot', "class='nav-btn'")
                        .div(
                            span(_('Fleet slot'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'FleetSlot', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['FleetSlot']['Symbol']['html']
.$this->fields['FleetSlot']['Algo']['html']
.$this->fields['FleetSlot']['TargetSlice']['html']
.$this->fields['FleetSlot']['Enabled']['html']
.$this->fields['FleetSlot']['State']['html']
.$this->fields['FleetSlot']['ConfirmUp']['html']
.$this->fields['FleetSlot']['ConfirmDown']['html']
.$this->fields['FleetSlot']['LastVerdict']['html']
.$this->fields['FleetSlot']['VerdictAt']['html']
.$this->fields['FleetSlot']['EpisodeStartedAt']['html']
.$this->fields['FleetSlot']['Activation']['html']
.$this->fields['FleetSlot']['IdGridRun']['html']
.$this->fields['FleetSlot']['LastEmptyAlertAt']['html']
.$this->fields['FleetSlot']['LastParkedAlertAt']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntFleetSlot", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formFleetSlot' class='mainForm formContent' ")
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
            $fields = array_keys($this->fields['FleetSlot']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['FleetSlot'][$field]['html'] = $this->fieldsRo['FleetSlot'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Symbol':
        $this->fieldsRo['FleetSlot']['Symbol']['html'] = stdFieldRow(_("Symbol"), div( htmlspecialchars((string)($dataObj->getSymbol()), ENT_QUOTES), 'Symbol_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Symbol', $dataObj->getSymbol(), "s='d'"), 'Symbol', "", $this->commentsSymbol, $this->commentsSymbol_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Algo':
        $this->fieldsRo['FleetSlot']['Algo']['html'] = stdFieldRow(_("Algorithm"), div( htmlspecialchars((string)($dataObj->getAlgo()), ENT_QUOTES), 'Algo_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Algo', $dataObj->getAlgo(), "s='d'"), 'Algo', "", $this->commentsAlgo, $this->commentsAlgo_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'TargetSlice':
        $this->fieldsRo['FleetSlot']['TargetSlice']['html'] = stdFieldRow(_("Target slice (USDT)"), div( htmlspecialchars((string)($dataObj->getTargetSlice()), ENT_QUOTES), 'TargetSlice_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'TargetSlice', $dataObj->getTargetSlice(), "s='d'"), 'TargetSlice', "", $this->commentsTargetSlice, $this->commentsTargetSlice_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Enabled':
        $this->fieldsRo['FleetSlot']['Enabled']['html'] = stdFieldRow(_("Enabled"), div( htmlspecialchars((string)($dataObj->getEnabled()), ENT_QUOTES), 'Enabled_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Enabled', $dataObj->getEnabled(), "s='d'"), 'Enabled', "", $this->commentsEnabled, $this->commentsEnabled_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'State':
        $this->fieldsRo['FleetSlot']['State']['html'] = stdFieldRow(_("Arm state"), div( htmlspecialchars((string)($dataObj->getState()), ENT_QUOTES), 'State_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'State', $dataObj->getState(), "s='d'"), 'State', "", $this->commentsState, $this->commentsState_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ConfirmUp':
        $this->fieldsRo['FleetSlot']['ConfirmUp']['html'] = stdFieldRow(_("Consecutive TREND_UP passes"), div( htmlspecialchars((string)($dataObj->getConfirmUp()), ENT_QUOTES), 'ConfirmUp_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ConfirmUp', $dataObj->getConfirmUp(), "s='d'"), 'ConfirmUp', "", $this->commentsConfirmUp, $this->commentsConfirmUp_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'ConfirmDown':
        $this->fieldsRo['FleetSlot']['ConfirmDown']['html'] = stdFieldRow(_("Consecutive non-TREND_UP passes"), div( htmlspecialchars((string)($dataObj->getConfirmDown()), ENT_QUOTES), 'ConfirmDown_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ConfirmDown', $dataObj->getConfirmDown(), "s='d'"), 'ConfirmDown', "", $this->commentsConfirmDown, $this->commentsConfirmDown_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LastVerdict':
        $this->fieldsRo['FleetSlot']['LastVerdict']['html'] = stdFieldRow(_("Last verdict"), div( htmlspecialchars((string)($dataObj->getLastVerdict()), ENT_QUOTES), 'LastVerdict_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LastVerdict', $dataObj->getLastVerdict(), "s='d'"), 'LastVerdict', "", $this->commentsLastVerdict, $this->commentsLastVerdict_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'VerdictAt':
        $this->fieldsRo['FleetSlot']['VerdictAt']['html'] = stdFieldRow(_("Verdict at"), div( htmlspecialchars((string)($dataObj->getVerdictAt()), ENT_QUOTES), 'VerdictAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'VerdictAt', $dataObj->getVerdictAt(), "s='d'"), 'VerdictAt', "", $this->commentsVerdictAt, $this->commentsVerdictAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'EpisodeStartedAt':
        $this->fieldsRo['FleetSlot']['EpisodeStartedAt']['html'] = stdFieldRow(_("TREND_UP episode started"), div( htmlspecialchars((string)($dataObj->getEpisodeStartedAt()), ENT_QUOTES), 'EpisodeStartedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EpisodeStartedAt', $dataObj->getEpisodeStartedAt(), "s='d'"), 'EpisodeStartedAt', "", $this->commentsEpisodeStartedAt, $this->commentsEpisodeStartedAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Activation':
        $this->fieldsRo['FleetSlot']['Activation']['html'] = stdFieldRow(_("Activation payload"), div( htmlspecialchars((string)($dataObj->getActivation()), ENT_QUOTES), 'Activation_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Activation', $dataObj->getActivation(), "s='d'"), 'Activation', "", $this->commentsActivation, $this->commentsActivation_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'IdGridRun':
        $this->fieldsRo['FleetSlot']['IdGridRun']['html'] = stdFieldRow(_("Run"), div( htmlspecialchars((string)(($dataObj->getGridRun())?($dataObj->getGridRun()->getLabel()):''), ENT_QUOTES), 'IdGridRun_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'LastEmptyAlertAt':
        $this->fieldsRo['FleetSlot']['LastEmptyAlertAt']['html'] = stdFieldRow(_("Empty alerted at"), div( htmlspecialchars((string)($dataObj->getLastEmptyAlertAt()), ENT_QUOTES), 'LastEmptyAlertAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LastEmptyAlertAt', $dataObj->getLastEmptyAlertAt(), "s='d'"), 'LastEmptyAlertAt', "", $this->commentsLastEmptyAlertAt, $this->commentsLastEmptyAlertAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LastParkedAlertAt':
        $this->fieldsRo['FleetSlot']['LastParkedAlertAt']['html'] = stdFieldRow(_("Parked alerted at"), div( htmlspecialchars((string)($dataObj->getLastParkedAlertAt()), ENT_QUOTES), 'LastParkedAlertAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LastParkedAlertAt', $dataObj->getLastParkedAlertAt(), "s='d'"), 'LastParkedAlertAt', "", $this->commentsLastParkedAlertAt, $this->commentsLastParkedAlertAt_css, 'readonly half', ' ', 'no', 'v2');

            break;
        }
    }

    /**
     * Query for FleetSlot_IdGridRun selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxFleetSlot_IdGridRun(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxFleetSlot_IdGridRun')
        && !method_exists($gcSbHost, 'selectboxDataFleetSlot_IdGridRun');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('grid_run', 'FleetSlot_IdGridRun', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
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
    if(method_exists($gcSbHost, 'beginSelectboxFleetSlot_IdGridRun') and $array)
        $ret = $gcSbHost->beginSelectboxFleetSlot_IdGridRun($q, $dataObj, $data, $obj);
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
            if(method_exists($gcSbHost, 'selectboxDataFleetSlot_IdGridRun')){
                $gcSbHost->selectboxDataFleetSlot_IdGridRun($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('grid_run', 'FleetSlot_IdGridRun', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('GridRun'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
