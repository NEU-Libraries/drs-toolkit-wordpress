<?php
namespace Ceres\Data;

use Ceres\Util\DataUtilities;
use Ceres\Util\StringUtilities;

/**
 * Algorithm for minting/looking up ids
 *
 * Until we move everything over to real db tables, ids are minted in the editing area
 * and set back to the server. They contain 3 parts
 *
 * 1. the optionName
 * 2. the pageId it is set on
 * 3. a hash of the value
 *
 * This means that no two options on the same page can have the same value
 *
 * @TODO: do I need a way to specify the page id from the admin
 */

function getCurrentValues() {
    $ceresCurrentValues = [
        // ['optionName' =>, 'value' =>],
        'id1' => ['optionName' => 'caption', 'value' => 'Chinatown Collections'],
        'id2' => ['optionName' => 'trClass', 'value' => 'classy-class'],
        'id3' => ['optionName' => 'trClass', 'value' => 'classless-class'],




    ];

    return $ceresCurrentValues;
}

function getAllOptions() {
    $ceresAllOptions = [
        'extractorReorderMappingFilePath' => [
            'label' => 'Reorder Mapping File',
            'desc' => 'The path to a JSON file for SPARQL-based view packages/extractors to use for reording the data rendering',
            'access' => ['coder'],
            'type' => 'FilePath',
            'defaults' => 'from optionsValues array',
            'shortcode' => 'extractor_reorder_mapping_file_path',
            'appliesTo' => 'extractors',
        ],

        'extractorRemoveVarsFilePath' => [
            'label' => 'Remove Vars File',
            'desc' => 'The path to a JSON file for SPARQL-based view packages/extractors to use for removing the data rendering',
            'access' => ['coder'],
            'type' => 'FilePath',
            'defaults' => 'from optionsValues array',
            'shortcode' => 'extractor_remove_vars_file_path',
            'appliesTo' => 'extractors',
        ],

        'extractorValueLabelMappingFilePath' => [
            'label' => 'Value/Label Mapping File',
            'desc' => 'The path to a JSON file for SPARQL-based view packages/extractors to use to make prettier labels',
            'access' => ['coder'],
            'type' => 'FilePath',
            'defaults' => 'from optionsValues array',
            'shortcode' => 'extractor_value_label_mapping_file_path',
            'appliesTo' => 'extractors',

        ],


        'fetchLocalData' => [
            'label' => 'Fetch Local Data',
            'desc'    => 'Used on conjunction with `localResponseDataPath` to mimic a live query',
            'access' => ['coder'],
            'type'    => 'bool',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetch_local_data',
            'appliesTo' => 'fetchers',
        ],

        'localResponseDataPath' => [
            'label' => 'Local Response Data Path',
            'desc'    => 'Path to a locally-saved copy of a Fetcher response',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'local_response_data_path',
            'appliesTo' => 'fetchers',

        ],

        'bounceBack' => [
            'label' => 'Bounce data back',
            'desc'    => 'Bounce the data received back without further processing',
            'access' => ['coder'],
            'type'    => 'bool',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'bounce_back',
            'appliesTo' => 'renderers',
        ],
        'rqFile' => [
            'label' => 'rqFile for query',
            'desc' => 'Path to the file containing the query', // needs coder-level documentation
            'access' => ['coder'],
            'type' => 'FilePath',
            'defaults' => 'from optionsValues array',
            'notes' => "The path is relative to CERES_ROOT_DIR, like `/data/{rqFiles | sqlFiles}/{your query}`",
            'shortcode' => 'rq_file',
            'appliesTo' => 'fetchers'
        ],
        'captionClass' => [
            'label' => 'Caption',
            'desc'    => 'Class to use for a table &lt;caption>',
            'access' => ['projectOwner', 'coder', 'contentCreator'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'caption_class',
            'appliesTo' => 'renderers',
        ],

        'text' => [
            'label' => 'Text',
            'desc'    => 'Plain Text to display',
            'access' => ['projectOwner', 'coder', 'contentCreator'],
            'type'    => 'text',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'text',
            'appliesTo' => 'renderers',
        ],

        'altTextProp' => [
            'label'   => 'Property to use for alt text',
            'desc'    => '',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'alt_text_prop',
            'appliesTo' => 'renderers',
        ],
        'float' => [
            'label'   => 'Float',
            'desc'    => 'How an element should float around text',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'enum',
            'defaults' => '',
            'notes' => '',
            'shortcode' => 'float',
            'appliesTo' => 'renderers',
        ],
        'caption' => [
            'label'   => 'Table caption',
            'desc'    => 'Caption text for an HTML table',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'text',
            'defaults' => '',
            'notes' => '',
            'shortcode' => 'caption',
            'appliesTo' => 'renderers',
        ],


        'extractorMetadataFilterBy' => [
            'label'   => 'Filter Metadata By',
            'desc'    => 'A property in the metadata used to filter metadata results for display',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_metadata_filter_by',
            'appliesTo' => 'extractors',
        ],


        'extractorResourcesFilterBy' => [
            'label'   => 'Filter Resources By',
            'desc'    => 'A property in the metadata used to filter search results',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_resources_filter_by',
            'appliesTo' => 'extractors',

        ],
        'extractorMetadataSortBy' => [
            'label'   => 'Sort By Metadata',
            'desc'    => 'How to sort metadata fields for display',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => '',
            'appliesTo' => 'extractors',

        ],
        'extractorResourcesSortBy' => [
            'label'   => 'Sort resources by',
            'desc'    => 'The property to use for sorting results',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_resource_sort_by',
            'appliesTo' => 'extractors',

        ],
        'extractorMetadataSortOrder' => [
            'label'   => 'Metadata sort order',
            'desc'    => 'The order to sort metadata by, e.g. asc or desc',
            'access' => ['coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_metadata_sort_order',
            'appliesTo' => 'extractors',

        ],
        'extractorResourcesSortOrder' => [
            'label'   => 'Resources Sort Order',
            'desc'    => 'The order to sort results by, e.g. asc or desc',
            'access' => ['coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_resources_sort_order',
            'appliesTo' => 'extractors',

        ],

        // @todo: remove this for extractor?
        'extractorMetadataSortByProperty' => [
            'label'   => 'Sort Metadata By Property',
            'desc'    => 'The metadata property to use for... ',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => 'Maybe remove this for Extractors',
            'shortcode' => 'extractor_metadata_sort_by_property',
            'appliesTo' => 'extractors',

        ],
        'extractorResourcesSortByProperty' => [
            'label'   => 'Sort Resources By Property',
            'desc'    => 'Property to use when sorting results',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_resources_sort_by_property',
            'appliesTo' => 'extractors',

        ],
        // @todo: do I need this?
        'extractorGroupBy' => [
            'label'   => 'Group By',
            'desc'    => 'Property to use for grouping ',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => 'Needed?',
            'shortcode' => '',
            'appliesTo' => 'extractors',

        ],
        'extractorMetadataToShow' => [
            'label'   => 'Metadata To Show',
            'desc'    => 'The specific metadata properties to display',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'extractor_group_by',
            'appliesTo' => 'extractors',

        ],
        'fetcherMetadataToShow' => [
            'label'   => 'Metadata To Show',
            'desc'    => 'The specific metadata properties to display',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_metadata_to_show',
            'appliesTo' => 'fetchers',

        ],
        'metadataToShow' => [
            'label'   => 'Metadata To Show',
            'desc'    => 'The specific metadata properties to display',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'metadata_to_show',
            'appliesTo' => 'renderers',

        ],
        'fetcherGroupBy' => [
            'label'   => 'Group by',
            'desc'    => 'Property to use for grouping results',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_group_by',
            'appliesTo' => 'fetchers',

        ],
        'fetcherFilterBy' => [
            'label'   => 'Filter by',
            'desc'    => 'Property to use for filtering results',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_filter_by',
            'appliesTo' => 'fetchers',

        ],
        'fetcherSortBy' => [
            'label'   => 'Sort by',
            'desc'    => 'Property to use for sorting results',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_sort_by',
            'appliesTo' => 'fetchers',

        ],
        'fetcherSortOrder' => [
            'label'   => 'Sort order',
            'desc'    => 'The order with which to sort results',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_sort_order',
            'appliesTo' => 'fetchers',

        ],
        'fetcherSortByProperty' => [
            'label'   => 'Sort by property',
            'desc'    => 'The property to use for sorting results',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'fetcher_sort_by_property',
            'appliesTo' => 'fetchers',

        ],
        'resourceLinkProp' => [
            'label'   => 'Resource Link Property',
            'desc'    => 'The property to use as the link back to the original resource',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'resource_link_prop',
            'appliesTo' => 'fetchers',

        ],
        'mediaLinkProp' => [
            'label'   => 'Media Link Property',
            'desc'    => 'The property to use as the link back to the original media',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'media_link_prop',
            'appliesTo' => 'fetchers',

        ],
        'mediaUriProp' => [
            'label'   => 'Media URI Property',
            'desc'    => 'The URI to use for displaying media',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'media_uri_prop',
            'appliesTo' => 'fetchers',

        ],
        'thClass' => [
            'label'   => 'Table Head Class Name',
            'desc'    => 'The CSS class to apply to &lt;th> elements',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'th_class',
            'appliesTo' => 'renderers',

        ],
        'tableClass' => [
            'label'   => 'Table Class Name',
            'desc'    => 'The CSS class to apply to &lt;table> elements',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'table_class',
            'appliesTo' => 'renderers',

        ],
        'tdClass' => [
            'label'   => 'Table Data Class Name',
            'desc'    => 'The CSS class to apply to &lt;td> elements',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'td_class',
            'appliesTo' => 'renderers',

        ],

        'trClass' => [
            'label'   => 'Table Row Class Name',
            'desc'    => 'The CSS class to apply to &lt;tr> elements',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'tr_class',
            'appliesTo' => 'renderers',

        ],
        'theadClass' => [
            'label'   => 'Table Head Class Name',
            'desc'    => 'The CSS class to apply to &lt;thead> elements',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'thead_class',
            'appliesTo' => 'renderers',

        ],
        'firstRowIsHeader' => [
            'label'   => 'First table row is a header',
            'desc'    => 'Treat the first row of data as a table header data',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'bool',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'first_row_is_header',
            'appliesTo' => 'renderers',
        ],

        'getAll' => [
            'label'   => 'Get All',
            'desc'    => 'Whether to get all resources matching the query, or force pagination of the queries for rolling loads',
            'access' => ['coder'],
            'type'    => 'bool',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'get_all',
            'appliesTo' => 'fetchers',

        ],
        'responseFormat' => [
            'label'   => 'Response Format',
            'desc'    => 'The data format the API should return',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'response_format',
            'appliesTo' => 'fetchers',

        ],
        'perPage' => [
            'label'   => 'Per Page',
            'desc'    => 'The number of results for each page returned by the API',
            'access' => ['coder'],
            'type'    => 'int',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'per_page',
            'appliesTo' => 'fetchers',

        ],
        'startPage' => [
            'label'   => 'Start Page',
            'desc'    => 'The start page of results from the API',
            'access' => ['coder'],
            'type'    => 'int',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'start_page',
            'appliesTo' => 'fetchers',

        ],
        'resourceIds' => [
            'label'   => 'Resource Ids',
            'desc'    => 'The resource ids to return',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'resource_ids',
            'appliesTo' => 'fetchers',

        ],
        'endpoint' => [
            'label'   => 'Endpoint',
            'desc'    => 'The API endpoint URI',
            'access' => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "??should this be private property on Fetchers??",
            'shortcode' => 'endpoint',
            'appliesTo' => 'fetchers',

        ],
        'searchType' => [
            'label'   => 'Search Type',
            'desc'    => 'The type of search to use for the specific API, e.g. search vs item in DRS',
            'access'  => ['coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'search_type',
            'appliesTo' => 'fetchers',
        ],
        'thumbnailSize' => [
            'label'   => 'Thumbnail Size',
            'desc'    => 'The size of the thumbnail to use',
            'access' => ['contentCreator', 'projectOwner', 'coder'],
            'type'    => 'enum',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => 'This will vary based on API result structure',
            'shortcode' => 'thumbnail_size',
            'appliesTo' => 'renderers',

        ],
        'separator' => [
            'label'   => 'Separator',
            'desc'    => 'The separator character to use for key/value pairs',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'separator',
            'appliesTo' => 'renderers',

        ],
        'keyClass' => [
            'label'   => 'Key Class',
            'desc'    => 'A CSS class to apply to keys in rendering Key-Value tables',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'key_class',
            'appliesTo' => 'renderers',

        ],
        'valueClass' => [
            'label'   => 'Value Class',
            'desc'    => 'A CSS class to apply to values in rendering Key-Value tables',
            'access' => ['projectOwner', 'coder'],
            'type'    => 'varchar',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => "",
            'shortcode' => 'value_class',
            'appliesTo' => 'renderers',

        ],
        'leafletCeres' => [
            'label'   => 'Leaflet CERES',
            'desc'    => '',
            'access' => ['coder'],
            'type'    => 'data',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => 'need to break this out',
            'shortcode' => 'leaflet_ceres',
            'appliesTo' => 'renderers',

        ],
        //passthroughs to Leaflet
        'leafletNative' =>[
            'label'   => '',
            'desc'    => '',
            'access' => ['coder'],
            'type'    => 'data',
            'defaults' => 'from $ceresOptionsValues array',
            'notes' => 'need to break this out? probably not if access remains limited to coders',
            'shortcode' => 'leaflet_native',
            'appliesTo' => 'renderers',
        ],

    ];
    return $ceresAllOptions;
}

// 'extractorMetadataFilterBy' => [
//     'currentValue' => null,
//     'defaults' => [
//         'ceres' => '',
//         '$projectName' => '',
//         '$viewPackageName' => '',
//     ]
// ],
// ],


function getOptionsValues() {
    $ceresOptionsValues = [
        'extractorValueLabelMappingFilePath' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => null,
            ]

        ],

        'altTextProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => null,
            ]
            ],

        'extractorReorderMappingFilePath' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => null,
            ]
        ],
        'extractorRemoveVarsFilePath' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => null,
            ]
        ],

        'extractorValueMappingFilePath' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => null,
            ]
            ],


        'fetchLocalData' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => false,
            ]
        ],

        'localResponseDataPath' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'leaflet_wikidata_for_public_art_map' => true,
            ]
        ],

        'bounceBack' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'leaflet_wikidata_for_public_art_map' => true,
            ]
        ],
        'rqFile' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'leaflet_wikidata_for_public_art_map' => CERES_ROOT_DIR . "/data/rqFiles/publicart/leaflet.rq",
                'tabular_wikibase_for_chinatown_people' => CERES_ROOT_DIR  . "/data/rqFiles/chinatown/en/people.rq",
                'tabular_wikibase_for_chinatown_maintainers' => CERES_ROOT_DIR . "/data/rqFiles/chinatown/en/maintainers.rq",
            ]
        ],
        'caption' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                'html_dev_test' => 'Howdy!' ,
                'tabular_dev_test' => '',
            ]
        ],

        'text' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                'html_dev_test' => 'Howdy!' ,
                '$viewPackageName' => '',
            ]
        ],

        'altLabelProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                'html_dev_test' => 'aria-label' ,
                '$viewPackageName' => '',
            ]
        ],
        'resourceLinkProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'searchType' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorMetadataFilterBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'float' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                'html_dev_test' => 'left' ,
                '$viewPackageName' => '',
            ]
        ],
        'extractorMetadataToShow' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => [],
                '$projectName' => '',
                '$viewPackageName' => '',
                ]
            ],

        'metadataToShow' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => [],
                '$projectName' => '',
                'html_dev_test' => ['all'],
                '$viewPackageName' => '',
                ]
            ],


        'thumbnailSize' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorResourcesFilterBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorMetadataSortBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorResourcesSortBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorMetadataSortOrder' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorResourcesSortOrder' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorMetadataSortByProperty' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorResourcesSortByProperty' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'extractorGroupBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'itemLinkProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'mediaLinkProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'mediaUriProp' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'thClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-th',
                '$projectName' => '',
                'html_dev_test' => 'ceres-test',
                'tabular_dev_test' => 'ceres-dev ceres-th',
            ]
        ],

        'trClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-tr',
                '$projectName' => '',
                'html_dev_test' => 'ceres-test',
                'tabular_dev_test' => 'ceres-dev ceres-tr',
            ]
        ],
        'theadClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-thead',
                '$projectName' => '',
                'html_dev_test' => 'ceres-td ceres-test',
                '$viewPackageName' => '',
                'tabular_dev_test' => 'ceres-dev ceres-td',
            ]
        ],
        'captionClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-caption',
                '$projectName' => '',
                'html_dev_test' => 'ceres-td ceres-test',
                '$viewPackageName' => '',
                'tabular_dev_test' => 'ceres-dev ceres-td',
            ]
        ],
        'tdClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-td',
                '$projectName' => '',
                'html_dev_test' => 'ceres-td ceres-test',
                '$viewPackageName' => '',
                'tabular_dev_test' => 'ceres-dev ceres-td',
            ]
        ],
        'tableClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => 'ceres-table',
                '$projectName' => '',
                'html_dev_test' => 'ceres-td ceres-test',
                '$viewPackageName' => '',
                'tabular_dev_test' => 'ceres-dev ceres-td',
            ]
        ],
        'endpoint' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'tabular_wikibase_for_chinatown_maintainers' => 'http://ec2-34-227-69-60.compute-1.amazonaws.com:8834/proxy/wdqs/bigdata/namespace/wdq/sparql',
                'leaflet_wikidata_for_public_art_table' => 'https://query.wikidata.org/sparql',
                'leaflet_wikidata_for_public_art_map' => 'https://query.wikidata.org/sparql',

            ]
        ],
        'fetcherMetadataToShow' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'getAll' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'responseFormat' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'perPage' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'startPage' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'resourceIds' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'query' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'queryFile' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'leaflet_wikidata_for_public_art_table' => CERES_ROOT_DIR . '/data/rqFiles/publicart/leaflet.rq'
            ]
        ],
        'firstRowIsHeader' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
                'leaflet_wikidata_for_public_art_table' => true,
                'tabular_wikibase_for_chinatown' => true,
            ]
        ],
        'fetcherGroupBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'fetcherFilterBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'fetcherSortBy' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'fetcherSortOrder' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'fetcherSortByProperty' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'separator' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'keyClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        'valueClass' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        //settings in the surrounding HTML
        'leafletCeres' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],
        //passthroughs to Leaflet
        'leafletNative' => [
            'currentValue' => null,
            'defaults' => [
                'ceres' => '',
                '$projectName' => '',
                '$viewPackageName' => '',
            ]
        ],

    ];

    return $ceresOptionsValues;
}

