<?php
namespace Ceres\Util;

use Ceres\Exception\DataException;
use Ceres\Data;
use Error;
use ErrorException;

class DataUtilities {

    protected static $allOptions = [];
    protected static $optionsValues = [];
    protected static $currentValues = [];
    protected static $viewPackages = [];
    protected static $optionsEnums = [];
    protected static $propertyLabels =  [];

    /**
     * Looks at GET params for overrides coming from 
     * content creator
     * Or parse from shortcode options
     */

    static function overrideForOption($optionName) {
        if (isset($_GET[$optionName])) {
            return $_GET[$optionName];
        }
        return null;
    }

    static function skipArrayLevel(array $array, bool $returnFirst = true) :array {
        $returnArray = [];
        foreach(array_keys($array) as $key) {
            $value = $array[$key];
            if (is_array($value)) {
                if ($returnFirst) {
                    return $value;
                } else {
                    $returnArray[] = $value;
                }
            }
        return $returnArray;
        }
    }

    // $scope is ceres, {project_name}, {view_package_name}
    static function valueForOption(string $optionName, $scope = 'ceres') {
        self::setData();

        if(isset(self::$optionsValues[$optionName])) {
            $optionValues = self::$optionsValues[$optionName];
            // first, check if content creator has set an override
            $overrideValue = self::overrideForOption($optionName);
            if (!is_null($overrideValue)) {
                return $overrideValue;
            }
            if (!empty($optionValues['currentValue'])) {
                return $optionValues['currentValue'];
            }
            if (!empty($scope) && !empty($optionValues['defaults'][$scope])) {
                // @todo what happens if this is empty?
                return $optionValues['defaults'][$scope];
            }

            // no precision to the scope value wanted, so
            // bubble up the hierarchy of default values

            $viewPackageName = ''; //@todo: dig this up
            $projectName = ''; // @todo dig this up

            // @todo this could be handled w/out the multiple ifs somehow
            // to maintain the hierarchy in a more versatile way
            if (!empty($optionValues['defaults'][$viewPackageName])
                && !empty($viewPackageName)
            ) {
                return $optionValues['defaults'][$viewPackageName];
            }

            if (!empty($optionValues['defaults'][$projectName])
                && !empty($projectName)
            ) {
                return $optionValues['defaults'][$projectName];
            }    

            if (!empty($optionValues['defaults']['ceres'])) {
                return $optionValues['defaults']['ceres'];
            }

            // return false or throw a Ceres\Exception
        }
    }

    static function viewPackageArray(string $vpId) : array {
        self::setData();
        return self::$viewPackages[$vpId];
    }

    static function labelForViewPackage(string $vpId): string {
        self::setData();
        $vpData = self::$viewPackages[$vpId];
        return $vpData['label'];
    }

    static function defaultsForOption($optionName, $scope='ceres') {
        self::setData();

        return self::$optionsValues[$optionName]['defaults'][$scope];
    }

    static function allDefaultsForOption($optionName) {
        self::setData();
        return self::$optionsValues[$optionName]['defaults'];
    }

    static function enumValuesForOption($optionName, $scope = 'ceres') : array {
        self::setData();
        return self::$optionsEnums[$optionName][$scope];
    }

    static function allEnumValuesForOption($optionName): array {
        self::setData();
        return self::$optionsEnums[$optionName];
    }

    static function accessValuesForOption($option) : array {
        self::setData();
        return self::$allOptions[$option]['access'];

    }

    /**
     * shortArrayExport
     * 
     * Does PHP's var_export for an array, but uses new, shorter syntax
     *  
     * Thanks https://gist.github.com/Bogdaan/ffa287f77568fcbb4cffa0082e954022
     * 
     * @param array $array  the array to export
     * @param boolean $return whether to return the result or echo it directly. Default true
     * @return string|void
     */
    function shortArrayExport(array $array, bool $return = true) {
        $export = var_export($array, TRUE);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        if ((bool)$return) {
            return $export; 
        } else {
            echo $export;
        }
    }

    static function typeForOption($option, $scope = 'ceres') {
        self::setData();
        return self::$allOptions[$option]['type'];
    }

