<?php

namespace App\Domains\Template;

use App\Domains\Template\Variables;

class MatchReplace
{

    private $dataObj;
    public $originalContent;
    public $newContent;
    public $allVariables;
    private $allVariablesTypes;
    public $warnings;
    public $errors;
    public $varDefine;
    public $hasCustom = array();
    private $customVar = array();
    public $requireObj = array();
    private $templateAddons;

    public $softlink = false;

    function __construct(string $content, string $addons = '')
    {
        $this->originalContent = $content;
        $this->newContent = $content;
        if ($addons) {
            if (json_decode($addons, true)) {
                $this->templateAddons = json_decode($addons, true);
            } else {
                $this->addWarnings("Template addons error");
            }
        }

        $this->varDefine = Variables::$varDef;
        $this->parseContent();
    }

    public function getContent()
    {
        $this->replaceDbVariables();
        $this->replaceCustomVariables();
        $this->replaceUtilsVariables();
        $this->replaceCalcVariables();
        $this->replaceFileVariables();
        return $this->newContent;
    }

    public function setDataObj($name, $obj)
    {
        if (is_object($obj)) {
            $this->dataObj[$name] = $obj;
            $this->loadedObj[] = $name;
        } else {
            $this->addWarnings("setDataObj " . $name . " failed");
            $this->dbg("setDataObj " . $name . " failed", 1, 2);
        }
    }

    public function setDataArray($name, $array)
    {
        if (is_array($array)) {
            $this->dataAr[$name] = $array;
        } else {
            $this->addWarnings("setDataArray " . $name . " failed");
            $this->dbg("setDataArray " . $name . " failed", 1, 2);
        }
    }

    private function dbg($str, $i = 0, $level = 0)
    {
        switch ($level) {
            case '2':
                $style = "style='background: #f0f0f0;padding-left:" . $i . "0px;'";
                break;
            default:
                $style = "style='background: #f0f0f0;padding: 10px;margin-top:10px;'";
        }

        if (is_array($str)) {
            $this->dbg .= div(pre(print_r($str, true), "style='font-size:10px;'"), '', $style);
        } else
            $this->dbg .= div($str, '', $style);
    }

    public function getDbg()
    {
        return div($this->dbg, '', "style='padding: 10px;'");
    }

    private function parseContent()
    {

        preg_match_all("/\[(.*?)\]/", $this->originalContent, $aMatches);
        $i = 0;

        foreach ($aMatches[1] as $var) {
            if (strstr($var, '-')) {
                preg_match_all("/\{(.*?)\}/", $var, $bMatches);
                $part = explode('-', $var);
                if (is_array($bMatches) && !empty($bMatches[0])) {
                    $part[1] = str_replace($part[0] . "-", '', $var);
                    $part[2] = $bMatches[1][0];
                }

                $tableVars = $this->varDefine[$part[0]];
                if (isset($tableVars[$part[1]]['ObjAnnex'])) {
                    $ObjName = camelize($tableVars[$part[1]]['ObjAnnex'], true);
                } else
                    $ObjName = camelize($tableVars["_table"], true);


                if (!is_array($this->requireObj[$ObjName])) {

                    $this->requireObj[$ObjName] = array(
                        'count' => 0,
                        'params' => json_decode($tableVars["_param"], true)
                    );

                    if ($tableVars["_param"] && $this->templateAddons[$ObjName]) {
                        $this->requireObj[$ObjName]['params'] = $this->templateAddons[$ObjName];
                    }
                    //$this->dbg($tableVars["_param"]);
                    if (isset($tableVars['_dependancy']))
                        $this->setDependancies($tableVars['_dependancy']);
                }

                $this->requireObj[$ObjName]['count']++;
                if (isset($tableVars[$part[1]]['Type']) && $tableVars[$part[1]]['Type'] == 'Calc') {
                    $type = 'Calc';
                } elseif ($part[0] == 'File') {
                    $type = 'File';
                    $varPart = explode(';', $var);
                    $namePart = explode('-', $varPart[0]);
                    $part['name'] = $varPart[1];
                    $part['width'] = $varPart[2];
                    $part['height'] = $varPart[3];
                    $part[1] = $namePart[1];
                    $part['opt'] = $varPart;
                } elseif (($part[0] == 'Stats' || $part[0] == 'Lists' || $part[0] == 'Ondemand') ||
                    (isset($tableVars[$part[1]]['Type']) && $tableVars[$part[1]]['Type'] == 'File')
                ) {
                    $type = 'Custom';
                    $this->hasCustom[] = $part[0];
                } elseif ($part[0] == 'Calc') {
                    $type = 'Calc';
                    $this->hasCustom[] = $part[0];
                } elseif (isset($tableVars[$part[1]]['Type']) && $tableVars[$part[1]]['Type'] == 'Img') {
                    $type = 'Img';
                } elseif ($part[0] == 'Utils') {
                    $type = 'Utils';
                } else {
                    $type = 'Db';
                }

                $this->allVariables[$type][trim($var)] = $part;
                $this->allVariablesTypes[trim($var)] = $type;
                //preprint($this->allVariables['File']);
                //preprint($this->requireObj);
                $i++;
            }
        }
        //echo $i;
        $this->dbg($this->allVariables);
    }

