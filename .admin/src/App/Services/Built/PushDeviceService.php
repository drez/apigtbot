<?php

declare(strict_types=1);

namespace App;


/**
 * AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 * Service Class
 * Provide Response for the backend controler
 */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Utility\BuilderLayout;
use ApiGoat\Utility\BuilderMenus;
use ApiGoat\Handlers\BuilderReturn;
use ApiGoat\Handlers\PropelErrorHandler;
use ApiGoat\Api\ApiResponse;
use ApiGoat\Api\Api;


class PushDeviceService
{

    /**
     * return abstract
     * @var array
     */
    public $content=['html'=>'', 'onReadyJs'=>'', 'js'=>'', 'json' =>''];
    /**
     *
     * @var BuilderLayout object
     */
    public $BuilderLayout;
    /**
     * legacy variable for all arguments
     * @var array
     */
    public $request=[];
    /**
     *
     * @var PSR-7 response object
     * immutable object
     */
    private $response;

    /**
     * Add custom callable actions
     *
     * @var array
     */
    public $customActions;
    public $rawRequest;
    public $Form;
    public $contentType;
    public $headers;
    public $body;
    public $Name;
    public $length;
    /** Confined on-disk path set by getFileContent(); routes.php streams
     *  video/audio from here instead of the in-memory body. */
    public $filePath;

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function __construct($request, $response, $args )
    {
        $this->rawRequest = $request;
        $this->response = $response;
        $this->BuilderLayout = new BuilderLayout(new BuilderMenus($args));
        // legacy
        $this->request = $args;
        $this->Form = new PushDeviceFormWrapper($request, $args);
    }

