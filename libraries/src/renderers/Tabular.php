<?php

namespace Ceres\Renderer;

use DOMElement;

class Tabular extends Html {

    protected string $templateFileName = 'tabular.html';
    protected DOMElement $tbodyNode;
    protected DOMElement $theadNode;
    protected DOMElement $tfootNode;
    protected DOMElement $captionNode;

    public function __construct() {
        parent::__construct();

        $this->tbodyNode = $this->htmlDom->getElementsByTagName('tbody')->item(0);
        $this->theadNode = $this->htmlDom->getElementsByTagName('thead')->item(0);
        $this->tfootNode = $this->htmlDom->getElementsByTagName('tfoot')->item(0);
        $this->captionNode = $this->htmlDom->getElementsByTagName('caption')->item(0);

        $this->appendToClass($this->tbodyNode, $this->getRendererOptionValue('tbodyClass'));
        $this->appendToClass($this->theadNode, $this->getRendererOptionValue('theadClass'));
        $this->appendToClass($this->tfootNode, $this->getRendererOptionValue('tfootClass'));
        $this->appendToClass($this->captionNode, $this->getRendererOptionValue('captionClass'));
        $this->appendToClass($this->containerNode, $this->getRendererOptionValue('tableClass'));
    }

    public function setRenderArray(?string $extractorName = null, ?string $pathToMockFetcherResponse = null, ?string $pathToMockExtractorData = null): void {
        //@todo the logic throughout this needs some TLC
        
        if (is_null($extractorName) && is_null($pathToMockFetcherResponse) && is_null($pathToMockExtractorData)) {
            //have this roll through the fetcher->fetchData --> $extractor->setSourceData() chain
            $fetcher = $this->fetchers[0];
            $extractor = $this->extractors[0];

            $sourceData = $fetcher->fetchData();
//  print_r($sourceData);
//  echo "<h3>sourceData?</h3>";
//  die();
            $extractor->setSourceData($sourceData);
            $extractor->extract();

            $this->renderArray = $extractor->getRenderArray();
        } else if (! is_null($pathToMockFetcherResponse)) {
            $extractor = $this->extractors[0];

            $sourceData = file_get_contents($pathToMockFetcherResponse);

            $extractor->setSourceData($sourceData);
            $extractor->extract();

            $this->renderArray = $extractor->getRenderArray();

        } else if (! is_null($pathToMockExtractorData)) {
        }
    }

    public function build(): void {
        $rowsData = $this->renderArray; 
        $firstRowIsHeader = $this->getRendererOptionValue('firstRowIsHeader');

        if ($firstRowIsHeader) {
            $headerRowData = array_shift($rowsData);
            $rowNode = $this->buildRow($headerRowData, 'th');
            $this->theadNode->appendChild($rowNode);
        }

        foreach ($rowsData as $rowData) {
            $row = $this->buildRow($rowData);
            $this->tbodyNode->appendChild($row);      
        }
    }

    public function buildRow($rowData, $cellElement = 'td'): DOMElement {
        $trClass = $this->getRendererOptionValue('trClass');
        $tdClass = $this->getRendererOptionValue('tdClass');
        $thClass = $this->getRendererOptionValue('thClass');
        $theadClass = $this->getRendererOptionValue('theadClass');

        $trNode = $this->htmlDom->createElement('tr');
        $this->appendToClass($trNode, $trClass);

        foreach ($rowData as $columnData) {
            $tdNode = $this->htmlDom->createElement($cellElement);

            //@todo handle column data that's a nested array, and different guidance from Extractor (e.g. 'list', 'img')
            // this should be abstracted out somewhere/how?


            if (is_array($columnData)) {
                $type = $columnData['type'];
                $data = $columnData['data'];
                switch ($type) {
                    case 'ul':
                        $columnData = $this->listRenderArrayToUl($data);
                    break;

                    case 'link':
                        $columnData = $this->linkRenderArrayToA($data);
                    break;

                    case 'keyValue':
                        $columnData = $this->kvRenderArrayToKeyValue($data); // or should this pass off to a KeyValue Renderer?
                    break;

                    case 'complexKeyValue':
                        //$columnData = $this->extractorComplexKeyValueArrayToUl($data);

                    // default:

                    //throw something?

                }
                $tdNode->appendChild($columnData);
            } else {
                $this->appendTextNode($tdNode, $columnData);
            }
            
            $this->appendToClass($tdNode, $tdClass);
            $trNode->appendChild($tdNode);
        }
        return $trNode;
    }
}