    private function setDependancies($depAr)
    {
        if (is_array($depAr)) {
            foreach ($depAr as $dep) {
                $tableVars = $this->varDefine[$dep];
                $this->requireObj[$dep] = array(
                    'count' => 0,
                    'params' => $tableVars["_param"]
                );
            }
        }
    }

    private function getDbValue($part, $varName)
    {
        $curDefine = $this->varDefine[$part[0]][$part[1]];
        $tableVars = $this->varDefine[$part[0]];


        /*if($part[0] == 'Sale' ){
            echo $part[1]." ->";
            echo print_r($curDefine, true);
            echo "<br>";
        }
        */
        $value = '';
        $fgTable = '';
        $fgRelation = "";
        $Field = '';
        $curObj = '';

        if (isset($curDefine['ObjAnnex']))
            $tableName = $curDefine['ObjAnnex'];
        else
            $tableName = $tableVars['_table'];
        $ObjName = camelize($tableName, true);
        $className = "App\\" . $ObjName;

        if (isset($curDefine['Filters'])) {
            $filterGet = 'get' . $curDefine['Filters'][0];
            $filterValue = $curDefine['Filters'][1];
            //echo $ObjName." ".count($this->dataObj[$ObjName])."->".$filterGet."(".$filterValue.")";
            foreach ($this->dataObj[$ObjName] as $obj) {

                if ($obj->$filterGet() == $filterValue) {
                    //echo $obj->$filterGet();
                    $curObj = $obj;
                }
            }
        } else {
            $curObj = $this->dataObj[$ObjName];
        }

        if (empty($curObj) || get_class($curObj) != $className) {
            $this->unkVar[] = $varName;
            $errorInfo = "";
            if (isset($curDefine['Filters'])) {
                $errorInfo = "- Make sure " . $curDefine['Filters'][0] . " is set to " . $curDefine['Filters'][1] . " on " . $ObjName;
            }
            $this->warnings .= "Unknown variable (6) " . htmlentities($varName) . " " . $errorInfo . " <br>";
            $this->dbg($ObjName . " does exists", 1, 2);
            return $varName;
        }

        if (is_array($curDefine)) {

            $fgTable = (!empty($curDefine['Table'])) ? $curDefine['Table'] : '';
            $fgRelation = (!empty($curDefine['Relation'])) ? $curDefine['Relation'] : $fgTable;
            $Field = (!empty($curDefine['Field'])) ? $curDefine['Field'] : $part[1];

            if (isset($curDefine['LocalField']))
                $Field = $curDefine['LocalField'];
        } else {

            if (isset($curDefine['LocalField']))
                $Field = $curDefine['LocalField'];
            else
                $Field = $part[1];
        }

        if ($Field) {

            $this->dbg("0." . $ObjName . "->get" . $Field, 1, 2);

            if (!empty($fgTable)) {
                $this->dbg("1." . $ObjName . "->get" . $fgRelation, 2, 2);
                if (method_exists($className, 'get' . $fgRelation)) {
                    $this->dbg("5." . $ObjName . "->get" . $fgRelation . "->get" . $Field, 3, 2);
                    if (method_exists("App\\" . $fgTable, 'get' . $Field)) {

                        $get1 = 'get' . $fgRelation;
                        $get2 = 'get' . $Field;
                        //echo $ObjName."->".$fgRelation."->".$Field."<br>";

                        if ($curObj->$get1()) {
                            $value = $curObj->$get1()->$get2();
                            //echo "2.".$ObjName."->".$get1."->".$get2."<br>";
                            if (isset($curDefine['Format'])) {
                                $value = $this->formatValue($value, $curDefine['Format'], $curDefine['Type']);
                            }
                        } else {
                            $this->unkVar[] = $varName;
                            $this->addWarnings("Empty (7) " . $ObjName . "->" . $get1 . "()");
                            //print_r($this->dataObj[$ObjName]->$get1());
                        }
                    } else {
                        $this->unkVar[] = $varName;
                        $this->addWarnings("Unknown variable (1) " . htmlentities($varName));
                    }
                } else {
                    $this->unkVar[] = $varName;
                    $this->addWarnings("Unknown variable (2) " . htmlentities($varName));
                }
            } elseif (method_exists($className, 'get' . $Field)) {

                $this->dbg("2." . $ObjName . "->get" . $Field . " : " . print_r($curDefine, true), 2, 2);
                $get = 'get' . $Field;

                if ($curObj) {
                    if (isset($curDefine['Format'])) {
                        $this->dbg("10." . $curDefine['Format'], 3, 2);
                        $value = $this->formatValue($curObj->$get(), $curDefine['Format'], $curDefine['Type']);
                        //echo $get."(".$curDefine['Type']."-".$curDefine['Format'].") = ".$value."<br>";
                    } else {
                        $value = $curObj->$get();

                        if (isset($curDefine['Type']) && $curDefine['Type'] == 'Multiple_f') {
                            $AnnexTableQuery = $curDefine['TableAnnex'] . 'Query';
                            $vals = explode(',', $value);
                            $filter = "filterById" . $curDefine['TableAnnex'];
                            $AnnexTableVals = $AnnexTableQuery::create()->$filter($vals)->find();
                            $value = '';
                            foreach ($AnnexTableVals as $AnnexTableVal) {
                                $value .= $AnnexTableVal->getName() . ", ";
                            }
                            $value = rtrim($value, ', ');
                        }
                    }
                } else {

                    $this->unkVar[] = $varName;
                    $this->addErrors("Object Empty (8) " . htmlentities($varName));
                }
            } else {
                $this->unkVar[] = $varName;
                $this->addWarnings("Unknown variable (3) " . htmlentities($varName) . " (" . $Field . ")");
            }
        } else {
            $this->unkVar[] = $varName;
            $this->addWarnings("Unknown variable (4) " . htmlentities($varName));
        }
        return $value;
    }

