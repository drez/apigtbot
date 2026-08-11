<?php

namespace App\Domains\Dashboard;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Landing-page override. The runtime WelcomeService instantiates this class
 * (when present) and, because $override = true, renders dashboard() as the
 * admin home page instead of the generic welcome screen — no route changes
 * needed (there must be no public/view/welcome.html, which apigtbot has none).
 */
class View
{
    public $request;
    public $override = false;
    public $args;
    public $model_name;
    private $entities = [];

    public function __construct(Request|null $request = null, array|null $args = null)
    {
        $this->override = true; // take over the Welcome screen
        $this->request = $request;
        $this->args = $args;
        $this->model_name = 'DashboardView';
    }

    public function dashboard()
    {
        $base = defined('_SITE_URL') ? _SITE_URL : '';

        // Multi-run: ?run=N picks the tab; default first active run. With no
        // active run, DashboardData falls back to the latest non-Done run and
        // the page renders tabless, exactly as before.
        $query = $this->request ? $this->request->getQueryParams() : $_GET;
        $requested = isset($query['run']) && is_numeric($query['run']) ? (int) $query['run'] : null;
        $active = DashboardData::activeRuns();
        $run = DashboardData::selectRun($active, $requested);

        $vm = (new DashboardData($run))->viewModel($base);
        if ($run) {
            $vm['tabs'] = DashboardData::tabs($active, (int) $run->getIdGridRun(), $base);
        }
        // System-wide band (mode/budget/wallet/account value/realized P/L)
        // does not depend on which run tab is selected, so it's wired
        // independently of the per-run view model above.
        $vm['band'] = DashboardData::globalBand();
        $html = (new DashboardRenderer())->render($vm);

        // Live-ish: soft auto-refresh every 60s (paused when the tab is hidden).
        $onReadyJs = "(function(){var t=setInterval(function(){"
            . "if(document.visibilityState==='visible'){location.reload();}"
            . "},60000);window.addEventListener('beforeunload',function(){clearInterval(t);});})();"
            // Restart button: clear the kill switch and resume trading.
            // Confirmation goes through gcScreens.confirm (Promise) — NOT the
            // window.confirm override from index.js, which is async/callback-
            // style and returns undefined (a native-style boolean check exits
            // before sending anything). Every step after the confirm reports
            // into an on-page banner (#gc-restart-diag) so a failure can never
            // be silent: "sending…", then OK/reload, the real HTTP status+body,
            // a network error, a thrown exception, or the 15s watchdog.
            . $this->restartButtonJs(rtrim($base, '/') . '/');

        return [
            'onReadyJs' => $onReadyJs,
            'html' => $html,
        ];
    }

