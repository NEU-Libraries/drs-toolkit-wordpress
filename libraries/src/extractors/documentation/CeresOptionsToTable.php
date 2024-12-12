<?php

namespace Ceres\Extractor\Documentation;

use Ceres\Data;
use Ceres\Util\DataUtilities as DataUtil;
use Ceres\Extractor\AbstractExtractor;


class CeresOptionsToTable extends AbstractExtractor {

    public function extract() {
        $this->preExtract();
        $dataToRender = $this->optionsToTableArray();

        $this->setRenderArray($dataToRender);
    }

    protected function preSetSourceData($sourceData)
    {
        return Data\getAllOptions();
    }

    protected function optionsToTableArray() {
        $tableArray = [];
        //$headings = ['OptionId', 'Label', 'Shortcode', 'Description', 'Defaults', 'Type', 'Access' ];
        $headings = ['OptionId', 'Label', 'Description', 'Access', 'Type', 'Defaults', 'Shortcode', 'Applies To' ];
        $tableArray[] = $headings;
    
        foreach($this->sourceData as $optionName=>$props) {
            $tableArray[] = $this->optionToRow($optionName, $props);
        }
        return $tableArray;
    }
    
    
    protected function optionToRow($optionName, $props) {
        $rowArray = [$optionName];
        $propsToShow = ['label', 'shortcode', 'desc', 'type', 'defaults', 'access'];
        foreach($props as $prop=>$value) {
            if (in_array($prop, $propsToShow)) {
                switch ($prop) {
                    case 'type':
                        switch ($value) {
                            case 'bool':
                                $newValue = "true/false";
                            break;
    
                            case 'varchar':
                                $newValue = "short text";
                            break;
    
                            case 'text':
                                $newValue = "long text";
                            break;
    
                            case 'int':
                                $newValue = "non-negative integer";
                            break;

                            case 'data':
                                $newValue = "a complex data structure";
                            break;
                            case 'enum':
                                //$newValue = DataUtil::allEnumValuesForOption($optionName);
                                $newValue['type'] = 'complexKeyValue';
                                //put all enum values into the correct structure
                                //@todo extract someday
                                $allEnumsData =  DataUtil::allEnumValuesForOption($optionName);
                                $scopeKeys = array_keys($allEnumsData);
                                $data = [];

                                //a value for each scope, so expand the keys for scope
                                //into the type/data structure (1)
                                //then put each of the values (which are arrays) (2)
                                //into their own type/data structures
                                foreach($scopeKeys as $scopeKey) { // (1)
                                    $data[$scopeKey] = ['type' => 'ul', // (1)
                                                        'data' => $allEnumsData[$scopeKey], // (2)
                                                       ];
                                }
                                $newValue['data'] = $data;
                            break;
                            case 'FilePath':
                                $newValue = "The id of a file with the relevant data";
                            break;
                        }
    
                    break;

                    case 'access':
                        $newValue = [];
                        $newValue['type'] = 'ul';
                        $newValue['data'] = $value;

                        
                    break;
    
                    case 'defaults':
                        $newValue = [];
                        $newValue['type'] = 'keyValue';
                        $newValue['data'] = DataUtil::allDefaultsForOption($optionName);
                    break;
    
                    default:
                        $newValue = $value;
    
                    break;
    
                }
                $rowArray[] = $newValue;
            }
            
        }
    
        return $rowArray;
    }
    

}
