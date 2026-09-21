<?php

declare(strict_types=1);

namespace App;


/**
 *  AUTO-GENERATED &mdash; edit the wrapper or the schema, not this file.
 *
 *  @version 1.1
 *  Generated Form class on the 'Authy' table.
 *
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use ApiGoat\Html\Tabs;
use ApiGoat\Utility\FormHelper as Helper;

class AuthyForm extends Authy
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

        public $arrayIdAuthyGroupOptions;
    public $queryObjAuthyGroupX;
    public $listActionCellAuthyGroupX;
    public $queryObjAuthyLog;
    public $listActionCellAuthyLog;
    public $commentsIdAuthy;
    public $commentsIdAuthy_css;
    public $commentsValidationKey;
    public $commentsValidationKey_css;
    public $commentsUsername;
    public $commentsUsername_css;
    public $commentsFullname;
    public $commentsFullname_css;
    public $commentsEmail;
    public $commentsEmail_css;
    public $commentsPasswdHash;
    public $commentsPasswdHash_css;
    public $commentsExpire;
    public $commentsExpire_css;
    public $commentsDeactivate;
    public $commentsDeactivate_css;
    public $commentsLanguage;
    public $commentsLanguage_css;
    public $commentsTheme;
    public $commentsTheme_css;
    public $commentsGoogleSub;
    public $commentsGoogleSub_css;
    public $commentsGoogleEmail;
    public $commentsGoogleEmail_css;
    public $commentsResetTokenHash;
    public $commentsResetTokenHash_css;
    public $commentsResetTokenExpires;
    public $commentsResetTokenExpires_css;
    public $commentsIdTenant;
    public $commentsIdTenant_css;
    public $commentsLocationAddress;
    public $commentsLocationAddress_css;
    public $commentsLocationLat;
    public $commentsLocationLat_css;
    public $commentsLocationLng;
    public $commentsLocationLng_css;
    public $commentsIsRoot;
    public $commentsIsRoot_css;
    public $commentsIdAuthyGroup;
    public $commentsIdAuthyGroup_css;
    public $commentsIsSystem;
    public $commentsIsSystem_css;
    public $commentsRightsAll;
    public $commentsRightsAll_css;
    public $commentsRightsGroup;
    public $commentsRightsGroup_css;
    public $commentsRightsOwner;
    public $commentsRightsOwner_css;
    public $commentsOnglet;
    public $commentsOnglet_css;
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
    public $Authy;
    public $AuthyGroupX;
    public $hookAuthyGroupXListTop;
    public $hookAuthyGroupXListBottom;
    public $hookAuthyGroupXTableFooter;
    public $hookListReadyJsAuthyGroupX;
    public $hookListReadyJsFirstAuthyGroupX;
    public $AuthyLog;
    public $hookAuthyLogListTop;
    public $hookAuthyLogListBottom;
    public $hookAuthyLogTableFooter;
    public $hookListReadyJsAuthyLog;
    public $hookListReadyJsFirstAuthyLog;
    public $commentsIdAuthyLog;
    public $commentsIdAuthyLog_css;
    public $commentsTimestamp;
    public $commentsTimestamp_css;
    public $commentsLogin;
    public $commentsLogin_css;
    public $commentsUserid;
    public $commentsUserid_css;
    public $commentsResult;
    public $commentsResult_css;
    public $commentsEvent;
    public $commentsEvent_css;
    public $commentsIp;
    public $commentsIp_css;
    public $commentsCount;
    public $commentsCount_css;


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
        $this->model_name = 'Authy';
        $this->virtualClassName = 'Authy';
        $this->childMaxPerPage = (defined('app_child_max_per_page') && (int) app_child_max_per_page > 0) ? (int) app_child_max_per_page : 30;
        $this->maxPerPage = (defined('app_max_per_page') && (int) app_max_per_page > 0) ? (int) app_max_per_page : 50;
        $this->hookFormBottom = '';
        $this->hookFormReadyJs = '';

    }

    public function login($username='', $mobile = false)
    {
        // Fallback FIRST: the old shape only assigned $logoAdmin in the else,
        // so a defined LOGO_URL_LOGIN pointing at a missing file left it
        // undefined and img($logoAdmin) emitted a warning + a broken image.
        $logoAdmin = _SITE_URL.'public/img/logo-admin.png';
        if(defined('LOGO_URL_LOGIN') && file_exists(_INSTALL_PATH . 'public/img/' . LOGO_URL_LOGIN)) {
            $logoAdmin = LOGO_URL_LOGIN;
        }

        if(empty($_SESSION[_AUTH_VAR]->getCsrf())) {
            $_SESSION[_AUTH_VAR]->setCsrf( md5(uniqid('GoAt').uniqid('', true)) );
        }

        $return['html'] =
                div(
                    div(
                        div(
                            img($logoAdmin)
                        ,'','class="ac-client-logo"')
                        .form(
                            div(
                                input('text', 'user', '', 'placeholder="Username" autocapitalize="none"')
                                .input('password', 'passwd', '', 'placeholder="Password" autocapitalize="none"')
                            , '', 'class="input-wrapper"')
                            .div('', 'login-message', 'class="login-message" role="alert" aria-live="polite"')
                            .button( _("Sign in"), 'id="logMe"' )

                            .href(_("Forgot password?"), '#', 'class="lost-password"')
                            // 'Sign in with Google' (GIS) — only when GOOGLE_CLIENT_ID is configured,
                            // so unconfigured projects render a byte-identical login page.
                            // We DON'T use the HTML auto-render (#g_id_onload + data-width): that
                            // renders a fixed-width button (overflows the ~322px card) AND auto-shows
                            // One Tap, which on some setups fills an account but never completes. The
                            // button is rendered via the JS API in onReadyJs instead (width measured
                            // from the card, no One Tap). NO SRI hash / NO crossorigin on gsi/client:
                            // it is an unversioned, Google-rotated script served without
                            // Access-Control-Allow-Origin, so either would break the load.
                            . (($gcGid = (string) ($_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '')) !== ''
                                ? '<div class="login-google"><div class="g_id_signin"></div></div>'
                                    . '<script src="https://accounts.google.com/gsi/client"' . gcNonceAttr() . ' async defer referrerpolicy="no-referrer"></script>'
                                : '')
                            .input("hidden", "csrf", $_SESSION[_AUTH_VAR]->getCsrf())
                        , 'id="login-form"')
                    , '', 'class="login-content"')
                , 'logMeContainer', 'class="login-wrapper"')
                .$this->hookLogin;

        $return['onReadyJs'] .= "

        function __gcEls(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }
        function __gcFade(els, dir, ms, cb){
            var pending = els.length, fired = false;
            function fin(){ if(fired){ return; } fired = true; if(cb){ cb(); } }
            if(!pending){ fin(); return; }
            els.forEach(function(el){
                el.style.transition = 'opacity '+ms+'ms';
                if(dir === 'in'){ el.style.opacity = '0'; el.style.display = ''; void el.offsetWidth; el.style.opacity = '1'; }
                else { el.style.opacity = getComputedStyle(el).opacity; void el.offsetWidth; el.style.opacity = '0'; }
                setTimeout(function(){ if(dir === 'out'){ el.style.display = 'none'; } if(--pending === 0){ fin(); } }, ms);
            });
        }
        function __gcSlide(el, dir, ms){
            if(!el){ return; }
            el.style.overflow = 'hidden';
            el.style.transition = 'height '+ms+'ms, opacity '+ms+'ms';
            if(dir === 'down'){
                el.style.display = ''; var h = el.scrollHeight;
                el.style.height = '0px'; el.style.opacity = '0'; void el.offsetWidth;
                el.style.height = h+'px'; el.style.opacity = '1';
                setTimeout(function(){ el.style.height = ''; el.style.overflow = ''; el.style.transition = ''; }, ms);
            } else {
                el.style.height = el.scrollHeight+'px'; void el.offsetWidth;
                el.style.height = '0px'; el.style.opacity = '0';
                setTimeout(function(){ el.style.display = 'none'; el.style.height = ''; el.style.overflow = ''; el.style.transition = ''; }, ms);
            }
        }
        function __gcLoginMsg(text, isError){
            var el = document.getElementById('login-message');
            if(!el){ return; }
            var msg = Array.isArray(text) ? text.join(' ') : (text || '');
            el.textContent = msg;
            el.classList.remove('error','success');
            if(msg){ el.classList.add(isError ? 'error' : 'success'); }
        }
        (function(){ var __u = document.querySelector('#login-form #user'); if(__u){ __u.focus(); } })();
        (function(){
            var __lp = document.querySelector('#login-form .lost-password');
            var __form = document.getElementById('login-form');
            if(!__lp || !__form){ return; }
            __lp.addEventListener('click', function() {
                if(!__form.classList.contains('forgot-password')){
                    __form.classList.add('forgot-password');
                    __gcFade(__gcEls('#login-form .lost-password,#login-form #logMe'), 'out', 250, function() {
                        __gcSlide(document.querySelector('#login-form #passwd'), 'up', 250);
                        var __us = document.querySelector('#login-form #user'); if(__us){ __us.setAttribute('placeholder','".addslashes(_("Email"))."'); }
                        __gcEls('#login-form input').forEach(function(__i){ __i.value = ''; });
                        var __lpc = document.querySelector('#login-form .lost-password'); if(__lpc){ __lpc.textContent = '".addslashes(_("Cancel"))."'; }
                        var __lm = document.querySelector('#login-form #logMe'); if(__lm){ __lm.textContent = '".addslashes(_("Reset"))."'; }
                        __gcFade(__gcEls('#login-form #user,#login-form .lost-password,#login-form #logMe'), 'in', 250);
                    });
                } else {
                    __form.classList.remove('forgot-password');
                    __gcFade(__gcEls('#login-form .lost-password,#login-form #logMe'), 'out', 250, function() {
                        __gcSlide(document.querySelector('#login-form #passwd'), 'down', 250);
                        var __us = document.querySelector('#login-form #user'); if(__us){ __us.setAttribute('placeholder','".addslashes(_("Username"))."'); }
                        var __lpc = document.querySelector('#login-form .lost-password'); if(__lpc){ __lpc.textContent = '".addslashes(_("Forgot password?"))."'; }
                        var __lm = document.querySelector('#login-form #logMe'); if(__lm){ __lm.textContent = '".addslashes(_("Sign in"))."'; }
                        __gcFade(__gcEls('#login-form #user,#login-form .lost-password,#login-form #logMe'), 'in', 250);
                    });
                }
            });
        })();
        (function(){
            var __lmc = document.getElementById('logMeContainer');
            if(!__lmc){ return; }
            __lmc.addEventListener('submit', function(__ev) {
                var __form = (__ev.target && __ev.target.closest) ? __ev.target.closest('#login-form') : null;
                if(!__form){ return; }
                __ev.preventDefault();
                var __user = document.getElementById('user'), __passwd = document.getElementById('passwd');
                var __uv = __user ? __user.value : '', __pv = __passwd ? __passwd.value : '';
                var __forgot = __form.classList.contains('forgot-password');
                if(!__forgot && (__uv == '' || __pv == '')) {
                    __gcLoginMsg('".addslashes(_("Please enter your username and password"))."', true);
                }
                if(__uv != '' && __pv != '' || __uv != '' && __forgot) {
                    login_ready = false;
                    if(!__forgot) {
                        // Send plain password over HTTPS - server uses password_verify() with bcrypt
                        var __csrf = document.getElementById('csrf'), __stay = document.getElementById('stay');
                        var __b = new URLSearchParams();
                        __b.append('u', __uv); __b.append('csrf', __csrf ? __csrf.value : ''); __b.append('p', __pv); __b.append('stay', __stay ? __stay.value : '');
                        fetch('"._SITE_URL."Authy/auth', {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:__b.toString()})
                        .then(function(__r){ return __r.text().then(function(__t){ var __d=null; try{ __d=JSON.parse(__t); }catch(__e){} return {ok:__r.ok, data:__d}; }); })
                        .then(function(__res){
                            if(!__res.ok){ var __rj = __res.data || {}; __gcLoginMsg(__rj.messages, true); return; }
                            var data = __res.data || {};
                            // Authy/auth returns HTTP 200 even on a bad password (status:'failure'
                            // in the body), so error styling must come from data.status, not __res.ok
                            // — otherwise a failed login renders with the muted 'success' style and
                            // auto-fades, looking like nothing happened. Mirrors the reset path below;
                            // a login failure sticks (no redirect follows) so the user actually sees it.
                            var __fail = data.status !== 'success';
                            __gcLoginMsg(data.messages, __fail);
                            var _msg = Array.isArray(data.messages) ? data.messages.join(' ') : (data.messages || '');
                            if(_msg.indexOf('session expired') !== -1) { location.reload(); return; }
                            if(data.status == 'success')
                            {
                                var __tz = getTimeZoneData();
                                var __gb = new URLSearchParams(); __gb.append('a', 'login');
                                for(var __k in __tz){ if(Object.prototype.hasOwnProperty.call(__tz, __k)){ __gb.append('t['+__k+']', __tz[__k]); } }
                                fetch('"._SITE_URL."GuiManager', {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:__gb.toString()}).then(function() {
                                    window.location.href = '"._SITE_URL."';
                                });
                            }
                            login_ready = true;
                        });
                    } else {
                        var __cu = document.querySelector('#login-form #user');
                        var __b2 = new URLSearchParams(); __b2.append('c', __cu ? __cu.value : '');
                        fetch('"._SITE_URL."Authy/reset', {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:__b2.toString()})
                        .then(function(__r){ return __r.ok ? __r.text().then(function(__t){ try{ return JSON.parse(__t); }catch(__e){ return null; } }) : null; })
                        .then(function(data){
                            if(!data){ return; }
                            __gcLoginMsg(data.messages, data.status !== 'success');
                            if(data.status === 'success') { setTimeout(function(){ window.location.href = '"._SITE_URL."'; }, 3000); }
                            login_ready = true;
                        });
                    }
                }
                return false;
            });
        })();
        ";

        // Google Identity Services credential callback — emitted only when
        // GOOGLE_CLIENT_ID is configured (same runtime conditional as the
        // markup above) so unconfigured projects stay byte-identical.
        // Success/failure handling duplicates the Authy/auth handler verbatim;
        // only the endpoint and request body differ.
        if ($gcGid !== '') {
            $return['onReadyJs'] .= "
        window.gcGoogleCredential = function (resp) {
            // Blocking loader: the verify -> session -> GuiManager -> redirect round-trip
            // takes a moment; cover the page with the design-system .page-loader (its
            // z-index 99999 overlay also swallows clicks, so the form can't be
            // re-triggered). Removed on failure; kept on success until the redirect.
            login_ready = false;
            var __gLoad = document.getElementById('gcGoogleLoader');
            if(!__gLoad){
                __gLoad = document.createElement('div');
                __gLoad.id = 'gcGoogleLoader';
                // Inline styles (not the #pageLoader-scoped CSS class) so the overlay is
                // self-contained and works on the login page regardless of stylesheet
                // scoping; reuses only the global 'pl-spin' keyframe for the spinner.
                __gLoad.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.65);';
                var __gSpin = document.createElement('div');
                __gSpin.style.cssText = 'width:48px;height:48px;border:4px solid rgba(0,0,0,0.15);border-top-color:#00d1b2;border-radius:50%;animation:pl-spin 0.8s linear infinite;';
                __gLoad.appendChild(__gSpin);
                document.body.appendChild(__gLoad);
            }
            function __gHide(){ var __e = document.getElementById('gcGoogleLoader'); if(__e && __e.parentNode){ __e.parentNode.removeChild(__e); } login_ready = true; }
            var __b = new URLSearchParams();
            __b.append('credential', resp.credential);
            fetch('"._SITE_URL."Authy/google', {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:__b.toString()})
            .then(function(__r){ return __r.text().then(function(__t){ var __d=null; try{ __d=JSON.parse(__t); }catch(__e){} return {ok:__r.ok, data:__d}; }); })
            .then(function(__res){
                if(!__res.ok){ __gHide(); var __rj = __res.data || {}; __gcLoginMsg(__rj.messages, true); return; }
                var data = __res.data || {};
                __gcLoginMsg(data.messages, false);
                var _msg = Array.isArray(data.messages) ? data.messages.join(' ') : (data.messages || '');
                if(_msg.indexOf('session expired') !== -1) { location.reload(); return; }
                if(data.status == 'success')
                {
                    var __tz = getTimeZoneData();
                    var __gb = new URLSearchParams(); __gb.append('a', 'login');
                    for(var __k in __tz){ if(Object.prototype.hasOwnProperty.call(__tz, __k)){ __gb.append('t['+__k+']', __tz[__k]); } }
                    fetch('"._SITE_URL."GuiManager', {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:__gb.toString()}).then(function() {
                        // Defer the parent navigation briefly so GIS can finish closing its
                        // account-chooser popup before this window unloads (otherwise the
                        // orphaned popup lingers open after a successful sign-in). Loader stays.
                        setTimeout(function(){ window.location.href = '"._SITE_URL."'; }, 400);
                    });
                } else {
                    __gHide();
                }
            })
            .catch(function(){ __gHide(); });
        };

        // Render the Google button via the JS API (not HTML auto-render): the width
        // is measured from the login card so the button never overflows, and One Tap
        // is NOT prompted (no google.accounts.id.prompt()), so there is no stray
        // account chooser that fills a name without signing in. Re-renders on resize.
        (function(){
            var __gEl = document.querySelector('.g_id_signin');
            if(!__gEl){ return; }
            function __gcRenderGoogle(){
                if(!(window.google && window.google.accounts && window.google.accounts.id)){ return false; }
                window.google.accounts.id.initialize({ client_id: '".$gcGid."', callback: window.gcGoogleCredential, auto_select: false, cancel_on_tap_outside: true });
                var __pw = __gEl.parentElement ? __gEl.parentElement.clientWidth : 0;
                var __w = Math.max(200, Math.min(400, __pw || __gEl.clientWidth || 320));
                __gEl.innerHTML = '';
                window.google.accounts.id.renderButton(__gEl, { type:'standard', shape:'rectangular', theme:'outline', text:'signin_with', size:'large', width: __w });
                return true;
            }
            var __gTries = 0;
            (function __gcWaitGsi(){ if(__gcRenderGoogle()){ return; } if(__gTries++ < 100){ setTimeout(__gcWaitGsi, 50); } })();
            var __gRt; window.addEventListener('resize', function(){ clearTimeout(__gRt); __gRt = setTimeout(__gcRenderGoogle, 150); });
        })();
            ";
        }
        return $return;
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

        $q = new AuthyQuery();
        $q = $this->setAclFilter($q);


        $q

                #alias required
                ->leftJoinWith('AuthyGroupRelatedByIdAuthyGroup a10');
        if(is_array( $this->searchMs )){
            # main search form

        if( isset($this->searchMs['Username']) ) {
            $criteria = \Criteria::LIKE;


            $value = $this->setCriteria($this->searchMs['Username'], $criteria);

            $q->filterByUsername($value, $criteria)->_or()->filterByEmail($value, $criteria);
        }
        if( isset($this->searchMs['IdAuthyGroup']) ) {
            $criteria = \Criteria::EQUAL;
            $value = $this->searchMs['IdAuthyGroup'];

            $q->filterByIdAuthyGroup($value, $criteria);
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
                                    . ' on Authy — ' . $gcOrdEx->getMessage());
                                unset($_SESSION['mem']['order']['Authy/'],
                                    $_SESSION['mem']['order']['Authy/child']);
                            }
                            if($gcOrdApplied){
                            # C8: $col / $sens come from the session (setOrderVar), so they
                            # are never interpolated raw into the JS source. JSON_HEX_* keeps
                            # quotes/tags/ampersands out of the surrounding <script> and the
                            # attribute selector is composed client-side from the JSON value.
                            $gcOrdCol = json_encode((string) $col, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $gcOrdSens = json_encode(strtolower((string) $sens), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
                            $this->orderReadyJsOrder .="
                                (function(){var __c=".$gcOrdCol.",__s=".$gcOrdSens.";var __se=document.querySelector(\"#AuthyListForm [th='sorted'][c=\"+JSON.stringify(__c)+\"]\");if(__se){__se.setAttribute('sens', __s);__se.setAttribute('order','on');__se.classList.add('sorted');}})();
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
                $trHead = th(_("Username"), " th='sorted' c='Username' title='" . _('Username')."' ")
.th(_("Fullname"), " th='sorted' c='Fullname' title='" . _('Fullname')."' ")
.th(_("Email"), " th='sorted' c='Email' title='" . _('Email')."' ")
.th(_("Expiration"), " th='sorted' c='Expire' title='" . _('Expiration')."' ")
.th(_("Deactivated"), " th='sorted' c='Deactivate' title='" . _('Deactivated')."' ")
.th(_("Language"), " th='sorted' c='Language' title='" . _('Language')."' ")
.th(_("Theme"), " th='sorted' c='Theme' title='" . _('Theme')."' ")
.th(_("Tenant"), " th='sorted' c='IdTenant' title='" . _('Tenant')."' ")
.th(_("Location Address"), " th='sorted' c='LocationAddress' title='" . _('Location Address')."' ")
.th(_("Root"), " th='sorted' c='IsRoot' title='" . _('Root')."' ")
.th(_("Primary group"), " th='sorted' c='AuthyGroupRelatedByIdAuthyGroup.Name' title='"._('AuthyGroupRelatedByIdAuthyGroup.Name')."' ")
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

        if($this->setReadOnly !== 'all'){ $this->arrayIdAuthyGroupOptions = $this->selectBoxAuthy_IdAuthyGroup($this, $emptyVar, $data); } else { $this->arrayIdAuthyGroupOptions = []; }


                $trSearch = ''
                .form(div(div(input('text', 'Username', $this->searchMs['Username'] ?? '', '  title="'._('Name').'" placeholder="'._('Search').' '._('Name').'"',''),'','class="ac-search-item"'), '', " class='va-mob-search-inline' ").$this->hookListSearchTop.button("<i class='ri-filter-3-line'></i>", " type='button' class='va-mob-filter-btn' aria-haspopup='dialog' aria-label='"._('Filter')."' ").div(
                    div('',''," class='va-filter-dim' ")
                    .div(
                        div("",''," class='sheet-handle' ")
                    .div(
                        span(_('Filter')." "._('Authy')," class='sheet-title' ")
                        .button("<i class='ri-close-line'></i>"," type='button' class='sheet-close' aria-label='"._('Close')."' ")
                    ,''," class='sheet-head' ")
                        .div(div(
                        div(_('Search'),''," class='va-fblock-lbl' ")
                        .div("<i class='ri-search-line'></i>".input('text','',''," class='va-fsearch-input' autocomplete='off' placeholder='"._('Search')." "._('Authy')."…' "),''," class='va-fsearch' ")
                    ,''," class='va-fblock' data-block='search' ").div(_('More filters'),''," class='va-fgroup-cap' ").div(
                        div(_('Primary group'),''," class='va-fblock-lbl' ")
                        .div(div(selectboxCustomArray('IdAuthyGroup', $this->arrayIdAuthyGroupOptions, 'Primary group' , "v='ID_AUTHY_GROUP'  s='d' ", ($this->searchMs['IdAuthyGroup'] ?? ''), '', false), '', ' class="ac-search-item "'),''," class='va-fselect' ")
                    ,''," class='va-fblock va-fblock-select' data-block='select' "),''," class='sheet-body' ")
                        .div(
                        button(span(_('Clear all'))," type='button' class='va-fclear' ")
                        .button(span(_('Cancel'))," type='button' class='va-fcancel' ")
                        .button("<i class='ri-check-line'></i>".span(_('Apply'))," type='button' class='va-fapply' ")
                    ,''," class='va-filter-foot' ")
                    ,''," class='va-filter-panel' tabindex='-1' ")
                ,''," class='va-filter-surface' role='dialog' aria-modal='true' aria-label='"._('Filter')." "._('Authy')."' hidden ").div(
                           button(span(_("Search")),'id="msAuthyBt" title="'._('Search').'" class="icon search"')
                           .button(span(_("Clear")),' title="'._('Clear search').'" id="msAuthyBtClear"')
                        ,'','class="ac-search-item ac-action-buttons"')
                ,"id='formMsAuthy' class='va-mob-searchform' data-entity='Authy'");
                return $trSearch;

            case 'add':
            ###### ADD
                if($_SESSION[_AUTH_VAR]->hasRights('Authy', 'a') && !$this->setReadOnly){

                                $this->listAddButton = htmlLink(
                                    _("Add new")
                                ,_SITE_URL.$this->virtualClassName."/edit/", "id='addAuthy' title='"._('Add')."' class='button-link-blue add-button'");
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
        $this->TableName = 'Authy';
        # A11: the per-row reset below restores this seed instead of nulling
        # $altValue — every `($altValue['X'] !== null) ? … : …` cell read from
        # row 2 on was an array offset on null (one warning per cell per row).
        $__altValueInit = array (
  'IdAuthy' => NULL,
  'ValidationKey' => NULL,
  'Username' => NULL,
  'Fullname' => NULL,
  'Email' => NULL,
  'PasswdHash' => NULL,
  'Expire' => NULL,
  'Deactivate' => NULL,
  'Language' => NULL,
  'Theme' => NULL,
  'GoogleSub' => NULL,
  'GoogleEmail' => NULL,
  'ResetTokenHash' => NULL,
  'ResetTokenExpires' => NULL,
  'IdTenant' => NULL,
  'LocationAddress' => NULL,
  'LocationLat' => NULL,
  'LocationLng' => NULL,
  'IsRoot' => NULL,
  'IdAuthyGroup' => NULL,
  'IsSystem' => NULL,
  'RightsAll' => NULL,
  'RightsGroup' => NULL,
  'RightsOwner' => NULL,
  'Onglet' => NULL,
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
            $this->isChild = 'Authy';
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
        $gcListKey = 'Authy/';
        if ($IdParent !== null && $IdParent !== '') {
            $gcListKey = 'Authy/child';
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
            $gcGroupCol = 'Username';
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
                if($_SESSION[_AUTH_VAR]->hasRights('Authy', 'd')){
                    $this->canDelete = htmlLink("<i class='ri-delete-bin-7-line'></i>", "Javascript:", "class='ac-delete-link' j='deleteAuthy' ");
                }
            }

            $gcListRows = [];
            $gcListRowsDt = [];
            foreach($pcData as $data) {
                # hoist the row PK encodings once — reused by the mobile + desktop row wrappers below
                $__pkJsonEsc = htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES);
                $__pkEsc = htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES);
                $this->listActionCell = '';



                                    $AuthyGroupRelatedByIdAuthyGroup_Name = "";
                                    if($data->getAuthyGroupRelatedByIdAuthyGroup()){
                                        $AuthyGroupRelatedByIdAuthyGroup_Name = $data->getAuthyGroupRelatedByIdAuthyGroup()->getName();
                                    }


                $actionInner = '' . $this->canDelete . $this->listActionCell;
                $actionCell =  td($actionInner, " class='actionrow' ");

                $gcRowHtml = div(
 ''
 . div(
   div('' . span(htmlspecialchars((string)((($altValue['Username'] !== null ) ? $altValue['Username'] : $data->getUsername())))." ", "   i='" . $__pkJsonEsc . "' c='Username' class=''  j='editAuthy'") ,''," class='name' ")
   . div(''  . span(htmlspecialchars((string)((($altValue['Fullname'] !== null ) ? $altValue['Fullname'] : $data->getFullname())))." ", "   i='" . $__pkJsonEsc . "' c='Fullname' class=''  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['Email'] !== null ) ? $altValue['Email'] : $data->getEmail())))." ", "   i='" . $__pkJsonEsc . "' c='Email' class=''  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['Expire'] !== null ) ? $altValue['Expire'] : $data->getExpire())))." ", "   i='" . $__pkJsonEsc . "' c='Expire' class=''  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['Deactivate'] !== null ) ? $altValue['Deactivate'] : isntPo($data->getDeactivate()))))." ", "   i='" . $__pkJsonEsc . "' c='Deactivate' class='center'  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['Language'] !== null ) ? $altValue['Language'] : isntPo($data->getLanguage()))))." ", "   i='" . $__pkJsonEsc . "' c='Language' class='center'  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['Theme'] !== null ) ? $altValue['Theme'] : isntPo($data->getTheme()))))." ", "   i='" . $__pkJsonEsc . "' c='Theme' class='center'  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['IdTenant'] !== null ) ? $altValue['IdTenant'] : $data->getIdTenant())))." ", "   i='" . $__pkJsonEsc . "' c='IdTenant' class=''  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['LocationAddress'] !== null ) ? $altValue['LocationAddress'] : $data->getLocationAddress())))." ", "   i='" . $__pkJsonEsc . "' c='LocationAddress' class=''  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['IsRoot'] !== null ) ? $altValue['IsRoot'] : isntPo($data->getIsRoot()))))." ", "   i='" . $__pkJsonEsc . "' c='IsRoot' class='center'  j='editAuthy'") . span(htmlspecialchars((string)((($altValue['IdAuthyGroup'] !== null ) ? $altValue['IdAuthyGroup'] : $AuthyGroupRelatedByIdAuthyGroup_Name)))." ", "   i='" . $__pkJsonEsc . "' c='IdAuthyGroup' class=''  j='editAuthy'") . $cCmoreCols ,''," class='meta' ")
 ,'', " class='body' ")
. div('' . '<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. div($actionInner, '', " class='actionrow' ")
                , '', "
                        rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."'
                        r='data'
                        class='va-mob-row ".$hook['class']." '
                        id='AuthyRow".$__pkEsc."'")
                ;
                $gcDtRowHtml = tr(
                td(span(htmlspecialchars((string)((($altValue['Username'] !== null ) ? $altValue['Username'] : $data->getUsername())))." "), "  i='" . $__pkJsonEsc . "' c='Username' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Fullname'] !== null ) ? $altValue['Fullname'] : $data->getFullname())))." "), "  i='" . $__pkJsonEsc . "' c='Fullname' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Email'] !== null ) ? $altValue['Email'] : $data->getEmail())))." "), "  i='" . $__pkJsonEsc . "' c='Email' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Expire'] !== null ) ? $altValue['Expire'] : $data->getExpire())))." "), "  i='" . $__pkJsonEsc . "' c='Expire' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Deactivate'] !== null ) ? $altValue['Deactivate'] : isntPo($data->getDeactivate()))))." "), "  i='" . $__pkJsonEsc . "' c='Deactivate' class='center'  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Language'] !== null ) ? $altValue['Language'] : isntPo($data->getLanguage()))))." "), "  i='" . $__pkJsonEsc . "' c='Language' class='center'  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['Theme'] !== null ) ? $altValue['Theme'] : isntPo($data->getTheme()))))." "), "  i='" . $__pkJsonEsc . "' c='Theme' class='center'  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['IdTenant'] !== null ) ? $altValue['IdTenant'] : $data->getIdTenant())))." "), "  i='" . $__pkJsonEsc . "' c='IdTenant' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['LocationAddress'] !== null ) ? $altValue['LocationAddress'] : $data->getLocationAddress())))." "), "  i='" . $__pkJsonEsc . "' c='LocationAddress' class=''  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['IsRoot'] !== null ) ? $altValue['IsRoot'] : isntPo($data->getIsRoot()))))." "), "  i='" . $__pkJsonEsc . "' c='IsRoot' class='center'  j='editAuthy'") .
                td(span(htmlspecialchars((string)((($altValue['IdAuthyGroup'] !== null ) ? $altValue['IdAuthyGroup'] : $AuthyGroupRelatedByIdAuthyGroup_Name)))." "), "  i='" . $__pkJsonEsc . "' c='IdAuthyGroup' class=''  j='editAuthy'") .  $actionCell, "  rid='".$__pkJsonEsc."' data-iterator='".$pcData->getPosition()."' r='data' class='va-dt-row ".$hook['class']." ' id='AuthyDtRow".$__pkEsc."'");

                # A10: the letter header reads $this->listCardNameVar, which for an
                # FK-labelled list is a local ($<Rel>_Name) or an $altValue key the
                # row body above assigns — so it is pushed here, after the body ran
                # and before the row itself, keeping header→row order.

                if ($gcGroupOn) {
                    $gcVal = (string) ((($altValue['Username'] !== null ) ? $altValue['Username'] : $data->getUsername()));
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
            $tr .= input('hidden', 'rowCountAuthy', $i);

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
                .div($controlsContent,'AuthyControlsList', "class='custom-controls'")
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
                        .span(_('User'), " class='title' ")
                        .span("".$resultsCount."", " class='count' ")
                        .button("<i class='ri-sort-desc'></i>", " type='button' class='va-mob-sort-btn' aria-haspopup='true' aria-label='"._('Sort')."' ")
                        .(($_SESSION[_AUTH_VAR]->hasRights('Authy', 'a') && !$this->setReadOnly) ? href("<i class='ri-add-line'></i>"._('New'), _SITE_URL.$this->virtualClassName."/edit/", " class='add-btn' ") : '')
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
                        .div("".$gcSortSheetClear . button(_("Username"), " type='button' th='sorted' c='Username' class='va-mob-sortrow' ") . button(_("Fullname"), " type='button' th='sorted' c='Fullname' class='va-mob-sortrow' ") . button(_("Email"), " type='button' th='sorted' c='Email' class='va-mob-sortrow' ") . button(_("Expiration"), " type='button' th='sorted' c='Expire' class='va-mob-sortrow' ") . button(_("Deactivated"), " type='button' th='sorted' c='Deactivate' class='va-mob-sortrow' ") . button(_("Language"), " type='button' th='sorted' c='Language' class='va-mob-sortrow' ") . button(_("Theme"), " type='button' th='sorted' c='Theme' class='va-mob-sortrow' ") . button(_("Tenant"), " type='button' th='sorted' c='IdTenant' class='va-mob-sortrow' ") . button(_("Location Address"), " type='button' th='sorted' c='LocationAddress' class='va-mob-sortrow' ") . button(_("Root"), " type='button' th='sorted' c='IsRoot' class='va-mob-sortrow' ") . button(_("Primary group"), " type='button' th='sorted' c='AuthyGroupRelatedByIdAuthyGroup.Name' class='va-mob-sortrow' "), '', " class='sheet-body va-mob-sortsheet-body' ")
                    ,''," class='va-mob-sortsheet-panel' ")
                ,''," class='va-mob-sortsheet' ")
                .input('hidden', 'rowCount', $i, "s='d'")
                .input('hidden', 'ip', $IdParent, "s='d'")
                 .div(
                     div(
                        div($tr, '', "id='AuthyTable' class='va-mob-list'")
                        .div("<table class='va-dt-table'>".$trHead."<tbody>".$trDt."</tbody></table>", '', " class='dt-card va-dt-only' ")
                     ,'',' class="content" ')
                ,'listForm',' class="ac-list" ')
                .$this->hookListBottom
                .$bottomRow
            , 'AuthyListForm', " class='va-mob proto-app' data-model='Authy' data-table='Authy' data-gc-db='authy' data-ui='".$this->uiTabsId."' ");





        $return['onReadyJs'] =
            $HelpDivJs

            ."







        (function(){var r=document.getElementById('tabsContain');if(r&&window.gcSelectBox){gcSelectBox.bindWithin(r);}})();
        ".$this->hookListReadyJsFirst.$editEvent."
        var __ab=document.getElementById('addAuthyAutoc');
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
    public function setCreateDefaultsAuthy(array $data): Authy
    {

        unset($data['IdAuthy']);
        $e = new Authy();

        if($data['noRights'] != 'y'){

            $data['RightsAll'] = serializeRights($data, 'RightsAll');
            $data['RightsOwner'] = serializeRights($data, 'RightsOwner');
            $data['RightsGroup'] = serializeRights($data, 'RightsGroup');
        }
        if(isset($data['PasswdHash']) && $data['PasswdHash'] !== ''){
            $data['PasswdHash'] = password_hash($data['PasswdHash'], PASSWORD_DEFAULT);
        } else {
            unset($data['PasswdHash']);
        }

        if( $data['Deactivate'] == '' )unset($data['Deactivate']);
        if( $data['Language'] == '' )unset($data['Language']);
        if( $data['Theme'] == '' )unset($data['Theme']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );

        #

        //varchar not required
        $e->setValidationKey( ($data['ValidationKey'] == '' ) ? null : $data['ValidationKey']);
        //varchar not required
        $e->setUsername( ($data['Username'] == '' ) ? null : $data['Username']);
        //varchar not required
        $e->setFullname( ($data['Fullname'] == '' ) ? null : $data['Fullname']);
        $e->setExpire( ($data['Expire'] == '' || $data['Expire'] == 'null' || substr($data['Expire'],0,10) == '-0001-11-30') ? null : $data['Expire'] );
        $e->setDeactivate(($data['Deactivate'] == '' ) ? null : $data['Deactivate']);
        $e->setLanguage(($data['Language'] == '' ) ? null : $data['Language']);
        $e->setTheme(($data['Theme'] == '' ) ? null : $data['Theme']);
        //varchar not required
        $e->setGoogleSub( ($data['GoogleSub'] == '' ) ? null : $data['GoogleSub']);
        //varchar not required
        $e->setGoogleEmail( ($data['GoogleEmail'] == '' ) ? null : $data['GoogleEmail']);
        //varchar not required
        $e->setResetTokenHash( ($data['ResetTokenHash'] == '' ) ? null : $data['ResetTokenHash']);
        //integer not required
        $e->setResetTokenExpires( ($data['ResetTokenExpires'] == '' ) ? null : $data['ResetTokenExpires']);
        //varchar not required
        $e->setLocationAddress( ($data['LocationAddress'] == '' ) ? null : $data['LocationAddress']);
        //decimal not required
        $e->setLocationLat( ($data['LocationLat'] == '' ) ? null : $data['LocationLat']);
        //decimal not required
        $e->setLocationLng( ($data['LocationLng'] == '' ) ? null : $data['LocationLng']);
        //longvarchar not required
        $e->setRightsAll( ($data['RightsAll'] == '' ) ? null : $data['RightsAll']);
        //longvarchar not required
        $e->setRightsGroup( ($data['RightsGroup'] == '' ) ? null : $data['RightsGroup']);
        //longvarchar not required
        $e->setRightsOwner( ($data['RightsOwner'] == '' ) ? null : $data['RightsOwner']);
        //longvarchar not required
        $e->setOnglet( ($data['Onglet'] == '' ) ? null : $data['Onglet']);
        #

        return $e;
    }

    /*
    *	Make sure default value are set before save
    */
    public function setUpdateDefaultsAuthy(array $data): ?Authy
    {


        $e = $_SESSION[_AUTH_VAR]->loadPkScoped(AuthyQuery::class, json_decode($data['i']), 'Authy', 'w');
        if ($e === null) { return null; }

        if($data['noRights'] != 'y'){

            $data['RightsAll'] = serializeRights($data, 'RightsAll');
            $data['RightsOwner'] = serializeRights($data, 'RightsOwner');
            $data['RightsGroup'] = serializeRights($data, 'RightsGroup');
        }
        if(isset($data['PasswdHash']) && $data['PasswdHash'] !== ''){
            $data['PasswdHash'] = password_hash($data['PasswdHash'], PASSWORD_DEFAULT);
        } else {
            unset($data['PasswdHash']);
        }

        if( $data['Deactivate'] == '' )unset($data['Deactivate']);
        if( $data['Language'] == '' )unset($data['Language']);
        if( $data['Theme'] == '' )unset($data['Theme']);
        foreach (['IsSystem','IsRoot','IdCreation','IdModification','IdGroupCreation','DateCreation','DateModification','IdTenant'] as $__gcDeny) { unset($data[$__gcDeny]); }
        $e->fromArray($data );



        if(isset($data['ValidationKey'])){
            $e->setValidationKey( ($data['ValidationKey'] == '' ) ? null : $data['ValidationKey']);
        }
        if(isset($data['Username'])){
            $e->setUsername( ($data['Username'] == '' ) ? null : $data['Username']);
        }
        if(isset($data['Fullname'])){
            $e->setFullname( ($data['Fullname'] == '' ) ? null : $data['Fullname']);
        }
        if(isset($data['Expire'])){
            $e->setExpire( ($data['Expire'] == '' || $data['Expire'] == 'null' || substr($data['Expire'],0,10) == '-0001-11-30') ? null : $data['Expire'] );
        }
        if(isset($data['Deactivate'])){
            $e->setDeactivate(($data['Deactivate'] == '' ) ? null : $data['Deactivate']);
        }
        if(isset($data['Language'])){
            $e->setLanguage(($data['Language'] == '' ) ? null : $data['Language']);
        }
        if(isset($data['Theme'])){
            $e->setTheme(($data['Theme'] == '' ) ? null : $data['Theme']);
        }
        if(isset($data['GoogleSub'])){
            $e->setGoogleSub( ($data['GoogleSub'] == '' ) ? null : $data['GoogleSub']);
        }
        if(isset($data['GoogleEmail'])){
            $e->setGoogleEmail( ($data['GoogleEmail'] == '' ) ? null : $data['GoogleEmail']);
        }
        if(isset($data['ResetTokenHash'])){
            $e->setResetTokenHash( ($data['ResetTokenHash'] == '' ) ? null : $data['ResetTokenHash']);
        }
        if(isset($data['ResetTokenExpires'])){
            $e->setResetTokenExpires( ($data['ResetTokenExpires'] == '' ) ? null : $data['ResetTokenExpires']);
        }
        if(isset($data['LocationAddress'])){
            $e->setLocationAddress( ($data['LocationAddress'] == '' ) ? null : $data['LocationAddress']);
        }
        if(isset($data['LocationLat'])){
            $e->setLocationLat( ($data['LocationLat'] == '' ) ? null : $data['LocationLat']);
        }
        if(isset($data['LocationLng'])){
            $e->setLocationLng( ($data['LocationLng'] == '' ) ? null : $data['LocationLng']);
        }
        if(isset($data['RightsAll'])){
            $e->setRightsAll( ($data['RightsAll'] == '' ) ? null : $data['RightsAll']);
        }
        if(isset($data['RightsGroup'])){
            $e->setRightsGroup( ($data['RightsGroup'] == '' ) ? null : $data['RightsGroup']);
        }
        if(isset($data['RightsOwner'])){
            $e->setRightsOwner( ($data['RightsOwner'] == '' ) ? null : $data['RightsOwner']);
        }
        if(isset($data['Onglet'])){
            $e->setOnglet( ($data['Onglet'] == '' ) ? null : $data['Onglet']);
        }
        $e->setNew(false);
        return $e;
    }
    /**
     * Produce a formated form of Authy
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

        $je = "AuthyTable";

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

        if($_SESSION[_AUTH_VAR]->hasRights('Authy', 'a') && !$this->setReadOnly) {
            $this->formAddButton = htmlLink(_("Add new"), 'Javascript:;' , "id='addAuthyForm' title='"._('Add')."' class='button-link-blue add-button'");
            $this->bindEditJs = "";
                if ($this->formAddButton) { $this->formAddButton = str_replace("add-button'", "add-button' data-gc-add='".$this->virtualClassName."' data-gc-ip='".htmlspecialchars((string) ($IdParent ?: ''), ENT_QUOTES)."'", $this->formAddButton); }
        }

        if($id && empty($data['reload'])) {


            $q = AuthyQuery::create()

                #alias required
                ->leftJoinWith('AuthyGroupRelatedByIdAuthyGroup a10')
            ;




            $gcEditPk = $id;
            if (!$_SESSION[_AUTH_VAR]->isRoot()) {
                if ($_SESSION[_AUTH_VAR]->get('id_tenant') && method_exists($q, 'filterByIdTenant')) {
                    $q->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }
                $_SESSION[_AUTH_VAR]->applyOwnerGroupScope($q, $_SESSION[_AUTH_VAR]->hasRights('Authy', 'r'));
            }
            $dataObj = $q->filterByPrimaryKey($gcEditPk)->findOne();


        }

        if($dataObj == null){
            $this->Authy['isNew'] = 'yes';
            $dataObj = new Authy();
        }



        if($dataObj->isNew()){
            if(is_array($data ))
               $dataObj->fromArray(array_filter($data));

        }else{
                $this->Authy['isNew'] = 'no';
        }
        $this->dataObj = $dataObj;




                                    ($dataObj->getAuthyGroupRelatedByIdAuthyGroup())?'':$dataObj->setAuthyGroupRelatedByIdAuthyGroup( new AuthyGroup() );


        if($this->setReadOnly !== 'all'){ $this->arrayIdAuthyGroupOptions = $this->selectBoxAuthy_IdAuthyGroup($this, $dataObj, $data); } else { $this->arrayIdAuthyGroupOptions = []; }


        
    ## Rights for RightsOwner
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




$this->fields['Authy']['Username']['html'] = stdFieldRow(_("Username"), input('text', 'Username', htmlentities((string)($dataObj->getUsername() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Username'))."' size='35'  v='USERNAME' s='d' class=''  ")."", 'Username', "", $this->commentsUsername, $this->commentsUsername_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['Fullname']['html'] = stdFieldRow(_("Fullname"), input('text', 'Fullname', htmlentities((string)($dataObj->getFullname() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Fullname'))."' size='35'  v='FULLNAME' s='d' class=''  ")."", 'Fullname', "", $this->commentsFullname, $this->commentsFullname_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['Email']['html'] = stdFieldRow(_("Email"), input('text', 'Email', htmlentities((string)($dataObj->getEmail() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Email'))."' size='35'  v='EMAIL' s='d' class='req'  ")."", 'Email', "", $this->commentsEmail, $this->commentsEmail_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['PasswdHash']['html'] = stdFieldRow(_("Password"), input('password', 'PasswdHash', '', "   placeholder='".str_replace("'","&#39;",_('Password'))."' size='69'  v='PASSWD_HASH' s='d' class='".($dataObj->isNew()?'req':'')."'  ")."", 'PasswdHash', "", $this->commentsPasswdHash, $this->commentsPasswdHash_css, '', ' ', 'no', 'v2');
$this->fields['Authy']['Expire']['html'] = stdFieldRow(_("Expiration"), input('date', 'Expire', $dataObj->getExpire(), "  j='date' autocomplete='off' placeholder='YYYY-MM-DD' size='10'  s='d' class='' title='Expiration'"), 'Expire', "", $this->commentsExpire, $this->commentsExpire_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['Deactivate']['html'] = stdFieldRow(_("Deactivated"), selectboxCustomArray('Deactivate', [ '0' => ['0'=>_("Yes"), '1'=>"Yes"],'1' => ['0'=>_("No"), '1'=>"No"], ], _('Deactivated'), "s='d'  ", $dataObj->getDeactivate(), '', true), 'Deactivate', "", $this->commentsDeactivate, $this->commentsDeactivate_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['Language']['html'] = stdFieldRow(_("Language"), selectboxCustomArray('Language', [ '0' => ['0'=>_("en_US"), '1'=>"en_US"],'1' => ['0'=>_("fr_CA"), '1'=>"fr_CA"], ], _('Language'), "s='d'  ", $dataObj->getLanguage(), '', true), 'Language', "", $this->commentsLanguage, $this->commentsLanguage_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['Theme']['html'] = stdFieldRow(_("Theme"), selectboxCustomArray('Theme', [ '0' => ['0'=>_("mint"), '1'=>"mint"],'1' => ['0'=>_("ink"), '1'=>"ink"],'2' => ['0'=>_("indigo"), '1'=>"indigo"],'3' => ['0'=>_("terracotta"), '1'=>"terracotta"],'4' => ['0'=>_("graphite"), '1'=>"graphite"],'5' => ['0'=>_("slate"), '1'=>"slate"],'6' => ['0'=>_("dusk"), '1'=>"dusk"], ], _('Theme'), "s='d'  ", $dataObj->getTheme(), '', true), 'Theme', "", $this->commentsTheme, $this->commentsTheme_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['GoogleSub']['html'] = stdFieldRow(_("Google sub"), input('text', 'GoogleSub', htmlentities((string)($dataObj->getGoogleSub() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Google sub'))."' size='35'  v='GOOGLE_SUB' s='d' class=''  ")."", 'GoogleSub', "", $this->commentsGoogleSub, $this->commentsGoogleSub_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['GoogleEmail']['html'] = stdFieldRow(_("Google email"), input('text', 'GoogleEmail', htmlentities((string)($dataObj->getGoogleEmail() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Google email'))."' size='69'  v='GOOGLE_EMAIL' s='d' class=''  ")."", 'GoogleEmail', "", $this->commentsGoogleEmail, $this->commentsGoogleEmail_css, '', ' ', 'no', 'v2');
$this->fields['Authy']['ResetTokenHash']['html'] = stdFieldRow(_("Reset token"), input('text', 'ResetTokenHash', htmlentities((string)($dataObj->getResetTokenHash() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Reset token'))."' size='69'  v='RESET_TOKEN_HASH' s='d' class=''  ")."", 'ResetTokenHash', "", $this->commentsResetTokenHash, $this->commentsResetTokenHash_css, '', ' ', 'no', 'v2');
$this->fields['Authy']['ResetTokenExpires']['html'] = stdFieldRow(_("Reset expires"), input('number', 'ResetTokenExpires', $dataObj->getResetTokenExpires(), " step='1' placeholder='".str_replace("'","&#39;",_('Reset expires'))."' v='RESET_TOKEN_EXPIRES' size='5' s='d' class=''"), 'ResetTokenExpires', "", $this->commentsResetTokenExpires, $this->commentsResetTokenExpires_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['IdTenant']['html'] = stdFieldRow(_("Tenant"), input('number', 'IdTenant', $dataObj->getIdTenant(), " step='1' placeholder='".str_replace("'","&#39;",_('Tenant'))."' v='ID_TENANT' size='5' s='d' class=''"), 'IdTenant', "", $this->commentsIdTenant, $this->commentsIdTenant_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['LocationAddress']['html'] = stdFieldRow(_("Location Address"), input('text', 'LocationAddress', htmlentities((string)($dataObj->getLocationAddress() ?? '')), "   placeholder='".str_replace("'","&#39;",_('Location Address'))."' size='69'  v='LOCATION_ADDRESS' s='d' class=''  ")."", 'LocationAddress', "", $this->commentsLocationAddress, $this->commentsLocationAddress_css, '', ' ', 'no', 'v2');
$this->fields['Authy']['IsRoot']['html'] = stdFieldRow(_("Root"), ($_SESSION[_AUTH_VAR]->isAdmin())?selectboxCustomArray('IsRoot', [ '0' => ['0'=>_("Yes"), '1'=>"Yes"],'1' => ['0'=>_("No"), '1'=>"No"], ], "", "s='d'  ", $dataObj->getIsRoot(), '', false):'', 'IsRoot', "", $this->commentsIsRoot, $this->commentsIsRoot_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['IdAuthyGroup']['html'] = stdFieldRow(_("Primary group"), selectboxCustomArray('IdAuthyGroup', $this->arrayIdAuthyGroupOptions, "", "v='ID_AUTHY_GROUP'  s='d'  val='".$dataObj->getIdAuthyGroup()."'", $dataObj->getIdAuthyGroup()), 'IdAuthyGroup', "", $this->commentsIdAuthyGroup, $this->commentsIdAuthyGroup_css, ' half', ' ', 'no', 'v2');
$this->fields['Authy']['RightsAll']['html'] = stdFieldRow(_("Rights"), $rightInputRightsAll, 'RightsAll', "", $this->commentsRightsAll, $this->commentsRightsAll_css, ' rightsTr half', ' ', 'no', 'v2');
$this->fields['Authy']['RightsGroup']['html'] = stdFieldRow(_("Rights (group records)"), '', 'RightsGroup', "", $this->commentsRightsGroup, $this->commentsRightsGroup_css, ' rightsTr hide half', ' ', 'no', 'v2');
$this->fields['Authy']['RightsOwner']['html'] = stdFieldRow(_("Rights (own records)"), '', 'RightsOwner', "", $this->commentsRightsOwner, $this->commentsRightsOwner_css, ' rightsTr hide half', ' ', 'no', 'v2');


        $this->lockFormField(array(0=>'IdCreation',1=>'IdModification',2=>'IdGroupCreation',), $dataObj);

        // Whole form read only
        if($this->setReadOnly == 'all' ) {
            $this->lockFormField('all', $dataObj);
        }



        $ChildOnglet = '';
        if( !isset($this->Authy['request']['ChildHide']) ) {

            # define child lists 'Group'
            $ongletTab['0']['t'] = _('Group');
            $ongletTab['0']['p'] = 'AuthyGroupX';
            $ongletTab['0']['lkey'] = 'IdAuthy';
            $ongletTab['0']['fkey'] = 'IdAuthy';
            $ongletTab['0']['icon'] = 'ri-folder-line';
            # define child lists 'Login log'
            $ongletTab['1']['t'] = _('Login log');
            $ongletTab['1']['p'] = 'AuthyLog';
            $ongletTab['1']['lkey'] = 'IdAuthy';
            $ongletTab['1']['fkey'] = 'IdAuthy';
            $ongletTab['1']['icon'] = 'ri-calendar-event-line';
        if(!empty($ongletTab) and $dataObj->getIdAuthy()){
            foreach($ongletTab as $value){
                if($_SESSION[_AUTH_VAR]->hasRights($value['p'], 'r')){
                    $getLocalKey = "get".$value['lkey']."";
                    if($dataObj->$getLocalKey()){
                        // Per-parent filtered count for the tab badge.
                        // Falls back to '' if the child Query class or the
                        // filterBy{FK} method can't be located. The runtime
                        // ChildCountCache (APCu MicroCache, generation-bumped
                        // on child writes) owns the cached lookup; the elseif
                        // arm is the pre-cache behavior for older runtime pins.
                        $gcChildCountHtml = '';
                        $gcChildCount = null;
                        if (class_exists('\\ApiGoat\\Utility\\ChildCountCache')) {
                            $gcChildCount = \ApiGoat\Utility\ChildCountCache::count($value['p'], $value['fkey'], $dataObj->$getLocalKey());
                        } elseif (class_exists($gcChildQueryClass = '\\App\\'.$value['p'].'Query')
                            && method_exists($gcChildQueryClass, $gcChildFilter = 'filterBy'.$value['fkey'])) {
                            $gcChildCount = $gcChildQueryClass::create()->$gcChildFilter($dataObj->$getLocalKey())->count();
                        }
                        if ($gcChildCount !== null) {
                            $gcChildCountHtml = span($gcChildCount, ' class="child-tab-count" ');
                        }
                        // Icon resolved at build time from the child description
                        // (see the emitter-side map above); the fallback covers
                        // hand-written $ongletTab entries without an 'icon' key.
                        $gcChildIcon = !empty($value['icon']) ? $value['icon'] : 'ri-folder-line';
                        $ChildOnglet .= button(
                                        "<i class='".$gcChildIcon." child-tab-icon'></i>" . _($value['t']) . $gcChildCountHtml
                                    , ' type="button" class="child-tab" role="tab" j="conglet_Authy" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" ')
                                    . htmlLink(
                                        "<i class='ri-add-line'></i>"
                                    , 'Javascript:;', ' class="child-tab-add header-controls" j="childadd_Authy" p="'.$value['p'].'" ip="'.$dataObj->$getLocalKey().'" data-title="'.htmlspecialchars(_($value['t']), ENT_QUOTES).'" title="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" aria-label="'.htmlspecialchars(_('Add'), ENT_QUOTES).'" style="display:inline-flex;align-items:center;padding:0 7px;margin-right:4px;" ');
                    }
                }
            }

            if($ChildOnglet){
                // #23 S5: the legacy inline conglet click-handler is RETIRED.
                // screens.js's capture-phase document onClick (closest
                // [j^=conglet_][p] -> openChildList -> push the child as a screen +
                // run bindWithin) and drawer.js own child-tab loading + active
                // state. The capture-phase stopPropagation suppresses any
                // still-emitted inline handler on un-regenerated projects too, so
                // dropping this per-table onReadyJs causes no double-load and needs
                // no capability marker — the child tab markers (j/p/ip) are
                // unchanged and the client handles them generically.
                $childTable['onReadyJs'] = "";
            }
        }
        }


        $this->formSaveBtn = '';
        if(!$this->setReadOnly){
            // #23 S5: render the Save button only when the user actually has save
            // rights (create on a new record / write on an edit). Replaces the
            // inline SaveButtonJs that rendered it then JS-removed it for read-only
            // users — off the screens.js push() re-exec arm, and no button flash.
            if( ($_SESSION[_AUTH_VAR]->hasRights('Authy','a') && !$id) || ($_SESSION[_AUTH_VAR]->hasRights('Authy','w') && $id) ){
                $this->formSaveBtn = input('button', 'saveAuthy', _('Save'),' class="nav-save can-save"');
            }
            $this->formSaveBar = div( input('hidden', 'formChangedAuthy','', 'j="formChanged"')
                            .input('hidden', 'idPk', urlencode($id), "s='d'")
                        .input('hidden', 'IdAuthy', $dataObj->getIdAuthy(), " s='d' pk").input('hidden', 'ValidationKey', htmlentities((string)($dataObj->getValidationKey() ?? '')), "   placeholder='".str_replace("'","&#39;",_(''))."' size='35'  v='VALIDATION_KEY' s='d' class=''  ")."".input('hidden', 'LocationLat', $dataObj->getLocationLat(), " v='LOCATION_LAT' s='d'").input('hidden', 'LocationLng', $dataObj->getLocationLng(), " v='LOCATION_LNG' s='d'").input('hidden', 'IdGroupCreation', $dataObj->getIdGroupCreation(), " s='d' nodesc").input('hidden', 'IdCreation', $dataObj->getIdCreation(), " s='d' nodesc").input('hidden', 'IdModification', $dataObj->getIdModification(), " s='d' nodesc")
                            .$this->hookListSearchButton
                        ,""," class='form-savehidden' ");
        }
        // add_hooks: afterFormObj (always emitted — the stub lives in the FormWrapper)
        if (method_exists($this, 'afterFormObj')) { $this->afterFormObj($data, $dataObj); }
        // Custom drawer tabs registered by the wrapper (FormHelper::addFormTab, e.g. from afterFormObj).
        [$gcTabNavFirst, $gcTabNavLast, $gcPanesFirst, $gcPanesLast, $gcFirstTabActive] = $this->renderFormCustomTabs();
        $ongletf =
            div(
                div($gcTabNavFirst . button(_('User'), ' type="button" class="tab-btn' . ($gcFirstTabActive ? ' is-active' : '') . '" role="tab" data-tab="tab_Authy" aria-selected="' . ($gcFirstTabActive ? 'true' : 'false') . '" aria-controls="tab_Authy" ')
                    .button(_('Rights'), ' type="button" class="tab-btn" role="tab" data-tab="tab_rights_all" aria-selected="false" aria-controls="tab_rights_all" ') . $gcTabNavLast,'',"class='sw-tabnav' role='tablist'")
            ,'cntOngletAuthy',' class="cntOnglet"')
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
        //stage child-tabs + child-pannel for the drawer scaffold
        $childTabsHtml = '';
        $childPannelHtml = '';
        if($dataObj->getIdAuthy()) {
            if($ChildOnglet) {
                $childTabsHtml = div($ChildOnglet, '', " class='child-tabs' role='tablist' ");
                $childPannelHtml = div('', 'cntAuthyChild', ' class="child-pannel" ');
            }
        }

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
                        href('<i class="ri-arrow-left-s-line"></i>'._('User'), _SITE_URL.'Authy', "class='nav-btn'")
                        .div(
                            span(_('User'), "class='nav-title-type'")
                        , '', "class='nav-title'")
                        .$this->formSaveBtn
                        .href('<i class="ri-close-line"></i>', _SITE_URL.'Authy', "class='nav-btn nav-close' title='"._('Close')."' aria-label='"._('Close')."'")
                    ,'',"class='form-nav'")
                    .$childTabsHtml
                ,'',"class='sw-header'")
                .$identityHtml
                .$header_top_onglet
                .div(

                    $this->hookFormInnerTop

                    .div('' . $gcPanesFirst .
                    '<div id="tab_Authy" class="tab-pane' . (($gcFirstTabActive ?? true) ? ' is-active' : '') . '" role="tabpanel" data-tab="tab_Authy"' . (($gcFirstTabActive ?? true) ? '' : ' hidden') . '><div class="sw-grid">'.
$this->fields['Authy']['Username']['html']
.$this->fields['Authy']['Fullname']['html']
.$this->fields['Authy']['Email']['html']
.$this->fields['Authy']['PasswdHash']['html']
.$this->fields['Authy']['Expire']['html']
.$this->fields['Authy']['Deactivate']['html']
.$this->fields['Authy']['Language']['html']
.$this->fields['Authy']['Theme']['html']
.$this->fields['Authy']['GoogleSub']['html']
.$this->fields['Authy']['GoogleEmail']['html']
.$this->fields['Authy']['ResetTokenHash']['html']
.$this->fields['Authy']['ResetTokenExpires']['html']
.$this->fields['Authy']['IdTenant']['html']
.$this->fields['Authy']['LocationAddress']['html']
.$this->fields['Authy']['IsRoot']['html']
.$this->fields['Authy']['IdAuthyGroup']['html']
.'</div></div><div id="tab_rights_all" class="tab-pane" role="tabpanel" data-tab="tab_rights_all" hidden><div class="sw-grid">'
.$this->fields['Authy']['RightsAll']['html']
.$this->fields['Authy']['RightsGroup']['html']
.$this->fields['Authy']['RightsOwner']['html'].'</div></div>' . $gcPanesLast ,'',"class='form-card'")

                    .$this->formSaveBar
                    .$this->hookFormInnerBottom
                    .$childPannelHtml
                ,"divCntAuthy", "class='sw-body form-scroll divStdform' CntTabs=1 ".$this->ccStdFormOptions)
                .$_gcFooterHtml
            ,'',"class='sw-drawer is-open proto-form'")
        , "id='formAuthy' class='mainForm formContent' ")
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

    if (window.gcLocationField) { gcLocationField.create({\"input\":\"LocationAddress\",\"lat\":\"LocationLat\",\"lng\":\"LocationLng\",\"zip\":null,\"country\":\"ca\"}); }
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
            $fields = array_keys($this->fields['Authy']);
        } elseif(!is_array($fields)) {
            return;
        }
        foreach($fields as $field) {
            if(!isset($this->gcFieldRoBuilt[$field])) {
                $this->gcFieldRoBuilt[$field] = true;
                $this->gcBuildFieldRo($field, $dataObj);
            }
            $this->fields['Authy'][$field]['html'] = $this->fieldsRo['Authy'][$field]['html'] ?? '';
        }
    }

    /** Build ONE column's read-only markup into $this->fieldsRo (A43). */
    private function gcBuildFieldRo($field, $dataObj)
    {
        switch($field) {
            case 'Username':
        $this->fieldsRo['Authy']['Username']['html'] = stdFieldRow(_("Username"), div( htmlspecialchars((string)($dataObj->getUsername()), ENT_QUOTES), 'Username_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Username', $dataObj->getUsername(), "s='d'"), 'Username', "", $this->commentsUsername, $this->commentsUsername_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Fullname':
        $this->fieldsRo['Authy']['Fullname']['html'] = stdFieldRow(_("Fullname"), div( htmlspecialchars((string)($dataObj->getFullname()), ENT_QUOTES), 'Fullname_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Fullname', $dataObj->getFullname(), "s='d'"), 'Fullname', "", $this->commentsFullname, $this->commentsFullname_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Email':
        $this->fieldsRo['Authy']['Email']['html'] = stdFieldRow(_("Email"), div( htmlspecialchars((string)($dataObj->getEmail()), ENT_QUOTES), 'Email_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Email', $dataObj->getEmail(), "s='d'"), 'Email', "", $this->commentsEmail, $this->commentsEmail_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'PasswdHash':
        $this->fieldsRo['Authy']['PasswdHash']['html'] = stdFieldRow(_("Password"), div( htmlspecialchars((string)($dataObj->getPasswdHash()), ENT_QUOTES), 'PasswdHash_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'PasswdHash', $dataObj->getPasswdHash(), "s='d'"), 'PasswdHash', "", $this->commentsPasswdHash, $this->commentsPasswdHash_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'Expire':
        $this->fieldsRo['Authy']['Expire']['html'] = stdFieldRow(_("Expiration"), div( htmlspecialchars((string)($dataObj->getExpire()), ENT_QUOTES), 'Expire_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Expire', $dataObj->getExpire(), "s='d'"), 'Expire', "", $this->commentsExpire, $this->commentsExpire_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Deactivate':
        $this->fieldsRo['Authy']['Deactivate']['html'] = stdFieldRow(_("Deactivated"), div( htmlspecialchars((string)($dataObj->getDeactivate()), ENT_QUOTES), 'Deactivate_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Deactivate', $dataObj->getDeactivate(), "s='d'"), 'Deactivate', "", $this->commentsDeactivate, $this->commentsDeactivate_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Language':
        $this->fieldsRo['Authy']['Language']['html'] = stdFieldRow(_("Language"), div( htmlspecialchars((string)($dataObj->getLanguage()), ENT_QUOTES), 'Language_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Language', $dataObj->getLanguage(), "s='d'"), 'Language', "", $this->commentsLanguage, $this->commentsLanguage_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'Theme':
        $this->fieldsRo['Authy']['Theme']['html'] = stdFieldRow(_("Theme"), div( htmlspecialchars((string)($dataObj->getTheme()), ENT_QUOTES), 'Theme_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'Theme', $dataObj->getTheme(), "s='d'"), 'Theme', "", $this->commentsTheme, $this->commentsTheme_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'GoogleSub':
        $this->fieldsRo['Authy']['GoogleSub']['html'] = stdFieldRow(_("Google sub"), div( htmlspecialchars((string)($dataObj->getGoogleSub()), ENT_QUOTES), 'GoogleSub_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'GoogleSub', $dataObj->getGoogleSub(), "s='d'"), 'GoogleSub', "", $this->commentsGoogleSub, $this->commentsGoogleSub_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'GoogleEmail':
        $this->fieldsRo['Authy']['GoogleEmail']['html'] = stdFieldRow(_("Google email"), div( htmlspecialchars((string)($dataObj->getGoogleEmail()), ENT_QUOTES), 'GoogleEmail_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'GoogleEmail', $dataObj->getGoogleEmail(), "s='d'"), 'GoogleEmail', "", $this->commentsGoogleEmail, $this->commentsGoogleEmail_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'ResetTokenHash':
        $this->fieldsRo['Authy']['ResetTokenHash']['html'] = stdFieldRow(_("Reset token"), div( htmlspecialchars((string)($dataObj->getResetTokenHash()), ENT_QUOTES), 'ResetTokenHash_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ResetTokenHash', $dataObj->getResetTokenHash(), "s='d'"), 'ResetTokenHash', "", $this->commentsResetTokenHash, $this->commentsResetTokenHash_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'ResetTokenExpires':
        $this->fieldsRo['Authy']['ResetTokenExpires']['html'] = stdFieldRow(_("Reset expires"), div( htmlspecialchars((string)($dataObj->getResetTokenExpires()), ENT_QUOTES), 'ResetTokenExpires_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'ResetTokenExpires', $dataObj->getResetTokenExpires(), "s='d'"), 'ResetTokenExpires', "", $this->commentsResetTokenExpires, $this->commentsResetTokenExpires_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IdTenant':
        $this->fieldsRo['Authy']['IdTenant']['html'] = stdFieldRow(_("Tenant"), div( htmlspecialchars((string)($dataObj->getIdTenant()), ENT_QUOTES), 'IdTenant_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdTenant', $dataObj->getIdTenant(), "s='d'"), 'IdTenant', "", $this->commentsIdTenant, $this->commentsIdTenant_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'LocationAddress':
        $this->fieldsRo['Authy']['LocationAddress']['html'] = stdFieldRow(_("Location Address"), div( htmlspecialchars((string)($dataObj->getLocationAddress()), ENT_QUOTES), 'LocationAddress_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'LocationAddress', $dataObj->getLocationAddress(), "s='d'"), 'LocationAddress', "", $this->commentsLocationAddress, $this->commentsLocationAddress_css, 'readonly', ' ', 'no', 'v2');

            break;
            case 'IsRoot':
        $this->fieldsRo['Authy']['IsRoot']['html'] = stdFieldRow(_("Root"), div( htmlspecialchars((string)($dataObj->getIsRoot()), ENT_QUOTES), 'IsRoot_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IsRoot', $dataObj->getIsRoot(), "s='d'"), 'IsRoot', "", $this->commentsIsRoot, $this->commentsIsRoot_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'IdAuthyGroup':
        $this->fieldsRo['Authy']['IdAuthyGroup']['html'] = stdFieldRow(_("Primary group"), div( htmlspecialchars((string)(($dataObj->getAuthyGroupRelatedByIdAuthyGroup())?$dataObj->getAuthyGroupRelatedByIdAuthyGroup()->getName():''), ENT_QUOTES), 'IdAuthyGroup_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'IdAuthyGroup', $dataObj->getIdAuthyGroup(), "s='d'"), 'IdAuthyGroup', "", $this->commentsIdAuthyGroup, $this->commentsIdAuthyGroup_css, 'readonly half', ' ', 'no', 'v2');

            break;
            case 'RightsAll':
        $this->fieldsRo['Authy']['RightsAll']['html'] = stdFieldRow(_("Rights"), div( htmlspecialchars((string)($dataObj->getRightsAll()), ENT_QUOTES), 'RightsAll_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsAll', $dataObj->getRightsAll(), "s='d'"), 'RightsAll', "", $this->commentsRightsAll, $this->commentsRightsAll_css, 'readonly rightsTr half', ' ', 'no', 'v2');

            break;
            case 'RightsGroup':
        $this->fieldsRo['Authy']['RightsGroup']['html'] = stdFieldRow(_("Rights (group records)"), div( htmlspecialchars((string)($dataObj->getRightsGroup()), ENT_QUOTES), 'RightsGroup_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsGroup', $dataObj->getRightsGroup(), "s='d'"), 'RightsGroup', "", $this->commentsRightsGroup, $this->commentsRightsGroup_css, 'readonly rightsTr hide half', ' ', 'no', 'v2');

            break;
            case 'RightsOwner':
        $this->fieldsRo['Authy']['RightsOwner']['html'] = stdFieldRow(_("Rights (own records)"), div( htmlspecialchars((string)($dataObj->getRightsOwner()), ENT_QUOTES), 'RightsOwner_label' , "class='readonly ro-value' s='d'")
                .input('hidden', 'RightsOwner', $dataObj->getRightsOwner(), "s='d'"), 'RightsOwner', "", $this->commentsRightsOwner, $this->commentsRightsOwner_css, 'readonly rightsTr hide half', ' ', 'no', 'v2');

            break;
        }
    }

    /**
     * Query for Authy_IdAuthyGroup selectBox 
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxAuthy_IdAuthyGroup(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true){
 $override=false;
 $gcSbHost = is_object($obj) ? $obj : $this;
 $gcSbUseCache = $array
        && class_exists('\\ApiGoat\\Utility\\SelectBoxCache')
        && method_exists('\\ApiGoat\\Utility\\SelectBoxCache', 'scopeToken')
        && !method_exists($gcSbHost, 'beginSelectboxAuthy_IdAuthyGroup')
        && !method_exists($gcSbHost, 'selectboxDataAuthy_IdAuthyGroup');
    if ($gcSbUseCache) {
        $gcSbHit = \ApiGoat\Utility\SelectBoxCache::fetch('authy_group', 'Authy_IdAuthyGroup', false, \ApiGoat\Utility\SelectBoxCache::scopeToken('AuthyGroup'));
        if ($gcSbHit !== null) {
            return $gcSbHit;
        }
    }
        $q = AuthyGroupQuery::create();

    $gcSbSess = $_SESSION[_AUTH_VAR] ?? null;
    if (is_object($gcSbSess) && method_exists($gcSbSess, 'applyOwnerGroupScope')) {
        $gcSbSess->applyOwnerGroupScope($q, $gcSbSess->hasRights('AuthyGroup', 'r'));
    }

    $gcSbHost = is_object($obj) ? $obj : $this;
    $ret = null;
    if(method_exists($gcSbHost, 'beginSelectboxAuthy_IdAuthyGroup') and $array)
        $ret = $gcSbHost->beginSelectboxAuthy_IdAuthyGroup($q, $dataObj, $data, $obj);
    if($ret !== false) {
            $q->select(['Name', 'IdAuthyGroup']);
            $q->orderBy('Name', 'ASC');

    }
        
            if(!$array){
                return $q;
            }else{
                $pcDataO = $q->find();
            }

            $gcSbHost = is_object($obj) ? $obj : $this;
            if(method_exists($gcSbHost, 'selectboxDataAuthy_IdAuthyGroup')){
                $gcSbHost->selectboxDataAuthy_IdAuthyGroup($pcDataO, $q, $override);
            }



        if($override === false){
            $arrayOpt = $pcDataO->toArray();

            $gcSbResult = assocToNum($arrayOpt );
            if (!empty($gcSbUseCache)) {
                \ApiGoat\Utility\SelectBoxCache::store('authy_group', 'Authy_IdAuthyGroup', false, $gcSbResult, \ApiGoat\Utility\SelectBoxCache::scopeToken('AuthyGroup'));
            }
            return $gcSbResult;
        }else{
            return $override;
        }
}
    /**
     * Query for AuthyGroupX_IdAuthyGroup selectBox
     * Thin parent wrapper → AuthyGroupXForm::selectBoxAuthyGroupX_IdAuthyGroup (IMPROVEMENTS-runtime #10).
     * Passes $this as $obj so beginSelectbox* / selectboxData* hooks on the
     * parent FormWrapper still run inside the canonical body.
     *
     * @param object $obj
     * @param object $dataObj
     * @param array $data
    **/
    public function selectBoxAuthyGroupX_IdAuthyGroup(&$obj = '', &$dataObj = '', &$data = '', $emptyVal = false, $array = true)
    {
        $host = is_object($obj) ? $obj : $this;
        $cls = class_exists('\\App\\AuthyGroupXFormWrapper')
            ? '\\App\\AuthyGroupXFormWrapper'
            : '\\App\\AuthyGroupXForm';
        $selectBox = new $cls($this->request, $this->args);
        return $selectBox->selectBoxAuthyGroupX_IdAuthyGroup($host, $dataObj, $data, $emptyVal, $array);
    }

    /**
     * function getAuthyGroupXList
     * @param string $IdAuthy
     * @param integer $page
     * @param string $uiTabsId
     * @param string $parentContainer
     * @param string $mja_list
     * @param array $search
     * @param array $params
     * @return string
     */
    public function getAuthyGroupXList(String $IdAuthy, array $request)
    {

        $this->TableName = 'AuthyGroupX';
        $altValue = array (
  'IdAuthy' => NULL,
  'IdAuthyGroup' => NULL,
  'DateCreation' => NULL,
  'DateModification' => NULL,
  'IdGroupCreation' => NULL,
  'IdCreation' => NULL,
  'IdModification' => NULL,
);
        $dataObj = null;
        $search = ['order' => null, 'page' => null, ];
        $uiTabsId = (empty($request['cui'])) ? 'cntAuthyChild' : $request['cui'];
        $parentContainer = $request['pc'];
        $gcChildSortAttr = '';  // #23 S5: child sort marker as data-gc-sort (was orderReadyJs inline)
        $param = [];
        $total_child = '';

        // if Search params
        $this->searchMs = $this->setSearchVar($request['ms'] ?? '', 'Authy/AuthyGroupX');

        // order
        $search['order'] = $this->setOrderVar($request['order'] ?? '', 'Authy/AuthyGroupX');

        // Clear-sort chip — rendered only while the session carries a user
        // ordering for this child list; c='*' routes through the list.js
        // sort handler and drops the whole stored ordering server-side.
        $gcSortClear = '';
        if (!empty($_SESSION['mem']['order']['Authy/AuthyGroupX'])) {
            $gcSortClear = div(button("<i class='ri-sort-desc'></i>"._('Sorted')."<span class='cl-active-filter-x' aria-hidden='true'>×</span>", " type='button' th='sorted' c='*' class='cl-active-filter cl-sort-clear' "), '', " class='va-mob-sortclear' ");
        }

        // page
        $search['page'] = $this->setPageVar($request['pg'] ?? '', 'Authy/AuthyGroupX');

        // Track active child table tab
        $_SESSION['mem']['Authy']['child']['list']['active'] = 'AuthyGroupX';




        /*column hide*/

        if($parentContainer == 'editDialog'){
            $diagNoClose = "diag:\"noclose\", ";
            $diagNoCloseEscaped = "diag:\\\"noclose\\\", ";
        }

        if(isset($this->Authy['request']['noHeader']) && $this->Authy['request']['noHeader'] == 'true'){
            $noHeader = "'noHeader':'true',";
        }

        $data['IdAuthy'] = $IdAuthy;
        if($dataObj == null){
            $dataObj = AuthyQuery::create()
            ->filterByIdAuthy($IdAuthy)
            ->findOneOrCReate();
        }

        $this->AuthyGroupX['list_add'] = "";
        $this->AuthyGroupX['list_delete'] = "";

        if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'r')){
            $this->AuthyGroupX['list_edit'] = "";
        }

        #filters validation

        $filterKey = $IdAuthy;
        $this->IdPk = $IdAuthy;


        #main query

        // many to many relation
        $maxPerPage = ((int) ($request['maxperpage'] ?? 0) > 0) ? (int) $request['maxperpage'] : $this->childMaxPerPage;
        $q = AuthyGroupXQuery::create();


        $q

                #alias NtN
                ->joinWith('AuthyGroupRelatedByIdAuthyGroup a100', \Criteria::RIGHT_JOIN)

            // no search
            ->addJoinCondition('a100', '(authy_group_x.id_authy IS NULL OR authy_group_x.id_authy = ?)', $IdAuthy)
                ;

        ;
        // Search



        // ordering

        if( is_array( $search['order'] ) ) {
            foreach ($search['order'] as $order) {
                foreach ($order as $col => $sens) {
                    if( $sens ) {
                        $tOrd = explode('.', $col);
                        $orderBy = "use" . $tOrd[0] . "Query";
                        if( $tOrd[1] && method_exists( $q, $orderBy )) {
                            $q->$orderBy( '', \Criteria::LEFT_JOIN )->orderBy( $tOrd[1], $sens )->endUse();
                        }elseif( method_exists( $q, 'filterBy' . $col )) {
                            $q->orderBy( $col, $sens );
                        }

                        // #23 S5: capture first sort col:dir; emitted as data-gc-sort on the
                        // child container, seeded by list.js enhance() (off the re-exec arm).
                        if ($gcChildSortAttr === '') { $gcChildSortAttr = $col.':'.$sens; }
                    }
                }
            }
        }

        $this->queryObjAuthyGroupX = $q;


        $pmpoData = $q->paginate( $search['page'], $maxPerPage );
        $resultsCount = $pmpoData->getNbResults();

        #options building (search-sheet FKs only)





        if(isset($this->Authy['request']['noHeader']) && $this->Authy['request']['noHeader'] == 'true'){
            $trSearch = "";
        }

        $actionRowHeader ='';
        if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'd')){
            $actionRowHeader = th('&nbsp;', " r='delrow' class='actionrow' ");
        }

        $header = tr( th(_("Group"), " th='sorted' c='AuthyGroupRelatedByIdAuthyGroup.Name' title='"._('AuthyGroupRelatedByIdAuthyGroup.Name')."' ")
.'' . $actionRowHeader, " ln='AuthyGroupX' class=''");



        $i=0;
        $tr = '';
        $gcChildRows = [];
        if( $pmpoData->isEmpty() ){
            $tr .= tr(	td(p(span(_("No Group found")),'class="no-results"'), "style='font-size:16px;' t='empty' ln='AuthyGroupX' colspan='100%' "));

        }else{
            $gcChildPcData = $pmpoData->getResults();

            foreach($gcChildPcData as $data){
                $this->listActionCellAuthyGroupX = '';

                $actionRow = '';



            $checkboxPk = [intval($IdAuthy), ($data->getAuthyGroupRelatedByIdAuthyGroup()) ? $data->getAuthyGroupRelatedByIdAuthyGroup()->getPrimaryKey():0 ];

                if($_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'd')){
                    $actionRow = "";
                }




                if(($searchNtNSet ?? '')  || $data->getIdAuthy() == intval($IdAuthy)){
                    $actionRow .= input('checkbox','check_'.$i, json_encode($checkboxPk), " checked='checked' i='".htmlspecialchars(json_encode($checkboxPk), ENT_QUOTES)."' class='hand'  j='check_multi_AuthyGroupX' ").label('','for="check_'.$i.'"');
                }else{
                    $actionRow .=
                    input('checkbox','check_'.$i, json_encode($checkboxPk),"  i='".htmlspecialchars(json_encode($checkboxPk), ENT_QUOTES)."' class='hand' j='check_multi_AuthyGroupX' ").label('','for="check_'.$i.'"');
                }



                $actionRow = $actionRow;
                $actionRow = (!empty($actionRow)) ? td($this->listActionCellAuthyGroupX.$actionRow," class='actionrow'") : "";

                                    $AuthyGroupRelatedByIdAuthyGroup_Name = "";
                                    if($data->getAuthyGroupRelatedByIdAuthyGroup()){
                                        $AuthyGroupRelatedByIdAuthyGroup_Name = $data->getAuthyGroupRelatedByIdAuthyGroup()->getName();
                                    }


                ;



                $gcChildRows[] =
                        div(
 div(
   div('' . span(htmlspecialchars((string)((($altValue['IdAuthyGroup'] !== null ) ? $altValue['IdAuthyGroup'] : $AuthyGroupRelatedByIdAuthyGroup_Name)))." ", "  crPk = '".(($data->getAuthyGroupRelatedByIdAuthyGroup())?$data->getAuthyGroupRelatedByIdAuthyGroup()->getIdAuthyGroup():0)."' i='" . htmlspecialchars(json_encode($data->getPrimaryKey()), ENT_QUOTES) . "' c='IdAuthyGroup' class=''  j='editAuthyGroupX'") ,''," class='name' ")
   . div(''  ,''," class='meta' ")
 ,'', " class='body' ")
. div('<i class="ri-arrow-right-s-line chev"></i>',''," class='trail' ")
. $actionRow

                        , '', "id='AuthyGroupXRow".htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES)."' rid='".htmlspecialchars((string)$data->getPrimaryKey(), ENT_QUOTES)."' r='data' data-iterator='{$i}' class='va-mob-row' ln='AuthyGroupX'  ");


                $i++;
            }
            $tr .= implode('', $gcChildRows);


        }

    $add_button_child = "";
    if(($_SESSION[_AUTH_VAR]->hasRights('AuthyGroupX', 'a')) ){
        $add_button_child = "";
    }

    //@PAGINATION
    $pagerRow = $this->getPager($pmpoData, $resultsCount, $search, true);

    $return['html'] =
            div(
                 $this->hookAuthyGroupXListTop
                .div(
                    div(
                        div(span(_('Group'), ' class="ch-list-title-name"').span($resultsCount, ' class="ch-count"'), '', ' class="ch-list-title" ')
                        .$add_button_child
                        .$trSearch
                    , '' ,'class="ac-list-form-header-child"')
                    .$gcSortClear
                    .div(
                        div(
                            div(
                                div($tr .$this->hookAuthyGroupXTableFooter, '', "id='AuthyGroupXTable' class='va-mob-list'")
                            , 'childlistAuthyGroupX')
                            .$this->hookAuthyGroupXListBottom
                        ,'',' class="content" ')
                    ,'listFormChild',' class="ac-list" ')
                    .$pagerRow
                ,'AuthyGroupXListForm', " class='va-mob proto-app' data-model='AuthyGroupX' data-table='AuthyGroupX' data-gc-db='authy_group_x' data-ui='cntAuthyGroupXdivChild' data-ip='".htmlspecialchars((string)$IdAuthy, ENT_QUOTES)."' data-tp='AuthyGroupX' data-parent='Authy' data-crmodel='AuthyGroup' data-gc-cbulk='".htmlspecialchars(json_encode(['model'=>'AuthyGroupX','ntn'=>1,'bu'=>0,'del'=>0,'parent'=>'Authy','table'=>'Authy','virtual'=>$this->virtualClassName,'pc'=>$parentContainer,'savedMsg'=>_('Saved'),'failMsg'=>_('Save failed'),'attentionMsg'=>_('Attention'),'checkOneMsg'=>_('Please check at least one line.'),'buTitle'=>_('Bulk Update')], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), ENT_QUOTES)."' data-gc-sort='".$gcChildSortAttr."' ")
            ,'cntAuthyGroupXdivChild', "class='childListWrapper'");




            $return['onReadyJs'] =
                $this->hookListReadyJsFirstAuthyGroupX
                .""
                .$this->AuthyGroupX['list_add']
                .$this->AuthyGroupX['list_delete']
                .$this->AuthyGroupX['list_edit']
            ."



            /*checkboxes*/


        /* PAGINATION + sort: app/list.js owns pager/sort on the child .va-mob.proto-app[data-model] container (stage 5 jquery-removal) */

        /* #23 S5: child gcSelectBox.bindWithin dropped — screens.js push() binds the pushed child-list screen (idempotent). Off the re-exec arm. */

        /* #23 S5: order marker is now declarative (data-gc-sort on the container) */
        ";

        $return['onReadyJs'] .= "
                "
                . $this->hookListReadyJsAuthyGroupX;
        return $return;
    }
    /**
     * function getAuthyLogList (delegates to child Form getList() (IMPROVEMENTS-runtime #9))
     * Thin parent wrapper: session tab marker + optional beforeChildList* hook,
     * then child Form::getList($request, $ui, $parentId). ACL stays on the
     * Service child-list dispatch (requireAccess child 'r').
     */
    public function getAuthyLogList(String $IdAuthy, array $request)
    {

        $uiTabsId = (empty($request['cui'])) ? 'cntAuthyChild' : $request['cui'];
        $_SESSION['mem']['Authy']['child']['list']['active'] = 'AuthyLog';

        if (method_exists($this, 'beforeChildListAuthyLog')) {
            $this->beforeChildListAuthyLog();
        }

        $svcCls = class_exists('\\App\\AuthyLogServiceWrapper')
            ? '\\App\\AuthyLogServiceWrapper'
            : '\\App\\AuthyLogService';
        $childService = new $svcCls($this->request, null, $this->args ?? []);
        $listOut = $childService->Form->getList($request, $uiTabsId, $IdAuthy);

        return [
            'html'      => $listOut['html'] ?? '',
            'js'        => $listOut['js'] ?? '',
            'onReadyJs' => $listOut['onReadyJs'] ?? '',
        ];
    }

}