function getViewPackages() {
    /**
     *
     * sets up the templates for various definitions of a
     * view package. No values are set here -- it is a template
     * for view packages to know what to display
     * permissions by ceresRole and default and set values
     * appear elsewhere
     *
     */



    /**
     *
     * for building up  view packages. template, not set values
     *
     */

    $extractorOptions = [
        'general' => [
            'extractorMetadataFilterBy',
            'extractorResourcesFilterBy',
            'extractorMetadataSortBy',
            'extractorResourcesSortBy',
            'extractorMetadataSortOrder',
            'extractorResourcesSortOrder',
            'extractorMetadataSortByProperty',
            'extractorResourcesSortByProperty',
            'extractorGroupBy',
            'itemLinkProp',
            'mediaLinkProp',
            'mediaUriProp',
            'extractorRemoveVarsFilePath',
            'extractorReorderMappingFilePath',
            'extractorValueLabelMappingFilePath',
        ],
        'tabular' => [
            'tableClass',
            'theadClass',
            'tdClass',
            'thClass',
            'trClass',
            'captionClass',
        ],
    ];


    /**
     *
     * for building up view packages. template, not set values
     */


    $fetcherOptions = [
        'general' => [
            'endpoint',
            'fetcherMetadataToShow',
            'getAll',
            'responseFormat',
            'perPage',
            'startPage',
            'resourceIds',
            'query',
            'rqFile',
            'fetcherGroupBy',
            'fetcherFilterBy',
            'fetcherSortBy',
            'fetcherSortOrder',
            'fetcherSortByProperty',
            'fetchLocalData',
            'localResponseDataPath',
            
        ],
        'wdqs' => [
            'endpoint',
            'responseFormat',

        ],
        'drs' => [
            'endpoint',
            'type',
            'thumbnailSize',

        ]


    ];


    /**
     *
     * for building up  view packages. template, not set values
     */


    $rendererOptions = [
        'general' => [
            'metadataToShow',
            'altLabelProp',
            'rqFile',
            'bounceBack',
        ],

        'tabular' => [
            'thClass',
            'tdClass',
            'trClass',
            'theadClass',
            'firstRowIsHeader',
            'caption',
        ],

        'keyValue' => [
            'separator',
            'keyClass',
            'valueClass',
        ],

        'html' => [
            'text',
        ],

        //settings in the surrounding HTML
        'leafletCeres',
        //passthroughs to Leaflet
        'leafletNative',


    ];


    $ceresViewPackages = [
// @todo following few VP should really be properly put into a better optionsValues system
// hackery around this from OptionsValues. stop when you get to 'tabular_wikibase_for_chinatown'
// alternatively, the prophesized parentViewPackage system


// 'rqFile' => [
//     'currentValue' => null,
//     'defaults' => [
//         'ceres' => '',
//         '$projectName' => '',
//         '$viewPackageName' => '',
//         'leaflet_wikidata_for_public_art_map' => CERES_ROOT_DIR . "/data/rqfiles/publicart/leaflet.rq",
//         'tabular_wikibase_for_chinatown_people' => CERES_ROOT_DIR  . "/data/rqFiles/chinatown/en/people.rq",
//         'tabular_wikibase_for_chinatown_maintainers' => CERES_ROOT_DIR . "/data/rqFiles/chinatown/en/maintainers.rq",
//     ]
// ],

    "tabular_wikibase_for_chinatown_people" => [
        'label' => "Tabular data from NU's WikiBase for chinatown info",
        'description' => "Builds tables of data for what's in our WikiBase",
        'parentViewPackage' => null,
        '$projectName' => null,
        
        'renderer' => [
            'Tabular' => [
                'fullClassName' => 'Ceres\Renderer\Tabular',
                'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                    array_merge(
                        $rendererOptions['general'],
                        $rendererOptions['tabular']
                    )
                    //after deduping options in the merge,
                    // stuff in the current values

            ],
        ],

        'fetchers' => [            
            'Wdqs' => [
                'fullClassName' => 'Ceres\Fetcher\Wdqs',
                'options' => array_merge(
                    $fetcherOptions['general'],
                    $fetcherOptions['wdqs']),
            ]
        ],

        'extractors' => [
            'WdqsToTabular' => [
                'fullClassName' => 'Ceres\Extractor\SparqlToTable',
                'options' => array_merge(
                    $extractorOptions['general'],
                    $extractorOptions['tabular']),
                ],
        ],
    ],


    "tabular_wikibase_for_chinatown_maintainers" => [
        'label' => "Tabular data from NU's WikiBase for chinatown info",
        'description' => "Builds tables of data for what's in our WikiBase",
        'parentViewPackage' => null,
        '$projectName' => null,
        
        'renderer' => [
            'Tabular' => [
                'fullClassName' => 'Ceres\Renderer\Tabular',
                'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                    array_merge(
                        $rendererOptions['general'],
                        $rendererOptions['tabular']
                    )
                    //after deduping options in the merge,
                    // stuff in the current values

            ],
        ],

        'fetchers' => [            
            'Wdqs' => [
                'fullClassName' => 'Ceres\Fetcher\Wdqs',
                'options' => array_merge(
                    $fetcherOptions['general'],
                    $fetcherOptions['wdqs']),
            ]
        ],

        'extractors' => [
            'WdqsToTabular' => [
                'fullClassName' => 'Ceres\Extractor\SparqlToTable',
                'options' => array_merge(
                    $extractorOptions['general'],
                    $extractorOptions['tabular']),
                ],
        ],
    ],







    "tabular_wikibase_for_chinatown" => [
        'label' => "Tabular data from NU's WikiBase for chinatown info",
        'description' => "Builds tables of data for what's in our WikiBase",
        'parentViewPackage' => null,
        '$projectName' => null,
        
        'renderer' => [
            'Tabular' => [
                'fullClassName' => 'Ceres\Renderer\Tabular',
                'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                    array_merge(
                        $rendererOptions['general'],
                        $rendererOptions['tabular']
                    )
                    //after deduping options in the merge,
                    // stuff in the current values

            ],
        ],

        'fetchers' => [            
            'Wdqs' => [
                'fullClassName' => 'Ceres\Fetcher\Wdqs',
                'options' => array_merge(
                    $fetcherOptions['general'],
                    $fetcherOptions['wdqs']),
            ]
        ],

        'extractors' => [
            'WdqsToTabular' => [
                'fullClassName' => 'Ceres\Extractor\SparqlToTable',
                'options' => array_merge(
                    $extractorOptions['general'],
                    $extractorOptions['tabular']),
                ],
        ],
    ],

    "tabular_wikidata_for_short_metadata" =>
            [
            'label' => "Tabular Wikidata For Short Metadata",
            'description' => "Description",
            'parentViewPackage' => null,
            '$projectName' => null,
            'renderer' => [
                    'Tabular' => [
                        'fullClassName' => 'Ceres\Renderer\Tabular',
                        'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                            array_merge(
                                $rendererOptions['general'],
                                $rendererOptions['tabular']
                            )
                            //after deduping options in the merge,
                            // stuff in the current values

                ],
            ],

            'fetchers' =>
                [
                    'Wdqs' => [
                        'fullClassName' => 'Ceres\Fetcher\Wdqs',
                        'options' => array_merge(
                            $fetcherOptions['general'],
                            $fetcherOptions['wdqs']),
                    ]
                ],

            'extractors' =>
                [
                    'WdqsToTabular' => [
                        'fullClassName' => 'Ceres\Extractor\WdqsToTabular',
                        'options' => array_merge(
                            $extractorOptions['general'],
                            $extractorOptions['tabular']),
                        ],
                ],
            ],
    "leaflet_wikidata_for_public_art_map" =>
        [
            'label' => "Leaflet Wikidata for Public Art Map",
            'description' => "Description",
            'parentViewPackage' => null,
            '$projectName' => null,
            'renderer' => [
                //@todo fold LeafletMapBrc into the more general LeafletMap
                    'LeafletMapBrc' => [
                        'fullClassName' => 'Ceres\Renderer\LeafletMapBrc',
                        'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                            array_merge(
                                $rendererOptions['general'],
                                //$rendererOptions['leafletCeres']
                            )
                            //after deduping options in the merge,
                            // stuff in the current values

                    ]
                ],

                'fetchers' =>
                    [
                        'Wdqs' => [
                            'fullClassName' => 'Ceres\Fetcher\Wdqs',
                            'options' => array_merge(
                                $fetcherOptions['general'],
                                $fetcherOptions['wdqs']),
                        ]
                    ],

            'extractors' =>
                [
                ],
            ],
    "leaflet_wikidata_for_public_art_table" =>
    [
                'label' => "Table Wikidata for Public Art Map",
                'description' => "Extract data from Wikidata for a map, but display as table.",
                'parentViewPackage' => null,
                '$projectName' => null,
                'renderer' => [
                        'Tabular' => [
                            'fullClassName' => 'Ceres\Renderer\Tabular',
                            'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                                array_merge(
                                    $rendererOptions['general'],
                                    $rendererOptions['tabular']
                                )
                                //after deduping options in the merge,
                                // stuff in the current values

                        ]
                    ],

                'fetchers' =>
                    [
                        'Wdqs' => [
                            'fullClassName' => 'Ceres\Fetcher\Wdqs',
                            'options' => array_merge(
                                $fetcherOptions['general'],
                                $fetcherOptions['wdqs'],
                            ),
                        ]
                    ],

                'extractors' =>
                    [
                        'WdqsToTabular' => [
                            'fullClassName' => 'Ceres\Extractor\SparqlToTable',
                            'options' => array_merge(
                                $extractorOptions['general'],
                                $extractorOptions['tabular']
                            ),
                            ],
                    ],
                ],
    "html_dev_test" =>
    [
        'label' => "Html Dev Test",
        'description' => "Description",
        'parentViewPackage' => null,
        '$projectName' => null,
        'renderer' => [
                'Html' => [
                    'fullClassName' => 'Ceres\Renderer\Html',
                    'options' =>   //redundant, yes. but helps keep the same patter with fetchers and extractors
                        array_merge(
                            $rendererOptions['general'],
                            $rendererOptions['html']
                        )
                        //after deduping options in the merge,
                        // stuff in the current values

                ]

            ],

        'fetchers' =>
            [
            ],

        'extractors' =>
            [
            ],
        ],

    "tabular_dev_test" =>
    [
        'label' => "Tabular Dev Test",
        'description' => "Description",
        'parentViewPackage' => null,
        '$projectName' => null,
        'renderer' => [
                'Tabular' => [
                    'fullClassName' => 'Ceres\Renderer\Tabular',
                    'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                        array_merge(
                            $rendererOptions['general'],
                            $rendererOptions['tabular']
                        )
                        //after deduping options in the merge,
                        // stuff in the current values

                ]
            ],

        'fetchers' =>
            [
            ],

        'extractors' =>
            [
            ],
        ],

    "leafletmap_dev_test" =>
    [
        'label' => "LeafletMap Dev Test",
        'description' => "Description",
        'parentViewPackage' => null,
        '$projectName' => null,
        'renderer' => [
                'LeafletMap' => [
                    'fullClassName' => 'Ceres\Renderer\LeafletMap',
                    'options' =>  //redundant, yes. but helps keep the same patter with fetchers and extractors
                        array_merge(
                            $rendererOptions['general'],
                            $rendererOptions['tabular']
                        )
                        //after deduping options in the merge,
                        // stuff in the current values

                ]
            ],

        'fetchers' =>
            [
            ],

        'extractors' =>
            [
            ],
        ],
    "tabular_wdqs_test" =>
    [
        'label' => "Tabular Wdqs Test",
        'description' => "Description",
        'parentViewPackage' => null,
        '$projectName' => null,
        'renderer' =>
            [
                'Tabular' =>
                    [
                    'fullClassName' => 'Ceres\Renderer\Tabular',
                    'options' => //redundant, yes. but helps keep the same patter with fetchers and extractors
                        array_merge(
                            $rendererOptions['general'],
                            $rendererOptions['tabular']
                        )
                        //after deduping options in the merge,
                        // stuff in the current values

                ],
        ],

        'fetchers' =>
            [
                'Wdqs' => [
                    'fullClassName' => 'Ceres\Fetcher\Wdqs',
                    'options' => array_merge(
                        $fetcherOptions['general'],
                        $fetcherOptions['wdqs']),
                ]
            ],

        'extractors' =>
            [
            ],
        ],
    "tabular_drs_test" =>
    [
        'label' => "Tabular Drs Test",
        'description' => "Description",
        'parentViewPackage' => null,
        '$projectName' => null,
        'renderer' => [
                'Tabular' => [
                    'fullClassName' => 'Ceres\Renderer\Tabular',
                    'options' => //redundant, yes. but helps keep the same patter with fetchers and extractors
                        array_merge(
                            $rendererOptions['general'],
                            $rendererOptions['tabular']
                        )
                        //after deduping options in the merge,
                        // stuff in the current values

                ]
            ],

        'fetchers' =>
            [
                'Drs' => [
                    'fullClassName' => 'Ceres\Fetcher\Drs',
                    'options' => array_merge(
                        $fetcherOptions['general'],
                        $fetcherOptions['wdqs']),
                ]
            ],

        'extractors' =>
            [
                'DrsToTabular' => [
                    'fullClassName' => 'Ceres\Extractor\DrsToTabular',
                    'options' => array_merge(
                        $extractorOptions['general'],
                    //    $extractorOptions['leaflet']
                    ),
                    ],
            ],
        ],

    //"another_view_package" => [],

];


    return $ceresViewPackages;
}

