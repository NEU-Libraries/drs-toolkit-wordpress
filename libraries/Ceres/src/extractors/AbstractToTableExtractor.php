<?php
namespace Ceres\Extractor;

abstract class AbstractToTableExtractor extends AbstractExtractor {

    protected ?array $headings = null; //@todo can I assume all tables have header rows?


    protected function setHeadings(array $headingsArray) {
        $this->headings = $headingsArray;
    }

    /**
     * 
     * For actions that must happen ONLY after I can lose the var-binding connection
     *
     * @return void
     */
    protected function preExtract():void {
        //@todo make varsToRemoveArray an option
        $this->removeHeadings($headingsToRemoveArray = null);
    }

    /**
     * mapValueToLabel
     * 
     * Take a value, generally from $this->headerDataToRender, and map it onto
     * a new, usually prettier, value for the Renderer to use
     * 
     * @todo worry about whether this should be in a TableRenderer
     *
     * @param string $value
     * @param string|null $valueLabelMapping
     * @return string
     */
    protected function mapValueToLabel(string $value, ?string $valueLabelMapping = null): string {
        //originalSequence can come from $this->vars
        //labelMapping can start with $this->vars
        //should ultimately be a map based on {var} and {var}Label
//echo "<h1>mapValueToLabel $value: STT</h1>";
        //from people.rq

        if (is_null($valueLabelMapping)) {
            $valueLabelMapping = $this->valueForExtractorOption('extractorValueLabelMappingFilePath');
        }
        if (is_null($valueLabelMapping)) {
            return $value;
        } else {
            $valueLabelMapping = json_decode(file_get_contents($valueLabelMapping), true);
        }



//@todo remove this!        
        $valueLabelMapping = [
            "langCode"  =>  "Language Code",
            "qid"  =>  "ID for use elsewhere", 
            "personLabel"  =>  null,
            "personDescription"  =>  "Description",
            "createdCollections"  =>  "Collections Created",
            "foundedOrganizations" =>  "Organizations Founded",
            "maintainsCollections" =>  "Collections Maintained",
            "donatedCollections"  =>  "Collections Donated",
            "donorPropLabel" =>  null,
            "creatorPropLabel" =>  null,
            "maintainerPropLabel" =>  null,
            "founderPropLabel" =>  null,
            "name"  =>  "Name",
            "namePropLabel"  =>  null,
            "officialWebsite"  =>  "Website",
            "officialWebsitePropLabel"  =>  null,
            "emailAddress"  =>  "Email",
            "emailAddressPropLabel"  =>  null
        ];
        
        if (key_exists($value, $valueLabelMapping)) {
                if (is_null($valueLabelMapping[$value])) {
                    return $value;
                }
                return $valueLabelMapping[$value];    
            } else {
                return $value;
            }
    }


/*
    protected function setBindings():void {
        $bindings = $this->sourceData['results']['bindings'];
        $bindings = $this->preSetBindings($bindings);
        $this->bindings = $this->sourceData['results']['bindings'];
        $this->postSetBindings();
    }
*/
    protected function reorderHeadings(?array $reorderMapping = null) {
//echo "<h3>reorderVars: AbsSplExt</h3>";
//$this->setExtractorOptionValue('no', 'whatevs');
//print_r($this->extractorOptions);
//echo "<h1>dafuq</h1>";
        if (is_null($reorderMapping)) {
            //echo "<h4>isnull: absSpqExt</h4>";
            $reorderMapping = $this->valueForExtractorOption('extractorReorderMappingFilePath');
        }
        if (is_null($reorderMapping)) {
            //echo "<h4>isnull: absSpqExt</h4>";
            return; //@todo 
            
        } else {
            $reorderMapping = json_decode(file_get_contents($reorderMapping), true);
        }
//echo "<h4>after ifs: AbsSplExt</h4>";
        $reordered = [];

        //go with the mapping provided as the new sequence,
        //but split out the diff between the two arrays and
        //slap the remainder onto the end
        $unreordered = array_diff($this->headings, $reorderMapping);
        $reordered = array_merge($reorderMapping, $unreordered);
        $this->headings = $reordered;
    }

    /**
     * removeVars
     *
     * remove vars so R'r doesn't have to deal with more than it cares about
     * must happen BEFORE and processing that is var-binding dependent happens
     * 
     * @param array $rowToRender a row that is part of the data to render
     * @param array $toRemoveArray a supplied array of the $vars to be removed
     * @return void
     */
    protected function removeHeadings(?array $headingsToRemoveArray = null): void {
        //@todo make $toRemoveArray an ExtratorOption? 2023-04-06 18:01:26
        //check if a $toRemoveArray exists (as an ExtractorOption) and run conditionally
        //as a postSetDataToRender?
        // this is ahead of refactoring the reordering???
        
        if (is_null($headingsToRemoveArray)) {
            $headingsToRemoveArray = $this->valueForExtractorOption('extractorRemoveVarsFilePath');
        }
        if (is_null($headingsToRemoveArray)) {
            return;

        } else {
            $headingsToRemoveArray = json_decode(file_get_contents($headingsToRemoveArray), true);
        }

        foreach($this->headings as $index => $var) {
            if(in_array($var, $headingsToRemoveArray)) {
                unset($this->headings[$index]);
            }
        }
    }

}