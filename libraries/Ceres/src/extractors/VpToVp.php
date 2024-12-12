<?php

namespace Ceres\Extractor;

use Ceres\Extractor\AbstractExtractor;
use Ceres\Util\DataUtilities as DataUtil;
use Ceres\Exception\DataException;

class VpToVp extends AbstractExtractor {

    public function __construct($sourceData) {
        parent::__construct($sourceData);
        $this->sourceData = DataUtil::getWpOption('ceres_view_packages');

    }

    public function extract(): void {
        $renderArray = [];
        if (isset($this->extractorOptions['vpNameId'])) {
            $vpArray = $this->sourceData[$this->extractorOptions['vpNameId']];
        } else {
            throw new DataException("Invalid vpNameId option");
        }
        
        foreach ($vpArray as $property => $value) {
            if (is_array($value)) {
                $value = print_r($value, true);
            }
            if (is_null($value)) {
                $value = 'null';
            }
            $renderArray[] = ['key' => $property,
                               'value' => $value
                              ];

        }
        $this->renderArray = $renderArray;
    }
}