    /**
     * Get the proper response
     * @return string
     */
    public function getResponse()
    {
        $this->content = "Unknown method";

        switch($this->request['a']){
            case '':
            case 'list':
                $this->content = $this->list();
            break;
            case 'edit':
            case 'view':
                if ($this->request['a'] === 'edit' && empty($this->request['ui'])) {
                    // Deep-link entry: a full-page /{Model}/edit/{id} renders the
                    // list and bootstraps the edit drawer open on top of it. The
                    // id is request-controlled, so json_encode with the HEX flags
                    // produces a JS string literal that is safe inside <script>
                    // (escapes < > ' " to \uXXXX) — no quote/script breakout.
                    $this->content = $this->list();
                    $gcEditPk = json_encode((string) $this->request['i'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
                    $this->content['onReadyJs'] .= "if(window.gcScreens){history.replaceState({gcEdit:null},'',_SITE_URL+'PushDevice/');gcScreens.openEdit('PushDevice'," . $gcEditPk . ");}";
                } else {
                    $this->content = $this->edit();
                }
            break;
            case 'update':
            case 'insert':
                $this->content = $this->saveUpdate();
                $this->content['onReadyJs'] .= ($this->content['error'] != 'yes')?"sw_message('".addslashes(_('Saved'))."');":'';
                return $this->BuilderLayout->renderXHR($this->content);
            case 'delete':
                $this->content = $this->deleteOne();
                return $this->BuilderLayout->renderXHR($this->content);



        case 'autoc':
            $this->body = $this->autocomplete();
            $this->contentType = 'application/json';
            return json_encode($this->body, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        break;













            default:
                if (method_exists($this, $this->customActions[$this->request['a']])) {
                    $callable = $this->customActions[$this->request['a']];
                    $this->content = $this->$callable($this->request);
                }
        }



        if($this->request['ui']){
            return $this->BuilderLayout->renderXHR($this->content);
        }else{
            return $this->BuilderLayout->render($this->content);
        }
    }
    
    /**
    * Get the proper api response
    * @return array
    */
    public function getApiResponse()
    {
        $this->body = ['status' => 'failure', 'errors' => ['Unknown method'], 'data' => null, 'messages' => null];
        $Api = new Api('PushDevice', $this, ['IdAuthy', 'Token', 'Platform']);

        if (isset($this->customActions[$this->request['a']]) && method_exists($this, $this->customActions[$this->request['a']])) {
            $callable = $this->customActions[$this->request['a']];
            $this->body = $this->$callable($Api);
        }else{
            switch($this->request['method']){
                case 'AUTH':
                    $dispatch = $this->request['a'];
                    $this->body = $this->$dispatch();
                    break;
                case 'GET':
                    $this->body = $Api->getJson($this->request);
                    break;
                case 'POST':
                case 'PATCH':
                    if ($this->request['a'] == 'list') {
                        $this->body = $Api->getJson($this->request);
                    } else {
                        $this->body = $Api->setJson($this->request);
                    }
                    break;
                case 'PUT':
                    $this->body = $Api->file($this->request);
                    break;
                case 'DELETE':
                    $this->body = $Api->deleteJson($this->request);
                    break;
            }
        }



        $ApiResponse = new ApiResponse($this->request, $this->response, $this->body);
        return $ApiResponse->getResponse();
    }





        function autocomplete()
    {
        $body = ['data' => [], 'count' => 0, 'status' => 'error'];

        $fkt    = $this->request['data']['fkt']    ?? null;
        $show   = $this->request['data']['show']   ?? null;
        $id     = $this->request['data']['id']     ?? null;
        $filter = $this->request['data']['filter'] ?? null;
        $str    = $this->request['data']['str']    ?? '';
        $limit  = (int)($this->request['data']['limit'] ?? 20);
        if ($limit <= 0 || $limit > 100) { $limit = 20; }

        if (!$fkt || !$id || !$filter || !$show) {
            $body['error'] = 'missing parameter';
            return $body;
        }
        if (!is_array($show)) {
            $show = array_values(array_filter(array_map('trim', explode(',', (string)$show)), 'strlen'));
        }
        if (!$show) {
            $body['error'] = 'empty show';
            return $body;
        }

        // SECURITY: require an authenticated session and bind fkt/show/id to the
        // build-time allowlist (autocAllowlist) of THIS form's own autocompletes.
        // Without this a logged-in user could select arbitrary columns of any
        // model (e.g. Authy.PasswdHash).
        if (!is_object($_SESSION[_AUTH_VAR] ?? null)) {
            $body['error'] = 'unauthenticated';
            return $body;
        }
        $allow = $this->autocAllowlist();
        if (!isset($allow[$fkt])) {
            $body['error'] = 'table not allowed';
            return $body;
        }
        $show = array_values(array_intersect($show, $allow[$fkt]['cols']));
        if (!$show) {
            $body['error'] = 'no allowed columns';
            return $body;
        }
        if (!in_array($id, $allow[$fkt]['ids'], true)) {
            $body['error'] = 'id not allowed';
            return $body;
        }

        $Model = '\\App\\' . $fkt . 'Query';
        if (!class_exists($Model)) {
            $body['error'] = 'unknown table';
            return $body;
        }

        $select = array_values(array_unique(array_merge($show, [$id])));
        $q = $Model::create()->select($select)->orderBy($show[0], 'ASC');

        // The text search may only filter on a column the picker actually
        // shows. Restricting to $show stops a crafted request from using
        // filter[] as a value-confirmation oracle on arbitrary columns.
        if (is_array($filter)) {
            foreach ($filter as $field => $val) {
                if (!in_array($field, $show, true)) { continue; }
                $method = 'filterBy' . $field;
                if (!method_exists($Model, $method)) { continue; }
                if (is_array($val)) {
                    $q->$method('%' . $val[0] . '%');
                    if (($val[1] ?? null) === 'or') { $q->_or(); }
                } else {
                    $q->$method('%' . $val . '%');
                }
            }
        } else {
            if (in_array($filter, $show, true)) {
                $method = 'filterBy' . $filter;
                if (method_exists($Model, $method)) {
                    $q->$method('%' . $str . '%');
                }
            }
        }

        // depends_on constraints: equality filters on the FK table, sent as
        // where{ColPhpName: value}. Empty values are ignored so an unset
        // source field leaves the lookup unconstrained. Bound to the columns
        // THIS form's autocompletes declare as depends_on sources (allowlist
        // 'where') so a crafted request can't equality-probe arbitrary FK-model
        // columns (a value-confirmation oracle) — the same guard $filter has.
        $where = $this->request['data']['where'] ?? null;
        if (is_array($where)) {
            $allowWhere = $allow[$fkt]['where'] ?? [];
            foreach ($where as $field => $val) {
                $val = trim((string)$val);
                if ($val === '') { continue; }
                $fieldPhp = preg_replace('/[^A-Za-z0-9_]/', '', (string)$field);
                if (!in_array($fieldPhp, $allowWhere, true)) { continue; }
                $method = 'filterBy' . $fieldPhp;
                if (method_exists($Model, $method)) {
                    $q->$method($val);
                }
            }
        }

        // Ownership scope: when the caller's read right on the FK target is
        // Owner/Group-scoped, autocomplete only the rows they may access
        // (mirrors Api::setAclFilter). method_exists guards mean ungoverned
        // reference tables (no id_creation column, e.g. Country) and "All"
        // rights are unaffected — browse stays open there.
        $s = $_SESSION[_AUTH_VAR] ?? null;
        // Tenant row-scoping: mirror AuthyACL::setAclFilter so autocomplete can't
        // surface FK-target rows from other tenants (non-root users).
        if (is_object($s) && method_exists($s, 'get')
            && !$s->get('isRoot') && $s->get('id_tenant')
            && method_exists($Model, 'filterByIdTenant')) {
            $q->filterByIdTenant($s->get('id_tenant'));
        }
        if (is_object($s) && method_exists($s, 'hasRights')) {
            $scope = $s->hasRights($fkt, 'r');
            if (is_array($scope)) {
                if (in_array('Owner', $scope, true) && method_exists($Model, 'filterByIdCreation')) {
                    $q->filterByIdCreation($s->getIdAuthy());
                    if (in_array('Group', $scope, true) && method_exists($Model, 'filterByIdGroupCreation')) {
                        $q->_or()->filterByIdGroupCreation($s->getGroups(), \Criteria::IN);
                    }
                } elseif (in_array('Group', $scope, true) && method_exists($Model, 'filterByIdGroupCreation')) {
                    $q->filterByIdGroupCreation($s->getGroups(), \Criteria::IN);
                }
            }
        }

        $q->limit($limit);
        $results = $q->find();

        foreach ($results as $row) {
            $row = (array)$row;
            $parts = [];
            foreach ($show as $f) {
                if (isset($row[$f]) && strlen((string)$row[$f])) { $parts[] = $row[$f]; }
            }
            $body['data'][] = ['show' => implode(' ', $parts), 'id' => $row[$id] ?? null];
        }
        $body['count'] = count($body['data']);
        $body['status'] = 'success';
        return $body;
    }
    private function autocAllowlist()
    {
        return array (
  'Authy' =>
  array (
    'cols' =>
    array (
      0 => 'Fullname',
      1 => 'IdAuthy',
    ),
    'ids' =>
    array (
      0 => 'IdAuthy',
    ),
    'where' =>
    array (
    ),
  ),
);
    }









    public function deleteOne()
    {
        $error = [];
        $messages = '';

        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(PushDeviceQuery::class, json_decode($this->request['i']), 'PushDevice', 'd');
        if ($obj) {



        $obj->delete();



        }

        $BuilderReturn = new BuilderReturn($this->request, $error, $messages);
        return $BuilderReturn->return();
    }

    public function saveUpdate(): array
    {
        $messages = null;
        $error = null;

        $extValidationErr = false;
        parse_str ($this->request['d'], $data );

        $data['i'] = ( $data['IdPushDevice'] ) ? $data['IdPushDevice'] : $this->request['i'];
        $data['ip'] = urldecode($this->request['data']['ip'] ?? '');
        $data['pc'] = urldecode($this->request['data']['pc'] ?? '');
        $this->PushDevice['request'] = $this->request;

        if(!empty($data['i'])) {
            ## Save

            $e = $this->Form->setUpdateDefaultsPushDevice($data);
            if ($e === null) {
                $error = [_('Record not found or access denied')];
            } else {



            if ($e->validate() && !$extValidationErr) {
                $e->save();
                $this->request['i'] = json_encode($e->getPrimaryKey());


            }else{
                $PropelErrorHandler = new PropelErrorHandler($e, $this->request['data']['ui'],_('Form field'), $extValidationErr, $this->virtualClassName);
                $error = $PropelErrorHandler->getValidationErrors();
            }
            }
        } else {
            ## Create


            $e = $this->Form->setCreateDefaultsPushDevice($data);



            if ($e->validate() && !$extValidationErr) {
                $e->save();
                $this->request['i'] = json_encode($e->getPrimaryKey());


                //
            }else{
                $PropelErrorHandler = new PropelErrorHandler($e, $this->request['data']['ui'],_('Form field'), $extValidationErr, $this->virtualClassName);
                $error = $PropelErrorHandler->getValidationErrors();
            }
        }

        $BuilderReturn = new BuilderReturn($this->request, $error, $messages);
        return $BuilderReturn->return();
    }



    /**
    * Return the main edit form, including child lists
    * @return string
    */
    private function edit()
    {
        $this->PushDevice['request'] = $this->request;
        $this->PushDevice['parentId'] = $this->request['data']['ip'];


        // Crossref (NtN) far-record view: ro=1 renders the whole form via the
        // dormant setReadOnly='all' switch — fields locked to fieldsRo, no
        // Save bar / delete column / Add-new. Display-only; write auth is RBAC.
        if (!empty($this->request['data']['ro'])) {
            $this->Form->setReadOnly = 'all';
        }

        $relData = $this->request;
        $output = $this->Form->getEditForm($this->request['i'], $this->request['ui'], $relData, '', $this->request['data']['je'], $this->request['data']['jet']);

        return $output;
    }

    /**
    * Retrun the main list
    * @return string
    */
    private function list()
    {
        $output = $this->Form->getList($this->request);

        return $output;
    }



}