    public function addCustomArray($CustomVar)
    {
        if (count($CustomVar))
            foreach ($CustomVar as $key => $val) {
                $this->customVar[$key] = $val;
            }
    }

    public function addCustomVar($CustomVar, $name)
    {

        $this->customVar[$name] = $CustomVar;
    }

    public function addCustomDef($parent, $name, $definition)
    {
        $this->varDefine[$parent][$name] = $definition;
    }

    public function replaceUtilsVariables()
    {
        if (is_array($this->allVariables['Utils'])) {
            foreach ($this->allVariables['Utils'] as $name => $part) {
                $params = $this->varDefine[$part[0]][$part[1]];

                if (!isset($this->utilsVar[$name])) {
                    if (strstr($name, '{')) {
                        preg_match_all("/\{(.*?)\}/", $name, $aMatches);

                        $val = $this->getUtilsValue(str_replace($aMatches[0], '', $name), $aMatches[1], $aMatches[1]);
                        $this->utilsVar[$name] = $this->formatValue($val['value'], $aMatches[1], $val['type']);
                    } else {
                        $val = $this->getUtilsValue($name, '');
                        $this->utilsVar[$name] = $val['value'];
                    }
                }
                $this->newContent = str_replace("[" . $name . "]", $this->utilsVar[$name], $this->newContent);
            }
        }
    }

    private function getUtilsValue($name, $value, $data = [])
    {
        static $val;
        static $count;

        switch ($name) {
            case 'Utils-Increment':

                if ($data[0]) {
                    $count = 0;
                    $val = $data[0];
                }

                $ret['value'] = $val + $count;
                $ret['type'] = '';

                $count++;

                return $ret;
                break;
            case 'Utils-Now':
                $ret['value'] = time();
                $ret['type'] = 'time';

                return $ret;
                break;
            case 'Utils-Url':
                $ret['value'] = _SITE_URL;
                return $ret;
                break;
            case 'Utils-GuiUrl':
                $ret['value'] = app_gui_url;
                return $ret;
                break;
            case 'Utils-Barcode':

                if (sizeof($value) > 1) {
                    foreach ($value as $val) {
                        if (strstr($val, '-')) {
                            $part = explode("-", $val);
                            $code .= $this->getDbValue($part, $val);
                            $id = 'BC.' . $code;
                        } else {
                            $code = $val;
                        }
                    }
                } else {
                    $id = 'BC.' . $value;
                    if (strstr($value, '-')) {
                        $part = explode("-", $value);
                        $code = $this->getDbValue($part, $value);
                    } else {
                        $code = $val;
                    }
                }

                $ret['value'] = img("", "", "", "id='" . $id . "' j='barcode' v='" . $code . "'");
                $ret['type'] = '';
                return $ret;
                break;
        }
    }

