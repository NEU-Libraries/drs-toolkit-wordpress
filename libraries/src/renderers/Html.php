<?php
namespace Ceres\Renderer;

use Ceres\Util\DataUtilities;
use DOMDocument;
use DOMNode;
use DOMElement;
use DOMXPath;

class Html extends AbstractRenderer {

    protected DOMNode $containerNode;
    protected DOMXPath $xPath;
    protected DOMDocument $htmlDom;
    protected string $templateFileName = 'html.html';

    public function __construct() {
        $this->setHtmlDom();
        $this->xPath = new DOMXPath($this->htmlDom);
        $this->setContainerNode();

        parent::__construct();
    }

    /**
     * renderFullHtml
     *
     * Renders the full HTML from DOCTYPE to closing </html>
     * Mostly used for dev/debuging
     * 
     * @return void
     */
    public function renderFullHtml(): string {
        $this->build();
        $this->stripCeresIds();
        return $this->htmlDom->saveHtml();
    }

    public function render(): string {
        $this->build();
        // only strip the ids related to ceres, marked by the string
        // `ceres`, just before rendering so any ids can be there for processing
        // but need to go to avoid multiple ids on one page
        $this->stripCeresIds();
        return $this->toHtmlString();
    }

    public function build(): void {
        $text = $this->getRendererOptionValue('text');
        $this->appendTextNode($this->containerNode, $text);
    }

    public function toHtmlString(?DOMNode $node = null): string {
        if (is_null($node)) {
            $node = $this->containerNode;
        }
        return $this->htmlDom->saveHtml($node);
    }

    public function setHtmlDom(): void {
        $this->htmlDom = new DOMDocument();

        //suppress warnings about tag usage
        set_error_handler(["Ceres\Util\DataUtilities", 'suppressWarnings'], E_WARNING);
        $this->htmlDom->loadHtmlFile(CERES_ROOT_DIR . "/data/rendererTemplates/$this->templateFileName");
        restore_error_handler();
    }

    protected function setContainerNode() {
        $this->containerNode = $this->htmlDom->getElementById('ceres-container');
    }

    protected function getContainerNode() : DOMNode {
        return $this->containerNode;
    }

    protected function appendToClass(DOMElement $node, $value ):void {
        $class = $node->getAttribute('class');
        $class = $class .= " $value";
        $node->setAttribute('class', $class);
    }

    protected function appendTextNode(DOMNode $node, string $text, ?string $htmlElement = null) {
        $textNode = $this->htmlDom->createTextNode($text);
        if (is_null($htmlElement)) {
            $node->appendChild($textNode);
        } else {
            $textContainerNode = $this->htmlDom->createElement($htmlElement);
            $textContainerNode->appendChild($textNode);
            $node->appendChild($textContainerNode);
        }
    }

    protected function stripCeresIds(): void {
        $xpath = "//div[contains(@id,'ceres')]";
        $nodes = $this->xPath->query($xpath, $this->htmlDom);
        foreach ($nodes as $node) {
            $node->removeAttribute('id');
        }
    }

    protected function setGlobalAttributes(array $globalAtts = [], DOMNode $node) {
        foreach($globalAtts as $att => $value) {
            $attributeNode = $this->htmlDom->createAttribute($att);
            $attributeNode->value = $value;
            $node->appendChild($attributeNode);
        }
    }

/* mini-renderers to build really simple HTML elements */

    protected function imgRenderArrayToImg(array $renderArray): DOMNode {
        $imgNode = $this->htmlDom->createElement('img');
        if (isset($renderArray['globalAtts'])) {
            $this->setGlobalAttributes($renderArray['globalAtts'], $imgNode);
            unset($renderArray['globalAtts']);
        }
        return $imgNode;
    }

    protected function linkArrayToA(array $linkData) : DOMNode {
        $aNode = $this->htmlDom->createElement('a');
        if (isset($renderArray['globalAtts'])) {
            $this->setGlobalAttributes($linkData['globalAtts'], $aNode);
            unset($renderArray['globalAtts']);   
        }
        
        $aNode->setAttribute('href', $linkData['url']);
        $this->appendTextNode($aNode, $linkData['label']);
        return $aNode;
    }

    protected function linkRenderArrayToA(array $linkData) : DOMNode {
        $aElement = $this->htmlDom->createElement('a');
        $aElement->setAttribute('href', $linkData['url']);
        $this->appendTextNode($aElement, $linkData['label']);
        return $aElement;
    }

    protected function enumRenderArrayToSelect(array $enumOptions) : DOMNode {
        $selectNode = $this->htmlDom->createElement('select');
        foreach ($enumOptions as $option) {
            $optionNode = $this->htmlDom->createElement('option');
            $this->appendTextNode($optionNode, $option);
            $selectNode->appendChild($optionNode);
        }
        return $selectNode;
    }

