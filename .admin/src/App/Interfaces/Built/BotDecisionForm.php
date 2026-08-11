<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'BotDecision' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class BotDecisionForm extends BotDecision
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

        public $arrayIdGridRunOptions;


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
        $this->model_name = 'BotDecision';
        $this->virtualClassName = 'BotDecision';
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

        $q = new BotDecisionQuery();
        $q = $this->setAclFilter($q);
        

        $q

                #required bot_decision
                ->leftJoinWith('GridRun');
        if(is_array( $this->searchMs )){
            # main search form
            
            
        }else{
            ## standard list
            
        }
        
        $hasParent = json_decode((string) $IdParent);
        if (!empty($hasParent)) {
            $q->filterByIdGridRun($hasParent);
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
                                var __se=document.querySelector(\"#BotDecisionListForm [th='sorted'][c='".$col."']\");if(__se){__se.setAttribute('sens', '".strtolower($sens)."');__se.setAttribute('order','on');__se.classList.add('sorted');}
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
                $trHead = th(_("Grid Run label"), " th='sorted' c='GridRun.Label' title='"._('GridRun.Label')."' ")
.th(_("Source"), " th='sorted' c='Source' title='" . _('Source')."' ")
.th(_("Range low"), " th='sorted' c='PLow' title='" . _('Range low')."' ")
.th(_("Range high"), " th='sorted' c='PHigh' title='" . _('Range high')."' ")
.th(_("Levels"), " th='sorted' c='NLevels' title='" . _('Levels')."' ")
.th(_("Deployed budget %"), " th='sorted' c='DeployPct' title='" . _('Deployed budget %')."' ")
.th(_("Reason"), " th='sorted' c='Reason' title='" . _('Reason')."' ")
.th(_("Price at decision"), " th='sorted' c='PriceAt' title='" . _('Price at decision')."' ")
.th(_("Realized before"), " th='sorted' c='RealizedBefore' title='" . _('Realized before')."' ")
.th(_("Eval"), " th='sorted' c='EvalStatus' title='" . _('Eval')."' ")
.th(_("Scored at"), " th='sorted' c='EvalAt' title='" . _('Scored at')."' ")
.th(_("Applied at"), " th='sorted' c='AppliedAt' title='" . _('Applied at')."' ")
.th(_("Cycles after"), " th='sorted' c='CyclesDelta' title='" . _('Cycles after')."' ")
.th(_("P/L after"), " th='sorted' c='RealizedDelta' title='" . _('P/L after')."' ")
.th(_("Price move %"), " th='sorted' c='PriceMovePct' title='" . _('Price move %')."' ")
.th(_("Verdict"), " th='sorted' c='Verdict' title='" . _('Verdict')."' ")
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
                 if($_SESSION[_AUTH_VAR]->hasRights('BotDecision', 'a') && !$this->setReadOnly){
                
                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addBotDecision' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'BotDecision';
        $altValue = array (
  'IdBotDecision' => NULL,
  'IdGridRun' => NULL,
  'Source' => NULL,
  'PLow' => NULL,
  'PHigh' => NULL,
  'NLevels' => NULL,
  'DeployPct' => NULL,
  'Reason' => NULL,
  'PriceAt' => NULL,
  'RealizedBefore' => NULL,
  'EvalStatus' => NULL,
  'EvalAt' => NULL,
  'AppliedAt' => NULL,
  'CyclesDelta' => NULL,
  'RealizedDelta' => NULL,
  'PriceMovePct' => NULL,
  'Verdict' => NULL,
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
            $this->isChild = 'BotDecision';
        }

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'BotDecision/');

        // Guideline filter chips (built from the first ENUM search col).
        $trChips = '';
        

        // order
        $this->searchOrder = $this->setOrderVar($request['order'] ?? '', 'BotDecision/');

        // Clear-sort affordances (chip strip + sort-sheet row), rendered only
        // while the session carries a user ordering for this list. Both carry
        // th='sorted' c='*' so app/list.js routes them through the existing
        // sort handler; the server drops the whole stored ordering on '*'.
        $gcSortClear = '';
        $gcSortSheetClear = '';
        if (!empty($_SESSION['mem']['order']['BotDecision/'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
            $gcSortSheetClear = button("<i class='ri-arrow-go-back-line'></i> "._('Default order'), " type='button' th='sorted' c='*' class='va-mob-sortrow va-mob-sortrow-clear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'BotDecision/');

        
        
        
        
        
        

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
                    $gcGroupOn = true;
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
                if($_SESSION[_AUTH_VAR]->hasRights('BotDecision', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteBotDecision' ");
                }
            }
        
            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label']));
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
                
                

        $altValue['GridRun_Label'] = "";
        if($data->getGridRun()){
            $altValue['GridRun_Label'] = $data->getGridRun()->getLabel();
        }
                

                $actionCell =  td($this->canDelete . $this->listActionCell, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." ", "   i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editBotDecision'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Source'] !== null ) ? $altValue['Source'] : isntPo($data->getSource()))))." ", "   i='" . $__pkJsonEsc . "' c='Source' class='center'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['PLow'] !== null ) ? $altValue['PLow'] : str_replace(',', '.', (string)($data->getPLow() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PLow' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['PHigh'] !== null ) ? $altValue['PHigh'] : str_replace(',', '.', (string)($data->getPHigh() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PHigh' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['NLevels'] !== null ) ? $altValue['NLevels'] : $data->getNLevels())))." ", "   i='" . $__pkJsonEsc . "' c='NLevels' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['DeployPct'] !== null ) ? $altValue['DeployPct'] : $data->getDeployPct())))." ", "   i='" . $__pkJsonEsc . "' c='DeployPct' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['Reason'] !== null ) ? $altValue['Reason'] : $data->getReason())))." ", "   i='" . $__pkJsonEsc . "' c='Reason' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['PriceAt'] !== null ) ? $altValue['PriceAt'] : str_replace(',', '.', (string)($data->getPriceAt() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PriceAt' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['RealizedBefore'] !== null ) ? $altValue['RealizedBefore'] : str_replace(',', '.', (string)($data->getRealizedBefore() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='RealizedBefore' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['EvalStatus'] !== null ) ? $altValue['EvalStatus'] : isntPo($data->getEvalStatus()))))." ", "   i='" . $__pkJsonEsc . "' c='EvalStatus' class='center'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['EvalAt'] !== null ) ? $altValue['EvalAt'] : $data->getEvalAt())))." ", "   i='" . $__pkJsonEsc . "' c='EvalAt' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['AppliedAt'] !== null ) ? $altValue['AppliedAt'] : $data->getAppliedAt())))." ", "   i='" . $__pkJsonEsc . "' c='AppliedAt' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['CyclesDelta'] !== null ) ? $altValue['CyclesDelta'] : $data->getCyclesDelta())))." ", "   i='" . $__pkJsonEsc . "' c='CyclesDelta' class=''  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['RealizedDelta'] !== null ) ? $altValue['RealizedDelta'] : str_replace(',', '.', (string)($data->getRealizedDelta() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='RealizedDelta' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['PriceMovePct'] !== null ) ? $altValue['PriceMovePct'] : str_replace(',', '.', (string)($data->getPriceMovePct() ?? '')))))." ", "   i='" . $__pkJsonEsc . "' c='PriceMovePct' class='right'  j='editBotDecision'") . span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : isntPo($data->getVerdict()))))." ", "   i='" . $__pkJsonEsc . "' c='Verdict' class='center'  j='editBotDecision'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionCell
                , '', " 
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='BotDecisionRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['IdGridRun'] !== null ) ? $altValue['IdGridRun'] : $altValue['GridRun_Label'])))." "), "  i='" . $__pkJsonEsc . "' c='IdGridRun' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['Source'] !== null ) ? $altValue['Source'] : isntPo($data->getSource()))))." "), "  i='" . $__pkJsonEsc . "' c='Source' class='center'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['PLow'] !== null ) ? $altValue['PLow'] : str_replace(',', '.', (string)($data->getPLow() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PLow' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['PHigh'] !== null ) ? $altValue['PHigh'] : str_replace(',', '.', (string)($data->getPHigh() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PHigh' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['NLevels'] !== null ) ? $altValue['NLevels'] : $data->getNLevels())))." "), "  i='" . $__pkJsonEsc . "' c='NLevels' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['DeployPct'] !== null ) ? $altValue['DeployPct'] : $data->getDeployPct())))." "), "  i='" . $__pkJsonEsc . "' c='DeployPct' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['Reason'] !== null ) ? $altValue['Reason'] : $data->getReason())))." "), "  i='" . $__pkJsonEsc . "' c='Reason' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['PriceAt'] !== null ) ? $altValue['PriceAt'] : str_replace(',', '.', (string)($data->getPriceAt() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PriceAt' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['RealizedBefore'] !== null ) ? $altValue['RealizedBefore'] : str_replace(',', '.', (string)($data->getRealizedBefore() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='RealizedBefore' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['EvalStatus'] !== null ) ? $altValue['EvalStatus'] : isntPo($data->getEvalStatus()))))." "), "  i='" . $__pkJsonEsc . "' c='EvalStatus' class='center'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['EvalAt'] !== null ) ? $altValue['EvalAt'] : $data->getEvalAt())))." "), "  i='" . $__pkJsonEsc . "' c='EvalAt' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['AppliedAt'] !== null ) ? $altValue['AppliedAt'] : $data->getAppliedAt())))." "), "  i='" . $__pkJsonEsc . "' c='AppliedAt' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['CyclesDelta'] !== null ) ? $altValue['CyclesDelta'] : $data->getCyclesDelta())))." "), "  i='" . $__pkJsonEsc . "' c='CyclesDelta' class=''  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['RealizedDelta'] !== null ) ? $altValue['RealizedDelta'] : str_replace(',', '.', (string)($data->getRealizedDelta() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='RealizedDelta' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['PriceMovePct'] !== null ) ? $altValue['PriceMovePct'] : str_replace(',', '.', (string)($data->getPriceMovePct() ?? '')))))." "), "  i='" . $__pkJsonEsc . "' c='PriceMovePct' class='right'  j='editBotDecision'") .
                td(span(htmlspecialchars((string)((($altValue['Verdict'] !== null ) ? $altValue['Verdict'] : isntPo($data->getVerdict()))))." "), "  i='" . $__pkJsonEsc . "' c='Verdict' class='center'  j='editBotDecision'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='BotDecisionDtRow".$__pkEsc."'");
                
                $gcListRows[] = $gcRowHtml;
                $gcListRowsDt[] = $gcDtRowHtml;

                $i++;
                $altValue = null;
            }
            $tr .= implode('', $gcListRows);
            $trDt .= implode('', $gcListRowsDt);
            $tr .= input('hidden', 'rowCountBotDecision', $i);
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
                .div($controlsContent,'BotDecisionControlsList', "class='custom-controls'")
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
                        .span(_('Refit Decision'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('BotDecision', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Grid Run label"), " type='button' th='sorted' c='GridRun.Label' class='va-mob-sortrow' ") . button(_("Source"), " type='button' th='sorted' c='Source' class='va-mob-sortrow' ") . button(_("Range low"), " type='button' th='sorted' c='PLow' class='va-mob-sortrow' ") . button(_("Range high"), " type='button' th='sorted' c='PHigh' class='va-mob-sortrow' ") . button(_("Levels"), " type='button' th='sorted' c='NLevels' class='va-mob-sortrow' ") . button(_("Deployed budget %"), " type='button' th='sorted' c='DeployPct' class='va-mob-sortrow' ") . button(_("Reason"), " type='button' th='sorted' c='Reason' class='va-mob-sortrow' ") . button(_("Price at decision"), " type='button' th='sorted' c='PriceAt' class='va-mob-sortrow' ") . button(_("Realized before"), " type='button' th='sorted' c='RealizedBefore' class='va-mob-sortrow' ") . button(_("Eval"), " type='button' th='sorted' c='EvalStatus' class='va-mob-sortrow' ") . button(_("Scored at"), " type='button' th='sorted' c='EvalAt' class='va-mob-sortrow' ") . button(_("Applied at"), " type='button' th='sorted' c='AppliedAt' class='va-mob-sortrow' ") . button(_("Cycles after"), " type='button' th='sorted' c='CyclesDelta' class='va-mob-sortrow' ") . button(_("P/L after"), " type='button' th='sorted' c='RealizedDelta' class='va-mob-sortrow' ") . button(_("Price move %"), " type='button' th='sorted' c='PriceMovePct' class='va-mob-sortrow' ") . button(_("Verdict"), " type='button' th='sorted' c='Verdict' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='BotDecisionTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'BotDecisionListForm', " class='va-mob proto-app' data-model='BotDecision' data-table='BotDecision' data-ui='".$this->uiTabsId."' " . ($IdParent !== null && $IdParent !== '' ? " data-ip='".htmlspecialchars((string)$IdParent, ENT_QUOTES)."' data-tp='BotDecision' data-parent='GridRun'" : ''));

        



        $return['onReadyJs'] =
            $HelpDivJs
            
            ."
        
        
        
        
        
        
        
        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addBotDecisionAutoc');
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
    public function setCreateDefaultsBotDecision(array $data): BotDecision
    {

        unset($data['IdBotDecision']);
        $e = new BotDecision();


        if(!$data['Source']){
            $data['Source'] = 'Claude';
        }
        if(!$data['EvalStatus']){
            $data['EvalStatus'] = 'Pending';
        }
        if( $data['Verdict'] == '' )unset($data['Verdict']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //integer not required
        $e->setDeployPct( ($data['DeployPct'] == '' ) ? null : $data['DeployPct']);
        //integer not required
        $e->setReason( ($data['Reason'] == '' ) ? null : $data['Reason']);
        //integer not required
        $e->setPriceAt( ($data['PriceAt'] == '' ) ? null : $data['PriceAt']);
        //integer not required
        $e->setRealizedBefore( ($data['RealizedBefore'] == '' ) ? null : $data['RealizedBefore']);
        $e->setEvalAt( ($data['EvalAt'] == '' || $data['EvalAt'] == 'null' || substr($data['EvalAt'],0,10) == '-0001-11-30') ? null : $data['EvalAt'] );
        $e->setAppliedAt( ($data['AppliedAt'] == '' || $data['AppliedAt'] == 'null' || substr($data['AppliedAt'],0,10) == '-0001-11-30') ? null : $data['AppliedAt'] );
        //integer not required
        $e->setCyclesDelta( ($data['CyclesDelta'] == '' ) ? null : $data['CyclesDelta']);
        //integer not required
        $e->setRealizedDelta( ($data['RealizedDelta'] == '' ) ? null : $data['RealizedDelta']);
        //integer not required
        $e->setPriceMovePct( ($data['PriceMovePct'] == '' ) ? null : $data['PriceMovePct']);
        $e->setVerdict(($data['Verdict'] == '' ) ? null : $data['Verdict']);
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
    public function setUpdateDefaultsBotDecision(array $data): ?BotDecision
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(BotDecisionQuery::class, json_decode($data['i']), 'BotDecision', 'w');
        if ($e === null) { return null; }


        if(!$data['Source']){
            $data['Source'] = 'Claude';
        }
        if(!$data['EvalStatus']){
            $data['EvalStatus'] = 'Pending';
        }
        if( $data['Verdict'] == '' )unset($data['Verdict']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['DeployPct'])){
            $e->setDeployPct( ($data['DeployPct'] == '' ) ? null : $data['DeployPct']);
        }
        if(isset($data['Reason'])){
            $e->setReason( ($data['Reason'] == '' ) ? null : $data['Reason']);
        }
        if(isset($data['PriceAt'])){
            $e->setPriceAt( ($data['PriceAt'] == '' ) ? null : $data['PriceAt']);
        }
        if(isset($data['RealizedBefore'])){
            $e->setRealizedBefore( ($data['RealizedBefore'] == '' ) ? null : $data['RealizedBefore']);
        }
        if(isset($data['EvalAt'])){
            $e->setEvalAt( ($data['EvalAt'] == '' || $data['EvalAt'] == 'null' || substr($data['EvalAt'],0,10) == '-0001-11-30') ? null : $data['EvalAt'] );
        }
        if(isset($data['AppliedAt'])){
            $e->setAppliedAt( ($data['AppliedAt'] == '' || $data['AppliedAt'] == 'null' || substr($data['AppliedAt'],0,10) == '-0001-11-30') ? null : $data['AppliedAt'] );
        }
        if(isset($data['CyclesDelta'])){
            $e->setCyclesDelta( ($data['CyclesDelta'] == '' ) ? null : $data['CyclesDelta']);
        }
        if(isset($data['RealizedDelta'])){
            $e->setRealizedDelta( ($data['RealizedDelta'] == '' ) ? null : $data['RealizedDelta']);
        }
        if(isset($data['PriceMovePct'])){
            $e->setPriceMovePct( ($data['PriceMovePct'] == '' ) ? null : $data['PriceMovePct']);
        }
        if(isset($data['Verdict'])){
            $e->setVerdict(($data['Verdict'] == '' ) ? null : $data['Verdict']);
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
     * Produce a formated form of BotDecision
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

        $je = "BotDecisionTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('BotDecision', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addBotDecision' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".($IdParent ?: '')."'", $this->formAddButton); }
        }

        if($id && !$data['reload']) {


            $q = BotDecisionQuery::create()

                #required bot_decision
                ->leftJoinWith('GridRun')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('BotDecision', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->BotDecision['isNew'] = 'yes';
            $dataObj = new BotDecision();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));
            if($IdParent){
                $strPkParent = "setIdGridRun";
                $dataObj->$strPkParent($IdParent);
            }
        }else{
                $this->BotDecision['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getGridRun())?'':$dataObj->setGridRun( new GridRun() );








$this->fields['BotDecision']['IdGridRun']['html'] = stdFieldRow(_("Run"),
    input('text', 'IdGridRunAutoc', $dataObj->getGridRun()?->getIdGridRun(), " title='".str_replace("'","", (string)($dataObj->getGridRun()?->getIdGridRun()))."' v='ID_GRID_RUN' rid='IdGridRun' placeholder='"._('Run')."' j='autocomplete' class='ui-autocomplete-input' data-gc-autoc='{&quot;name&quot;:&quot;IdGridRun&quot;,&quot;table&quot;:&quot;BotDecision&quot;,&quot;childTable&quot;:&quot;GridRun&quot;,&quot;spec&quot;:{&quot;fkt&quot;:&quot;GridRun&quot;,&quot;show&quot;:[&quot;IdGridRun&quot;],&quot;id&quot;:&quot;IdGridRun&quot;,&quot;filter&quot;:&quot;IdGridRun&quot;,&quot;term&quot;:&quot;str&quot;,&quot;limit&quot;:20}}'")
    .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, '', ' ', 'no', 'v2');
$this->fields['BotDecision']['Source']['html'] = stdFieldRow(_("Source"), selectboxCustomArray('Source', [ '0' => ['0'=>_("Claude"), '1'=>"Claude"],'1' => ['0'=>_("Cron"), '1'=>"Cron"],'2' => ['0'=>_("Manual"), '1'=>"Manual"], ], "", "s='d'  ", $dataObj->getSource(), '', false), 'Source', "", $this->commentsSource, $this->commentsSource_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['PLow']['html'] = stdFieldRow(_("Range low"), input('number', 'PLow', $dataObj->getPLow(), "  placeholder='".str_replace("'","&#39;",_('Range low'))."'  v='P_LOW' size='10' s='d' class='req'"), 'PLow', "", $this->commentsPLow, $this->commentsPLow_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['PHigh']['html'] = stdFieldRow(_("Range high"), input('number', 'PHigh', $dataObj->getPHigh(), "  placeholder='".str_replace("'","&#39;",_('Range high'))."'  v='P_HIGH' size='10' s='d' class='req'"), 'PHigh', "", $this->commentsPHigh, $this->commentsPHigh_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['NLevels']['html'] = stdFieldRow(_("Levels"), input('number', 'NLevels', $dataObj->getNLevels(), " step='1' placeholder='".str_replace("'","&#39;",_('Levels'))."' v='N_LEVELS' size='5' s='d' class='req'"), 'NLevels', "", $this->commentsNLevels, $this->commentsNLevels_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['DeployPct']['html'] = stdFieldRow(_("Deployed budget %"), input('number', 'DeployPct', $dataObj->getDeployPct(), " step='1' placeholder='".str_replace("'","&#39;",_('Deployed budget %'))."' v='DEPLOY_PCT' size='5' s='d' class=''"), 'DeployPct', "", $this->commentsDeployPct, $this->commentsDeployPct_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['Reason']['html'] = stdFieldRow(_("Reason"), input('text', 'Reason', htmlentities((string)($dataObj->getReason() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Reason'))."' size='69'  v='REASON' s='d' class=''  ")."", 'Reason', "", $this->commentsReason, $this->commentsReason_css, '', ' ', 'no', 'v2');
$this->fields['BotDecision']['PriceAt']['html'] = stdFieldRow(_("Price at decision"), input('number', 'PriceAt', $dataObj->getPriceAt(), "  placeholder='".str_replace("'","&#39;",_('Price at decision'))."'  v='PRICE_AT' size='10' s='d' class=''"), 'PriceAt', "", $this->commentsPriceAt, $this->commentsPriceAt_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['RealizedBefore']['html'] = stdFieldRow(_("Realized before"), input('number', 'RealizedBefore', $dataObj->getRealizedBefore(), "  placeholder='".str_replace("'","&#39;",_('Realized before'))."'  v='REALIZED_BEFORE' size='10' s='d' class=''"), 'RealizedBefore', "", $this->commentsRealizedBefore, $this->commentsRealizedBefore_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['EvalStatus']['html'] = stdFieldRow(_("Eval"), selectboxCustomArray('EvalStatus', [ '0' => ['0'=>_("Pending"), '1'=>"Pending"],'1' => ['0'=>_("Scored"), '1'=>"Scored"], ], "", "s='d'  ", $dataObj->getEvalStatus(), '', false), 'EvalStatus', "", $this->commentsEvalStatus, $this->commentsEvalStatus_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['EvalAt']['html'] = stdFieldRow(_("Scored at"), input('datetime-local', 'EvalAt', $dataObj->getEvalAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Scored at'"), 'EvalAt', "", $this->commentsEvalAt, $this->commentsEvalAt_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['AppliedAt']['html'] = stdFieldRow(_("Applied at"), input('datetime-local', 'AppliedAt', $dataObj->getAppliedAt(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD hh:mm:ss' size='30'  s='d' class='' title='Applied at'"), 'AppliedAt', "", $this->commentsAppliedAt, $this->commentsAppliedAt_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['CyclesDelta']['html'] = stdFieldRow(_("Cycles after"), input('number', 'CyclesDelta', $dataObj->getCyclesDelta(), " step='1' placeholder='".str_replace("'","&#39;",_('Cycles after'))."' v='CYCLES_DELTA' size='5' s='d' class=''"), 'CyclesDelta', "", $this->commentsCyclesDelta, $this->commentsCyclesDelta_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['RealizedDelta']['html'] = stdFieldRow(_("P/L after"), input('number', 'RealizedDelta', $dataObj->getRealizedDelta(), "  placeholder='".str_replace("'","&#39;",_('P/L after'))."'  v='REALIZED_DELTA' size='10' s='d' class=''"), 'RealizedDelta', "", $this->commentsRealizedDelta, $this->commentsRealizedDelta_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['PriceMovePct']['html'] = stdFieldRow(_("Price move %"), input('number', 'PriceMovePct', $dataObj->getPriceMovePct(), "  placeholder='".str_replace("'","&#39;",_('Price move %'))."'  v='PRICE_MOVE_PCT' size='5' s='d' class=''"), 'PriceMovePct', "", $this->commentsPriceMovePct, $this->commentsPriceMovePct_css, ' half', ' ', 'no', 'v2');
$this->fields['BotDecision']['Verdict']['html'] = stdFieldRow(_("Verdict"), selectboxCustomArray('Verdict', [ '0' => ['0'=>_("Win"), '1'=>"Win"],'1' => ['0'=>_("Flat"), '1'=>"Flat"],'2' => ['0'=>_("Loss"), '1'=>"Loss"],'3' => ['0'=>_("Superseded"), '1'=>"Superseded"], ], _('Verdict'), "s='d'  ", $dataObj->getVerdict(), '', true), 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, ' half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'Source',1=>'PLow',2=>'PHigh',3=>'NLevels',4=>'Reason',5=>'PriceAt',6=>'RealizedBefore',7=>'EvalStatus',8=>'EvalAt',9=>'AppliedAt',10=>'CyclesDelta',11=>'RealizedDelta',12=>'PriceMovePct',13=>'Verdict',14=>'IdCreation',15=>'IdModification',16=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }

        if($IdParent) {
            $this->fields['BotDecision']['IdGridRun']['html'] = input('hidden', 'IdGridRun', $IdParent, "s='d'");
        }




        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('BotDecision','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('BotDecision','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveBotDecision', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedBotDecision','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdBotDecision', $dataObj->getIdBotDecision(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Refit Decision'), _SITE_URL.'BotDecision', "class='nav-btn'")
                        .div(
                            span(_('Refit Decision'), "class='nav-title-type'")
                            .(isset($_gcNameVal) && trim((string)$_gcNameVal) !== ''
                                ? span(htmlspecialchars($_gcNameVal), "class='nav-title-name'")
                                : '')
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'BotDecision', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div("<div class='sw-grid'>" .
$this->fields['BotDecision']['IdGridRun']['html']
.$this->fields['BotDecision']['Source']['html']
.$this->fields['BotDecision']['PLow']['html']
.$this->fields['BotDecision']['PHigh']['html']
.$this->fields['BotDecision']['NLevels']['html']
.$this->fields['BotDecision']['DeployPct']['html']
.$this->fields['BotDecision']['Reason']['html']
.$this->fields['BotDecision']['PriceAt']['html']
.$this->fields['BotDecision']['RealizedBefore']['html']
.$this->fields['BotDecision']['EvalStatus']['html']
.$this->fields['BotDecision']['EvalAt']['html']
.$this->fields['BotDecision']['AppliedAt']['html']
.$this->fields['BotDecision']['CyclesDelta']['html']
.$this->fields['BotDecision']['RealizedDelta']['html']
.$this->fields['BotDecision']['PriceMovePct']['html']
.$this->fields['BotDecision']['Verdict']['html'] ."</div>",'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntBotDecision", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formBotDecision' class='mainForm formContent' ")
        .$this->hookFormBottom;

        // Remember-last-tab restore retired (also closes a HIGH XSS finding):
        // onglet tabs are now <button class="tab-btn" data-tab=...> (see $ongletf
        // above), so the legacy [href=...] selector matched no tab. Worse, it
        // interpolated the user-influenced session value ['ogf'] unescaped into a
        // JS string literal — a break-out/self-XSS vector. drawer.js marks the
        // first tab active by default; the stale session ['ogf'] value is inert.
        $tabs_act = '';

        if($_SESSION['mem']['BotDecision']['ixmemautocapp'] and $_GET['Autocapp'] == 1) {
            $Autocapp = $_SESSION['mem']['BotDecision']['ixmemautocapp'];
            unset($_SESSION['mem']['BotDecision']['ixmemautocapp']);
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

        $this->fieldsRo['BotDecision']['IdGridRun']['html'] = stdFieldRow(_("Run"), div( htmlspecialchars((string)(($dataObj->getGridRun())?($dataObj->getGridRun()->getLabel()):''), ENT_QUOTES), 'IdGridRun_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdGridRun', $dataObj->getIdGridRun(), "s='d'"), 'IdGridRun', "", $this->commentsIdGridRun, $this->commentsIdGridRun_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['Source']['html'] = stdFieldRow(_("Source"), div( htmlspecialchars((string)($dataObj->getSource()), ENT_QUOTES), 'Source_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Source', $dataObj->getSource(), "s='d'"), 'Source', "", $this->commentsSource, $this->commentsSource_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['PLow']['html'] = stdFieldRow(_("Range low"), div( htmlspecialchars((string)($dataObj->getPLow()), ENT_QUOTES), 'PLow_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PLow', $dataObj->getPLow(), "s='d'"), 'PLow', "", $this->commentsPLow, $this->commentsPLow_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['PHigh']['html'] = stdFieldRow(_("Range high"), div( htmlspecialchars((string)($dataObj->getPHigh()), ENT_QUOTES), 'PHigh_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PHigh', $dataObj->getPHigh(), "s='d'"), 'PHigh', "", $this->commentsPHigh, $this->commentsPHigh_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['NLevels']['html'] = stdFieldRow(_("Levels"), div( htmlspecialchars((string)($dataObj->getNLevels()), ENT_QUOTES), 'NLevels_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'NLevels', $dataObj->getNLevels(), "s='d'"), 'NLevels', "", $this->commentsNLevels, $this->commentsNLevels_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['DeployPct']['html'] = stdFieldRow(_("Deployed budget %"), div( htmlspecialchars((string)($dataObj->getDeployPct()), ENT_QUOTES), 'DeployPct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DeployPct', $dataObj->getDeployPct(), "s='d'"), 'DeployPct', "", $this->commentsDeployPct, $this->commentsDeployPct_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['Reason']['html'] = stdFieldRow(_("Reason"), div( htmlspecialchars((string)($dataObj->getReason()), ENT_QUOTES), 'Reason_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Reason', $dataObj->getReason(), "s='d'"), 'Reason', "", $this->commentsReason, $this->commentsReason_css, 'readonly', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['PriceAt']['html'] = stdFieldRow(_("Price at decision"), div( htmlspecialchars((string)($dataObj->getPriceAt()), ENT_QUOTES), 'PriceAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PriceAt', $dataObj->getPriceAt(), "s='d'"), 'PriceAt', "", $this->commentsPriceAt, $this->commentsPriceAt_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['RealizedBefore']['html'] = stdFieldRow(_("Realized before"), div( htmlspecialchars((string)($dataObj->getRealizedBefore()), ENT_QUOTES), 'RealizedBefore_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RealizedBefore', $dataObj->getRealizedBefore(), "s='d'"), 'RealizedBefore', "", $this->commentsRealizedBefore, $this->commentsRealizedBefore_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['EvalStatus']['html'] = stdFieldRow(_("Eval"), div( htmlspecialchars((string)($dataObj->getEvalStatus()), ENT_QUOTES), 'EvalStatus_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EvalStatus', $dataObj->getEvalStatus(), "s='d'"), 'EvalStatus', "", $this->commentsEvalStatus, $this->commentsEvalStatus_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['EvalAt']['html'] = stdFieldRow(_("Scored at"), div( htmlspecialchars((string)($dataObj->getEvalAt()), ENT_QUOTES), 'EvalAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'EvalAt', $dataObj->getEvalAt(), "s='d'"), 'EvalAt', "", $this->commentsEvalAt, $this->commentsEvalAt_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['AppliedAt']['html'] = stdFieldRow(_("Applied at"), div( htmlspecialchars((string)($dataObj->getAppliedAt()), ENT_QUOTES), 'AppliedAt_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'AppliedAt', $dataObj->getAppliedAt(), "s='d'"), 'AppliedAt', "", $this->commentsAppliedAt, $this->commentsAppliedAt_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['CyclesDelta']['html'] = stdFieldRow(_("Cycles after"), div( htmlspecialchars((string)($dataObj->getCyclesDelta()), ENT_QUOTES), 'CyclesDelta_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'CyclesDelta', $dataObj->getCyclesDelta(), "s='d'"), 'CyclesDelta', "", $this->commentsCyclesDelta, $this->commentsCyclesDelta_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['RealizedDelta']['html'] = stdFieldRow(_("P/L after"), div( htmlspecialchars((string)($dataObj->getRealizedDelta()), ENT_QUOTES), 'RealizedDelta_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RealizedDelta', $dataObj->getRealizedDelta(), "s='d'"), 'RealizedDelta', "", $this->commentsRealizedDelta, $this->commentsRealizedDelta_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['PriceMovePct']['html'] = stdFieldRow(_("Price move %"), div( htmlspecialchars((string)($dataObj->getPriceMovePct()), ENT_QUOTES), 'PriceMovePct_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PriceMovePct', $dataObj->getPriceMovePct(), "s='d'"), 'PriceMovePct', "", $this->commentsPriceMovePct, $this->commentsPriceMovePct_css, 'readonly half', ' ', 'no', 'v2');

        $this->fieldsRo['BotDecision']['Verdict']['html'] = stdFieldRow(_("Verdict"), div( htmlspecialchars((string)($dataObj->getVerdict()), ENT_QUOTES), 'Verdict_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Verdict', $dataObj->getVerdict(), "s='d'"), 'Verdict', "", $this->commentsVerdict, $this->commentsVerdict_css, 'readonly half', ' ', 'no', 'v2');


        if($fields == 'all') {
            foreach($this->fields['BotDecision'] as $field => $ar) {
                $this->fields['BotDecision'][$field]['html'] = $this->fieldsRo['BotDecision'][$field]['html'];
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field) {
                $this->fields['BotDecision'][$field]['html'] = $this->fieldsRo['BotDecision'][$field]['html'];
            }
        }
    }

    /**
     * Query for BotDecision_IdGridRun selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxBotDecision_IdGridRun(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && !method_exists($gcSbHost, 'beginSelectboxBotDecision_IdGridRun')
        && !method_exists($gcSbHost, 'selectboxDataBotDecision_IdGridRun');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('grid_run', 'BotDecision_IdGridRun', false);
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = GridRunQuery::create();

    $gcSbHost = is_object($obj) ? $obj : $this;
    if(method_exists($gcSbHost, 'beginSelectboxBotDecision_IdGridRun') and $array)
        $ret = $gcSbHost->beginSelectboxBotDecision_IdGridRun($q, $dataObj, $data, $obj);
    if($ret !== false)
            $q->addAsColumn('selDisplay', ''.GridRunPeer::LABEL.'');
            $q->select(['selDisplay', 'IdGridRun']);
            $q->orderBy('selDisplay', 'ASC');
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataBotDecision_IdGridRun')){
                $gcSbHost->selectboxDataBotDecision_IdGridRun($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt , true);
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('grid_run', 'BotDecision_IdGridRun', false, $gcSbResult);
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
}