    static function descriptionForOption($option, $scope = 'ceres') {
        self::setData();
        return self::$allOptions[$option]['desc'];
    }

    static function labelForProperty($property, $scope='ceres') {
        // @todo: mods: first, see if @displayLabel is set in Extractors

        // @todo: wikidata can be built into the query, but still needs
        //a fallback if rdfs:label isn't present
        // wikidata SERVICE:label should be built into SPARQL query

        //ECDA and Thoreau make use of mods:@displayLabel
        //so I need a way to bail out to that, based on whether
        //a project does it's own thing
        // do that in the DataUtil::labelForoption
        self::setData();
        if (isset(self::$propertyLabels[$scope][$property])) {
            $label = self::$propertyLabels[$scope][$property];
        } else {
            // @todo fallback label and/or Exception
        }

        if(empty($label)) {
            return null; // actually have a fallback and/or exception
        }
        return $label;
    }

    static function userHasAccess($user, $optionName) {
        self::setData();
        $userRole = ''; //@todo: dig this up, or have wp something something something
        if (in_array($userRole, self::$allOptions[$optionName]['access'])) {
            return true;
        }

        return false; // or throw something?

    }

    // $pageId works for the contentCreator screen, where they get minted
    //      which means I'd be recording the overrides as currentValue
    // need what works on the admin screen
    // or just drop that to project scope
    //   'cuz having access to admin screen implies a project-wide access
    //   if a project-level person wants to change the specific page value, go to the page
    //   and fight it out with contentCreator
    //   currentValue would drop to the default for project
    //      implies contentCreator/page level inaccessible to project level on admin side
    //      implies lookup is page-specific, and $currentValue is set by contentCreator at
    //          that level
    //
    //   contentCreator side needs to send up the params to get the $currentValue
    static function mintCurrentValueId(string $optionName, mixed $pageId, mixed $currentValue) : string {
        return "id2";
    }

    /**
     * Gets an option as stored in WP's option table, e.g
     * `ceres_view_packages` or `siteurl`
     * 
     *
     * @param string $optionName
     * @return mixed
     */

    static function getWpOption(string $wpOptionName) {
        self::setData();

        if (function_exists('get_option') && get_option($wpOptionName) ) {
            return get_option($wpOptionName);
        } else {
            //save it all to a file in dev for now
            switch($wpOptionName) {
                case 'ceres_all_options':
                    return self::$allOptions;
                break;
                case 'ceres_options_values':
                    return self::$optionsValues;
                break;                    
                case 'ceres_view_packages':
                    return self::$viewPackages;
                break;                    
                case 'ceres_options_enums':
                    return self::$optionsEnums;
                break;                    
                case 'ceres_property_labels':
                    return self::$propertyLabels;
                break;
                case 'ceres_current_values':
                    return self::$currentValues;
                break;

                case 'site_url':
                    return "https://" . $_SERVER['SERVER_NAME'];
                break;
                default:
                
                    throw new DataException("Option $wpOptionName does not exist");
            }
        }
    }

    static function updateWpOption(string $wpOptionName, array $wpOptionData) {
        if (substr($wpOptionName, 0, 6) != 'ceres_') {
            throw new DataException("Cannot update a non-CERES wp option: $wpOptionName");
        }
        if (function_exists('update_option')) {
            update_option($wpOptionName, $wpOptionData);
        } else {
//save it all to a file in dev for now
            $optionDataJson = json_encode($wpOptionData);
            $fileName = CERES_ROOT_DIR . "/devscraps/data/$wpOptionName.json";
            
            file_put_contents($fileName, $optionDataJson);
            $fileName = CERES_ROOT_DIR . "/data/$wpOptionName.json";
            file_put_contents($fileName, $optionDataJson);
        }
    }

/**
 * for use to compare option names, vp names, etc to make
 * sure they are unique when set elsewhere
 * 
 */