function getOptionsEnums() {
    $ceresOptionsEnums = [
        'bounceBack' => [
            'ceres' => false,
            'leaflet_map_for_public_art',
            '$projectName' => false,
            '$viewPackageName' => false,

        ],
        'float' => [
            'ceres' => ['left', 'right'],
            '$projectName' => ['left', 'right'],
            '$viewPackageName' => ['left', 'right']
        ],
        'thumbnailSize' => [
            'ceres' => ['extra small', 'small', 'medium', 'large', 'extra large'],
        ],
        'extractorMetadataSortOrder' => [
            'ceres' => ['query default'],
        ],
        'extractorResourcesSortOrder' => [
            'ceres' => ['query default'],
        ],
        'extractorMetadataToShow' => [
            'ceres' => ['all']
        ],
        'fetcherMetadataToShow' => [
            'ceres' => ['all']
        ],

        'metadataToShow' => [
            'ceres' => ['all']
        ],


    ];
    return $ceresOptionsEnums;
}

function getPropertyLabels() {
    $ceresPropertyLabels = [
        'ceres' => [
            'dcterms:title' => 'Title',
            'dcterms:subject' => 'Subject(s)',
            //etc

            // @todo: mods: first, see if @displayLabel is set in Extractors
            'mods:' => '',

            'darwincore:' => '',

            // @todo: wikidata can be built into the query, but still needs
            //a fallback if rdfs:label isn't present

        ],
        '$projectName' => []
            //ECDA and Thoreau make use of mods:@displayLabel
            //so I need a way to bail out to that, based on whether
            //a project does it's own thing

            // do that in the DataUtil::labelForProperty
        ,
        '$ecda' => [],

    ];

    return $ceresPropertyLabels;

}
