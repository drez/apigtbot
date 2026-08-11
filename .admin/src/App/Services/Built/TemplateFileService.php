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
use ApiGoat\Sessions\AuthySession as AuthySession;


class TemplateFileService
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
        $this->Form = new TemplateFileFormWrapper($request, $args);
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
                    $this->content['onReadyJs'] .= "if(window.gcScreens){history.replaceState({gcEdit:null},'',_SITE_URL+'TemplateFile/');gcScreens.openEdit('TemplateFile'," . $gcEditPk . ");}";
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

            case 'upload':
                return $this->file();
            break;
            case 'file':
            case 'open':
                return $this->getFileContent();
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
        $Api = new Api('TemplateFile', $this, ['IdTemplate', 'Name', 'File']);

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




    public function getFileContent()
    {
        if($this->request['i']) {
            // AUTHORIZATION (security): only serve a file to a caller who holds
            // read rights on this entity, and scope the load to their tenant /
            // Owner-Group (root and Admin bypass). Mirrors the privileged
            // edit-form load (AuthySession::loadPkScoped). Without this, any
            // authenticated user could download any record's file by id (IDOR).
            $gcAuth = $_SESSION[_AUTH_VAR] ?? null;
            if (!is_object($gcAuth) || $gcAuth->get('connected') !== 'YES') {
                error_log("TemplateFile download denied: unauthenticated request");
                return '';
            }
            if (!$gcAuth->isRoot() && $gcAuth->hasRights('TemplateFile', 'r') === false) {
                error_log("TemplateFile download denied: caller lacks read right");
                return '';
            }
            $pc = $gcAuth->loadPkScoped(TemplateFileQuery::class, json_decode($this->request['i']), 'TemplateFile', 'r');

            // Not found, or outside the caller's tenant / Owner-Group scope.
            if (!$pc) {
                error_log("TemplateFile download error: Record ID {$this->request['i']} not found");
                return '';
            }

            // Check if file path is set
            $filePath = $pc->getFile();
            if (empty($filePath)) {
                error_log("TemplateFile download error: No file path set for record ID {$this->request['i']}");
                return '';
            }

            // Build full path
            $fullPath = _BASE_DIR . $filePath;

            // PATH-TRAVERSAL CONFINEMENT (security): the file column is a plain
            // value in the row and can be poisoned (mass-assignment via the edit
            // form, an imported row, …) to '../../../etc/passwd', an absolute
            // path, or another entity's upload dir. The upload writer only ever
            // puts files under public/file/TemplateFile/, so the
            // resolved real path MUST stay inside THIS table's own upload dir.
            // realpath() collapses ../ and resolves symlinks; a path escaping
            // the dir (or not yet existing) is refused — this closes the
            // arbitrary-file-read even when the column value lies.
            $gcUploadRoot = realpath(_BASE_DIR . 'public/file/TemplateFile');
            $gcReal = realpath($fullPath);
            if ($gcUploadRoot === false || $gcReal === false
                || strncmp($gcReal, $gcUploadRoot . DIRECTORY_SEPARATOR, strlen($gcUploadRoot) + 1) !== 0) {
                error_log("TemplateFile download denied: path outside upload dir for record ID {$this->request['i']}");
                return '';
            }
            $fullPath = $gcReal;

            // Check if file exists
            if (!file_exists($fullPath)) {
                error_log("TemplateFile download error: File does not exist at path: {$fullPath}");
                return '';
            }

            // Check if file is readable
            if (!is_readable($fullPath)) {
                error_log("TemplateFile download error: File is not readable at path: {$fullPath}");
                return '';
            }

            // File exists and is readable, proceed with download
            $this->Name = $pc->getName();
            $this->length = filesize($fullPath);
            $this->contentType = mime_content_type($fullPath);
            $this->filePath = $fullPath;

            // VIDEO/AUDIO: leave the body empty on purpose — routes.php streams
            // the bytes straight from $this->filePath (with HTTP Range support
            // on 'open': Safari/iOS refuse to play media without 206 partial
            // responses, and seeking needs them in every browser). Media files
            // are also far too large to funnel through file_get_contents.
            if (preg_match('#^(video|audio)/#', (string) $this->contentType)) {
                return '';
            }

            $contents = file_get_contents($fullPath);

            // file_get_contents can also return false on error
            if ($contents === false) {
                error_log("TemplateFile download error: Failed to read file contents from: {$fullPath}");
                return '';
            }

            return $contents;
        }

        // No record ID provided
        return '';
    }


    public function file()
    {
        $this->contentType = "application/json";

        // AUTHORIZATION (security): uploads MUTATE a record's file, so require a
        // connected caller holding WRITE rights on this entity (root/Admin
        // bypass). Mirrors getFileContent's read guard; without it any
        // authenticated user could overwrite another record's file (IDOR).
        $gcAuth = $_SESSION[_AUTH_VAR] ?? null;
        if (!is_object($gcAuth) || $gcAuth->get('connected') !== 'YES'
            || (!$gcAuth->isRoot() && $gcAuth->hasRights('TemplateFile', 'w') === false)) {
            error_log("TemplateFile upload denied: unauthenticated or lacks write right");
            $ret['status'] = 'failure';
            $ret['messages'][] = 'Not authorized';
            return json_encode($ret);
        }

        if (!isset($_FILES["file"]) || !is_uploaded_file($_FILES["file"]["tmp_name"]) || $_FILES["file"]["error"] != 0) {
            $ret['status'] = 'failure';
            $ret['messages'][] = "File missing";
            return json_encode($ret);
        } else {
            $size = round($_FILES["file"]["size"] / 1024, 2);

            $allowedSize = intval('10mb')*1024; /* assumes Mb*/
            if($size > $allowedSize){ /* Size in Kb */
                $ret['status'] = 'error';
                $ret['messages'][] = "File too big";
                return json_encode($ret);
            }

            $tab_style = getimagesize($_FILES['file']['tmp_name']);
            $path_info = pathinfo($_FILES['file']['name']);
            $path_info["extension"] = strtolower($path_info["extension"]);

            if($path_info["extension"]) {

                // Server-side extension allowlist: never trust the client
                // filename. Executable / inline-renderable types (php*, phtml,
                // phar, html, svg, ...) are rejected — an uploaded .php under
                // public/file/ would otherwise be served and executed (RCE).
                // SECURITY (review M2): no inline-renderable types (html/htm/svg/
                // svgz/xhtml/xml). The .htaccess ForceType defense is Apache-only,
                // so on nginx an uploaded .html/.svg would serve inline = stored
                // XSS. Drop them from the allowlist (server-agnostic).
                // List = base defaults UNION this table's own declared
                // filters.mime_types extensions, MINUS the hard denylist
                // (denylist always wins — see allowedUploadExtensions()).
                $gcAllowedExt = ['jpg','jpeg','png','gif','webp','bmp','pdf','doc','docx','xls','xlsx','ppt','pptx','csv','txt','md','zip','odt','ods','mp4','m4v','mov','webm','ogv','avi','mkv','mpg','mpeg'];
                if (!in_array($path_info["extension"], $gcAllowedExt, true)) {
                    if(is_file($_FILES['file']['tmp_name'])) { unlink($_FILES['file']['tmp_name']); }
                    $ret['status'] = 'failure';
                    $ret['messages'][] = 'File type not allowed';
                    return json_encode($ret);
                }

                // request['data'] hydrates to '' (string) when the multipart
                // POST carries no extra fields (plain list-header upload) —
                // normalize before offset reads (PHP 8 TypeError on
                // string['key']).
                $reqData = (isset($this->request['data']) && is_array($this->request['data'])) ? $this->request['data'] : [];
                // Replace mode: the edit form sent an existing record's pk
                // (idUpd) → overwrite that row's file in place. Delete the old
                // file, write the new one, update the column. No new row.
                $idUpd = isset($reqData['idUpd']) ? $reqData['idUpd'] : '';
                if($idUpd !== '' && $idUpd !== '0') {
                    $data = [];
                    // Scope the replace target to the caller's tenant / Owner-Group
                    // (root/Admin bypass) — a raw findPk here would let a writer
                    // overwrite any record's file across tenants/owners (IDOR).
                    $eUpd = $gcAuth->loadPkScoped(TemplateFileQuery::class, $idUpd, 'TemplateFile', 'w');
                    if(!$eUpd) {
                        $data['status'] = 'failure';
                        $data['messages'][] = 'Record not found';
                        if(is_file($_FILES['file']['tmp_name'])) { unlink($_FILES['file']['tmp_name']); }
                        return json_encode($data);
                    }
                    $path_file = 'public/file/TemplateFile/';
                    $destDir = _INSTALL_PATH.'/'.$path_file;
                    if(!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                    $gcHtUpd = rtrim($destDir, '/') . '/.htaccess';
                    if(!file_exists($gcHtUpd)) { @file_put_contents($gcHtUpd, '# GoatCheese upload directory - no script execution
<IfModule mod_php.c>
php_flag engine off
</IfModule>
<IfModule mod_php7.c>
php_flag engine off
</IfModule>
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar .phps
RemoveType .php .phtml .php3 .php4 .php5 .php7 .phar .phps
<FilesMatch "\\.(php[0-9]?|phtml|phar|phps)$">
SetHandler none
Require all denied
</FilesMatch>
<FilesMatch "\\.(html?|xhtml|svgz?|xml)$">
ForceType text/plain
<IfModule mod_headers.c>
Header set Content-Disposition attachment
Header set X-Content-Type-Options nosniff
</IfModule>
</FilesMatch>
'); }
                    $oldFile = $eUpd->getFile();
                    $newRel = $path_file.md5((string)$eUpd->getPrimaryKey()).'.'.$path_info['extension'];
                    $copyret = copy($_FILES['file']['tmp_name'], _INSTALL_PATH.'/'.$newRel);
                    if(is_file($_FILES['file']['tmp_name'])) { unlink($_FILES['file']['tmp_name']); }
                    if($copyret === false) {
                        $data['status'] = 'failure';
                        $data['messages'][] = 'Cannot copy file to destination';
                        return json_encode($data);
                    }
                    // Remove the previous file once the replacement is written
                    // (skip when the path is unchanged — same pk + extension).
                    // PATH-TRAVERSAL CONFINEMENT: the stored path can be poisoned,
                    // so only unlink when it resolves inside this table's upload dir.
                    $gcOldReal = ($oldFile && $oldFile !== $newRel) ? realpath(_INSTALL_PATH.'/'.ltrim($oldFile, '/')) : false;
                    $gcOldRoot = realpath(_INSTALL_PATH.'/public/file/TemplateFile');
                    if($gcOldReal !== false && $gcOldRoot !== false
                        && strncmp($gcOldReal, $gcOldRoot . DIRECTORY_SEPARATOR, strlen($gcOldRoot) + 1) === 0
                        && is_file($gcOldReal)) {
                        unlink($gcOldReal);
                    }
                    $eUpd->setFile($newRel);
                    $eUpd->save();
                    $data['status'] = 'success';
                    $data['idPk'] = $eUpd->getPrimaryKey();
                    $data['File'] = $newRel;
                    // Fire AfterFileUpload AFTER idPk is set — the hook keys on $data['idPk'].

                    return json_encode($data);
                }

                $data = [
                                'data' => [
                                    'name' => $_FILES['file']['name'],
                                    'size' => $size,
                                    'extension' => $path_info['extension'],
                                    'ip' => (isset($reqData['ip']) ? $reqData['ip'] : '')
                                ]
                        ];
                $tabIp = explode(',', isset($reqData['ip']) ? $reqData['ip'] : '');
                if($tabIp) {
                    foreach(array_unique($tabIp) as $ip) {
                        $e = $this->Form->setCreateDefaultsTemplateFile($data['data']);
                        if ($_SESSION[_AUTH_VAR]->loadPkScoped(TemplateQuery::class, $ip, 'Template', 'w') === null) { continue; }
                        $e->setIdTemplate($ip);
                        $e->setName($_FILES['file']['name']);
                        $path_dest = 'public/file/';
                        $path_file= 'public/file/TemplateFile/';


                        if($data['error'] == '') {

                            $gcEnsureDir = function($dir, $indexFile) {
                                if(!is_dir($dir)) {
                                    if(!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                                        return 'Cannot create directory: ' . $dir;
                                    }
                                }
                                if(!file_exists($indexFile)) {
                                    $fp = @fopen($indexFile, 'w');
                                    if($fp === false) { return 'Cannot write guard: ' . $indexFile; }
                                    fwrite($fp, '<?php header(\'Location:'._SITE_URL.'\'); ');
                                    fclose($fp);
                                }
                                $gcHt = rtrim($dir, '/') . '/.htaccess';
                                if(!file_exists($gcHt)) {
                                    if(@file_put_contents($gcHt, '# GoatCheese upload directory - no script execution
<IfModule mod_php.c>
php_flag engine off
</IfModule>
<IfModule mod_php7.c>
php_flag engine off
</IfModule>
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar .phps
RemoveType .php .phtml .php3 .php4 .php5 .php7 .phar .phps
<FilesMatch "\\.(php[0-9]?|phtml|phar|phps)$">
SetHandler none
Require all denied
</FilesMatch>
<FilesMatch "\\.(html?|xhtml|svgz?|xml)$">
ForceType text/plain
<IfModule mod_headers.c>
Header set Content-Disposition attachment
Header set X-Content-Type-Options nosniff
</IfModule>
</FilesMatch>
') === false) { return 'Cannot write guard: ' . $gcHt; }
                                }
                                return '';
                            };
                            $gcDirErr = $gcEnsureDir(_INSTALL_PATH.'/'.$path_dest, _INSTALL_PATH.'/public/file/index.php');
                            if($gcDirErr === '') {
                                $gcDirErr = $gcEnsureDir(_INSTALL_PATH.'/'.$path_file, _INSTALL_PATH.'/'.$path_file.'/index.php');
                            }
                            if($gcDirErr !== '') {
                                $data['messages'][] = $gcDirErr;
                                $data['status'] = 'failure';
                                if(is_file($_FILES['file']['tmp_name'])) { unlink($_FILES['file']['tmp_name']); }
                                return json_encode($data);
                            }

                            $e->setFile('tmp-placeholder');

                            if ($e->validate()) {
                                $e->save();
                                $data['idPk'] = $e->getPrimaryKey();

                                $copyret = copy($_FILES['file']['tmp_name'], _INSTALL_PATH.'/'.$path_file.md5((string)$data['idPk']) . "." . $path_info["extension"] . "");

                                if($copyret === false) {
                                    $data['messages'][] = "Cannot copy file to destination ("._INSTALL_PATH.'/'.$path_file.md5((string)$data['idPk']) . '.' . $path_info['extension'].")";
                                    $data['status'] = 'failure';
                                    $e->delete();
                                } else {
                                    $data['File'] = $path_file.md5((string)$data['idPk']) . "." . $path_info["extension"];

                                    $e->setFile($data['File']);
                                    $e->save();
                                    $data['data']['IdTemplateFileFile'] = $e->getPrimaryKey();

                                    $data['status'] = 'success';
                                }
                            } else {
                                $data['messages'][] = "Db error";
                                $msg = handleValidationError($e, ((isset($this->request['data']['ui']) ? $this->request['data']['ui'] : '')), _('User'), $extValidationErr);
                                $data['messages'][] = $msg['txt'];
                                $data['status'] = 'failure';
                            }
                        }
                    }

                    if(is_file($_FILES['file']['tmp_name'])) {
                        unlink($_FILES['file']['tmp_name']);
                    }
                }

            }

            return json_encode($data);
        }
    }










    public function deleteOne()
    {
        $error = [];
        $messages = '';

        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(TemplateFileQuery::class, json_decode($this->request['i']), 'TemplateFile', 'd');
        if ($obj) {



        $obj->delete();

            // PATH-TRAVERSAL CONFINEMENT: the file column can be poisoned; only
            // unlink inside this table's own upload dir (mirrors getFileContent).
            $gcDelReal = realpath(_INSTALL_PATH.$obj->getFile());
            $gcDelRoot = realpath(_INSTALL_PATH.'/public/file/TemplateFile');
            if($gcDelReal !== false && $gcDelRoot !== false
                && strncmp($gcDelReal, $gcDelRoot . DIRECTORY_SEPARATOR, strlen($gcDelRoot) + 1) === 0
                && is_file($gcDelReal)) {
                unlink($gcDelReal);
            }




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

        $data['i'] = ( $data['IdTemplateFile'] ) ? $data['IdTemplateFile'] : $this->request['i'];
        $data['ip'] = urldecode($this->request['data']['ip'] ?? '');
        $data['pc'] = urldecode($this->request['data']['pc'] ?? '');
        $this->TemplateFile['request'] = $this->request;

        if(!empty($data['i'])) {
            ## Save

            $e = $this->Form->setUpdateDefaultsTemplateFile($data);
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


            $e = $this->Form->setCreateDefaultsTemplateFile($data);

            if($data['ip']){
                $e->setIdTemplate(json_decode($data['ip']));
            }


            if ($e->validate() && !$extValidationErr) {
                $e->save();
                $this->request['i'] = json_encode($e->getPrimaryKey());


                //$data['IdTemplate'] = json_encode($e->getIdTemplate());
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
        $this->TemplateFile['request'] = $this->request;
        $this->TemplateFile['parentId'] = $this->request['data']['ip'];

            if($this->request['data']['ip']){
                $relData['IdTemplate'] = $this->request['data']['ip'];
                $relData['ip'] = json_decode($this->request['data']['ip']);
                $relData['pc'] = $this->request['data']['pc'];
            }

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