    static function setData() {
        // todo: an environment variable for wp, dev, etc
//for real WP integration
        // self::$viewTemplates = get_option('ceres_view_templates');
        // self::$allOptions = get_option('ceres_all_options');
        // self::$optionValues = get_option('ceres_option_values');

        self::$viewPackages = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_view_packages.json'), true);
        self::$allOptions = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_all_options.json'), true);
        self::$optionsValues = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_options_values.json'), true);
        self::$propertyLabels = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_property_labels.json'), true);
        self::$optionsEnums = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_options_enums.json'), true);
        self::$currentValues = json_decode(file_get_contents(CERES_ROOT_DIR . '/data/ceres_current_values.json'), true);

//for dev/testing

        // self::$viewPackages = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_view_packages.json'), true);
        // self::$allOptions = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_all_options.json'), true);
        // self::$optionsValues = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_options_values.json'), true);
        // self::$propertyLabels = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_property_labels.json'), true);
        // self::$optionsEnums = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_options_enums.json'), true);
        // self::$currentValues = json_decode(file_get_contents(CERES_ROOT_DIR . '/devscraps/data/ceres_current_values.json'), true);

        // self::$viewPackages = Config\getViewPackages();
        // self::$allOptions = Config\getAllOptions();
        // self::$optionValues = Config\getOptionsValues();
        // self::$propertyLabels = Config\getPropertyLabels();
        // self::$optionsEnums = Config\getOptionsEnums();
    }

    /**
     * rebuildViewPackages
     * 
     * The scopes for R/E/Fs are kept separate for maintenance,
     * but get merged together in getViewPackages
     * 
     * With the move to json, need to rebuild the merges and update json
     * for updates
     *
     * @return void
     */
    static function rebuildViewPackages() {
        $vpsArray = self::$viewPackages = Data\getViewPackages();
        $ref = ['renderer', 'extractor', 'fetcher'];
        
        foreach ($vpsArray as $vpNameId => $vpData) {
            foreach ($vpData as $key => $data) {
                if (in_array($key, $ref)) {
                    foreach ($data as $class => $classData) {
                        $vpsArray[$vpNameId][$key][$class]['options'] = array_values( array_unique($classData['options']) );
                        
                    }
                }
            }
        }
        self::updateWpOption('ceres_view_packages', $vpsArray );
    }

    static function rebuildOptionsValues() {
        $data = Data\getOptionsValues();
        self::updateWpOption('ceres_options_values', $data);
    }

    static function rebuildOptionsEnums() {
        $data = Data\getOptionsEnums();
        self::updateWpOption('ceres_options_enums', $data);
    }

    static function rebuildPropertyLabels() {
        $data = Data\getPropertyLabels();
        self::updateWpOption('ceres_property_labels', $data);
    }

    static function rebuildAllOptions() {
        $data = Data\getAllOptions();
        self::updateWpOption('ceres_all_options', $data);
    }

    static function rebuildCurrentValues() {
        $data = Data\getCurrentValues();
        self::updateWpOption('ceres_current_values', $data);
    }

    static function rebuildAllWpOptions() {
        self::rebuildAllOptions();
        self::rebuildOptionsEnums();
        self::rebuildOptionsValues();
        self::rebuildPropertyLabels();
        self::rebuildViewPackages();
        self::rebuildCurrentValues();

    }

    static function updateCeresValue($id, $value) {
        self::$currentValues[$id]['value'] = $value;

    }

    /**
     * getOptionsFromSubmission
     * 
     * lookup user-submitted options, e.g. from a WP submission (e.g. shortcode) or GET params
     *
     * @return void
     */

     //@todo how to write for an arbitrary signature??

    static function getOptionsFromSubmission() {
        //WP. see https://github.com/WordPress/wordpress-develop/blob/6.1/src/wp-includes/shortcodes.php#L566

        if (function_exists('shortcode_atts')) {
            // I _think_ $wpPairs is just the list of ALL option=>default pairs,
            // but I also think I'm handling my defaults separately, so ignorable?

            //$shortcode is the name of the shortcode if it needs somethings special,
            // but that's to be avoided

            $options = shortcode_atts($wpPairs, [], $shortcode);
            return $options;
        }

        //last fallback to GET params
        return $_GET;
    }

    /**
     * Undocumented function
     *
     * see https://stackoverflow.com/a/11206244/752724
     * 
     * @param [type] $errno
     * @param [type] $errstr
     * @return void
     */
    static function suppressWarnings(    
        int $errno,
        string $errstr,
        ?string $errfile,
        ?int $errline) {
    }

}
