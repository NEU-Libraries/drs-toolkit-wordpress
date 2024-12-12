<?php

/*
namespace Ceres\Renderer;


use DOMDocument;
use DOMElement;
use DOMXPath;
use Ceres\Util\DataUtilities as DataUtil;

class ViewPackage extends Html {
    //@todo: a temp shim until the extractor is built
    protected array $renderArray = [];
    protected string $templateFileName = 'details.html';
    protected string $viewPackageName;
    protected DOMElement $vpRendererDetailsNode;
    protected DOMElement $vpExtractorDetailsNode;
    protected DOMElement $vpFetcherDetailsNode;
    

    public function __construct(string $viewPackageName) {
        
        
        $this->viewPackageName = $viewPackageName;

        parent::__construct();


        $this->setRenderArray();
    }

    // public function render() {
    //     print_r($this->renderArray);

    //     parent::render();
    // }

    public function setRenderArray(string $extractorName = null) {
        $allVpData = DataUtil::getWpOption('ceres_view_packages');
        $rendererData =$allVpData[$this->viewPackageName];
        //$rendererClassInfo = $rendererData['renderer'];
        $rendererClassInfo = DataUtil::skipArrayLevel($rendererData['renderer']);

        $rendererName = $rendererClassInfo['fullClassName'];
        $rendererOptions = $rendererClassInfo['options'];
        
        foreach ($rendererOptions as $index => $optionName) {
            $optionValue = DataUtil::valueForOption($optionName, $this->viewPackageName);
            $rendererOptions[$optionName] = $optionValue;
            unset($rendererOptions[$index]);
        }

        $rendererData['renderer']['Tabular']['options'] = $rendererOptions;
        $this->renderArray = $rendererData;
    }

    public function build() {
        $rendererData = $this->renderArray['renderer']['Tabular']; //@todo remove hardcoded Tabular
        $vpData = [];
        foreach($this->renderArray as $option => $value) {
            switch ($option) {
                case 'humanName':
                case 'description':
                case 'parentViewPackage':
                case 'projectName':
                    $vpData[$option] = $value;
                break;
                default:
            }
        }
        
        $detailsNodeVp = $this->buildDetailsNode($vpData);


        $detailsNodeRenderer = $this->buildDetailsNode($rendererData);

        $detailsNodeVp->appendChild($detailsNodeRenderer);
        $this->containerNode->appendChild($detailsNodeVp);
        $this->containerNode->appendChild($detailsNodeRenderer);
        $detailsNodeVp->appendChild($detailsNodeRenderer);

    }

    public function buildDetailsNode(array $dataForDetails) {
        $detailsNode = $this->htmlDom->createElement(('details'));
        $summaryNode = $this->htmlDom->createElement('summary');
        $detailsNode->appendChild($summaryNode);
        $detailsNode->appendChild($this->textToHeading('Options', 'h2'));

        foreach($dataForDetails as $option=>$value) {
            if ( ($option == 'humanName') || $option == 'fullClassName' ) {
                $summaryHeading = $this->textToHeading($value, 'h1');
                $summaryHeading->setAttribute('style', 'display: inline');
                $summaryNode->appendChild($summaryHeading);
            } else {

                if(is_array($value)) {

                    foreach($value as $subOption=>$subValue) {
                        $dlNode = $this->htmlDom->createElement('dl');
                    

                        $dtNode = $this->htmlDom->createElement('dt');

                        $ddNodeValue = $this->htmlDom->createElement('dd');
                        $ddNodeDescription = $this->htmlDom->createElement('dd');
                        $ddNodeType = $this->htmlDom->createElement('dd');
                        $ddNodeAccess = $this->htmlDom->createElement('dd');
                        $ddNodeFormEl = $this->htmlDom->createElement('dd');
                    
                        $subValue = DataUtil::valueForOption($subOption, $this->viewPackageName);
                        $optionDescription = DataUtil::descriptionForOption($subOption);
                        $optionType = DataUtil::typeForOption($subOption);
                        //$accessValues = DataUtil::accessValuesForOption($subOption);

                        if ($optionType == 'enum') {
                            $enumOptions = DataUtil::enumValuesForOption($subOption);
                        }

                        switch ($optionType) {
                            case 'varchar':
                                $formNodeEl = $this->varcharToInput($subValue);
                            break;

                            case 'text':
                                $formNodeEl = $this->textToTextArea($subValue);
                            break;

                            case 'enum':
                                $formNodeEl = $this->enumToSelect($enumOptions);
                            break;

                            case 'bool':
                            break;
                        }

                        $ddNodeFormEl->appendChild($this->textToHeading('Input Form', 'h4'));
                        $ddNodeFormEl->appendChild($formNodeEl);
                        $dtNode->appendChild($this->textToHeading($subOption, 'h3'));

                    
                        //$this->appendTextNode($ddNodeDescription, "Description: $optionDescription");
                        $ddNodeDescription->appendChild($this->textToHeading('Description', 'h4'));
                        $this->appendTextNode($ddNodeDescription, $optionDescription);


                        //$this->appendTextNode($ddNodeType, "Type: $optionType");
                        $ddNodeType->appendChild($this->textToHeading('Type', 'h4'));
                        $this->appendTextNode($ddNodeType, $optionType);

                        //$this->appendTextNode($ddNodeAccess, "Access:");
                        $ddNodeAccess->appendChild($this->textToHeading('Access', 'h4'));
                        //$ddNodeAccess->appendChild($this->arrayToUl($accessValues));
                        
                        $ddNodeValue->appendChild($this->textToHeading('Applied Value', 'h4'));
                        $this->appendTextNode($ddNodeValue, $subValue);
                        
                        $dlNode->appendChild($dtNode);
                        $dlNode->appendChild($ddNodeValue);
                        $dlNode->appendChild($ddNodeDescription);
                        $dlNode->appendChild($ddNodeType);
                        $dlNode->appendChild($ddNodeAccess);
                        $dlNode->appendChild($ddNodeFormEl);
    
                        $detailsNode->appendChild($dlNode);                        
                    }

                } else {
                    $dlNode = $this->htmlDom->createElement('dl');

                    $dtNode = $this->htmlDom->createElement('dt');
                    $ddNode = $this->htmlDom->createElement('dd');;
                
                    $this->appendTextNode($dtNode, $option);
                    $this->appendTextNode($ddNode, $value);

                    $dlNode->appendChild($dtNode);
                    $dlNode->appendChild($ddNode);

                    $detailsNode->appendChild($dlNode);
                }
            }
        }
        return $detailsNode;
    }

}

*/