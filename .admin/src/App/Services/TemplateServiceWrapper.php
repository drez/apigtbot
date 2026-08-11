<?php

namespace App;


/**
 * Skeleton subclass for representing a services for the TemplateService entity.
 *
 * User
 *
 * You should add additional methods/hooks to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.GoatCheese.
 */

 use App\Domains\Template\Variables;
class TemplateServiceWrapper extends TemplateService{
    public function __construct($request, $response, $args)
    {
        parent::__construct($request, $response, $args);

        $this->Form = new TemplateFormWrapper($request, $args);

        # add a custom action to the current route
        $this->customActions['viewVar'] = 'viewVar';
    }

    public function viewVar(){
        $Variables = new Variables();
        $content = $Variables->print();
        return [
            'html' => div(
                        div(
                            div('Variables', '', "class='panel-heading'")
                            .div(
                                $content['html']
                            )
                        , '', "class='divStdform'")
                    , '', "class='mainForm'"),
            'onReadyJs' => $content['onReadyJs']
        ];
    }

    public function afterGetResponseSwitch(){}

    // Save/delete hooks intentionally NOT declared: the runtime Api calls them
    // with the Propel MODEL first (Api::setEntry / Api::deleteJson) and skips
    // absent hooks via method_exists(). The old empty stubs here type-hinted
    // $obj as TemplateService, so every API/MCP Template write fataled with a
    // TypeError (opaque -32603). Declare a hook only when implementing it, with
    // the model type: e.g. beforeSave(Template $obj, array &$data, bool $isNew,
    // &$messages, &$extValidationErr, $error).

}
