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


class AuthyGroupService
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
        $this->Form = new AuthyGroupFormWrapper($request, $args);
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
                    $this->content['onReadyJs'] .= "if(window.gcScreens){history.replaceState({gcEdit:null},'',_SITE_URL+'AuthyGroup/');gcScreens.openEdit('AuthyGroup'," . $gcEditPk . ");}";
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
        $Api = new Api('AuthyGroup', $this, ['Name', 'Desc', 'DefaultGroup', 'Admin', 'RightsAll', 'RightsOwner', 'RightsGroup']);

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














    public function deleteOne()
    {
        $error = [];
        $messages = '';

        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyGroupQuery::class, json_decode($this->request['i']), 'AuthyGroup', 'd');
        if ($obj) {


            if($obj->countAuthiesRelatedByIdGroupCreation()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'User'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countPushDevices()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Push device'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countCountries()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Country'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countGridRuns()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Grid Run'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countBotOrders()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Order'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countTradeCycles()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Trade Cycle'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countBotEvents()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Event'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countBotCommands()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Command'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countSimWallets()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Paper Wallet'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countMarketSummaries()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Market Data'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countMarketRegimes()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Regime History'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countBotDecisions()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Refit Decision'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countAuthyGroupsRelatedByIdAuthyGroup()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Group'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countAuthyGroupxesRelatedByIdGroupCreation()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Group'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countConfigs()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Setting'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countApiRbacs()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'API ACL'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countTemplates()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'Template'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countTemplateFiles()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." 'File'. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countAuthyRefreshTokens()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countOauthClients()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countOauthAuthCodes()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countOauthAccessTokens()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countOauthRefreshTokens()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }
            if($obj->countMessageI18ns()){
                $error = handleNotOkResponse(_("This entry cannot be deleted. It is in use in ")." ''. ", '', true,'Group'); die( $error['onReadyJs'] );
            }

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

        $data['i'] = ( $data['IdAuthyGroup'] ) ? $data['IdAuthyGroup'] : $this->request['i'];
        $data['ip'] = urldecode($this->request['data']['ip'] ?? '');
        $data['pc'] = urldecode($this->request['data']['pc'] ?? '');
        $this->AuthyGroup['request'] = $this->request;

        if(!empty($data['i'])) {
            ## Save

            $e = $this->Form->setUpdateDefaultsAuthyGroup($data);
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


            $e = $this->Form->setCreateDefaultsAuthyGroup($data);



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
        $this->AuthyGroup['request'] = $this->request;
        $this->AuthyGroup['parentId'] = $this->request['data']['ip'];


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