    protected function listRenderArrayToUl(array $renderArray): DOMNode {
        $ulNode = $this->htmlDom->createElement('ul');
        if (isset($renderArray['globalAtts'])) {
            $this->setGlobalAttributes($renderArray['globalAtts'], $ulNode);
            unset($renderArray['globalAtts']);
        }
        
        foreach($renderArray as $liText) {
            $liNode = $this->htmlDom->createElement('li');
            $this->appendTextNode($liNode, $liText);
            $ulNode->appendChild($liNode);
        }
        return $ulNode;
    }

    protected function listRenderArrayToOl(array $renderArray) : DOMNode {
        $olNode = $this->htmlDom->createElement('ol');
        if (isset($renderArray['globalAtts'])) {
            $this->setGlobalAttributes($renderArray['globalAtts'], $olNode);
            unset($renderArray['globalAtts']);  
        }
        
        foreach($renderArray as $liText) {
            $liNode = $this->htmlDom->createElement('li');
            $this->appendTextNode($liNode, $liText);
            $olNode->appendChild($liNode);
        }
        return $olNode;
    }

    protected function varcharToInput(?string $text) : DOMNode {
        $inputNode = $this->htmlDom->createElement('input');
        $inputNode->setAttribute('type', 'text');
        $inputNode->setAttribute('value', $text);
        return $inputNode;
    }

    protected function textRenderArrayToTextArea(string $text): DOMNode {
        $textAreaNode = $this->htmlDom->createElement('textarea');
        $this->appendTextNode($textAreaNode, $text);
        return $textAreaNode;
    }

    protected function textRenderArrayToText(array $renderArray): DOMNode {
        $textNode = $this->htmlDom->createTextNode($renderArray['data']);
        if (isset($renderArray['subtype'])) {
            $htmlElement = $renderArray['subtype'];
            $innerNode = $this->htmlDom->createElement($htmlElement);
            $innerNode->appendChild($textNode);
        } else {
            return $textNode;
        }
    }

    protected function boolToCheckbox(?bool $value) {

    }

    protected function dlRenderArrayToDl(array $renderArray): DOMNode {
        $dlNode = $this->htmlDom->createElement('dl');
        foreach($renderArray as $dtDdGroup) {
            foreach($dtDdGroup['dts'] as $dt) {
                $dtNode = $this->htmlDom->createElement('dt');
                $innerDtNode = $this->handleInnerRenderArray($dt);
                $dtNode->appendChild($innerDtNode);
                $dlNode->appendChild($dtNode);
            }
            foreach($dtDdGroup['dds'] as $dd) {
                $ddNode = $this->htmlDom->createElement('dd');
                $innerDdNode = $this->handleInnerRenderArray($dd);
                $ddNode->appendChild($innerDdNode);
                $dlNode->appendChild($ddNode);
            }
        }
        return $dlNode;
    }

    protected function handleInnerRenderArray(array $renderArray): DOMNode {
        switch ($renderArray['type']) {
            case 'text':
                $innerNode = $this->textRenderArrayToText($renderArray);
                break;
            case 'list':
                if (isset($renderArray['subtype'])) {
                    switch ($renderArray['subtype']) {
                        case 'ul':
                            $innerNode = $this->listRenderArrayToUl($renderArray['data']);
                            break;
            
                        case 'ol':
                            $innerNode = $this->listRenderArrayToOl($renderArray['data']);
                            break;

                        default:
                            $innerNode = $this->listRenderArrayToUl($renderArray['data']);
                    }
                } else {
                    $innerNode = $this->listRenderArrayToUl($renderArray['data']);
                    return $innerNode;
                }
                break;
            case 'dl':
                $innerNode = $this->dlRenderArrayToDl($renderArray['data']);
                break;

            case 'img':
                $innerNode = $this->imgRenderArrayToImg($renderArray['data']);
                break;

            case 'link':

                break;

            case 'card':
                if (isset($renderArray['subtype'])) {
                    switch($renderArray['subtype']) {
                        case 'details':
                            $subRenderer = $this->spawnSubRenderer('Ceres\Html\Details');
                            $subRenderer->setRenderArrayFromArray();
                            $subRenderer->build();
                            $innerNode = $subRenderer->renderNode();
                        break;

                        default:
                            $subRenderer = $this->spawnSubRenderer(('Ceres\Html\Card'));
                            $subRenderer->setRenderArrayFromArray();
                            $subRenderer->build();
                            $innerNode = $subRenderer->renderNode();
                    } 
                } else {
                    $subRenderer = $this->spawnSubRenderer(('Ceres\Html\Card'));
                    $subRenderer->setRenderArrayFromArray();
                    $subRenderer->build();
                    $innerNode = $subRenderer->renderNode();
                }
            case 'dl':
                if (isset($renderArray['subtype'])) {
                    switch($renderArray['subtype']) {
                        case 'keyValue':

                            break;
                        default:

                    }
                } else {

                }
            }
            return $innerNode;
    }
}
