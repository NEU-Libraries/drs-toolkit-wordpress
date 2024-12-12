<?php

namespace Ceres\Renderer;

use DOMNode;

require_once(CERES_ROOT_DIR . '/src/renderers/Html.php');
class Card extends Html {

    protected DOMNode $mainNode;
    protected DOMNode $secondaryNode;
    protected string $templateFileName = 'card.html';

    public function __construct() {
        parent::__construct();
        $this->mainNode = $this->htmlDom->getElementById('ceres-card-main');
        $this->secondaryNode = $this->htmlDom->getElementById('ceres-card-secondary');
    }

    public function build(): void {
        $mainRenderArray = $this->renderArray['data']['main'];
        $this->buildMainNode($mainRenderArray);
        $secondaryRenderArray = $this->renderArray['data']['secondary'];
        $this->buildSecondaryNode($secondaryRenderArray);
    }

    protected function buildMainNode($mainRenderArray): void {
        $innerNode = $this->handleInnerRenderArray($mainRenderArray);
        $this->mainNode->appendChild($innerNode);
    }

    protected function buildSecondaryNode($secondaryRenderArray): void {
        $innerNode = $this->handleInnerRenderArray($secondaryRenderArray);
        $this->secondaryNode->appendChild($innerNode);
    }
}
