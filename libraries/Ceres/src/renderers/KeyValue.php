<?php
namespace Ceres\Renderer;

use Ceres\Renderer\Html;
use Ceres\Util\DataUtilities as DataUtil;
use Ceres\Util\StringUtilities as StringUtil;
use DOMElement;

class KeyValue extends Html {
    protected string $templateFileName = 'keyvalue.html';

    public function build(): void {
        foreach($this->renderArray as $rowData) {
            $row = $this->buildRow($rowData);
            $this->containerNode->appendChild($row);
        }
    }

    public function buildRow($rowData): DOMElement {
        $row = $this->htmlDom->createElement('div');
        $this->appendToClass($row, 'ceres-keyvalue-row');
        $keyNode = $this->htmlDom->createElement('span');
        $this->appendToClass($keyNode, 'ceres-key');
        $valueNode = $this->htmlDom->createElement('span');
        $this->appendToClass($valueNode, 'ceres-value');
        $separatorNode = $this->htmlDom->createElement('span');
        $this->appendToClass($separatorNode, 'ceres-separator');

        $this->appendTextNode($keyNode, $rowData['key']);
        $this->appendTextNode($valueNode, $rowData['value']);
        $separator = $this->getRendererOptionValue('separator');
        $this->appendTextNode($separatorNode, $separator);

        $row->appendChild($keyNode);
        $row->appendChild($separatorNode);
        $row->appendChild($valueNode);
        return $row;
    }
}
