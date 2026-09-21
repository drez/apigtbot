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
use Selective\Config\Configuration;
use ApiGoat\Sessions\AuthySession as AuthySession;


class AuthyService
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
    public $virtualClassName = 'Authy';
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
        $this->Form = new AuthyFormWrapper($request, $args);
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
                    $this->content['onReadyJs'] .= "if(window.gcScreens){history.replaceState({gcEdit:null},'',_SITE_URL+'Authy/');gcScreens.openEdit('Authy'," . $gcEditPk . ");}";
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
















                case 'NtNsaveAuthyGroupX':
                    $this->content = $this->NtNsaveAuthyGroupX();
                break;

                /**
                *   Child table
                **/
                case 'AuthyGroupX':
            if(!$_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'r')){
                security_redirect(false);
            }
                    $this->content = $this->Form->getAuthyGroupXList($this->request['i'], $this->request);
                break;

                /**
                *   Child table
                **/
                case 'AuthyLog':
            if(!$_SESSION[_AUTH_VAR]->hasRights('AuthyLog', 'r')){
                security_redirect(false);
            }
                    $this->content = $this->Form->getAuthyLogList($this->request['i'], $this->request);
                break;


            case 'login':
                return $this->BuilderLayout->renderLogin($this->login());
            case 'logout':
                return $this->BuilderLayout->renderXHR($this->logout());
            case 'auth':
                $this->contentType = 'application/json';
                return json_encode($this->auth());
            case 'google':
                $this->contentType = 'application/json';
                return json_encode($this->googleAuth());
            case 'reset':
                $this->contentType = 'application/json';
                return json_encode($this->passReset());
            case 'resetConfirm':
                return $this->BuilderLayout->renderLogin($this->resetConfirm());
            case 'autoc':
                $this->contentType = 'application/json';
                return $this->iarcAutoc();

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
        $Api = new Api('Authy', $this, ['Username', 'Fullname', 'Email', 'Expire', 'Deactivate', 'Language', 'Theme', 'GoogleEmail', 'LocationAddress', 'LocationLat', 'LocationLng', 'IdAuthyGroup', 'Onglet']);

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


        #update for checkboxes NtN
        private function NtNsaveAuthyGroupX()
        {
            $response['status'] = 'failure';

            $data['i'] = json_decode($this->request['i'], true);

            if(!$_SESSION[_AUTH_VAR]->hasRights('Authy', 'w')){
                security_redirect(false);
            }
            // The row toggled is the JUNCTION's: the route only proved 'w' on the
            // parent, and with no 'w' on the junction loadPkScoped() below would
            // hand it back unscoped.
            if (!$_SESSION[_AUTH_VAR]->isRoot() && $_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'w') === false) {
                $this->halt(['status' => 'failure', 'messages' => [_('Access denied')]]);
            }
            if(is_array($data['i'])){
                if ($_SESSION[_AUTH_VAR]->loadPkScoped(AuthyQuery::class, $data['i'][0], 'Authy', 'w') === null) {
                    $this->halt(['status' => 'failure', 'messages' => [_('Access denied')]]);
                }
                if (!$_SESSION[_AUTH_VAR]->isRoot() && $_SESSION[_AUTH_VAR]->hasRights('AuthyGroup', 'r') === false) {
                    $this->halt(['status' => 'failure', 'messages' => [_('Access denied')]]);
                }
                if ($_SESSION[_AUTH_VAR]->loadPkScoped(AuthyGroupQuery::class, $data['i'][1], 'AuthyGroup', 'r') === null) {
                    $this->halt(['status' => 'failure', 'messages' => [_('Access denied')]]);
                }
                // loadPkScoped (not findPk(): findPkSimple()/instance-pool bypass
                // the tenant + Owner/Group query behaviors). Routes through
                // findOne() so a junction carrying id_tenant can't be toggled
                // across tenants. Null = out of scope or absent → fall to the
                // create branch, where a unique/PK collision safely refuses.
                $e = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyGroupXQuery::class, $data['i'], 'AuthyGroupX', 'w');
                if($e){
                    $e->delete();
                    $response['status'] = 'success';
                    $response['messages'][] = _('Removed');
                }else{
                    $e = new AuthyGroupX();
                    $e->setPrimaryKey($data['i']);
                     if ($e->validate()) {
                        $e->save();
                        $response['status'] = 'success';
                        $response['messages'][] = _('Added');
                    }else{
                        foreach ($e->getValidationFailures() as $failure) {
                            $response['messages'][] = _($failure->getMessage());
                        }
                        if (empty($response['messages'])) {
                            $response['messages'][] = _('Save failed');
                        }
                    }
                }
            }
            $this->halt($response);

        }















    public function deleteOne()
    {
        $error = [];
        $messages = '';

        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyQuery::class, json_decode($this->request['i']), 'Authy', 'd');
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


            $gcBlocker = \ApiGoat\Orm\ReferentialGuard::firstBlocker($obj, [
                ['q'=>'AuthyGroupXQuery','f'=>'filterByAuthyRelatedByIdAuthy','pk'=>'IdAuthy','rel'=>'AuthyGroupxesRelatedByIdAuthy','label'=>'Group'],
                ['q'=>'AuthyLogQuery','f'=>'filterByAuthy','pk'=>'IdAuthyLog','rel'=>'AuthyLogs','label'=>'Login log'],
            ]);
            if ($gcBlocker !== null) {
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." '".$gcBlocker."'. ", '', true,'User');
                $this->halt($error['onReadyJs']);
            }

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
            error_log('Authy delete refused by a foreign key: ' . $gcDelMsg);
            // F5: name the blocking table when MySQL said which one it was. The
            // audit FKs are not in the pre-delete guard list, so this literal is
            // the only place their label can come from.
            $gcFkBlocker = \ApiGoat\Orm\ReferentialGuard::blockerFromMessage($gcDelMsg, ['authy_group_x'=>'Group','authy_log'=>'Login log','authy'=>'User','push_device'=>'Push device','grid_run'=>'Grid Run','fleet_slot'=>'Fleet slot','regime_episode'=>'Regime episode','bot_order'=>'Order','trade_cycle'=>'Trade Cycle','bot_event'=>'Event','bot_command'=>'Command','sim_wallet'=>'Paper Wallet','market_summary'=>'Market Data','market_regime'=>'Regime History','market_candle'=>'Candles','bot_decision'=>'Refit Decision','market_outlook'=>'Market Outlook','market_outlook_state'=>'Outlook State','wallet_nav'=>'Wallet NAV','authy_group'=>'Group','config'=>'Setting','api_rbac'=>'API ACL','template'=>'Template','template_file'=>'File','grid_run_audit'=>'Change history','country'=>'Country']);
            $error = handleNotOkResponse(
                $gcFkBlocker !== null
                    ? _("This entry cannot be deleted. It is in use in ")." '".$gcFkBlocker."'. "
                    : _("This entry cannot be deleted. It is still referenced by other records."),
                '', true,'User');
            $this->halt($error['onReadyJs']);
        }



    }

    public function saveUpdate(): array
    {
        $messages = null;
        $error = null;

        $extValidationErr = false;
        parse_str ($this->request['d'], $data );

        $data['i'] = ( $data['IdAuthy'] ) ? $data['IdAuthy'] : $this->request['i'];
        $data['ip'] = urldecode($this->request['data']['ip'] ?? '');
        $data['pc'] = urldecode($this->request['data']['pc'] ?? '');

        if(!empty($data['i'])) {
            ## Save

            $e = $this->Form->setUpdateDefaultsAuthy($data);
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


            $e = $this->Form->setCreateDefaultsAuthy($data);



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


    

public $omMap;
public $lang;
public $group;
public $userRights;
public $loginFormClass;
public $lastTry;
# custom variables
public array $sessVar = [];





    /**
     * Main login function
     * @param string $username
     * @param string $passHash
     * @param booleen $isWs
     * @param booleen $stay
     */
    public function tryLog($username, $passHash, $isWs=false, $stay=null, $csrf='')
    {
        $settings = new Configuration(require _BASE_DIR . 'config/settings.php');
        $jwt_settings = $settings->getArray('jwt_middleware');
        $tocken = [];
        $Authy = null;   // stays null when throttled / CSRF-rejected
        $jwt = null;     // only set on the JWT (isWs) path
        $return = null;  // error-message list; null = no error

        if(!$isWs){
            $CsrfError = false;

            if($_SESSION[_AUTH_VAR]->getCsrf() != $csrf){
                $CsrfError = true;
            }
        }else{
            $CsrfError = false;
        }
        
        // 'Stay logged in' previously stored [username, passHash] in a 10-year
        // 'authy' cookie via en_de(). That cookie was never read back (no recall
        // path anywhere in the runtime, middleware, or client), so it only ever
        // leaked credentials. Removed — a real persistent-login feature must use
        // a server-side selector/validator token, never store credentials.

        // SECURITY (throttle race, review LOW): serialize the check->verify->log
        // section per (ip, username) with a MySQL advisory lock so concurrent
        // login POSTs can't all clear checkAttemptsOk() before any logs a failure
        // (Race A), and the attempt-counter upsert in setAuthyLog() can't lose
        // updates / fragment its accumulator row (Race B). Best-effort: if the
        // lock infra is unavailable, login still proceeds (degrades to the prior
        // behavior) rather than failing.
        $gcThrottleCon = null; $gcThrottleKey = null;
        try {
            $gcThrottleCon = \Propel::getConnection(_DATA_SRC);
            $gcThrottleKey = 'gcauth:' . md5(($_SERVER['REMOTE_ADDR'] ?? '') . '|' . strtolower(trim((string)$username)));
            $gcThrottleCon->query('SELECT GET_LOCK(' . $gcThrottleCon->quote($gcThrottleKey) . ', 5)');
        } catch (\Throwable $gcLockErr) { $gcThrottleCon = null; }
        try {
        $attemptOk = $this->checkAttemptsOk($username);

        // No character guards on username/password: queryUser() looks the user
        // up via a parameterized Propel filterByUsername/Email (= comparison, so
        // '%'/spaces are literal and injection-safe) and verifies the password
        // with password_verify() — the password never enters SQL. The old
        // strstr('%')/strstr(' ') checks were dead legacy from the MD5-in-raw-SQL
        // era and only silently rejected valid passwords containing % or a space.
        if($attemptOk && !$CsrfError){
            $Authy = $this->queryUser($username, $passHash);
            $this->lastTry = time();
        }elseif($attemptOk == false){
            $return[] = "Too many attempts — please wait a few minutes and try again.";
        }elseif($CsrfError){
            $return[] = "Your session expired. Refreshing…";
        }
        
        if(!$isWs){
            $this->setSession($Authy);
        }else{
            if(empty($return)){
                $jwt = $this->getToken($Authy, $jwt_settings['secret'], $jwt_settings['expire']);
                if (is_array($jwt) && ($jwt['status'] ?? '') === 'success') {
                    $refresh = $this->mintRefreshToken($Authy);
                    if ($refresh !== null) {
                        $jwt['refresh_token'] = $refresh;
                    }
                }
            }
        }
        
        $this->setAuthyLog($username, $jwt);
        
        if (is_array($jwt)) {
            return $jwt;
        }

        return $return;
        } finally {
            if ($gcThrottleCon && $gcThrottleKey) {
                try { $gcThrottleCon->query('SELECT RELEASE_LOCK(' . $gcThrottleCon->quote($gcThrottleKey) . ')'); } catch (\Throwable $gcRelErr) {}
            }
        }
    }

    /**
     *
     * @param string $username
     * @param string $passHash
     * @return string|null
     */
    private function queryUser($username, $passHash)
    {
        $q = new AuthyQuery();
        // No escaping helper here: Propel binds every filterBy* value as a
        // PDO parameter. mres() (legacy backslash escaper) only corrupted the
        // comparison, so a login like o'brien could never match its own row.
        $q
            ->filterByUsername($username)->_or()
            ->filterByEmail(strtolower($username))
            ->filterByDeactivate('No')->_or()->filterByDeactivate(null, \Criteria::EQUAL)
            ;
        $user = $q->findOne();

        if ($user) {
            // Use password_verify to compare plaintext password to stored hash
            $storedHash = $user->getPasswdHash();
            if (!empty($storedHash) && password_verify($passHash, $storedHash)) {
                return $user;
            }

        }

        return null;
    }

    /**
     * return a JWT tocken if the user is valid
     * @param type $pmpoData
     * @param type $username
     * @param type $secret
     * @return array response data
     */
    public function getToken($pmpoData, $secret, $expire="now +2 hours")
    {
        if( !empty($pmpoData) ){
            $now = new \DateTime();
            $future = new \DateTime($expire);
            $jti = (new \Tuupola\Base62)->encode(random_bytes(16));
            
            $payload = [
                "iat" => $now->getTimeStamp(),
                "exp" => $future->getTimeStamp(),
                "jti" => $jti,
                "sub" => (string) $pmpoData->getIdAuthy(),
                "scope" => [],
                "username" => $pmpoData->getUsername(),
                "authyId" => $pmpoData->getIdAuthy(),
                "group" => $pmpoData->getIdAuthyGroup(),
                "isRoot" => $pmpoData->getIsRoot()
            ];

            $token = \Firebase\JWT\JWT::encode($payload, $secret, "HS256");

            $data["token"] = $token;
            $data["expires"] = $future->getTimeStamp();
            $data["status"] = 'success';
            return $data;
        }

        return ["status" => "failure", "messages" => ["We couldn't sign you in. Check your details and try again."] ];
    }

    /**
     * Mint a refresh token for $Authy. Delegates to
     * \ApiGoat\Auth\RefreshTokenService; returns null when the runtime
     * service is unavailable (project has no authy_refresh_token table).
     */
    private function mintRefreshToken($Authy): ?string
    {
        if (empty($Authy)) { return null; }
        $svc = \ApiGoat\Auth\RefreshTokenService::forProject();
        return $svc ? $svc->mintForLogin((int) $Authy->getIdAuthy()) : null;
    }

    /**
     * Exchange a valid refresh token for a fresh access JWT. Rotates the
     * presented token (revokes it and issues a new one). Returns the same
     * shape as auth(): {status, token, expires, refresh_token}.
     */
    public function refresh(): array
    {
        $svc = \ApiGoat\Auth\RefreshTokenService::forProject();
        if (!$svc) { return ['status' => 'error', 'message' => 'unsupported']; }
        $jwtset = (new \Selective\Config\Configuration(require _BASE_DIR . 'config/settings.php'))->getArray('jwt_middleware');
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
        // The POST body's refresh_token lands at top level via RouteHelper::getPOSTArgs
        // (array_merge of getParsedBody); the ['data'] nesting is only populated on the
        // RouteParser-middleware path, which these explicit auth-route closures bypass.
        // Read top level first, fall back to ['data'] for the middleware path.
        $raw = (string) ($this->request['refresh_token'] ?? $this->request['data']['refresh_token'] ?? '');
        $self = $this;
        return $svc->redeem($raw, $ip, function ($idAuthy) use ($self, $jwtset) {
            $Authy = \App\AuthyQuery::create()->findPk($idAuthy);
            if (empty($Authy)) { return ['status' => 'failure']; }
            return $self->getToken($Authy, $jwtset['secret'], $jwtset['expire']);
        });
    }

    /**
     * Set the session object AuthySession
     * @param propel collection $pmpoData
     * @param string $username
     * @param booleen $isWs
     */
    public function setSession($pmpoData)
    {
        if( !empty($pmpoData) ){
            if(($pmpoData->getExpire() != null && $pmpoData->getExpire() <= date('Y-m-d'))){
                $_SESSION[_AUTH_VAR]->set('isConnected','NO');
                $return[] = "Oh! User has expired.";
            }else{
                // Prevent session fixation: issue a fresh session id at the
                // privilege boundary (login), before sess_id is recorded.
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                // Start every login from a clean session object. logout() only
                // flips isConnected and the impersonate switch reuses the live
                // object, while setRights()/setGroups() only ever append — so
                // the previous user's menus, ACL grants and group ids leaked
                // into this login. Only the csrf token and the UI language are
                // per-browser rather than per-user, so only they carry over —
                // plus ->config (jwt + locale): per-REQUEST app settings that
                // config/container.php writes once, before any login. Dropping it
                // broke the rest of every bearer / MCP / OAuth / impersonate
                // request (Api::applyI18n() saw no supported locales,
                // message_label() count(null)). ->configdb is NOT carried: its
                // loader rebuilds it on the next request when it is missing.
                $gcPrevSession = $_SESSION[_AUTH_VAR] ?? null;
                $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
                if (is_object($gcPrevSession)) {
                    $_SESSION[_AUTH_VAR]->setCsrf($gcPrevSession->csrf ?? null);
                    $_SESSION[_AUTH_VAR]->lang = $gcPrevSession->lang ?? null;
                    $_SESSION[_AUTH_VAR]->config = is_array($gcPrevSession->config ?? null) ? $gcPrevSession->config : [];
                }
                $passHash = $pmpoData->getPasswdHash();
                $_SESSION[_AUTH_VAR]->set('sess_id', md5(session_id()));
                $_SESSION[_AUTH_VAR]->set('isConnected','YES');
                $_SESSION[_AUTH_VAR]->set('id',$pmpoData->getIdAuthy());
                $_SESSION[_AUTH_VAR]->set('isRoot',($pmpoData->getIsRoot() == 'Yes')?true:false);
                $_SESSION[_AUTH_VAR]->set('passHash',$passHash);
                $_SESSION[_AUTH_VAR]->set('email',$pmpoData->getEmail());
                $_SESSION[_AUTH_VAR]->set('id_tenant', method_exists($pmpoData, 'getIdTenant') ? $pmpoData->getIdTenant() : null);
                $_SESSION[_AUTH_VAR]->config_changed = 'no';
                $_SESSION[_AUTH_VAR]->set('username',$pmpoData->getUsername());
                $rightsGroup = array (
  'All' => 'RightsAll',
  'Owner' => 'RightsOwner',
  'Group' => 'RightsGroup',
);
                if(is_array($rightsGroup)){
                    foreach($rightsGroup as $group => $columnName){
                        $getColumn = "get{$columnName}";
                        $userRightsAr[$group] = json_decode($pmpoData->$getColumn() ?? '', true);
                    }
                    $_SESSION[_AUTH_VAR]->setRights( $userRightsAr );
                }

                if($pmpoData->getAuthyGroupRelatedByIdAuthyGroup()){
                    $_SESSION[_AUTH_VAR]->setPrimaryGroup( $pmpoData->getIdAuthyGroup(), $pmpoData->getAuthyGroupRelatedByIdAuthyGroup()->getAdmin() );
                }
                $_SESSION[_AUTH_VAR]->setGroups();
                
                $_SESSION[_AUTH_VAR]->set('lastMsg', _("Connection succesful"));
                $this->sessionVarSet($pmpoData);
                
            $_SESSION['mem'] = json_decode((string)($pmpoData->getOnglet() ?? ''), true) ?? [];
                
                return true;
            }
        }else{
            $_SESSION[_AUTH_VAR]->set('isConnected','NO');
            $return[] = "The username/password combination is not know to us!";
        }
        return $return;
    }

    /**
     * Throttle login attempts.
     *  - Per (IP, username): max 5 failed attempts in 5 minutes.
     *  - Per username globally (all IPs): max 10 failed attempts in 5 minutes,
     *    to defend against distributed credential-stuffing against one account.
     * @return boolean true if the attempt is allowed, false if throttled.
     */
    private function checkAttemptsOk($username)
    {
        // Normalize the throttle key so case/whitespace variants of the same
        // login share one counter (prevents lockout-throttle bypass).
        $username = strtolower(trim((string)$username));
        $perIpUser = AuthyLogQuery::create()
                ->filterByIp($_SERVER['REMOTE_ADDR'])
                ->filterByLogin($username)
                ->filterByResult('w')
                ->filterByTimestamp(strtotime('5 min ago'), \Criteria::GREATER_EQUAL)
                ->filterByCount(5, \Criteria::GREATER_EQUAL)
                ->count();

        if($perIpUser){
            return false;
        }

        // Sum in SQL (one scalar row) instead of hydrating every matching
        // authy_log row just to add up its count column.
        $globalAttempts = (int) AuthyLogQuery::create()
                ->filterByLogin($username)
                ->filterByResult('w')
                ->filterByTimestamp(strtotime('5 min ago'), \Criteria::GREATER_EQUAL)
                ->withColumn('SUM(App\\AuthyLog.Count)', 'gcAttemptTotal')
                ->select(['gcAttemptTotal'])
                ->findOne();

        if($globalAttempts >= 10){
            return false;
        }

        return true;
    }

    /**
     * Log login attempts and results
     * @param string $username
     */
    private function setAuthyLog($username, $jwt = [])
    {
        // Normalize the throttle key to match checkAttemptsOk (shared counter).
        $username = strtolower(trim((string)$username));

        $jwt = (isset($jwt['status'])) ? $jwt : ['status' => ''];

        $al = AuthyLogQuery::create()
                ->filterByIp($_SERVER['REMOTE_ADDR'])
                ->filterByLogin($username)
                ->filterByTimestamp(strtotime('5 min ago'), \Criteria::GREATER_EQUAL)
                ->filterByResult( (($_SESSION[_AUTH_VAR]->get('isConnected') == 'YES' || $jwt['status'] == 'success')?'g':'w') )
                ->findOne();

        if(!$al){
            $al = new AuthyLog();
            $al->setIp($_SERVER['REMOTE_ADDR']);
            $al->setLogin($username);
        }

        $al->setTimestamp(time());
        
        if($_SESSION[_AUTH_VAR]->get('isConnected') == 'YES' || $jwt['status'] == 'success'){
            $al->setIdAuthy($_SESSION[_AUTH_VAR]->get('id'));
            $al->setResult('g');

        }else{
            $al->setCount( $al->getCount()+1 );
            $al->setIdAuthy(null);
            $al->setResult('w');
        }
        $al->save();
    }

    /**
     * Set predefined builder session variables
     * @param DataCollection $pmpoData
     */
    public function sessionVarSet($pmpoData)
    {
    
    }

    public function getRightsArray($arrayRights)
    {
        return json_decode($arrayRights, true);
    }

    // isConnected(): removed. It read a $this->isConnected property that was
    // never assigned anywhere (always null), so it returned false for every
    // caller. The real state lives in $_SESSION[_AUTH_VAR]->get('isConnected').

    /**
     * Get the html login form
     * @return string
     */
    public function login()
    {
        return $this->Form->login();
    }

    /**
     * Logout and redirect
     * @return array
     */
    public function logout()
    {
        $_SESSION[_AUTH_VAR]->set('isConnected', 'NO');
        return ['html' => "&nbsp;", 'js' => script("document.location = 'login';")];
    }

    /**
     * Validate inputs, try to authenticate and respond
     * @return array
     */
    public function auth()
    {
        $return['status'] = 'failure';
        $return['messages'] = _("We couldn't sign you in. Check your details and try again.");
        $return['error'] = 'fail-connect0';

        // I-2: every one of these request keys is OPTIONAL on the wire.
        // 'isApiCall' is set by RouteHelper only on the api/v1/Authy route,
        // 'pw' only by the legacy client, 'stay' only when "keep me signed in"
        // is ticked and 'csrf' only by the browser form. Reading them raw wrote
        // 7 "Undefined array key" lines to tmp/logs/php-error.log on EVERY
        // login attempt — the most-hit unauthenticated endpoint on every
        // deployment, and the one a credential-stuffing run hammers. Resolve
        // them once, defensively; the values are identical to what the raw
        // reads produced (null/'' when absent), so behaviour is unchanged.
        $gcIsApiCall = !empty($this->request['isApiCall']);
        $gcPw        = $this->request['data']['pw'] ?? null;
        $gcStay      = $this->request['stay'] ?? null;
        $gcCsrf      = $this->request['data']['csrf'] ?? null;

        if(!$gcIsApiCall && $_SESSION[_AUTH_VAR]->get('isConnected') == 'YES'){
            $json['username'] = $_SESSION[_AUTH_VAR]->get('username');
            $return['messages'] = _("You're already signed in.");
            $return['status'] = 'success';
            $return['error'] = 'user-connected';
        }else{
            if(!empty($this->request['data']['u']) && (!empty($this->request['data']['p']) || !empty($gcPw))) {
                $this->request['data']['p'] = ($gcPw)?$gcPw:($this->request['data']['p'] ?? '');

                if($gcIsApiCall || $_SESSION[_AUTH_VAR]->get('isConnected') == 'NO') {
                    if ($gcIsApiCall) {
                        // start a clean session for API auth call
                        unset($_SESSION[_AUTH_VAR]);
                        $_SESSION[_AUTH_VAR] = new AuthySession();
                    }
                    // try to Authenticate
                    $logReturn = $this->tryLog($this->request['data']['u'], $this->request['data']['p'], $gcIsApiCall, $gcStay, $gcCsrf);

                    if($gcIsApiCall) {
                        return is_array($logReturn)?$logReturn:['status' => 'failure', 'messages' => $logReturn];
                    }

                    if($_SESSION[_AUTH_VAR]->get('isConnected') == 'YES') {
                        $return['messages'] = 'Welcome back! Signing you in…';
                        $return['status'] = 'success';
                        $return['success'] = 'success-connect';
                    }elseif($logReturn) {
                        $return['messages'] = $logReturn;
                        $return['error'] = 'fail-connect1';
                    }
                } else {
                    $return['error'] = 'fail-connect2';
                    $this->reload();
                }
            } else {
                $return['error'] = 'fail-connect3';
            }
        }

        $return['method'] = 'login';
        return $return;
    }

    #login with a Google ID token (GIS credential)
    public function googleAuth()
    {
        $return['status'] = 'failure';
        $return['messages'] = _('Google sign-in failed');
        $return['method'] = 'login';

        $clientId = (string) ($_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '');
        $credential = (string) ($this->request['data']['credential'] ?? '');
        if ($clientId === '' || $credential === '') {
            return $return;
        }
        try {
            $claims = (new \ApiGoat\Auth\GoogleIdToken())->verify($credential, $clientId);
        } catch (\Exception $e) {
            $return['messages'] = $e->getMessage();
            return $return;
        }

        // 1) already-linked user (google_sub is the permanent key)
        $q = new AuthyQuery();
        $q
            ->filterByGoogleSub($claims['sub'])
            ->filterByDeactivate('No')->_or()->filterByDeactivate(null, \Criteria::EQUAL)
            ;
        $Authy = $q->findOne();

        // 2) fallback: exact verified-email match on an unlinked active user -> auto-link
        if (!$Authy) {
            $q2 = new AuthyQuery();
            $q2
                ->filterByEmail(strtolower($claims['email']))
                ->filterByGoogleSub(null, \Criteria::EQUAL)
                ->filterByDeactivate('No')->_or()->filterByDeactivate(null, \Criteria::EQUAL)
                ;
            $Authy = $q2->findOne();
            if ($Authy) {
                $Authy->setGoogleSub($claims['sub']);
                $Authy->setGoogleEmail($claims['email']);
                $Authy->save();
            }
        }

        // 3) self-registration — OFF by default. Only when the project .env sets
        //    GOOGLE_AUTO_REGISTER (+ optional _GROUP / _DOMAINS allowlist) AND
        //    neither the sub nor the verified email matched an active user.
        //    A deactivated user with that email is NOT revived: the email
        //    validator's unique rule refuses the create below.
        $gcRegistered = false;
        if (!$Authy) {
            $gcReg = \ApiGoat\Auth\GoogleRegistration::fromEnv();
            if ($gcReg->isEnabled()) {
                if (!$gcReg->allowsEmail($claims['email'])) {
                    $return['messages'] = sprintf(_('Registration is not open for %s'), $claims['email']);
                    return $return;
                }
                $gcGroup = null;
                if ($gcReg->groupName() !== null) {
                    $gcGroup = AuthyGroupQuery::create()->filterByName($gcReg->groupName())->findOne();
                    if (!$gcGroup) {
                        error_log('Authy/google: GOOGLE_AUTO_REGISTER_GROUP \'' . $gcReg->groupName() . '\' not found - falling back to the default group');
                    }
                }
                if (!$gcGroup) {
                    $gcGroup = AuthyGroupQuery::create()->filterByDefaultGroup('Yes')->orderByIdAuthyGroup()->findOne();
                }
                if (!$gcGroup) {
                    error_log('Authy/google: no default authy_group - cannot self-register');
                    return $return;
                }
                $gcNew = new Authy();
                $gcNew->setEmail(strtolower($claims['email']));
                $gcNew->setGoogleSub($claims['sub']);
                $gcNew->setGoogleEmail($claims['email']);
                $gcNew->setFullname(mb_substr(trim((string) ($claims['name'] ?? '')), 0, 100));
                // Google-only login: an unguessable random password until the
                // user runs the normal password reset (is_root / is_system keep
                // their 'No' defaults; id_tenant keeps the column default).
                $gcNew->setPasswdHash(password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT));
                $gcNew->setDeactivate('No');
                $gcNew->setIdAuthyGroup($gcGroup->getIdAuthyGroup());
                if (!$gcNew->validate()) {
                    return $return;
                }
                try {
                    $gcNew->save();
                } catch (\Exception $e) {
                    error_log('Authy/google: self-registration failed: ' . $e->getMessage());
                    return $return;
                }
                $Authy = $gcNew;
                $gcRegistered = true;
            }
        }

        if (!$Authy) {
            $return['messages'] = sprintf(_('No account for %s'), $claims['email']);
            return $return;
        }

        $this->setSession($Authy);
        if ($_SESSION[_AUTH_VAR]->get('isConnected') == 'YES') {
            $return['status'] = 'success';
            $return['messages'] = $gcRegistered ? _('Welcome! Your account has been created…') : 'Welcome back! Signing you in…';
            $return['success'] = 'success-connect';
        }
        return $return;
    }

    public function passReset()
    {
        // One generic response regardless of outcome (anti-enumeration). A token
        // email is sent only for a matching active user; the caller learns
        // nothing about whether the account exists or is active.
        $generic = ['status' => 'success', 'method' => 'restore',
            'messages' => _('If that account exists, password reset instructions have been sent.')];

        $email = strtolower(trim((string) ($this->request['data']['c'] ?? '')));
        if ($email === '') { return $generic; }

        // Per-IP rate limit (anti email-bombing across many accounts): cap reset
        // requests per IP in a 15-min window and log each attempt. Uses a
        // distinct authy_log result marker ('reset') so it never conflates with
        // the login-attempt throttle ('w'/'g'). Runs for every request (incl.
        // unknown emails) so it can't be used to probe account existence.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if ($ip !== '') {
            $recentResets = AuthyLogQuery::create()
                ->filterByIp($ip)
                ->filterByResult('reset')
                ->filterByTimestamp(strtotime('15 min ago'), \Criteria::GREATER_EQUAL)
                ->count();
            if ($recentResets >= 10) { return $generic; }
            $rl = new AuthyLog();
            $rl->setIp($ip);
            $rl->setLogin($email);
            $rl->setResult('reset');
            $rl->setTimestamp(time());
            $rl->save();
        }

        $data_user = AuthyQuery::create()->filterByEmail($email)->findOne();

        // Tokenized reset: NEVER mutate the password here. The old flow
        // overwrote the password with a random one on any request, so an
        // anonymous attacker could permanently lock out / take over any known
        // account. Instead store a hashed, single-use token (1h expiry) and
        // email a confirmation LINK. Requires the reset_token_* columns — guard
        // so projects that haven't added them stay safe (no-op, fail-closed).
        if ($data_user && $data_user->getDeactivate() == 'No'
            && method_exists($data_user, 'setResetTokenHash')) {

            // Per-account rate limit: don't re-issue if a token was minted in the
            // last 60s (limits reset-email spam to one account).
            if ((int) $data_user->getResetTokenExpires() > time() + 3600 - 60) {
                return $generic;
            }

            $token = bin2hex(random_bytes(32));
            $data_user->setResetTokenHash(password_hash($token, PASSWORD_DEFAULT));
            $data_user->setResetTokenExpires(time() + 3600);
            $data_user->save();
            $resetLink = _SITE_URL . 'Authy/resetConfirm?id=' . $data_user->getIdAuthy() . '&t=' . $token;

            // Localized reset email: prefer a lang-aware 'Forgotten password
            // email' template row (by the recipient's language, fr_CA fallback);
            // any miss or error falls back to the built-in copy so the reset
            // email always sends.
            $_resetSubject = _('Password reset');
            $_resetBody = '';
            try {
                $_userLang = method_exists($data_user, 'getLanguage') ? (string) $data_user->getLanguage() : '';
                $_tq = \App\TemplateQuery::create()->filterByName('Forgotten password email');
                $_resetTpl = ($_userLang !== '') ? (clone $_tq)->filterByLang($_userLang)->findOne() : null;
                if (!$_resetTpl) { $_resetTpl = (clone $_tq)->filterByLang('fr_CA')->findOne(); }
                if (!$_resetTpl) { $_resetTpl = $_tq->findOne(); }
                if ($_resetTpl && trim((string) $_resetTpl->getBody()) !== '') {
                    $_resetSubject = (string) $_resetTpl->getSubject();
                    $_resetBody = str_replace('{{reset_link}}', $resetLink, (string) $_resetTpl->getBody());
                }
            } catch (\Throwable $_e) { $_resetBody = ''; }
            if ($_resetBody !== '') {
                $body = $_resetBody;
            } else {
                $body = '<html><body style="font-family:Open Sans,Arial,sans-serif;color:#2f2f2f;">'
                . '<div style="max-width:600px;margin:30px auto;">'
                . '<h2 style="color:#00a4de;">' . _('Password reset') . '</h2>'
                . '<p>' . _('Someone requested a password reset for this account.') . '</p>'
                . '<p><a href="' . $resetLink . '" style="display:inline-block;padding:10px 18px;background:#00d1b2;color:#fff;border-radius:6px;text-decoration:none;">' . _('Set a new password') . '</a></p>'
                . '<p style="color:#888;font-size:13px;">' . _('This link is valid for one hour and can be used once.') . '</p>'
                . '<p style="color:#888;font-size:13px;">' . _('If you did not request this, ignore this email — your password is unchanged.') . '</p>'
                . '</div></body></html>';
            }

            $_emailConfig = (new \Selective\Config\Configuration(require _BASE_DIR . 'config/settings.php'))->getArray('email');
            $_mailer = new \PHPMailer\PHPMailer\PHPMailer();
            if (!empty($_emailConfig['host'])) {
                $_mailer->isSMTP();
                $_mailer->SMTPAuth = true;
                $_mailer->SMTPAutoTLS = false;
                $_mailer->Host = $_emailConfig['host'];
                $_mailer->Port = $_emailConfig['port'];
                $_mailer->SMTPSecure = $_emailConfig['smtp_secure'];
                $_mailer->Username = $_emailConfig['username'];
                $_mailer->Password = $_emailConfig['password'];
                // TLS peer verification stays ON unless the operator opts out
                // for an internal relay with a self-signed cert.
                if (!empty($_emailConfig['allow_self_signed'])) {
                    $_mailer->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
                }
            } else {
                $_mailer->isSendmail();
            }
            $_mailer->setFrom($_emailConfig['default_from']);
            $_mailer->addAddress($email);
            $_mailer->Subject = $_resetSubject;
            $_mailer->msgHTML($body);
            try { $_mailer->send(); } catch (\Exception $e) { /* never leak send failures */ }
        }

        return $generic;
    }

    /**
     * Confirm a password reset from the emailed link. GET (id+t in the query)
     * renders the new-password form; POST (id+t+np+npc) verifies the single-use
     * token + 1h expiry and sets the new password, then clears the token. The
     * password is only ever changed here, by whoever holds the emailed token.
     * Unauthenticated route (privilege-excluded); CSRF n/a (no session).
     */
    public function resetConfirm()
    {
        $id    = (int) ($this->request['data']['id'] ?? ($_GET['id'] ?? 0));
        $token = (string) ($this->request['data']['t'] ?? ($_GET['t'] ?? ''));
        $np    = (string) ($this->request['data']['np'] ?? '');
        $npc   = (string) ($this->request['data']['npc'] ?? '');

        $loginUrl = _SITE_URL . 'Authy/login';
        $wrap = function ($inner) {
            return '<div style="max-width:360px;margin:48px auto;font-family:Open Sans,Arial,sans-serif;text-align:center;color:#2f2f2f;">' . $inner . '</div>';
        };

        // Shape-check the token before it is used for anything: passReset()
        // mints it as bin2hex(random_bytes(32)), so a well-formed token is
        // exactly 64 hex chars. Anything else is refused up front (same policy
        // as the with_register confirm() key check) and never reaches
        // password_verify(). The lookup itself is findPk() + password_verify(),
        // so there is no wildcard/IS NULL promotion here - this is defence in
        // depth, not the load-bearing check.
        $user = ($id > 0 && preg_match('/^[0-9a-f]{64}$/', $token)) ? AuthyQuery::create()->findPk($id) : null;
        $valid = false;
        if ($user && method_exists($user, 'getResetTokenHash')) {
            $hash = (string) $user->getResetTokenHash();
            $exp  = (int) $user->getResetTokenExpires();
            if ($hash !== '' && $token !== '' && $exp > time()
                && $user->getDeactivate() == 'No' && password_verify($token, $hash)) {
                $valid = true;
            }
        }

        if (!$valid) {
            return ['html' => $wrap('<h2>' . _('Reset link invalid') . '</h2><p>'
                . _('This reset link is invalid or has expired. Request a new one.') . '</p>'
                . '<p><a href="' . $loginUrl . '">' . _('Back to sign in') . '</a></p>')];
        }

        $error = '';
        if ($np !== '' || $npc !== '') {
            if (strlen($np) < 8) {
                $error = _('Password must be at least 8 characters.');
            } elseif ($np !== $npc) {
                $error = _('Passwords do not match.');
            } else {
                $user->setPasswdHash(password_hash($np, PASSWORD_DEFAULT));
                $user->setResetTokenHash(null);      // single-use
                $user->setResetTokenExpires(null);
                $__svc = \ApiGoat\Auth\RefreshTokenService::forProject();
                if ($__svc) { $__svc->revokeAllForUser((int) $user->getIdAuthy()); }
                $__oauth = \ApiGoat\OAuth\RefreshTokenRepository::class;
                if (class_exists('\\App\\OauthRefreshToken')) { (new $__oauth())->revokeAllForUser((int) $user->getIdAuthy()); }
                $user->save();
                return ['html' => $wrap('<h2>' . _('Password updated') . '</h2><p>'
                    . _('You can now sign in with your new password.') . '</p>'
                    . '<p><a href="' . $loginUrl . '">' . _('Sign in') . '</a></p>')];
            }
        }

        $errHtml = $error !== '' ? '<p style="color:#d33;">' . htmlspecialchars($error, ENT_QUOTES) . '</p>' : '';
        $form = '<h2>' . _('Choose a new password') . '</h2>' . $errHtml
            . '<form method="post" action="' . _SITE_URL . 'Authy/resetConfirm" style="display:flex;flex-direction:column;gap:10px;text-align:left;">'
            . '<input type="hidden" name="id" value="' . (int) $id . '">'
            . '<input type="hidden" name="t" value="' . htmlspecialchars($token, ENT_QUOTES) . '">'
            . '<input type="password" name="np" placeholder="' . _('New password') . '" autocomplete="new-password" required minlength="8" style="padding:10px;border:1px solid #ccc;border-radius:6px;">'
            . '<input type="password" name="npc" placeholder="' . _('Confirm new password') . '" autocomplete="new-password" required minlength="8" style="padding:10px;border:1px solid #ccc;border-radius:6px;">'
            . '<button type="submit" style="padding:10px;background:#00d1b2;color:#fff;border:0;border-radius:6px;cursor:pointer;">' . _('Update password') . '</button>'
            . '</form>';
        return ['html' => $wrap($form)];
    }

    public function renew(){
        $Authy = AuthyQuery::create()->findPk($_SESSION[_AUTH_VAR]->getIdAuthy());
        $settings = new Configuration(require _BASE_DIR . 'config/settings.php');
        $jwt_settings = $settings->getArray('jwt_middleware');

        return $this->getToken($Authy, $jwt_settings['secret'], $jwt_settings['expire']);
    }

    /**
     * IARC impersonation autocomplete. Dispatched from Authy/autoc.
     * Delegates to runtime so logic stays out of generated code.
     */
    public function iarcAutoc()
    {
        return \ApiGoat\Services\IarcAutoc::handle($this->request);
    }
}