    public function getValue($name)
    {
        $type = $this->allVariablesTypes[$name];
        $part = explode('-', $name);
        if (empty($type) && $part[0] == 'Ondemand') {
            $value = $this->customVar[$name];
        }/*else{
            $getFunction = "get".$type."Value";
            $value = $this->$getFunction($part, $name);
        }*/

        return $value;
    }

    public function replaceDbVariables()
    {
        if (is_array($this->allVariables['Db'])) {
            foreach ($this->allVariables['Db'] as $name => $part) {
                $value = $this->getDbValue($part, $name);
                $this->newContent = str_replace("[" . $name . "]", $value, $this->newContent);
                $this->allVariables['Db'][$name] = $value;
            }
            //echo "<pre>".print_r($this->allVariables['Db'], true)."</pre>";
        }
    }

    public function replaceCustomVariables()
    {
        if (is_array($this->allVariables['Custom'])) {
            foreach ($this->allVariables['Custom'] as $name => $part) {
                #unique variable specific format

                if (strstr($name, '{')) {
                    preg_match_all("/\{(.*?)\}/", $name, $aMatches);
                    $varName = str_replace($aMatches[0][0], '', $name);
                    if (strstr($aMatches[1][0], ":")) {
                        $more = explode(":", $aMatches[1][0]);
                        if ($more[0] == 'format') {
                            $this->customVar[$name] = $this->formatValue($this->customVar[$varName], $more[1], $params['type']);
                        }
                    }
                }
                $params = $this->varDefine[$part[0]][$part[1]];
                if (isset($params['format'])) {
                    $this->customVar[$name] = $this->formatValue($this->customVar[$name], $params['format'], $params['type']);
                }
                $this->newContent = str_replace("[" . $name . "]", $this->customVar[$name], $this->newContent);
                $this->allVariables['Custom'][$name] = $this->customVar[$name];
            }
        }
    }

    public function replaceCalcVariables()
    {
        if (is_array($this->allVariables['Calc'])) {
            foreach ($this->allVariables['Calc'] as $name => $part) {
                $value = $this->getCalcValue($part, $name);
                $this->newContent = str_replace("[" . $name . "]", $value, $this->newContent);
                $this->allVariables['Calc'][$name] = $value;
            }
        }
    }

    public function replaceFileVariables()
    {
        if (is_array($this->allVariables['File'])) {
            foreach ($this->allVariables['File'] as $name => $part) {
                $filepart = explode(';', $name);
                if (count($filepart) > 2) {
                    $file = $filepart[0] . ';' . $filepart[1];
                } else {
                    $file = $name;
                }
                $value = img(_SITE_URL . $this->dataAr['PrintTemplateFile'][$file], $part['height'], $part['width']);
                $this->newContent = str_replace("[" . $name . "]", $value, $this->newContent);
                $this->allVariables['File'][$name] = $value;
            }
        }
    }


