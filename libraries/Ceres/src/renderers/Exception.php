<?php

namespace Ceres\Renderer;

use Ceres\Renderer\AbstractRenderer;
//use Ceres\Renderer\Html;
use Ceres\Exception\CeresException;

require_once( CERES_ROOT_DIR . '/src/renderers/Html.php');

class Exception extends Html {

    public function render(): string {
        return "something bad happened";
    }
}
