<?php
namespace Ceres\Documentation;
 
use Ceres\Renderer\Html\Details as Details;
use Ceres\Util\DataUtilities as DataUtil;

require_once('/var/www/html/Ceres/config/ceresSetup.php');

$allVpData = DataUtil::getWpOption('ceres_view_packages');
DataUtil::rebuildAllWpOptions();


$options = DataUtil::getWpOption('ceres_all_options');

$testOption1 = $options['extractorReorderMappingFilePath'];
$testOption2 = $options['extractorRemoveVarsFilePath'];

$dlRenderArray = [];

$dlRenderArray[] = extractOptionToDlRenderArray($testOption1);
//$dlRenderArray[] = extractOptionToDlRenderArray($testOption2);


$detailsCardArray = extractOptionToDetailsCardArray($testOption1);

function extractArrayToListRenderArray($array) {
    $listRenderArray = ['type' => 'list',
                        'data' => $array,
    ];
    return $listRenderArray;
}

function extractOptionToDlRenderArray($option) {
    $dlRenderArray['type'] = 'dl';
    $dlRenderArray['subtype'] = "keyValue";

    $optionDetailsMap = [
        'Description' => $option['desc'],
        'Type' => $option['type'],
        'Defaults' => $option['defaults'],
        'Access' => $option['access'],
    ];

    foreach($optionDetailsMap as $optionSettingLabel => $value) {
        // The extra array for the $detail and $value is
        // not needed here. For single string values, the 
        // simple string will suffice. But, for multiple dt's
        // or dd's, the array is used, so it's an example
        // of the complete usage

        $valueType = gettype($value);
        switch ($valueType) {
            case 'string':
                $value = extractTextToTextRenderArray($value);
                break;
            case 'array':
                $value = extractArrayToListRenderArray($value);
                break;

        }

        switch($optionSettingLabel) {
            case 'Access':
                if (is_string($optionSettingLabel)) {
                    $optionSettingLabel = extractTextToTextRenderArray($optionSettingLabel);
                }

                $dlRenderArray['data'][] = [
                    'dts' => [
                                $optionSettingLabel,
                             ],
                    'dds' => [ $value ],
                ];
            break;

            default:
                if (is_string($optionSettingLabel)) {
                    $optionSettingLabel = extractTextToTextRenderArray($optionSettingLabel);
                }
                $dlRenderArray['data'][] = [
                    'dts' => [$optionSettingLabel],
                    'dds' => [$value]
                ];
        }
    }
    return $dlRenderArray;
}

function extractTextToTextRenderArray(string $text, ?string $htmlElement = null) {
    $textRenderArray = ['type' => 'text',
                        'data' => $text,
                        ];
    if (! is_null($htmlElement)) {
        $textRenderArray['subtype'] = $htmlElement;
    }
    return $textRenderArray;
}

function extractOptionToDetailsCardArray($option) {
    $detailsCardRenderArray = ['type' => 'card',
                               'subtype' => 'details'
    ];
    $detailsCardRenderArray['data'] = [
        'main' => extractTextToTextRenderArray($option['label']),
        'secondary' => extractOptionToDlRenderArray($option)
    ];
    return $detailsCardRenderArray;
}

$detailsRenderer = new \Ceres\Renderer\Details;
$detailsRenderer->setRenderArrayFromArray($detailsCardArray);

$cardRenderer = new \Ceres\Renderer\Card;
$cardRenderer->setRenderArrayFromArray($detailsCardArray);

//$detailsRenderer->build(); RENDER CALLS BUILD()
echo $detailsRenderer->render();

echo $cardRenderer->render();