    private function formatValue($value, $format, $type = '')
    {
        //echo $value."/".$format."/".$type.":".date($format, strtotime($value))."<br>";

        $type = ucfirst($type);
        switch ($type) {
            case 'Time':
                return date($format, $value);
                break;
            case 'Date':
                return date($format, strtotime($value));
                break;
        }

        switch ($format) {
            case 'number_in_writing':
                $f = new NumberFormatter("en", NumberFormatter::SPELLOUT, NumberFormatter::TYPE_CURRENCY);
                return $f->format(str_replace(",", '', $value));
                break;
            case 'amount_format':
            case 'amount':
                return number_format($value, 2, '.', ',');
                break;
            case 'quantity_format':
            case 'quantity':
                return number_format($value, 0, '.', ' ');
                break;
            case 'phone':
                return $value;
                break;
            case 'ucwords':
                return ucwords($value);
                break;
            case 'ucfirst':
                return ucfirst($value);
                break;
            case 'base64enc':
                return urlencode(base64_encode(trim($value)));
                break;
            case 'image':
                if ($this->softlink)
                    return img(_SITE_URL . $value, '', '', "class='" . $type . "'");
                else
                    return img(_BASE_DIR . $value, '', '', "class='" . $type . "'");
                break;
            default:
                return $value;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addErrors($warnings)
    {
        $this->errors .= div($warnings);
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function addWarnings($warnings)
    {
        $this->warnings .= div($warnings);
    }

    private function getCalcValue($part, $name)
    {
        $curDefine = $this->varDefine[$part[0]][$part[1]];
        $i = 0;
        if (is_array($curDefine['Fields'])) {
            foreach ($curDefine['Fields'] as $var) {
                if ($this->allVariables['Db'][$var]) {
                    $val[$i] = $this->allVariables['Db'][$var];
                } else {
                    $val[$i] = $this->getDbValue(explode('-', $var), $var);
                    $this->allVariables['Db'][$var] = $val[$i];
                }
                //echo $var." = ".$val[$i]."<br>";
                $i++;
            }
        }

        //echo $name."<br>";
        //preprint($this->allVariables);

        if (strstr($name, '{')) {
            preg_match_all("/\{(.*?)\}/", $name, $aMatches);
            //preprint($aMatches);
            if (strstr($aMatches[1][0], ":")) {
                $operands = explode(":", $aMatches[1][0]);
                if (count($operands) >= 3) {
                    $i = 0;
                    foreach ($operands as $operand) {
                        if (strstr($operand, '-')) {

                            if ($this->allVariables['Db'][$operand]) {
                                $val[$i] = $this->allVariables['Db'][$operand];
                            } elseif ($this->allVariables['Custom'][$operand]) {
                                if (!empty($this->allVariables['Custom'][$operand])) {
                                    $val[$i] = $this->allVariables['Custom'][$operand];
                                }
                            } else {
                                $val[$i] = $this->getValue($operand);
                            }

                            $val[$i] = str_replace(',', '', $val[$i]);
                            $i++;
                        } else {
                            $operation = $operand;
                        }
                    }
                    array_shift($aMatches[1]);
                } else {
                    $value = $this->allVariables['Custom'][str_replace($aMatches[0][0], '', $name)];
                }
            } else {

                $value = $this->allVariables['Custom'][str_replace($aMatches[0][0], '', $name)];
            }

            //echo $value;

            foreach ($aMatches[1] as $match) {
                if (strstr($match, ":")) {
                    $part = explode(":", $match);
                    if ($part[0] == 'format') {
                        $format = $part[1];
                    }
                    if ($part[0] == 'name') {
                        $name = $part[1];
                    }
                    if ($part[0] == 'display') {
                        $display = $part[1];
                    }
                }
            }
        } else {
            $value = $this->allVariables['Custom'][$name];
        }

        if (empty($operation)) {
            $operation = $curDefine['Operand'];
        }

        //preprint($val);

        switch ($operation) {
            case '%':
                $value = bcmul($val[0], $val[1], 2) / 100;
                break;
            case '-':
                $value = bcsub($val[0], $val[1], 2);
                break;
            case '+':
                $value = bcadd($val[0], $val[1], 2);
                break;
            case 'day_add':
                $curDefine['Format'] = ($curDefine['Format']) ? $curDefine['Format'] : 'Y-m-d';
                if ($val[0] && $val[1]) {
                    $val[0] = str_replace('/', '-', $val[0]);
                    //echo $val[0]." / ".$val[1]." = ".date($curDefine['Format'], bcadd(strtotime($val[0]),($val[1] *86400)))."<br>";

                    $value = date($curDefine['Format'], bcadd(strtotime($val[0]), ($val[1] * 86400)));
                } else {
                    $this->addErrors('Missing value for [' . $var . ']');
                }

                break;
            case 'if(No)show':
                //echo "->".$val[0]." - ".$val[1]."<br>";
                $value = ($val[0] != 'Yes') ? $val[1] : '';
                break;
            case 'if(Yes)show':
                $value = ($val[0] != 'No') ? $val[1] : '';
                break;
        }

        if (isset($format)) {
            $value = $this->formatValue($value, $format, $params['type']);
        }
        if ($name) {
            $this->allVariables['Custom']['Calc-' . $name] = $value;
        }

        if ($display == 'none')
            return '';

        return $value;
    }
}
