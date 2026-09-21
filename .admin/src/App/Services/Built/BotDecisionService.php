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


class BotDecisionService
{
    // A40/C7: halt() replaces every die() this class used to end a
    // short-circuit branch with, so the response still travels back out
    // through the middleware stack (CORS, security headers, server timing,
    // session release). The generated services are plain classes, so the
    // helper comes in as a trait rather than from a base class.
    use \ApiGoat\Services\Concerns\HaltsResponses;

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
    public $customActions = [];
    /**
     * Custom actions ($customActions KEYS) that are READS and may therefore be
     * reached over a cookie-auth GET.
     *
     * I-5: Service::MUTATING_ACTIONS only inventories the case labels this
     * emitter writes, so a project-registered custom action is invisible to it
     * and used to be fail-OPEN — a cross-site <a href=".../Model/myAction/42">
     * ran it on the SameSite=Lax session cookie. An unknown custom action is now
     * treated as MUTATING on GET and answered with "This action requires POST";
     * list a genuine read here to opt it back in (case-insensitive):
     *
     *     public $readOnlyCustomActions = ['agingReport'];
     *
     * @var array
     */
    public $readOnlyCustomActions = [];
    /**
     * PhpName of the model this service serves. Passed to PropelErrorHandler
     * (which scopes the validation-error highlight to #form{Class} when the
     * request carries no drawer container).
     *
     * @var string
     */
    public $virtualClassName = 'BotDecision';
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
        $this->Form = new BotDecisionFormWrapper($request, $args);
    }

    /**
     * Get the proper response
     * @return string
     */
    public function getResponse()
    {
        // Mutating actions must never be reachable by a GET navigation. The
        // generated HTML route is registered for GET as well as POST and this
        // switch dispatches purely off the {a} URL segment, so without this a
        // cross-site <a href=".../Model/delete/42"> ran the write on the
        // SameSite=Lax session cookie. AuthyMiddleware::checkMutatingGet()
        // refuses these first; this is defence in depth at the controller, and
        // it shares ONE action inventory with the middleware
        // (ApiGoat\Services\Service::MUTATING_ACTIONS) so the two can never
        // drift. There are no exemptions any more: every mutating action is
        // POST-only. Refusal shape matches the middleware's
        // ApiResponse body (status/data/errors), not a die().
        if (method_exists('\ApiGoat\Services\Service', 'mutatingGetRefusal')
            && \ApiGoat\Services\Service::mutatingGetRefusal($this->request, $this->rawRequest)) {
            error_log('mutating GET refused (service): ' . ($this->request['route'] ?? '')
                . ' action=' . ($this->request['a'] ?? '')
                . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
            $this->contentType = 'application/json';
            return json_encode(['status' => 'failure', 'data' => null, 'errors' => [_('This action requires POST')]]);
        }

        // Same shape as every other $this->content assignment (and the runtime
        // base Service): a bare string reaches BuilderLayout::render() as an
        // array offset read on a string.
        $this->content = ['html' => 'Unknown method', 'js' => '', 'onReadyJs' => ''];

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
                    $this->content['onReadyJs'] .= "if(window.gcScreens){history.replaceState({gcEdit:null},'',_SITE_URL+'BotDecision/');gcScreens.openEdit('BotDecision'," . $gcEditPk . ");}";
                } else {
                    $this->content = $this->edit();
                }
            break;
            // A26: only 'update' is emitted. Nothing sends a=insert — the
            // client posts {Model}/update for both create and update (see
            // template public/js/app/screens.js) — and the unreachable arm
            // dispatched to BuilderReturn::insert_return(). It stays on the
            // runtime's MUTATING_ACTIONS list, so a hand-crafted /insert is
            // still refused on GET before it reaches the default arm.
            case 'update':
                $this->content = $this->saveUpdate();
                // BuilderReturn only sets ['error'] on a failure, so the bare read
                // warned on every successful save.
                $this->content['onReadyJs'] .= (($this->content['error'] ?? '') != 'yes')?"sw_message('".addslashes(_('Saved'))."');":'';
                return $this->BuilderLayout->renderXHR($this->content);
            case 'delete':
                $this->content = $this->deleteOne();
                return $this->BuilderLayout->renderXHR($this->content);



        case 'autoc':
            $this->body = $this->autocomplete();
            $this->contentType = 'application/json';
            return json_encode($this->body, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);















            default:
                // Guarded like getApiResponse(): $customActions is empty on
                // most services, so the bare read was a null array offset plus
                // method_exists(null) on every unknown action.
                if (isset($this->customActions[$this->request['a']])
                    && method_exists($this, $this->customActions[$this->request['a']])) {
                    // I-5: a PROJECT-defined action is invisible to
                    // Service::MUTATING_ACTIONS (that inventory only lists the
                    // case labels this emitter writes), so the refusal above
                    // never fired for it and a cross-site
                    // <a href=".../Model/approveInvoice/42"> ran the write on
                    // the SameSite=Lax session cookie. Fail CLOSED here instead:
                    // on a cookie-auth GET an unknown custom action is treated
                    // as mutating unless the wrapper declares it read-only in
                    // $readOnlyCustomActions (see ApiGoat\Services\Service).
                    if (method_exists('\ApiGoat\Services\Service', 'customActionGetRefusal')
                        && \ApiGoat\Services\Service::customActionGetRefusal($this, $this->request, $this->rawRequest)) {
                        error_log('custom action GET refused (service): ' . ($this->request['route'] ?? '')
                            . ' action=' . ($this->request['a'] ?? '')
                            . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
                        $this->contentType = 'application/json';
                        return json_encode(['status' => 'failure', 'data' => null, 'errors' => [_('This action requires POST')]]);
                    }
                    $callable = $this->customActions[$this->request['a']];
                    $this->content = $this->$callable($this->request);
                }
        }



        if(!empty($this->request['ui'])){
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
        $Api = new Api('BotDecision', $this, ['IdGridRun', 'Source', 'PLow', 'PHigh', 'NLevels', 'DeployPct', 'Reason', 'PriceAt', 'RealizedBefore', 'EvalStatus', 'EvalAt', 'AppliedAt', 'CyclesDelta', 'RealizedDelta', 'PriceMovePct', 'Verdict', 'CounterfactualDelta', 'CandidateDelta', 'RequestedJson', 'ClampsJson', 'BriefJson']);

        if (isset($this->customActions[$this->request['a']]) && method_exists($this, $this->customActions[$this->request['a']])) {
            $callable = $this->customActions[$this->request['a']];
            $this->body = $this->$callable($Api);
        }else{
            switch($this->request['method']){
                case 'AUTH':
                    // The action segment is request-controlled; without the
                    // guard an unknown one is a fatal call to an undefined
                    // method. RouteHelper::reassertTrustedArgs is the other
                    // half of this (it fixes what the action may be).
                    $dispatch = (string) $this->request['a'];
                    if ($dispatch !== '' && method_exists($this, $dispatch)) {
                        $this->body = $this->$dispatch();
                    }
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






    use \ApiGoat\Services\Concerns\HandlesAutocomplete;

    private function autocAllowlist()
    {
        return array (
  'GridRun' =>
  array (
    'cols' =>
    array (
      0 => 'Label',
      1 => 'IdGridRun',
    ),
    'ids' =>
    array (
      0 => 'IdGridRun',
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

        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(BotDecisionQuery::class, json_decode($this->request['i']), 'BotDecision', 'd');
        if ($obj) {
            $this->gcDeleteRow($obj, $error, $messages);
        }

        $BuilderReturn = new BuilderReturn($this->request, $error, $messages);
        return $BuilderReturn->return();
    }

    /**
     * Delete ONE already-loaded (ACL-scoped) row through the guarded path:
     * referential guard, BeforeDelete/AfterDelete hooks, upload file cleanup.
     * A refusal halts (HaltResponse) — deleteOne() lets it reach the client,
     * massDelete() catches it per row.
     */
    protected function gcDeleteRow($obj, &$error, &$messages)
    {



        try {
            $obj->delete();
        } catch (\Exception $gcDelErr) {
            // A39: the pre-delete guard deliberately does not probe the audit
            // FKs (id_creation / id_modification / id_group_creation), so a row
            // still referenced only from an audit trail now reaches InnoDB and
            // comes back as a RESTRICT violation (SQLSTATE 23000 / errno 1451).
            // Answer with the same refusal the guard would have produced instead
            // of a 500; anything else is a real error and is re-thrown.
            $gcDelMsg = $gcDelErr->getMessage();
            for ($gcDelPrev = $gcDelErr->getPrevious(); $gcDelPrev !== null; $gcDelPrev = $gcDelPrev->getPrevious()) {
                $gcDelMsg .= ' ' . $gcDelPrev->getMessage();
            }
            if (strpos($gcDelMsg, '1451') === false && strpos($gcDelMsg, '23000') === false) {
                throw $gcDelErr;
            }
            error_log('BotDecision delete refused by a foreign key: ' . $gcDelMsg);
            // F5: name the blocking table when MySQL said which one it was. The
            // audit FKs are not in the pre-delete guard list, so this literal is
            // the only place their label can come from.
            $gcFkBlocker = \ApiGoat\Orm\ReferentialGuard::blockerFromMessage($gcDelMsg, []);
            $error = handleNotOkResponse(
                $gcFkBlocker !== null
                    ? _("This entry cannot be deleted. It is in use in ")." '".$gcFkBlocker."'. "
                    : _("This entry cannot be deleted. It is still referenced by other records."),
                '', true,'Refit Decision');
            $this->halt($error['onReadyJs']);
        }



    }

    public function saveUpdate(): array
    {
        $messages = null;
        $error = null;

        $extValidationErr = false;
        parse_str ($this->request['d'], $data );

        $data['i'] = ( $data['IdBotDecision'] ) ? $data['IdBotDecision'] : $this->request['i'];
        $data['ip'] = urldecode($this->request['data']['ip'] ?? '');
        $data['pc'] = urldecode($this->request['data']['pc'] ?? '');

        if(!empty($data['i'])) {
            ## Save

            $e = $this->Form->setUpdateDefaultsBotDecision($data);
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


            $e = $this->Form->setCreateDefaultsBotDecision($data);

            if($data['ip']){
                $e->setIdGridRun(json_decode($data['ip']));
            }


            if ($e->validate() && !$extValidationErr) {
                $e->save();
                $this->request['i'] = json_encode($e->getPrimaryKey());


                //$data['IdGridRun'] = json_encode($e->getIdGridRun());
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
        // A27: the parent prefill that used to be built here ($relData['Id<Parent>']
        // / ['ip'] / ['pc'] from $this->request['data']) was overwritten by
        // $relData = $this->request on the very next line and never reached the
        // form. getEditForm() does the prefill itself, off $data['data']['ip']
        // (Form.php: $data['ip']/['pc'] then the $data['pc'] switch), so the
        // request array alone is all it needs.

        // Crossref (NtN) far-record view: ro=1 renders the whole form via the
        // dormant setReadOnly='all' switch — fields locked to fieldsRo, no
        // Save bar / delete column / Add-new. Display-only; write auth is RBAC.
        if (!empty($this->request['data']['ro'])) {
            $this->Form->setReadOnly = 'all';
        }

        $relData = $this->request;
        $output = $this->Form->getEditForm($this->request['i'], $this->request['ui'] ?? '', $relData, '', $this->request['data']['je'] ?? '', $this->request['data']['jet'] ?? '');

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
