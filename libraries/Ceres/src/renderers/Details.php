<?php
namespace Ceres\Renderer;

use Ceres\Renderer\Html;
use DOMNode;

//only here because I don't have a real autoloader
require_once(CERES_ROOT_DIR . '/src/renderers/Html.php');
class Details extends Html {
    protected DOMNode $detailsNode;
    protected DOMNode $summaryNode;
    protected string $templateFileName = 'details.html';

    public function __construct() {
        parent::__construct();
        $this->summaryNode = $this->htmlDom->getElementById('ceres-summary');
        $this->detailsNode = $this->htmlDom->getElementById('ceres-details');
    }

    public function build(): void {
        $mainRenderArray = $this->renderArray['data']['main'];
        $this->buildSummaryNode($mainRenderArray);
        $secondaryRenderArray = $this->renderArray['data']['secondary'];
        $this->buildDetailsNode($secondaryRenderArray);
    }

    protected function buildSummaryNode($mainRenderArray): void {
        $innerNode = $this->handleInnerRenderArray($mainRenderArray);
        $this->summaryNode->appendChild($innerNode);
    }

    protected function buildDetailsNode($secondaryRenderArray): void {
        $innerNode = $this->handleInnerRenderArray($secondaryRenderArray);
        $this->detailsNode->appendChild($innerNode);
    }
}
