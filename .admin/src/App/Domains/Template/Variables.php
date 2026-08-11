<?php

namespace App\Domains\Template;

class Variables
{
    static public $varDef = [
        'Authy' => [
            "_table" => "Authy",
            "_desc" => "User table",
            "_dependancy" => array('Authy'),
            "IdAuthy" => "",
            "Fullname"  => "",
            "Email" => "",
            "ValidationKey" => ""
        ],
        'Utils' =>
        [
            "_desc" => "Collection of utilitarian variables",
            "_table" => "",
            "Now" => ["Desc" => "Todays date {format} ie: [Utils-Now{Y-m-d}] => 2020-01-01"],
            "Url" => ["Desc" => "This app base url: " . _SITE_URL],
            "GuiUrl" => ["Desc" => "The GUI url Settings->app_gui_url: " . app_gui_url]
        ]
    ];

    public function __construct()
    {
    }

    public function print()
    {
        $allVars = '';
        $lis = '';
        foreach ($this::$varDef as $name => $field) {
            $TableVars = '';
            if (is_array($field)) {

                foreach ($field as $sname => $data) {
                    if (!strstr($sname, '_') && (!isset($data['Type']) || $data['Type'] != 'Hidden')) {
                        $variable = '';
                        $variable .= "[" . $name . "-" . camelize($sname, true) . "";

                        if (is_array($data) && ($data['Field'] && ($data['Relation'] || $data['Table']))) {

                            $variable .= "-" . camelize($data['Field'], true) . "]";
                            if ($data['Desc'] || isset($data['Format'])) {
                                $Format = ($data['Format']) ? span(" Format : " . $data['Format'] . " ", "style=''") : "";
                                $variable .= "]" . div($Format . $data['Desc'], '', "style='float: right;width: 75%;font-style: italic;'");
                            }
                            $variable .= "";
                        } else {
                            $desc = '';
                            if (isset($data['Desc']) || isset($data['Format'])) {
                                $Format = ($data['Format']) ? span(" Format : " . $data['Format'] . " ", "style=''") : "";
                                $desc .= "" . div($Format . $data['Desc'], '', "style='float: right;width: 75%;font-style: italic;'");
                            }
                            $variable .= "]" . $desc;
                        }
                        $TableVars .= div($variable, '', "style='padding:2px;'");
                    } elseif (strstr($sname, '_') && $sname == '_desc') {
                        $TableVars = div($data, '', "style='padding: 5px 10px;font-style: italic;font-weight: 600;'");
                    }
                }
            }
            $lis .= li(htmlLink($name, "#" . $name), " class='ui-state-default'");
            $allVars .=
                div($TableVars, $name, "style='margin-bottom:10px;' class='divStdform'");
        }

        // Vanilla tab strip (was jQuery-UI $('#VarDoc').tabs()). The
        // ui-tabs / ui-tabs-nav / ui-state-* classes are kept so the
        // existing main.scss styling still applies; the JS only toggles
        // active state and shows/hides the matching panel.
        $tabsJs =
            "(function(){"
            . "var root=document.getElementById('VarDoc'); if(!root){return;}"
            . "var links=root.querySelectorAll('ul>li>a[href^=\"#\"]'); var panels=[];"
            . "for(var i=0;i<links.length;i++){panels.push(document.getElementById(links[i].getAttribute('href').slice(1)));}"
            . "function act(idx){for(var j=0;j<links.length;j++){var li=links[j].parentNode;"
            . "if(j===idx){li.classList.add('ui-tabs-active','ui-state-active');}else{li.classList.remove('ui-tabs-active','ui-state-active');}"
            . "if(panels[j]){panels[j].style.display=(j===idx)?'':'none';}}}"
            . "for(var k=0;k<links.length;k++){(function(idx){links[idx].addEventListener('click',function(e){e.preventDefault();act(idx);});})(k);}"
            . "if(links.length){act(0);}"
            . "})();";

        return ['html' =>
            div(
                ul($lis, " class='ui-tabs-nav'") . $allVars,
                "VarDoc",
                " class='mainForm ui-tabs' style=''"
            ),
            'onReadyJs' => $tabsJs
        ];
    }
}