    /** Click handler for .dash-restart-btn (see dashboard() for the contract). */
    private function restartButtonJs(string $base): string
    {
        $js = <<<'JS'
(function(){
 var VER='rb5';
 function show(msg){
   var d=document.getElementById('gc-restart-diag');
   if(!d){d=document.createElement('div');d.id='gc-restart-diag';
     d.style.cssText='position:fixed;bottom:12px;left:12px;right:12px;z-index:99999;background:#fff4e5;color:#7a3b00;border:2px solid #b26a00;border-radius:8px;padding:10px 14px;font:13px/1.5 monospace;white-space:pre-wrap;';
     document.body.appendChild(d);}
   d.textContent='[restart '+VER+'] '+msg;
 }
 function send(b){
   try{
     b.disabled=true;
     show('sending…');
     // Plain XHR on purpose: the admin's global fetch patch has interception
     // layers (csrf attach, 401 queue) that make failures invisible; XHR is
     // dependency-free and we attach the CSRF token ourselves from the meta tag.
     var xhr=new XMLHttpRequest();
     xhr.open('POST',__BASE__+'Dashboard/restart',true);
     xhr.setRequestHeader('Content-Type','application/json');
     xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
     var m=document.querySelector('meta[name="csrf-token"]');
     if(m&&m.content){xhr.setRequestHeader('X-Csrf-Token',m.content);}
     xhr.timeout=15000;
     xhr.onload=function(){
       if(xhr.status>=200&&xhr.status<300&&xhr.responseText.indexOf('"ok"')!==-1){show('OK — reloading');location.reload();return;}
       b.disabled=false;show('HTTP '+xhr.status+' — '+String(xhr.responseText).slice(0,300));
     };
     xhr.onerror=function(){b.disabled=false;show('network error (request blocked?)');};
     xhr.ontimeout=function(){b.disabled=false;show('no response after 15s');};
     xhr.send(JSON.stringify({run:parseInt(b.getAttribute('data-run'),10)}));
   }catch(ex){b.disabled=false;show('exception: '+(ex&&ex.message?ex.message:ex));}
 }
 function sendCmd(b,action){
   try{
     b.disabled=true;
     show(action+': sending…');
     var xhr=new XMLHttpRequest();
     xhr.open('POST',__BASE__+'Dashboard/command',true);
     xhr.setRequestHeader('Content-Type','application/json');
     xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
     var m=document.querySelector('meta[name="csrf-token"]');
     if(m&&m.content){xhr.setRequestHeader('X-Csrf-Token',m.content);}
     xhr.timeout=15000;
     xhr.onload=function(){
       if(xhr.status>=200&&xhr.status<300&&xhr.responseText.indexOf('"ok"')!==-1){show(action+' queued — the daemon consumes it next tick; reloading');setTimeout(function(){location.reload();},1200);return;}
       b.disabled=false;show('HTTP '+xhr.status+' — '+String(xhr.responseText).slice(0,300));
     };
     xhr.onerror=function(){b.disabled=false;show('network error (request blocked?)');};
     xhr.ontimeout=function(){b.disabled=false;show('no response after 15s');};
     xhr.send(JSON.stringify({run:parseInt(b.getAttribute('data-run'),10),action:action}));
   }catch(ex){b.disabled=false;show('exception: '+(ex&&ex.message?ex.message:ex));}
 }
 function sendMode(b,target){
   try{
     b.disabled=true;
     show('mode '+target+': sending…');
     var xhr=new XMLHttpRequest();
     xhr.open('POST',__BASE__+'Dashboard/mode',true);
     xhr.setRequestHeader('Content-Type','application/json');
     xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
     var m=document.querySelector('meta[name="csrf-token"]');
     if(m&&m.content){xhr.setRequestHeader('X-Csrf-Token',m.content);}
     xhr.timeout=15000;
     xhr.onload=function(){
       if(xhr.status>=200&&xhr.status<300&&xhr.responseText.indexOf('"ok"')!==-1){show('mode '+target+' — reloading');setTimeout(function(){location.reload();},1200);return;}
       b.disabled=false;show('HTTP '+xhr.status+' — '+String(xhr.responseText).slice(0,300));
     };
     xhr.onerror=function(){b.disabled=false;show('network error (request blocked?)');};
     xhr.ontimeout=function(){b.disabled=false;show('no response after 15s');};
     xhr.send(JSON.stringify({mode:target}));
   }catch(ex){b.disabled=false;show('exception: '+(ex&&ex.message?ex.message:ex));}
 }
 document.addEventListener('click',function(e){
   var mb=e.target&&e.target.closest?e.target.closest('.dash-mode-btn'):null;
   if(mb){
     var target=mb.getAttribute('data-target');
     var mmsg=target==='real'
       ?'Switch ALL runs to REAL trading? This is the money switch: daemons will refuse to start until prod API keys + GTBOT_USE_TESTNET=0/GTBOT_DRY_RUN=0 are set. Continue?'
       :'Switch ALL runs back to SIMULATED (paper) trading?';
     var mdanger=target==='real';
     // Same gcScreens.confirm Promise API + native-confirm fallback as the
     // restart/command buttons above — window.confirm is either native
     // (boolean) or the index.js async override (undefined return), so a
     // native-style `if(!confirm(...))` would silently bail without gcScreens.
     if(window.gcScreens&&gcScreens.confirm){
       gcScreens.confirm(mmsg,{confirmLabel:target==='real'?'Switch to REAL':'Switch to SIMULATED',danger:mdanger}).then(function(ok){if(ok){sendMode(mb,target);}});
       return;
     }
     var mok=false;
     try{mok=(window.confirm(mmsg)===true);}
     catch(err){show('confirm unavailable: '+(err&&err.message?err.message:err));return;}
     if(mok){sendMode(mb,target);}
     return;
   }
   var c=e.target&&e.target.closest?e.target.closest('.dash-cmd-btn'):null;
   if(c){
     var action=c.getAttribute('data-action');
     var msgs={start:'Resume trading (clears Pause)?',
               stop:'Pause trading? Working orders stay on the exchange.',
               reload:'Restart the daemon process? It exits cleanly and the watchdog/systemd relaunches it on current code (reboots from the DB, reconciles before trading).'};
     var cmsg=msgs[action]||('Send '+action+'?');
     if(window.gcScreens&&gcScreens.confirm){
       gcScreens.confirm(cmsg,{confirmLabel:'Send',danger:action!=='start'}).then(function(ok){if(ok){sendCmd(c,action);}});
       return;
     }
     var cok=false;
     try{cok=(window.confirm(cmsg)===true);}
     catch(err){show('confirm unavailable: '+(err&&err.message?err.message:err));return;}
     if(cok){sendCmd(c,action);}
     return;
   }
   var b=e.target&&e.target.closest?e.target.closest('.dash-restart-btn'):null;
   if(!b){return;}
   var msg='Clear the kill switch and resume trading?';
   // index.js overrides window.confirm with an ASYNC gcScreens modal that
   // returns undefined and only runs a callback passed as the 2nd argument —
   // a native-style `if(!confirm(...))` therefore always bails out silently
   // (rb1–rb4 all died here). Use the app's own Promise API directly.
   if(window.gcScreens&&gcScreens.confirm){
     gcScreens.confirm(msg,{confirmLabel:'Restart',danger:false}).then(function(ok){if(ok){send(b);}});
     return;
   }
   // No gcScreens on this page: window.confirm is either native (boolean) or
   // the index.js override, which now throws on native-style calls — only
   // proceed on an explicit native true, and surface the throw in the banner.
   var ok=false;
   try{ok=(window.confirm(msg)===true);}
   catch(err){show('confirm unavailable: '+(err&&err.message?err.message:err));return;}
   if(ok){send(b);}
 });
})();
JS;
        return str_replace('__BASE__', json_encode($base), $js);
    }
}
