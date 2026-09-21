<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'AuthyGroup' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class AuthyGroupForm extends AuthyGroup
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

        public $commentsIdAuthyGroup;
    public $commentsIdAuthyGroup_css;
    public $commentsName;
    public $commentsName_css;
    public $commentsDesc;
    public $commentsDesc_css;
    public $commentsDefaultGroup;
    public $commentsDefaultGroup_css;
    public $commentsAdmin;
    public $commentsAdmin_css;
    public $commentsRightsAll;
    public $commentsRightsAll_css;
    public $commentsRightsOwner;
    public $commentsRightsOwner_css;
    public $commentsRightsGroup;
    public $commentsRightsGroup_css;
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
    public $AuthyGroup;


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
        $this->model_name = 'AuthyGroup';
        $this->virtualClassName = 'AuthyGroup';
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

        $q = new AuthyGroupQuery();
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
                                    . ' on AuthyGroup — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['AuthyGroup/'],
                                    $_SESSION['mem']['order']['AuthyGroup/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#AuthyGroupListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
                $trHead = th(_("Name"), " th='sorted' c='Name' title='" . _('Name')."' ")
.th(_("Description"), " th='sorted' c='Desc' title='" . _('Description')."' ")
.th(_("Default"), " th='sorted' c='DefaultGroup' title='" . _('Default')."' ")
.th(_("Admin"), " th='sorted' c='Admin' title='" . _('Admin')."' ")
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
                if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addAuthyGroup' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'AuthyGroup';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdAuthyGroup' => NULL,
  'Name' => NULL,
  'Desc' => NULL,
  'DefaultGroup' => NULL,
  'Admin' => NULL,
  'RightsAll' => NULL,
  'RightsOwner' => NULL,
  'RightsGroup' => NULL,
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
            $this->isChild = 'AuthyGroup';
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
        $gcListKey = 'AuthyGroup/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'AuthyGroup/child';
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
            $gcGroupCol = 'Name';
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
                if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteAuthyGroup' ");
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
   div('' . span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." ", "   i='" . $__pkJsonEsc . "' c='Name' class=''  j='editAuthyGroup'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Desc'] !== null ) ? $altValue['Desc'] : $data->getDesc())))." ", "   i='" . $__pkJsonEsc . "' c='Desc' class=''  j='editAuthyGroup'") . span(htmlspecialchars((string)((($altValue['DefaultGroup'] !== null ) ? $altValue['DefaultGroup'] : isntPo($data->getDefaultGroup()))))." ", "   i='" . $__pkJsonEsc . "' c='DefaultGroup' class='center'  j='editAuthyGroup'") . span(htmlspecialchars((string)((($altValue['Admin'] !== null ) ? $altValue['Admin'] : isntPo($data->getAdmin()))))." ", "   i='" . $__pkJsonEsc . "' c='Admin' class='center'  j='editAuthyGroup'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='AuthyGroupRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName())))." "), "  i='" . $__pkJsonEsc . "' c='Name' class=''  j='editAuthyGroup'") .
                td(span(htmlspecialchars((string)((($altValue['Desc'] !== null ) ? $altValue['Desc'] : $data->getDesc())))." "), "  i='" . $__pkJsonEsc . "' c='Desc' class=''  j='editAuthyGroup'") .
                td(span(htmlspecialchars((string)((($altValue['DefaultGroup'] !== null ) ? $altValue['DefaultGroup'] : isntPo($data->getDefaultGroup()))))." "), "  i='" . $__pkJsonEsc . "' c='DefaultGroup' class='center'  j='editAuthyGroup'") .
                td(span(htmlspecialchars((string)((($altValue['Admin'] !== null ) ? $altValue['Admin'] : isntPo($data->getAdmin()))))." "), "  i='" . $__pkJsonEsc . "' c='Admin' class='center'  j='editAuthyGroup'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='AuthyGroupDtRow".$__pkEsc."'");

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Name'] !== null ) ? $altValue['Name'] : $data->getName()));
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
            $tr .= input('hidden', 'rowCountAuthyGroup', $i);

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
                .div($controlsContent,'AuthyGroupControlsList', "class='custom-controls'")
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
                        .span(_('Group'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Name"), " type='button' th='sorted' c='Name' class='va-mob-sortrow' ") . button(_("Description"), " type='button' th='sorted' c='Desc' class='va-mob-sortrow' ") . button(_("Default"), " type='button' th='sorted' c='DefaultGroup' class='va-mob-sortrow' ") . button(_("Admin"), " type='button' th='sorted' c='Admin' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='AuthyGroupTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'AuthyGroupListForm', " class='va-mob proto-app' data-model='AuthyGroup' data-table='AuthyGroup' data-gc-db='authy_group' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addAuthyGroupAutoc');
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
    public function setCreateDefaultsAuthyGroup(array $data): AuthyGroup
    {

        unset($data['IdAuthyGroup']);
        $e = new AuthyGroup();

        if($data['noRights'] != 'y'){

            $data['RightsAll'] = serializeRights($data, 'RightsAll');
            $data['RightsOwner'] = serializeRights($data, 'RightsOwner');
            $data['RightsGroup'] = serializeRights($data, 'RightsGroup');
        }

        if(!$data['DefaultGroup']){
            $data['DefaultGroup'] = 'No';
        }
        if(!$data['Admin']){
            $data['Admin'] = 'No';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //varchar not required
        $e->setName( ($data['Name'] == '' ) ? null : $data['Name']);
        //varchar not required
        $e->setDesc( ($data['Desc'] == '' ) ? null : $data['Desc']);
        //varchar not required
        $e->setRightsAll( ($data['RightsAll'] == '' ) ? null : $data['RightsAll']);
        //varchar not required
        $e->setRightsOwner( ($data['RightsOwner'] == '' ) ? null : $data['RightsOwner']);
        //varchar not required
        $e->setRightsGroup( ($data['RightsGroup'] == '' ) ? null : $data['RightsGroup']);
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsAuthyGroup(array $data): ?AuthyGroup
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyGroupQuery::class, json_decode($data['i']), 'AuthyGroup', 'w');
        if ($e === null) { return null; }

        if($data['noRights'] != 'y'){

            $data['RightsAll'] = serializeRights($data, 'RightsAll');
            $data['RightsOwner'] = serializeRights($data, 'RightsOwner');
            $data['RightsGroup'] = serializeRights($data, 'RightsGroup');
        }

        if(!$data['DefaultGroup']){
            $data['DefaultGroup'] = 'No';
        }
        if(!$data['Admin']){
            $data['Admin'] = 'No';
        }
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['Name'])){
            $e->setName( ($data['Name'] == '' ) ? null : $data['Name']);
        }
        if(isset($data['Desc'])){
            $e->setDesc( ($data['Desc'] == '' ) ? null : $data['Desc']);
        }
        if(isset($data['RightsAll'])){
            $e->setRightsAll( ($data['RightsAll'] == '' ) ? null : $data['RightsAll']);
        }
        if(isset($data['RightsOwner'])){
            $e->setRightsOwner( ($data['RightsOwner'] == '' ) ? null : $data['RightsOwner']);
        }
        if(isset($data['RightsGroup'])){
            $e->setRightsGroup( ($data['RightsGroup'] == '' ) ? null : $data['RightsGroup']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of AuthyGroup
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

        $je = "AuthyGroupTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addAuthyGroupForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = AuthyGroupQuery::create()

            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->AuthyGroup['isNew'] = 'yes';
            $dataObj = new AuthyGroup();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->AuthyGroup['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;








        
    ## Rights for RightsGroup
    if($_SESSION[_AUTH_VAR]->get('group') == 'Admin'){
        $rightsGroup = array (
  'All' => 'RightsAll',
  'Owner' => 'RightsOwner',
  'Group' => 'RightsGroup',
);
        if(is_array($rightsGroup)){
            foreach($rightsGroup as $group => $columnName){
                $getColumn = "get{$columnName}";
                $userRightsAr[$group] = json_decode($dataObj->$getColumn() ?? '', true);
            }

            if(!isset($this->omMap)){
                require _BASE_DIR."config/permissions.php";
                $this->omMap = $omMap;
            }
            unset($rightTables);
            foreach($this->omMap as $key => $row){
                $name[$key] = $row['display'];
            }
                array_multisort($name,SORT_ASC,$this->omMap);
                $rightTables = \ApiGoat\Renderers\Rights::getRightsTable('All', $this->omMap, $userRightsAr['All']);
                $rightTables .= \ApiGoat\Renderers\Rights::getRightsTable('Group', $this->omMap, $userRightsAr['Group']);
                $rightTables .= \ApiGoat\Renderers\Rights::getRightsTable('Owner', $this->omMap, $userRightsAr['Owner']);

            $tabRights = new tabs(
                    [
                        'All' => ['id' => 'RigthsAllTab', 'targetDiv' => '#RigthsAll', 'defaultSelected' => 'true'], 'Group' => ['id' => 'RigthsGroupTab', 'targetDiv' => '#RigthsGroup'], 'Owner' => ['id' => 'RigthsOwnerTab', 'targetDiv' => '#RigthsOwner']
                    ],
                    "RightsTabs",
                    '',
                    'Rights'
                );
            $tabRights->setParentContentDivId("RigthsContainer");
            $tabRights->setLabel("Set the rights (All gives unrestricted access; Group refers to the creation group; Owner refers to the creation user) ");

            $rightInputRightsAll = $tabRights->getHtml()
                . div(
                    $rightTables,
                    'RigthsContainer'
                );
        }

    }




$this->fields['AuthyGroup']['Name']['html'] = stdFieldRow(_("Name"), input('text', 'Name', htmlentities((string)($dataObj->getName() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Name'))."' size='35'  v='NAME' s='d' class='req'  ")."", 'Name', "", $this->commentsName, $this->commentsName_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['Desc']['html'] = stdFieldRow(_("Description"), input('text', 'Desc', htmlentities((string)($dataObj->getDesc() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Description'))."' size='35'  v='DESC' s='d' class=''  ")."", 'Desc', "", $this->commentsDesc, $this->commentsDesc_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['DefaultGroup']['html'] = stdFieldRow(_("Default"), selectboxCustomArray('DefaultGroup', [ '0' => ['0'=>_("No"), '1'=>"No"],'1' => ['0'=>_("Yes"), '1'=>"Yes"], ], "", "s='d'  ", $dataObj->getDefaultGroup(), '', false), 'DefaultGroup', "", $this->commentsDefaultGroup, $this->commentsDefaultGroup_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['Admin']['html'] = stdFieldRow(_("Admin"), selectboxCustomArray('Admin', [ '0' => ['0'=>_("No"), '1'=>"No"],'1' => ['0'=>_("Yes"), '1'=>"Yes"], ], "", "s='d'  ", $dataObj->getAdmin(), '', false), 'Admin', "", $this->commentsAdmin, $this->commentsAdmin_css, ' half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['RightsAll']['html'] = stdFieldRow(_("Rights"), $rightInputRightsAll, 'RightsAll', "", $this->commentsRightsAll, $this->commentsRightsAll_css, ' rightsTr half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['RightsOwner']['html'] = stdFieldRow(_("Rights owner"), '', 'RightsOwner', "", $this->commentsRightsOwner, $this->commentsRightsOwner_css, ' rightsTr hide half', ' ', 'no', 'v2');
$this->fields['AuthyGroup']['RightsGroup']['html'] = stdFieldRow(_("Rights group"), '', 'RightsGroup', "", $this->commentsRightsGroup, $this->commentsRightsGroup_css, ' rightsTr hide half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

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
            if( ($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('AuthyGroup','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveAuthyGroup', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedAuthyGroup','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdAuthyGroup', $dataObj->getIdAuthyGroup(), " s='d' pk").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        // Custom drawer tabs registered by the wrapper (FormHelper::addFormTab, e.g. from afterFormObj).
        [$gcTabNavFirst, $gcTabNavLast, $gcPanesFirst, $gcPanesLast, $gcFirstTabActive] = $this->renderFormCustomTabs();
        $ongletf =
            div(
                div($gcTabNavFirst . button(_('Group'), ' type="button" class="tab-btn' . ($gcFirstTabActive ? ' is-active' : '') . '" role="tab" data-tab="tab_AuthyGroup" aria-selected="' . ($gcFirstTabActive ? 'true' : 'false') . '" aria-controls="tab_AuthyGroup" ')
                    .button(_('Rights'), ' type="button" class="tab-btn" role="tab" data-tab="tab_rights_all" aria-selected="false" aria-controls="tab_rights_all" ') . $gcTabNavLast,'',"class='sw-tabnav' role='tablist'")
            ,'cntOngletAuthyGroup',' class="cntOnglet"')
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
                        href('<i class="ri-arrow-left-s-line"></i>'._('Group'), _SITE_URL.'AuthyGroup', "class='nav-btn'")
                        .div(
                            span(_('Group'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'AuthyGroup', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div('' . $gcPanesFirst .
                    '<div id="tab_AuthyGroup" class="tab-pane' . (($gcFirstTabActive ?? true) ? ' is-active' : '') . '" role="tabpanel" data-tab="tab_AuthyGroup"' . (($gcFirstTabActive ?? true) ? '' : ' hidden') . '><div class="sw-grid">'.
$this->fields['AuthyGroup']['Name']['html']
.$this->fields['AuthyGroup']['Desc']['html']
.$this->fields['AuthyGroup']['DefaultGroup']['html']
.$this->fields['AuthyGroup']['Admin']['html']
.'</div></div><div id="tab_rights_all" class="tab-pane" role="tabpanel" data-tab="tab_rights_all" hidden><div class="sw-grid">'
.$this->fields['AuthyGroup']['RightsAll']['html']
.$this->fields['AuthyGroup']['RightsOwner']['html']
.$this->fields['AuthyGroup']['RightsGroup']['html'].'</div></div>' . $gcPanesLast ,'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntAuthyGroup", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formAuthyGroup' class='mainForm formContent' ")
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
        
{$tabRights?->getOnReadyJs()}

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
            $fields = array_keys($this->fields['AuthyGroup']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['AuthyGroup'][$field]['html'] = $this->fieldsRo['AuthyGroup'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Name':
        $this->fieldsRo['AuthyGroup']['Name']['html'] = stdFieldRow(_("Name"), div( htmlspecialchars((string)($dataObj->getName()), ENT_QUOTES), 'Name_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Name', $dataObj->getName(), "s='d'"), 'Name', "", $this->commentsName, $this->commentsName_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Desc':
        $this->fieldsRo['AuthyGroup']['Desc']['html'] = stdFieldRow(_("Description"), div( htmlspecialchars((string)($dataObj->getDesc()), ENT_QUOTES), 'Desc_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Desc', $dataObj->getDesc(), "s='d'"), 'Desc', "", $this->commentsDesc, $this->commentsDesc_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'DefaultGroup':
        $this->fieldsRo['AuthyGroup']['DefaultGroup']['html'] = stdFieldRow(_("Default"), div( htmlspecialchars((string)($dataObj->getDefaultGroup()), ENT_QUOTES), 'DefaultGroup_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'DefaultGroup', $dataObj->getDefaultGroup(), "s='d'"), 'DefaultGroup', "", $this->commentsDefaultGroup, $this->commentsDefaultGroup_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Admin':
        $this->fieldsRo['AuthyGroup']['Admin']['html'] = stdFieldRow(_("Admin"), div( htmlspecialchars((string)($dataObj->getAdmin()), ENT_QUOTES), 'Admin_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Admin', $dataObj->getAdmin(), "s='d'"), 'Admin', "", $this->commentsAdmin, $this->commentsAdmin_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'RightsAll':
        $this->fieldsRo['AuthyGroup']['RightsAll']['html'] = stdFieldRow(_("Rights"), div( htmlspecialchars((string)($dataObj->getRightsAll()), ENT_QUOTES), 'RightsAll_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsAll', $dataObj->getRightsAll(), "s='d'"), 'RightsAll', "", $this->commentsRightsAll, $this->commentsRightsAll_css, 'readonly rightsTr half', ' ', 'no', 'v2');

            break;
            case 'RightsOwner':
        $this->fieldsRo['AuthyGroup']['RightsOwner']['html'] = stdFieldRow(_("Rights owner"), div( htmlspecialchars((string)($dataObj->getRightsOwner()), ENT_QUOTES), 'RightsOwner_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsOwner', $dataObj->getRightsOwner(), "s='d'"), 'RightsOwner', "", $this->commentsRightsOwner, $this->commentsRightsOwner_css, 'readonly rightsTr hide half', ' ', 'no', 'v2');

            break;
            case 'RightsGroup':
        $this->fieldsRo['AuthyGroup']['RightsGroup']['html'] = stdFieldRow(_("Rights group"), div( htmlspecialchars((string)($dataObj->getRightsGroup()), ENT_QUOTES), 'RightsGroup_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsGroup', $dataObj->getRightsGroup(), "s='d'"), 'RightsGroup', "", $this->commentsRightsGroup, $this->commentsRightsGroup_css, 'readonly rightsTr hide half', ' ', 'no', 'v2');

            break;
        }
    }
}
