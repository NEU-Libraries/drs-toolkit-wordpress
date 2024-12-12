/**
 * Backbone Application File
 * @package drstk.backbone_modal
 */
var drstk = {
    backbone_modal: {
        __instance: undefined
    }
};

drstk.Item = Backbone.Model.extend({
    title: '',
    pid: '',
    thumbnail: '',
    repo: '',
    color: '',
    key_date: '',
    coords: ''
});

drstk.Setting = Backbone.Model.extend({
    name: '',
    value: [],
    choices: {},
    label: '',
    helper: '',
    tag: '',
    selectedId: '',
    colorHex: '',
    colorId: ''
});

drstk.ColorSetting = Backbone.Model.extend({
    name: '',
    value: [],
    label: '',
    tag: '',
    colorname: '',
    colorHex: '',
});

drstk.Items = Backbone.Collection.extend({
    model: drstk.Item
});

drstk.Settings = Backbone.Collection.extend({
    model: drstk.Setting
});

drstk.ColorSettings = Backbone.Collection.extend({
    model: drstk.ColorSetting
});

drstk.Shortcode = Backbone.Model.extend({
    defaults: {
        type: '',
        items: new drstk.Items(),
        settings: new drstk.Settings(),
        colorsettings: new drstk.ColorSettings(),
    },
    initialize: function() {
        this.set('items', new drstk.Items());
        this.set('settings', new drstk.Settings());
        this.set('colorsettings', new drstk.ColorSettings());
    },
    parse: function(response) {
        response.items = new drstk.Items(response.items);
        response.settings = new drstk.Settings(response.settings);
        response.colorsettings = new drstk.ColorSettings(response.colorsettings);
        return response;
    },
    set: function(attributes, options) {
        if (attributes.items !== undefined && !(attributes.items instanceof drstk.Items)) {
            attributes.items = new drstk.Items(attributes.items);
        }
        if (attributes.settings !== undefined && !(attributes.settings instanceof drstk.Settings)) {
            attributes.settings = new drstk.Settings(attributes.settings);
        }
        if (attributes.colorsettings !== undefined && !(attributes.colorsettings instanceof drstk.ColorSettings)) {
            attributes.colorsettings = new drstk.ColorSettings(attributes.colorsettings);
        }
        return Backbone.Model.prototype.set.call(this, attributes, options);
    }
});

drstk.ItemView = Backbone.View.extend({
    tagName: 'li',
    item_template: _.template("<label for='tile-<%=pid%>'><img src='<%=thumbnail%>' /><br/><input id='tile-<%=pid%>' type='checkbox' class='tile <%=repo%>' value='<%=pid%>'/><span class='title'><%=title%></span></label>"),
    item_noimg_template: _.template("<label for='tile-<%=pid%>'><span class='dashicons dashicons-format-image'></span><br/><input id='tile-<%=pid%>' type='checkbox' class='tile <%=repo%>' value='<%=pid%>'/><span class='title'><%=title%></span></label>"),
    initialize: function() {
        this.render();
    },
    render: function() {
        if (this.model.attributes.thumbnail === undefined) {
            this.$el.html(this.item_noimg_template(this.model.toJSON()));
        } else {
            this.$el.html(this.item_template(this.model.toJSON()));
        }
    }
});
var click_counter = 1;
var colorArray = [];

drstk.SettingView = Backbone.View.extend({
    checkbox_template: wp.template("drstk-setting-checkbox"),
    select_template: wp.template("drstk-setting-select"),
    text_template: wp.template("drstk-setting-text"),
    number_template: wp.template("drstk-setting-number"),
    tagName: 'tr',
    initialize: function() {
        this.render();
    },
    render: function() {
        if (this.model.attributes.tag == 'select') {
            this.$el.html(this.select_template(this.model.toJSON()));
        } else if (this.model.attributes.tag == 'checkbox') {
            this.$el.html(this.checkbox_template(this.model.toJSON()));
        } else if (this.model.attributes.tag == 'text') {
            this.$el.html(this.text_template(this.model.toJSON()));
        } else if (this.model.attributes.tag == 'number') {
            this.$el.html(this.number_template(this.model.toJSON()));
        }
    },
});

drstk.ColorSettingView = Backbone.View.extend({
    color_row_template: wp.template("drstk-setting-colorinput"),
    tagName: 'tr',
    initialize: function() {
        this.render();
    },
    render: function() {
        if (this.model.attributes.tag == 'inputcolor') {
            this.$el.html(this.color_row_template(this.model.toJSON()));
        }
    },
    remove: function() {
        this.collection.remove(this.model);
    },
});

/**
 * Primary Modal Application Class
 */
drstk.backbone_modal.Application = Backbone.View.extend({
    id: "backbone_modal_dialog",
    events: {
        "change #drs-select-all-item": "selectAllItem",
        "click .backbone_modal-close": "closeModal",
        "click #btn-cancel": "closeModal",
        "click #btn-ok": "insertShortcode",
        "click .navigation-bar a": "navigate",
        "click .backbone_modal-main article table .button": "navigate",
        "change .tile": "selectItem",
        "click .tablenav-pages a": "paginate",
        "click .nav-tab": "navigateShortcode",
        "click .search-button": "search",
        "change #settings input": "settingsChange",
        "change #settings select": "settingsChange",
        "change #selected select[name='color']": "changeColor",
        "click #local #wp_media": "addMediaItems",
        "change select[name='dpla-sort']": "dplaSort",
        "change select[name='drs-sort']": "drsSort",
        "click .dpla-facets-button": "dplaFacetToggle",
        "click .drs-facets-button": "drsFacetToggle",
        "click .dpla-close-facets": "dplaFacetToggle",
        "click .drs-close-facets": "drsFacetToggle",
        "click .dpla-facet-add": "dplaFacet",
        "click .drs-facet-add": "drsFacet",
        "click .dpla-update-date": "dplaUpdateDate",
        "click .dpla-facet-remove": "dplaFacetRemove",
        "click .drs-facet-remove": "drsFacetRemove",
        "click .dpla-expand-facet": "dplaFacetExpand",
        "click .drs-expand-facet": "drsFacetExpand",
        "click #addcolorbutton": "settingsAddColor",
        "click .delete-color-row": "deleteColorRow",
    },

    /**
     * Simple object to store any UI elements we need to use over the life of the application.
     */
    ui: {
        nav: undefined,
        content: undefined
    },

    /**
     * Container to store our compiled templates. Not strictly necessary in such a simple example
     * but might be useful in a larger one.
     */
    templates: {},

    shortcode: null,
    geo_count: 0,
    time_count: 0,
    select_all: false,
    old_shortcode: null,
    collection_id: drstk_backbone_modal_l10n.collection_id,
    options: {},
    search_q: '',
    result_count: 0,
    search_page: 1,
    search_params: {
        q: this.search_q,
        page: this.search_page,
        facets: {},
        sort: ""
    },
    current_tab: 0, // store our current tab as a variable for easy lookup
    tabs: { // dictionary of key/value pairs for our tabs
        1: 'single',
        2: 'tile',
        3: 'slider',
        4: 'media',
        5: 'map',
        6: 'timeline'
    },

    /**
     * Instantiates the Template object and triggers load.
     */
    initialize: function(options) {
        "use strict";
        this.options = options;

        _.bindAll(this, 'render', 'preserveFocus', 'closeModal', 'insertShortcode', 'navigate', 'showTab', 'getDRSitems', 'selectItem', 'paginate', 'navigateShortcode', 'search', 'setDefaultSettings', 'appendSingleItem', 'selectAllItem', 'settingsAddColor', 'deleteColorRow');
        this.initialize_templates();
        this.render();
        this.shortcode = new drstk.Shortcode({});
        if (this.options && this.options.current_tab != "") {
            var e = {
                currentTarget: ""
            };
            var num = _.invert(this.tabs)[this.options.current_tab];
            var words = {
                1: "one",
                2: "two",
                3: "three",
                4: "four",
                5: "five",
                6: "six"
            }
            var word = words[num];
            e.currentTarget = "<a href='#" + word + "'></a>";
            this.search_params.q = "";
            this.search_params.page = 1;
            this.navigate(e);
            this.current_tab = num;
            this.shortcode.type = this.tabs[this.current_tab];
        } else {
            this.current_tab = 1;
        }
        var self = this;
        click_counter = 1;
        if (this.options && this.options.items && this.options.items.length > 0) {
            _.each(this.options.items, function(item, i) {
                if (i == 0) {
                    self.shortcode.items = new drstk.Items(item);
                } else {
                    self.shortcode.items.add(item);
                }
            });
        } else if (this.options) { //starting with collection_id
            self.select_all = true;
            jQuery(".backbone_modal-main #drs-select-all-item").prop("checked", true);
        }
        if (this.options && ((this.options.items && this.options.items.length > 0) || (this.options.collection_id && this.options.collection_id.length > 0))) {
            var settings = this.options.settings;
            _.each(this.options.settings, function(setting, setting_name) {
                if (setting_name.match(/([a-zA-Z_0-9-]*)_color_desc_id/)) {
                    var desc = setting_name.match(/([a-zA-Z_0-9-]*)_color_desc_id/)[1];
                    var code = "#" + settings[desc + '_color_hex'];
                    if (desc && code) {
                        var colorsettings = self.shortcode.get('colorsettings');
                        var name = 'label-text-' + click_counter + '_desc';
                        var value = 'label-' + click_counter;
                        var label = 'label-' + click_counter;
                        desc = desc.replace("_", " ");
                        colorsettings.add({
                            'name': name,
                            'value': value,
                            'label': label,
                            'tag': 'inputcolor',
                            'colorname': desc,
                            'colorHex': code,
                        });
                        self.shortcode.set('colorsettings', colorsettings);
                        var colored_ids = setting.split(",");
                        for (var x = 0; x < colored_ids.length; x++) {
                            colored_ids[x] = colored_ids[x].trim();
                        }
                        _.each(colored_ids, function(id) {
                            repo = id.split(":")[0];
                            if (repo != "neu") {
                                id = id.split(":")[1];
                            }
                            var item = self.shortcode.items.where({
                                'pid': id
                            });
                            item[0].attributes["color"] = desc;
                        });
                        click_counter++;
                    }
                }
            });
            e.currentTarget = jQuery(".nav-tab[href='#selected']");
            this.navigateShortcode(e);
        }
        if (this.options && this.options.old_shortcode) {
            this.old_shortcode = this.options.old_shortcode
        }
    },


    /**
     * Creates compiled implementations of the templates. These compiled versions are created using
     * the wp.template class supplied by WordPress in 'wp-util'. Each template name maps to the ID of a
     * script tag ( without the 'tmpl-' namespace ) created in template-data.php.
     */
    initialize_templates: function() {
        this.templates.window = wp.template("drstk-modal-window");
        this.templates.backdrop = wp.template("drstk-modal-backdrop");
        this.templates.menuItem = wp.template("drstk-modal-menu-item");
        this.templates.menuItemSeperator = wp.template("drstk-modal-menu-item-separator");
        this.templates.tabMenu = wp.template("drstk-modal-tab-menu");
        this.templates.tabItem = wp.template("drstk-modal-tab-item");
        this.templates.tabContent = wp.template("drstk-modal-tab-content");
    },

    /**
     * Assembles the UI from loaded templates.
     * @internal Obviously, if the templates fail to load, our modal never launches.
     */
    render: function() {
        "use strict";

        // Build the base window and backdrop, attaching them to the $el.
        // Setting the tab index allows us to capture focus and redirect it in Application.preserveFocus
        this.$el.attr('tabindex', '0')
            .append(this.templates.window())
            .append(this.templates.backdrop());

        // Save a reference to the navigation bar's unordered list and populate it with items.
        // This is here mostly to demonstrate the use of the template class.
        this.ui.nav = this.$('.navigation-bar nav ul')
            .append(this.templates.menuItem({
                url: "#one",
                name: "Single Item"
            }))
            .append(this.templates.menuItem({
                url: "#two",
                name: "Tile Gallery"
            }))
            .append(this.templates.menuItem({
                url: "#three",
                name: "Gallery Slider"
            }))
            .append(this.templates.menuItemSeperator())
            .append(this.templates.menuItem({
                url: "#four",
                name: "Media Playlist"
            }))
            .append(this.templates.menuItem({
                url: "#five",
                name: "Map"
            }))
            .append(this.templates.menuItem({
                url: "#six",
                name: "Timeline"
            }));


        // The l10n object generated by wp_localize_script() should be available, but check to be sure.
        // Again, this is a trivial example for demonstration.
        if (typeof drstk_backbone_modal_l10n === "object") {
            this.ui.content = this.$('.backbone_modal-main article')
                .append("<p>" + drstk_backbone_modal_l10n.replace_message + "</p>");
        }

        // Handle any attempt to move focus out of the modal.
        jQuery(document).on("focusin", this.preserveFocus);

        // set overflow to "hidden" on the body so that it ignores any scroll events while the modal is active
        // and append the modal to the body.
        // TODO: this might better be represented as a class "modal-open" rather than a direct style declaration.
        jQuery("body").css({
            "overflow": "hidden"
        }).append(this.$el);

        // Set focus on the modal to prevent accidental actions in the underlying page
        // Not strictly necessary, but nice to do.
        this.$el.focus();
    },

    /**
     * Ensures that keyboard focus remains within the Modal dialog.
     * @param e {object} A jQuery-normalized event object.
     */
    preserveFocus: function(e) {
        "use strict";
        if (this.$el[0] !== e.target && !this.$el.has(e.target).length) {
            this.$el.focus();
        }
    },

    /* close the modal */
    closeModal: function(e) {
        "use strict";
        click_counter = 1;
        e.preventDefault
        this.undelegateEvents();
        jQuery(document).off("focusin");
        jQuery("body").css({
            "overflow": "auto"
        });
        this.remove();
        if (this.old_shortcode && jQuery(e.currentTarget).attr("id") != "btn-ok") {
            window.wp.media.editor.insert(this.old_shortcode);
        }
        drstk.backbone_modal.__instance = undefined;
    },

    /* select all items when 'Select All' checkbox is enabled */

    selectAllItem: function(e) {
        "use strict";
        e.preventDefault
        if (jQuery("#drs-select-all-item").prop("checked")) {
            jQuery("#sortable-" + this.tabs[this.current_tab] + "-list").find("li input").prop("checked", true);
            jQuery("#sortable-" + this.tabs[this.current_tab] + "-list").find("li input").prop("disabled", true);
            jQuery(".tile").trigger("change"); //This will call the selectItem function for all the selected items.
            if (jQuery(".drs-pagination .tablenav-pages").children().length > 1) {
                this.loopThroughPages();
            }
            this.select_all = true;
        } else {
            jQuery("#sortable-" + this.tabs[this.current_tab] + "-list").find("li input").prop("checked", false);
            jQuery("#sortable-" + this.tabs[this.current_tab] + "-list").find("li input").prop("disabled", false);
            this.shortcode.items.models.length = 0; //When the "Select All" checkbox is enabled, all the shortcodes should become null.
        }
    },

    loopThroughPages: function() {
        var cur = parseInt(jQuery(".drs-pagination .tablenav-pages .current-page").text());
        var next = jQuery(".drs-pagination .tablenav-pages .current-page").next("a");
        if (parseInt(next.text()) == cur + 1) {
            jQuery(next.trigger('click')); //trigger paginate
            jQuery(".drs-pagination .tablenav-pages .current-page").removeClass("current-page");
            next.addClass("current-page");
            this.loopThroughPages();
        } else {
            //if it doesn't need to paginate anymore we just send it back to the first page
            jQuery(".drs-pagination .tablenav-pages .prev-page").next("a").trigger('click');
        }
    },

    /* inserts shortcode and closes modal */
    insertShortcode: function(e) {
        var items = this.shortcode.items;
        var ids = [];

        if (items != undefined || ids.length != 0) {
            start_date = this.shortcode.get('settings').where({
                name: 'start-date'
            })[0];
            if (start_date != undefined) {
                start_date = start_date.attributes.value[0];
            }
            end_date = this.shortcode.get('settings').where({
                name: 'end-date'
            })[0];
            if (end_date != undefined) {
                end_date = end_date.attributes.value[0];
            }

            if ((this.current_tab == 6
                && ((start_date != "" && start_date != undefined) || (end_date != "" && end_date != undefined))
                && this.validTime() == true)
                || (this.current_tab == 6 && start_date == undefined && end_date == undefined && this.validTime() == true)
                || (this.current_tab == 5 && this.validMap() == true)
                || (this.current_tab == 1)
                || (this.current_tab != 6 && this.current_tab != 1 && this.current_tab != 5)) {// current_tab is actually not a tab -- it's the button for the type of shortcode

                shortcode = '<p>[drstk_' + this.tabs[this.current_tab];

                // If check box is checked then add collection_Id attribute to the shortcode
                if (jQuery("#drs-select-all-item").prop("checked")) {
                    shortcode += ' collection_id="' + this.collection_id + '"';
                }



                if (ids.length == 0) {
                    jQuery.each(items.models, function(i, item) {
                        if (item.attributes.repo == 'dpla') {
                            pid = "dpla:" + item.attributes.pid;
                        } else if (item.attributes.repo == 'drs') {
                            pid = item.attributes.pid;
                        } else if (item.attributes.repo == 'local') {
                            pid = "wp:" + item.attributes.pid;
                        }
                        ids.push(pid);
                    });
                }
                ids.join(",");

                if (!jQuery("#drs-select-all-item").prop("checked")) {
                    shortcode += ' id="' + ids + '"';
                }

                if (this.current_tab == 5 || this.current_tab == 6) {
                    var self = this;
                    var col_desc = "";
                    _.each(this.shortcode.get('colorsettings').models, function(color) {
                        arr = [];
                        color = color.attributes.colorname;
                        items = self.shortcode.items.where({
                            'color': color
                        });
                        _.each(items, function(i) {
                            if (i.attributes.repo == 'dpla') {
                                pid = "dpla:" + i.attributes.pid;
                            } else if (i.attributes.repo == 'drs') {
                                pid = i.attributes.pid;
                            } else if (i.attributes.repo == 'local') {
                                pid = "wp:" + i.attributes.pid;
                            }
                            arr.push(pid);
                        });
                        if (arr.length > 0) {
                            color_desc = color.replace(" ", "_");
                            shortcode += ' ' + color_desc + '_color_desc_id="' + arr.join(",") + '"';
                        }
                    });
                }
                _.each(this.shortcode.get('settings').models, function(setting, i) {
                    vals = setting.get('value');
                    if (Array.isArray(vals) && vals.length > 0) {
                        vals = vals.join(",");
                        shortcode += ' ' + setting.get('name') + '="' + vals + '"';
                    } else if (vals != "") {
                        shortcode += ' ' + setting.get('name') + '="' + vals + '"';
                    }
                });
                if (this.current_tab == 5 || this.current_tab == 6) {
                    var color_shortcode = " ";
                    _.each(this.shortcode.get('colorsettings').models, function(color) {
                        var color_desc = color.attributes.colorname.replace(" ", "_");
                        var hexval = color.attributes.colorHex.substring(1, color.attributes.colorHex.length);
                        color_shortcode += color_desc + '_color_hex' + '="' + hexval + '" ';
                    });
                    shortcode += color_shortcode;
                }
                shortcode += ']</p>';
                this.closeModal(e);
                window.wp.media.editor.insert(shortcode);
            } else if (this.current_tab == 1 && this.shortcode.items.length > 1) {
                alert("There are more than 1 items selected for a single item shortcode.");
            } else if (this.current_tab == 6) {
                titles = this.validTime();
                titles = titles.join("\n");
                alert("The following item(s) are outside the specified date range or do not have date values: \n" + titles);
            } else if (this.current_tab == 5) {
                titles = this.validMap();
                titles = titles.join("\n");
                alert("The following item(s) may not have coordinate or location values: \n" + titles);
            }
        } else {
            alert("Please select items before inserting a shortcode");
        }
    },

    setDefaultSettings: function(options_settings) {
        type = this.shortcode.get('type');
        settings = this.shortcode.get('settings');
        if (this.options && this.options.settings) {
            options = this.options.settings;
        } else if (this.options) {
            options = this.options;
        } else {
            options = {};
        }
        if (type == 'tile') {
            var tile_type = options["tile-type"] ? options["tile-type"] : options['type'];
            settings.add({
                'name': 'tile-type', //previously called type
                'value': tile_type ? [tile_type] : ['pinterest-hover'],
                'choices': {
                    'pinterest-below': "Pinterest style with caption below",
                    'pinterest-hover': "Pinterest style with caption on hover",
                    'even-row': "Even rows with caption on hover",
                    'square': "Even Squares with caption on hover"
                },
                'label': 'Layout Type',
                'tag': 'select'
            });
            settings.add({
                'name': 'text-align',
                'value': options["text-align"] ? [options["text-align"]] : ['left'],
                'choices': {
                    'center': "Center",
                    'left': "Left",
                    'right': "Right"
                },
                'label': 'Caption Alignment',
                'tag': 'select'
            });
            settings.add({
                'name': 'cell-height',
                'value': options["cell-height"] ? [options["cell-height"]] : [200],
                'label': 'Cell Height (auto for Pinterest style)',
                'tag': 'number'
            });
            settings.add({
                'name': 'cell-width',
                'value': options["cell-width"] ? [options["cell-width"]] : [200],
                'label': 'Cell Width',
                'tag': 'number',
                'helper': 'Make the height and width the same for squares'
            });
            settings.add({
                'name': 'image-size',
                'value': options["image-size"] ? [options["image-size"]] : [4],
                'label': 'Image Size',
                'tag': 'select',
                'choices': {
                    1: 'Largest side is 85px',
                    2: 'Largest side is 170px',
                    3: 'Largest side is 340px',
                    4: 'Largest side is 500px',
                    5: 'Largest side is 1000px'
                }
            });
            settings.add({
                'name': 'metadata',
                'label': 'Metadata for Captions',
                'tag': 'checkbox',
                'value': options["metadata"] ? options["metadata"] : ['full_title_ssi', 'creator_tesim'],
                'choices': {
                    'full_title_ssi': 'Title',
                    'creator_tesim': 'Creator,creator',
                    'date_ssi': 'Date Created',
                    'abstract_tesim': 'Abstract/Description'
                },
            });
            this.shortcode.set('settings', settings);
        } else if (type == 'single') {
            settings.add({
                'name': 'image-size',
                'value': options["image-size"] ? [options["image-size"]] : [4],
                'label': 'Image Size',
                'tag': 'select',
                'choices': {
                    1: 'Largest side is 85px',
                    2: 'Largest side is 170px',
                    3: 'Largest side is 340px',
                    4: 'Largest side is 500px',
                    5: 'Largest side is 1000px'
                }
            });
            settings.add({
                'name': 'display-video',
                'value': options["display-video"] ? [options["display-video"]] : ['true'],
                'label': 'Display Audio/Video',
                'helper': 'Note: DPLA items cannot be used as embedded media',
                'tag': 'checkbox',
                'choices': {
                    0: 'true'
                },
            });
            settings.add({
                'name': 'display-issuu',
                'value': options["display-issuu"] ? [options["display-issuu"]] : ['true'],
                'label': 'Display Embedded Page Turner',
                'helper': 'Note: Only for DRS items. Requires special metadata.',
                'tag': 'checkbox',
                'choices': {
                    0: 'true'
                },
            });
            settings.add({
                'name': 'align',
                'value': options['align'] ? [options['align']] : ['center'],
                'label': 'Image Alignment',
                'tag': 'select',
                'choices': {
                    'center': 'Center',
                    'left': 'Left',
                    'right': 'Right'
                }
            });
            settings.add({
                'name': 'float',
                'value': options['float'] ? [options['float']] : ['none'],
                'label': 'Image Flow',
                'helper': 'Allow the text to float around the image by floating it to one side.',
                'tag': 'select',
                'choices': {
                    'none': 'None',
                    'left': 'Left',
                    'right': 'Right'
                }
            });
            settings.add({
                'name': 'caption-align',
                'value': options['caption-align'] ? [options['caption-align']] : ['left'],
                'choices': {
                    'center': "Center",
                    'left': "Left",
                    'right': "Right"
                },
                'label': 'Caption Alignment',
                'tag': 'select'
            });
            settings.add({
                'name': 'caption-position',
                'value': options['caption-position'] ? [options['caption-position']] : ['below'],
                'label': 'Caption Position',
                'choices': {
                    'below': 'Below',
                    'hover': 'Over Image on Hover'
                },
                'tag': 'select'
            });
            settings.add({
                'name': 'zoom',
                'value': options['zoom'] ? [options['zoom']] : ['on'],
                'label': 'Enable Zoom',
                'choices': {
                    0: 'on'
                },
                'tag': 'checkbox'
            });
            settings.add({
                'name': 'zoom-position',
                'value': options['zoom-position'] ? [options['zoom-position']] : [1],
                'label': 'Zoom Position',
                'helper': 'Recommended and Default position:Top Right',
                'choices': {
                    1: 'Top Right',
                    2: 'Middle Right',
                    3: 'Bottom Right',
                    4: 'Bottom Corner Right',
                    5: 'Under Right',
                    6: 'Under Middle',
                    7: 'Under Left',
                    8: 'Bottom Corner Left',
                    9: 'Bottom Left',
                    10: 'Middle Left',
                    11: 'Top Left',
                    12: 'Top Corner Left',
                    13: 'Above Left',
                    14: 'Above Middle',
                    15: 'Above Right',
                    16: 'Top Right Corner',
                    'inner': "Over image itself"
                },
                'tag': 'select'
            });
            if (options["metadata"]) {
                var choices = {}
                _.each(options['metadata'], function(val) {
                    choices[val] = val;
                });
                settings.add({
                    'name': 'metadata',
                    'label': 'Metadata',
                    'tag': 'checkbox',
                    'value': options['metadata'] ? options['metadata'] : [],
                    'choices': choices,
                });
            }
            this.shortcode.set('settings', settings);
        } else if (type == 'slider') {
            settings.add({
                'name': 'image-size',
                'value': options["image-size"] ? [options["image-size"]] : [4],
                'label': 'Image Size',
                'tag': 'select',
                'choices': {
                    1: 'Largest side is 85px',
                    2: 'Largest side is 170px',
                    3: 'Largest side is 340px',
                    4: 'Largest side is 500px',
                    5: 'Largest side is 1000px'
                }
            });
            settings.add({
                'name': 'auto',
                'value': options['auto'] ? [options['auto']] : ['on'],
                'label': 'Auto rotate',
                'choices': {
                    0: 'on'
                },
                'tag': 'checkbox'
            });
            settings.add({
                'name': 'nav',
                'value': options['nav'] ? [options['nav']] : ['on'],
                'label': 'Next/Prev Buttons',
                'choices': {
                    0: 'on'
                },
                'tag': 'checkbox'
            });
            settings.add({
                'name': 'pager',
                'value': options['pager'] ? [options['pager']] : ['on'],
                'label': 'Dot pager',
                'choices': {
                    0: 'on'
                },
                'tag': 'checkbox'
            });
            settings.add({
                'name': 'speed',
                'value': options['speed'] ? [options['speed']] : [],
                'label': 'Rotation Speed',
                'tag': 'number',
                'helper': 'Speed is in milliseconds. 5000 milliseconds = 5 seconds'
            });
            settings.add({
                'name': 'max-height',
                'value': options['max-height'] ? [options['max-height']] : [],
                'label': 'Max Height',
                'tag': 'number'
            });
            settings.add({
                'name': 'max-width',
                'value': options['max-width'] ? [options['max-width']] : [],
                'label': 'Max Width',
                'tag': 'number',
            });
            settings.add({
                'name': 'caption',
                'value': options['caption'] ? [options['caption']] : ['on'],
                'label': 'Enable captions',
                'choices': {
                    0: 'on'
                },
                'tag': 'checkbox'
            });
            settings.add({
                'name': 'caption-align',
                'value': options['caption-align'] ? [options['caption-align']] : ['center'],
                'choices': {
                    'center': "Center",
                    'left': "Left",
                    'right': "Right"
                },
                'label': 'Caption Alignment',
                'tag': 'select'
            });
            settings.add({
                'name': 'caption-position',
                'value': options['caption-position'] ? [options['caption-position']] : ['relative'],
                'label': 'Caption Position',
                'choices': {
                    'absolute': 'Over Image',
                    'relative': 'Below Image'
                },
                'tag': 'select'
            });
            settings.add({
                'name': 'caption-width',
                'value': options['caption-width'] ? [options['caption-width']] : ['below'],
                'label': 'Caption Width',
                'choices': {
                    '100%': 'Width of gallery',
                    'image': 'Width of image'
                },
                'tag': 'select'
            });
            settings.add({
                'name': 'transition',
                'value': options['transition'] ? [options['transition']] : ['slide'],
                'label': 'Transition Type',
                'choices': {
                    'slide': 'Slide',
                    'fade': 'Fade'
                },
                'tag': 'select'
            });
            settings.add({
                'name': 'metadata',
                'label': 'Metadata for Captions',
                'tag': 'checkbox',
                'value': options['metadata'] ? options['metadata'] : ['full_title_ssi', 'creator_tesim'],
                'choices': {
                    'full_title_ssi': 'Title',
                    'creator_tesim': 'Creator,Contributor',
                    'date_ssi': 'Date Created',
                    'abstract_tesim': 'Abstract/Description'
                },
            });

            this.shortcode.set('settings', settings);
        } else if (type == 'timeline') {
            settings.add({
                'name': 'start-date',
                'value': options['start-date'] ? [options['start-date']] : [],
                'label': 'Start Date Boundary',
                'tag': 'number',
                'helper': 'year eg:1960'
            });
            settings.add({
                'name': 'end-date',
                'value': options['end-date'] ? [options['end-date']] : [],
                'label': 'End Date Boundary',
                'tag': 'number',
                'helper': 'year eg:1990'
            });
            settings.add({
                'name': 'metadata',
                'label': 'Metadata',
                'tag': 'checkbox',
                'value': options["metadata"] ? options["metadata"] : ['creator_tesim'],
                'choices': {
                    'creator_tesim': 'Creator,Contributor',
                    'abstract_tesim': 'Abstract/Description'
                },
            });
            settings.add({
                'name': 'increments',
                'label': 'Scale Increments',
                'tag': 'select',
                'value': options['increments'] ? [options['increments']] : [5],
                'choices': {
                    .5: 'Very Low',
                    2: 'Low',
                    5: 'Medium',
                    8: 'High',
                    13: 'Very High'
                },
                'helper': 'Specifies the granularity to represent items on the timeline'
            });
            this.shortcode.set('settings', settings);
        } else if (type == 'media') {
            settings.add({
                'name': 'height',
                'value': options['height'] ? [options['height']] : ["270"],
                'label': 'Height',
                'helper': '(Enter in pixels or %, Default is 270)',
                'tag': 'text'
            });
            settings.add({
                'name': 'width',
                'value': options['width'] ? [options['width']] : ["100%"],
                'label': 'Width',
                'tag': 'text',
                'helper': '(Enter in pixels or %, Default is 100%)'
            });
            //we historically have not provided interface for aspectratio, skin, and listbarwidth, TODO - add these
            this.shortcode.set('settings', settings);
        } else if (type == 'map') {
            settings.add({
                'name': 'story',
                'value': options['story'] ? options['story'] : ['yes'],
                'label': 'Story',
                'tag': 'checkbox',
                'choices': {
                    0: 'yes'
                },
            });
            settings.add({
                'name': 'metadata',
                'label': 'Metadata',
                'tag': 'checkbox',
                'value': options["metadata"] ? options["metadata"] : ['creator_tesim'],
                'choices': {
                    'creator_tesim': 'Creator,creator',
                    'date_ssi': 'Date Created',
                    'abstract_tesim': 'Abstract/Description'
                },

            });

            this.shortcode.set('settings', settings);
        } else {
            // @TODO presumably this should give something to the UI? But what? PMJ
            console.log("not a known shortcode type");
        }
    },

    settingsAddColor: function(e) {
        type = this.shortcode.get('type');
        colorsettings = this.shortcode.get('colorsettings');
        name = 'label-text-' + click_counter + '_desc';
        value = 'label-' + click_counter;
        label = 'label-' + click_counter;
        colorname = 'label-color-' + click_counter;
        colorsettings.add({
            'name': name,
            'value': value,
            'label': label,
            'tag': 'inputcolor',
            'colorname': colorname,
            'colorHex': '#0080ff',
        });
        this.shortcode.set('colorsettings', colorsettings);
        this.getSettings();
        click_counter = click_counter + 1;
    },

    deleteColorRow: function(e) {
        e.preventDefault();
        var id = "";
        var index_val = -1;
        id = jQuery(e.currentTarget).attr("id");
        type = this.shortcode.get('type');
        class_name_original = id.substr(7, id.length);
        class_name = "#settings > table.color-table > tbody > tr." + class_name_original;
        colorsettings = this.shortcode.get('colorsettings');
        jQuery.each(colorsettings.models, function(index, value) {
            if (value.attributes["name"].toString() == class_name_original) {
                index_val = index;
            }
        });
        colorsettings.models.splice(index_val, 1);
        this.shortcode.set('colorsettings', colorsettings);
        this.getSettings();
        jQuery(class_name).remove();
    },

    /* navigation between shortcode types */
    navigate: function(e) {
        "use strict";
        this.search_params.page = 1;
        this.geo_count = 0;
        this.time_count = 0;
        this.shortcode.set('settings', new drstk.Settings());
        this.shortcode.set('colorsettings', new drstk.ColorSettings());
        if (this.shortcode.items) {
            this.select_all = false;
            this.shortcode.items = new drstk.Items();
        }
        jQuery(".navigation-bar a").removeClass("active");
        this.showTab(jQuery(e.currentTarget).attr("href"));
    },

    /* navigate tabs within a chosen shortcode type */
    navigateShortcode: function(e) {
        var path = jQuery(e.currentTarget).attr("href");
        jQuery(".nav-tab").removeClass("nav-tab-active");
        jQuery(e.currentTarget).addClass("nav-tab-active");
        this.search_params.page = 1;
        this.search_params.q = "";
        jQuery(".pane").hide();
        // @TODO this looks like switch is more appropriate
        if (path == '#drs') {
            jQuery("#drs").show();
            jQuery("#drs input[name='search']").val(this.search_params.q);
            this.getDRSitems();
        } else if (path == '#dpla') {
            jQuery("#dpla input[name='search']").val(this.search_params.q);
            jQuery("#dpla").show();
            if (this.current_tab == 4) {
                jQuery("#dpla").html("<div class='notice notice-warning'><p>DPLA items cannot be used in embedded media. If you would like to use a media item from the DPLA, consider downloading it and upload it using the 'Local Items' tab.</p></div>");
            } else {
                jQuery("#dpla ol").children("li").remove();
                // PMJ putting in an empty thing until the messages can be refactored
                // because of the new direct insert of URL code as of e814f1d8bf930512a8e4d079a7fdc15456932d59
                jQuery(".dpla-items").html("<div class='notice notice-info'></div>");
            }
        } else if (path == '#local') {
            jQuery("#local").show();
            this.getMediaitems();
        } else if (path == '#selected') {
            jQuery("#selected").show();
            this.getSelecteditems();
            tab_name = this.tabs[this.current_tab]
            var self = this;

            //Display items as disabled after switching tab between DRSItems and Selected items if the select-all
            //checkbox is enables
            if (jQuery("#drs-select-all-item").prop("checked")) {
                jQuery("#selected #sortable-" + tab_name + "-list").find("li input").prop("disabled", true);
            }
            jQuery("#selected #sortable-" + tab_name + "-list").sortable({
                update: function(event, ui) {
                    _.each(_.clone(self.shortcode.items.models), function(model) {
                        model.destroy();
                    });
                    jQuery.each(event.target.children, function(i, item) {
                        pid = jQuery(item).find("input").val();
                        title = jQuery(item).find(".title").text();
                        thumbnail = jQuery(item).find("img").attr("src");
                        repo = jQuery(item).find("input").attr("class").split(" ")[1];
                        if (self.shortcode.items.length == 0) {
                            self.shortcode.items = new drstk.Items({
                                'title': title,
                                'pid': pid,
                                'thumbnail': thumbnail,
                                'repo': repo
                            })
                        } else {
                            self.shortcode.items.add({
                                'title': title,
                                'pid': pid,
                                'thumbnail': thumbnail,
                                'repo': repo
                            })
                        }
                    });
                }
            });
        } else if (path == '#settings') {
            jQuery("#settings").show();
            this.getSettings();
        }
    },

    showTab: function(id) {
        jQuery(".backbone_modal-main article").html("");
        var title = ""
        switch (id) {
            case "#one":
                this.current_tab = 1
                title = "Single Item"
                //clear items if there are more than one at this point
                if (this.shortcode.items != undefined && this.shortcode.items.length > 1) {
                    var self = this;
                    _.each(_.clone(this.shortcode.items.models), function(item) {
                        item.destroy();
                    });
                }
                break;
            case "#two":
                this.current_tab = 2
                title = "Tile Gallery"
                break;
            case "#three":
                this.current_tab = 3
                title = "Gallery Slider"
                break;
            case "#four":
                this.current_tab = 4
                title = "Media Playlist"
                break;
            case "#five":
                this.current_tab = 5
                title = "Map"
                break;
            case "#six":
                this.current_tab = 6
                title = "Timeline"
                break;
        }
        jQuery(".backbone_modal-main article").append(this.templates.tabContent({
            title: title,
            type: this.tabs[this.current_tab]
        }));
        jQuery(".navigation-bar a[href=" + id + "]").addClass("active");
        jQuery("#drs").show();
        if (!this.select_all) {
            this.getDRSitems();
        }
        this.shortcode.set({
            "type": this.tabs[this.current_tab]
        });
        this.setDefaultSettings();
    },

    getDRSitems: function() {
        if (this.current_tab == 4) {
            this.search_params.avfilter = true;
        } else {
            delete this.search_params.avfilter;
        }
        if (this.current_tab == 5) {
            this.search_params.spatialfilter = true;
        } else {
            delete this.search_params.spatialfilter;
        }
        if (this.current_tab == 6) {
            this.search_params.timefilter = true;
        } else {
            delete this.search_params.timefilter;
        }
        var self = this;
        if (self.search_params.page == 1) { //reset time/geo counts when we're on the first page
            self.geo_count = 0;
            self.time_count = 0;
        }
        tab_name = this.tabs[this.current_tab]
        jQuery.post(drs_ajax_obj.ajax_url, {
            _ajax_nonce: drs_ajax_obj.drs_ajax_nonce,
            action: "get_drs_code",
            params: this.search_params,
        }, function(response) {
            var data = jQuery.parseJSON(response);
            jQuery("#drs #sortable-" + tab_name + "-list").children("li").remove();
            jQuery(".drs-pagination").html("");
            if (jQuery.type(data) === "string") {
                jQuery(".drs-items").html("<div class='notice notice-warning'><p>No results were retrieved for your query. Please try a different query.</p></div>");
            } else if (data.response != undefined && data.response.response.numFound > 0) {
                jQuery.each(data.response.response.docs, function(id, item) {
                    if (id === 19) { // this is the last one
                        last = true;
                    } else {
                        last = false;
                    }
                    if (item.active_fedora_model_ssi == 'CoreFile') {
                        this_item = new drstk.Item;
                        thumb = "https://repository.library.northeastern.edu" + item.thumbnail_list_tesim[0];
                        this_item.set("pid", item.id).set("thumbnail", thumb).set("repo", "drs").set("title", item.full_title_ssi);
                        if (item.key_date_ssi) {
                            this_item.set("key_date", item.key_date_ssi)
                        }
                        if (item.subject_geographic_tesim) {
                            this_item.set("coords", item.subject_geographic_tesim[0])
                        }
                        if (item.subject_cartographics_coordinates_tesim) {
                            this_item.set("coords", item.subject_cartographics_coordinates_tesim)
                        }
                        view = new drstk.ItemView({
                            model: this_item
                        });
                        jQuery("#drs #sortable-" + tab_name + "-list").append(view.el);
                        if (self.current_tab == 6) {
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type").append("<p>Date: <span class='key_date'>" + item.key_date_ssi + "</span></p>");
                        }
                        if (self.current_tab == 5) {
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type").append("<p>Map Info: <span class='coords'>" + this_item.get("coords") + "</span></p>");
                        }
                        if (self.shortcode.items != undefined && self.shortcode.items.where({
                                pid: item.id
                            }).length > 0) {
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("checked", true);
                            if (self.select_all == true) {
                                jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("disabled", true);
                            }
                            short_item = self.shortcode.items.where({
                                pid: item.id
                            })[0];
                            if (!short_item.get("title")) {
                                short_item.set("title", item.full_title_ssi);
                            }
                            if (!short_item.get("thumbnail")) {
                                short_item.set("thumbnail", thumb);
                            }
                            if (!short_item.get("key_date") && item.key_date_ssi) {
                                short_item.set("key_date", item.key_date_ssi)
                            }
                            if (!short_item.get("coords") && item.subject_geographic_tesim) {
                                short_item.set("coords", item.subject_geographic_tesim[0])
                            }
                            if (item.subject_cartographics_coordinates_tesim) {
                                short_item.set("coords", item.subject_cartographics_coordinates_tesim)
                            }
                        } else if (self.select_all == true) { //if its a selectAll then we automatically do that selectAllItem
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("checked", true);
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("disabled", true);
                            jQuery("#drs #sortable-" + tab_name + "-list").find("li:last-of-type .tile").trigger("change");
                        }
                        jQuery(".drs-items").html("");
                    }
                });
                self.updateDRSPagination(data);
                if (self.search_params.facets != {}) {
                    jQuery(".drs-type, .drs-subject").html("");
                    _.each(data.response.facet_counts.facet_fields, function(facet_vals, facet_name) {
                        if (facet_name == "creator_sim" || facet_name == "subject_sim" || facet_name == "type_sim" || facet_name == "creation_year_sim") {
                            if (facet_name == "creator_sim") {
                                this_facet = "creator";
                            }
                            if (facet_name == "subject_sim") {
                                this_facet = "subject";
                            }
                            if (facet_name == "type_sim") {
                                this_facet = "type";
                            }
                            if (facet_name == "creation_year_sim") {
                                this_facet = "date";
                            }
                            jQuery(".drs-" + this_facet).html("<b>" + this_facet.charAt(0).toUpperCase() + this_facet.slice(1) + "</b>");
                            if (facet_vals != undefined) {
                                if (Object.keys(facet_vals).length > 0) {
                                    var sorted = [];
                                    _.each(facet_vals, function(facet_count, facet_val) {
                                        var obj = {};
                                        obj[facet_val] = facet_count;
                                        sorted.push(obj);
                                    });
                                    sorted.sort(function(a, b) {
                                        if (a[Object.keys(a)[0]] > b[Object.keys(b)[0]]) {
                                            return -1;
                                        }
                                        if (a[Object.keys(a)[0]] < b[Object.keys(b)[0]]) {
                                            return 1;
                                        }
                                        return 0; // a must be equal to b
                                    });
                                    var facet_html = "<table class='facets-filter'><tbody>";
                                    for (var i = 0; i <= 4; i++) {
                                        if (sorted[i] != undefined) {
                                            key = Object.keys(sorted[i])[0];
                                            // this puts spaces around subject headings (or other) that create super long string,
                                            // which bork up the layout
                                            var spacedKey = key.replace(/--/g, ' -- ');

                                            facet_html += "<tr>" +
                                            		        "<td>" +
                                            		          "<a href='' data-facet-val='" + key + "' data-facet-name='" + this_facet + "' class='drs-facet-add'>" + spacedKey + "</a>" +
                                            		        "</td>" +
                                            		        "<td>" +
                                            		          "<span class='facet-value'>" + sorted[i][key] + "</span>" +
                                            		        "</td>" +
                                            		      "</tr>";
                                        }
                                    }
                                    facet_html += '</tbody></table>';
                                    jQuery(".drs-" + this_facet).append(facet_html);
                                    if (sorted.length > 5) {
                                        facet_html = "<a href='' class='drs-expand-facet' data-facet-name='" + this_facet + "'>View More</a><div class='drs-expanded-facet-" + this_facet + " hidden'>" +
                                        		"<table class='facets-filter>";
                                        _.each(sorted, function(facet_obj, i) {
                                            if (i > 4) { //don't repeat already displayed facets
                                                key = Object.keys(facet_obj)[0];
                                                // this puts spaces around subject headings (or other) that create super long string,
                                                // which bork up the layout
                                                var spacedKey = key.replace(/--/g, ' -- ');
                                                facet_html += "<tr>" +
                                                		        "<td>" +
                                                		          "<a href='' data-facet-val='" + key + "' data-facet-name='" + this_facet + "' class='drs-facet-add'>" + spacedKey + "</a>" +
                                                		        "</td>" +
                                                		        "<td>" +
                                                		          "<span class='facet-value'>" + facet_obj[key] + "</span>" +
                                                		        "</td>" +
                                                		      "</tr>";
                                            }
                                        });
                                        facet_html += "</table>";
                                        jQuery(".drs-" + this_facet).append(facet_html);
                                    }
                                    jQuery(".drs-" + this_facet).append('</div>');
                                }
                            }
                        }
                    });
                    facet_buttons = "";
                    _.each(self.search_params.facets, function(facet_val, facet_name) {
                        if (typeof facet_val == "string" || typeof facet_val == "number") {
                            facet_buttons += "<a href='' data-facet-name='" + facet_name + "' data-facet-val='" + facet_val + "' class='button drs-facet-remove'>" + facet_name.charAt(0).toUpperCase() + facet_name.slice(1) + " : " + facet_val + " <span class='dashicons dashicons-trash'> </span></a>";
                        } else {
                            _.each(facet_val, function(facet_value) {
                                facet_buttons += "<a href='' data-facet-name='" + facet_name + "' data-facet-val='" + facet_value + "' class='button drs-facet-remove'>" + facet_name.charAt(0).toUpperCase() + facet_name.slice(1) + " : " + facet_value + " <span class='dashicons dashicons-trash'> </span></a>";
                            });
                        }
                    });
                    jQuery(".drs-chosen").html(facet_buttons);
                }
            } else {
                jQuery(".drs-items").html("<div class='notice notice-warning'><p>No results were retrieved for your query. Please try a different query.</p></div>");
            }
        });
    },

    selectItem: function(e) {
        item = jQuery(e.currentTarget);
        pid = item.val();
        title = item.siblings(".title").text();
        thumbnail = item.siblings("img").attr("src");
        parent = item.parents(".pane").attr("id");
        if (item.parents("li").find(".key_date").text()) {
            key_date = item.parents("li").find(".key_date").text();
        } else {
            key_date = '';
        }
        if (item.parents("li").find(".coords").text()) {
            coords = item.parents("li").find(".coords").text();
        } else {
            coords = '';
        }
        if (parent == 'drs') {
            repo = 'drs'
        } else if (parent == 'dpla') {
            repo = 'dpla'
        } else {
            repo = 'local'
        }
        if (item.is(":checked")) {
            if (this.shortcode.items === undefined) {
                this.shortcode.items = new drstk.Items({
                    'title': title,
                    'pid': pid,
                    'thumbnail': thumbnail,
                    'repo': repo,
                    'key_date': key_date,
                    'coords': coords
                })
            } else if (this.shortcode.items.where({
                    pid: pid
                }).length == 0) {
                this.shortcode.items.add({
                    'title': title,
                    'pid': pid,
                    'thumbnail': thumbnail,
                    'repo': repo,
                    'key_date': key_date,
                    'coords': coords
                })
            }
            if (this.shortcode.get('type') == 'single') {
                var self = this;
                //single items can only have one items so we'll clear the rest out
                item.parents("ol").find("input:checked").not(item).each(function() {
                    jQuery(this).prop("checked", false);
                    pid = jQuery(this).val();
                    var remove = self.shortcode.items.where({
                        pid: pid
                    });
                    self.shortcode.items.remove(remove);
                });
            }
            if (this.shortcode.get('type') == 'single' && parent == 'drs') {
                settings = self.shortcode.get('settings');
                choices_array = ["Title", "Abstract/Description", "Creator", "Date Created"];
                choices = {}
                jQuery.each(choices_array, function(i, choice) {
                    choices[choice] = choice;
                });
                oldmeta = settings.where({
                    name: 'metadata'
                });
                settings.remove(oldmeta);
                settings.add({
                    'name': 'metadata',
                    'label': 'Metadata to Display',
                    'tag': 'checkbox',
                    'value': [],
                    'choices': choices,
                });
                self.shortcode.set('settings', settings);
            } else if (this.shortcode.get('type') == 'single' && parent == 'dpla') {
                old_search = this.search_params;
                local_params = this.search_params;
                var self = this;
                local_params.pid = pid;
                jQuery.post(dpla_ajax_obj.ajax_url, {
                    _ajax_nonce: dpla_ajax_obj.dpla_ajax_nonce,
                    action: "get_dpla_code",
                    params: local_params,
                }, function(data) {
                    var data = jQuery.parseJSON(data);
                    data = data.docs[0]
                    choices = {}
                    settings = self.shortcode.get('settings');
                    if (data.sourceResource.title) {
                        choices["Title"] = "Title"
                    }
                    if (data.sourceResource.description) {
                        choices["Abstract/Description"] = "Abstract/Description"
                    }
                    if (data.sourceResource.contributor) {
                        choices["Creator"] = "Creator"
                    }
                    if (data.sourceResource.date.displayDate) {
                        choices["Date Created"] = "Date Created"
                    }
                    oldmeta = settings.where({
                        name: 'metadata'
                    });
                    settings.remove(oldmeta);
                    if (Object.keys(choices).length > 0) {
                        settings.add({
                            'name': 'metadata',
                            'label': 'Metadata to Display',
                            'tag': 'checkbox',
                            'value': [],
                            'choices': choices,
                        });
                        self.shortcode.set('settings', settings);
                    }
                });
                this.search_params = old_search;
            }
        } else {
            var remove = this.shortcode.items.where({
                pid: pid
            });
            this.shortcode.items.remove(remove);
        }
    },

    updateDRSPagination: function(data) {
        if (data.pagination.table.num_pages > 1) {
            this.result_count = data.pagination.table.total_count;
            var pagination = "";
            if (data.pagination.table.current_page > 1) {
                pagination += "<a href='#' class='prev-page'>&lt;&lt;</a>";
            } else {
                pagination += "<a href='#' class='prev-page disabled'>&lt;&lt;</a>";
            }
            for (var i = 1; i <= data.pagination.table.num_pages; i++) {
                if (data.pagination.table.current_page == i) {
                    var pagination_class = 'current-page active';
                } else {
                    var pagination_class = '';
                }
                pagination += "<a href='#' class='" + pagination_class + "'>" + i + "</a>";
            }
            if (data.pagination.table.current_page == data.pagination.table.num_pages) {
                pagination += "<a href='#' class='next-page' data-val='" + data.pagination.table.num_pages + "'>&gt;&gt;</a>";
            } else {
                pagination += "<a href='#' class='next-page disabled' data-val='" + data.pagination.table.num_pages + "'>&gt;&gt;</a>";
            }
            jQuery(".drs-pagination").html("<span class='tablenav'><span class='tablenav-pages'>" + pagination + "</span></span>");

        } else {
            jQuery(".drs-pagination").html("");
        }
    },

    paginate: function(e) {
        val = jQuery(e.currentTarget).html();
        val = jQuery.trim(val);
        type = jQuery(e.currentTarget).parents(".pane").attr("id");
        current_page = jQuery("#" + type + " .tablenav-pages .current-page").html();
        if (val == '&lt;&lt;') {
            val = parseInt(current_page) - 1;
        }
        if (val == '&gt;&gt;') {
            val = parseInt(current_page) + 1;
            if (jQuery("#" + type + " .tablenav-pages .current-page").next('a').html() == '&gt;&gt;') { //last page
                val = 0;
            }
        }
        if (jQuery.isNumeric(val) && val != 0) {
            this.search_params.page = val;
            if (type == 'drs') {
                this.getDRSitems();
            } else if (type == 'dpla') {
                this.getDPLAitems();
            }
        }
    },

    getDPLAitems: function() {
        if (this.current_tab == 4) {
            this.search_params.avfilter = true;
        } else {
            delete this.search_params.avfilter;
        }
        if (this.current_tab == 5) {
            this.search_params.spatialfilter = true;
        } else {
            delete this.search_params.spatialfilter;
        }
        if (this.current_tab == 6) {
            this.search_params.timefilter = true;
        } else {
            delete this.search_params.timefilter;
        }
        var self = this;
        tab_name = this.tabs[this.current_tab];
        jQuery.post(dpla_ajax_obj.ajax_url, {
            _ajax_nonce: dpla_ajax_obj.dpla_ajax_nonce,
            action: "get_dpla_code",
            params: this.search_params,
        }, function(data) {
            var data = jQuery.parseJSON(data);
            jQuery("#dpla #sortable-" + tab_name + "-list").children("li").remove();
            if (data.count > 0) {
                jQuery(".dpla-items").html("");
                jQuery.each(data.docs, function(id, item) {
                    if (self.current_tab == 6) {
                        date = self.getDateFromSourceResource(item.sourceResource);
                    } else {
                        date = ""
                    }
                    if (self.current_tab == 5) {
                        coords = item.sourceResource.spatial[0].name;
                        if (item.sourceResource.spatial[0].coordinates != "" && item.sourceResource.spatial[0].coordinates != undefined) {
                            coords = item.sourceResource.spatial[0].coordinates;
                        }
                    } else {
                        coords = ""
                    }
                    if ((self.current_tab == 6 && date != "") || (self.current_tab == 5 && coords != "") || (self.current_tab != 5 && self.current_tab != 6)) {
                        this_item = new drstk.Item;
                        var title = item.sourceResource.title;
                        if (Array.isArray(title)) {
                            title = title[0];
                        }
                        this_item.set("pid", item.id).set("thumbnail", item.object).set("repo", "dpla").set("title", title);
                        if (self.current_tab == 6) {
                            this_item.set("key_date", date);
                        }
                        if (self.current_tab == 5) {
                            this_item.set("coords", coords);
                        }
                        view = new drstk.ItemView({
                            model: this_item
                        });
                        jQuery("#dpla #sortable-" + tab_name + "-list").append(view.el);
                        if (self.current_tab == 6) {
                            jQuery("#dpla #sortable-" + tab_name + "-list").find("li:last-of-type").append("<p>Date: <span class='key_date hidden'>" + date.join("-") + "</span>" + item.sourceResource.date.displayDate + "</p>");
                        }
                        if (self.current_tab == 5) {
                            jQuery("#dpla #sortable-" + tab_name + "-list").find("li:last-of-type").append("<p>Map Info: <span class='coords hidden'>" + this_item.get("coords") + "</span>" + this_item.get("coords") + "</p>");
                        }
                        if (self.shortcode.items != undefined && self.shortcode.items.where({
                                pid: item.id
                            }).length > 0) {
                            jQuery("#dpla #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("checked", true);
                            short_item = self.shortcode.items.where({
                                pid: item.id
                            })[0];
                            if (!short_item.get("title")) {
                                short_item.set("title", title);
                            }
                            if (!short_item.get("thumbnail")) {
                                short_item.set("thumbnail", item.object);
                            }
                            if ((!short_item.get("key_date") || short_item.get("key_date") == "" || short_item.get("key_date") == undefined || short_item.get("key_date") == []) && self.current_tab == 6) {
                                short_item.set("key_date", date);
                            }
                            if ((!short_item.get("coords") || short_item.get("coords") == "" || short_item.get("coords") == undefined) && self.current_tab == 5) {
                                short_item.set("coords", coords);
                            }
                        }
                    }
                });
                if (self.search_params.q != "") { //too much pagination if there isn't a query
                    self.updateDPLAPagination(data);
                }
                if (self.search_params.facets != {}) {
                    jQuery(".dpla-type, .dpla-subject").html("");
                    _.each(data.facets, function(facet, facet_name) {
                        if (facet_name == "sourceResource.contributor" || facet_name == "sourceResource.subject.name" || facet_name == "sourceResource.type") {
                            if (facet_name == "sourceResource.contributor") {
                                this_facet = "creator";
                            }
                            if (facet_name == "sourceResource.subject.name") {
                                this_facet = "subject";
                            }
                            if (facet_name == "sourceResource.type") {
                                this_facet = "type";
                            }
                            jQuery(".dpla-" + this_facet).html("<b>" + this_facet.charAt(0).toUpperCase() + this_facet.slice(1) + "</b>");
                            if (facet.terms != undefined) {
                                if (facet.terms.length > 0) {
                                    for (var i = 0; i <= 4; i++) {
                                        if (facet.terms[i] != undefined) {
                                            facet_val = facet.terms[i].term;
                                            facet_count = facet.terms[i].count;
                                            facet_html = "<tr><td><a href='' data-facet-val='" + facet_val + "' data-facet-name='" + this_facet + "' class='dpla-facet-add'>" + facet_val + "</a></td><td><a href=''>" + facet_count + "</a></td></tr>";
                                            jQuery(".dpla-" + this_facet).append(facet_html);
                                        }
                                    }
                                    if (facet.terms.length > 5) {
                                        facet_html = "<a href='' class='dpla-expand-facet' data-facet-name='" + this_facet + "'>View More</a><div class='dpla-expanded-facet-" + this_facet + " hidden'><table>";
                                        _.each(facet.terms, function(facet_obj, i) {
                                            if (i > 4) { //don't repeat already displayed facets
                                                facet_html += "<tr><td><a href='' data-facet-val='" + facet_obj.term + "' data-facet-name='" + this_facet + "' class='dpla-facet-add'>" + facet_obj.term + "</a></td><td><a href=''>" + facet_obj.count + "</a></td></tr>";
                                            }
                                        });
                                        facet_html += "</table></div>";
                                        jQuery(".dpla-" + this_facet).append(facet_html);
                                    }
                                }
                            }
                        }
                    });
                    jQuery(".dpla-date").html("<b>Date Created</b><br/><div class='dpla-date-slider'></div><span class='start'></span> - <span class='end'> </span><a class='button dpla-update-date'>Update</a>");
                    dates = [1000, new Date().getFullYear()];
                    var min = 1000;
                    var max = new Date().getFullYear();
                    if (self.search_params.facets.date != undefined) {
                        dates = self.search_params.facets.date;
                    }
                    jQuery(".dpla-date-slider").slider({
                        range: true,
                        min: parseInt(min),
                        max: parseInt(max),
                        values: dates,
                        slide: function(event, ui) {
                            self.search_params.facets.date = [ui.values[0], ui.values[1]];
                            jQuery('.dpla-date .start').text(ui.values[0]);
                            jQuery('.dpla-date .end').text(ui.values[1]);
                        },
                        create: function(event) {
                            if (self.search_params.facets.date != undefined) {
                                jQuery('.dpla-date .start').text(self.search_params.facets.date[0]);
                                jQuery('.dpla-date .end').text(self.search_params.facets.date[1]);
                            } else {
                                jQuery('.dpla-date .start').text(parseInt(min));
                                jQuery('.dpla-date .end').text(parseInt(max));
                            }
                        }
                    });
                    facet_buttons = ""
                    _.each(self.search_params.facets, function(facet_val, facet_name) {
                        if (facet_name != "date") {
                            if (typeof facet_val == "string") {
                                facet_buttons += "<a href='' data-facet-name='" + facet_name + "' data-facet-val='" + facet_val + "' class='button dpla-facet-remove'>" + facet_name.charAt(0).toUpperCase() + facet_name.slice(1) + " : " + facet_val + " <span class='dashicons dashicons-trash'> </span></a>";
                            } else {
                                _.each(facet_val, function(facet_value) {
                                    facet_buttons += "<a href='' data-facet-name='" + facet_name + "' data-facet-val='" + facet_value + "' class='button dpla-facet-remove'>" + facet_name.charAt(0).toUpperCase() + facet_name.slice(1) + " : " + facet_value + " <span class='dashicons dashicons-trash'> </span></a>";
                                });
                            }
                        }
                    });
                    jQuery(".dpla-chosen").html(facet_buttons);
                }
            } else {
                jQuery(".dpla-items").html("<div class='notice notice-warning'><p>No results were retrieved for your query. Please try a different query.</p></div>");
                jQuery("#dpla-pagination").html("");
            }
        });
        if (jQuery(".dpla-facets-button").hasClass("hidden") && jQuery(".dpla-facets").hasClass("hidden")) {
            jQuery(".dpla-facets-button").removeClass("hidden");
        }
    },

    getDateFromSourceResource: function(source) {
        date = "";
        if (Array.isArray(source.date)) {
            source.date = source.date[0];
        }
        date = source.date.displayDate;
        if (date != undefined && date != "") {
            date = date.split("-");
            if (date[0] && date[0].length != 4 && source.date.begin != undefined) {
                begin_date = source.date.begin;
            } else if (date[0] == undefined && source.date.begin != undefined) {
                begin_date = source.date.begin;
            } else if (date[0] == undefined && source.date.begin == undefined) {
                begin_date = "";
            } else {
                begin_date = date[0];
            }
            begin_date = begin_date.split("-")[0];
            if (date[1] && date[1].length != 4 && source.date.end != undefined) {
                end_date = source.date.end;
            } else if (date[1] == undefined && source.date.end != undefined) {
                end_date = source.date.end;
            } else if (date[1] == undefined && source.date.end == undefined) {
                end_date = "";
            } else {
                end_date = date[1];
            }
            end_date = end_date.split("-")[0];
            if (!jQuery.isNumeric(begin_date)) {
                begin_date = ""
            }
            if (!jQuery.isNumeric(end_date)) {
                end_date = ""
            }
            date = [begin_date, end_date];
        }
        return date;
    },

    updateDPLAPagination: function(data) {
        num_pages = Math.round(data.count / data.limit);
        current_page = parseInt(this.search_params.page);
        if (num_pages > 1) {
            var pagination = "";
            if (current_page > 1) {
                pagination += "<a href='#' class='prev-page' data-val='" + parseInt(current_page - 1) + "'>&lt;&lt;</a>";
            }
            if (current_page >= 3) {
                pagination += "<a href='#' class=''>" + parseInt(current_page - 2) + "</a>";
            }
            if (current_page >= 2) {
                pagination += "<a href='#' class=''>" + parseInt(current_page - 1) + "</a>";
            }
            pagination += "<a href='#' class='current-page active'>" + current_page + "</a>";
            if (current_page + 1 < num_pages) {
                pagination += "<a href='#' class=''>" + parseInt(current_page + 1) + "</a>";
            }
            if (current_page + 2 < num_pages) {
                pagination += "<a href='#' class=''>" + parseInt(current_page + 2) + "</a>";
            }
            if (current_page + 1 != num_pages) {
                pagination += "<a href='#' class='next-page' data-val='" + parseInt(current_page + 1) + "'>&gt;&gt;</a>";
            }
            jQuery("#dpla-pagination").html("<span class='tablenav'><span class='tablenav-pages'>" + pagination + "</span></span>");
        } else {
            jQuery("#dpla-pagination").html("");
        }
    },

    search: function(e) {
        this.search_params.q = jQuery(e.currentTarget).siblings("input.drstk-search-input").val();
        parent = jQuery(e.currentTarget).parents(".pane").attr("id");
        if (parent == 'drs') {
            this.getDRSitems();
        } else if (parent == 'dpla') {
            this.getDPLAitems();
        }
    },

    getSelecteditems: function() {
        tab_name = this.tabs[this.current_tab];
        if (this.shortcode.items != undefined) {
            count = this.shortcode.items.length;
            if (count > 0) {
                jQuery(".selected-items").html("");
                if (tab_name == "tile" || tab_name == "slider" || tab_name == "media") {
                    jQuery(".selected-items").append("<div class='notice notice-info'><p>Drag and drop items to reorder.</p></div>");
                }
                jQuery("#selected #sortable-" + tab_name + "-list").children("li").remove();
                var self = this;
                new_items = [];
                jQuery.each(this.shortcode.items.models, function(i, item) {
                    if (!item.get("title")) {
                        jQuery(".selected-items").html("Loading...");
                        count = parseInt(count) + 1;
                        repo = item.get("repo");
                        if (repo == "drs") {
                            jQuery.ajax({
                                url: item_admin_obj.ajax_url,
                                type: "POST",
                                data: {
                                    action: "get_item_solr_admin",
                                    _ajax_nonce: item_admin_obj.item_admin_nonce,
                                    pid: item.get("pid"),
                                },
                                complete: function(data) {
                                    var data = jQuery.parseJSON(data.responseJSON);
                                    data = data['_source'];
                                    item.set("title", data.full_title_ssi);
                                    if (!item.get("thumbnail")) {
                                        item.set("thumbnail", "https://repository.library.northeastern.edu" + data.fields_thumbnail_list_tesim[0]);
                                    }
                                    if (!item.get("key_date") || item.get("key_date") == "" || item.get("key_date") == undefined) {
                                        item.set("key_date", data.key_date_ssi);
                                    }
                                    if (!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) {
                                        if (data.subject_geographic_tesim) {
                                            item.set("coords", data.subject_geographic_tesim[0])
                                        }
                                        if (data.subject_cartographics_coordinates_tesim) {
                                            item.set("coords", data.subject_cartographics_coordinates_tesim)
                                        }
                                    }
                                    new_items.push(item.get("pid"));
                                }
                            });
                        } else if (repo == "dpla") {
                            jQuery.post(dpla_ajax_obj.ajax_url, {
                                _ajax_nonce: dpla_ajax_obj.dpla_ajax_nonce,
                                action: "get_dpla_code",
                                params: {
                                    q: item.get("pid")
                                },
                            }, function(data) {
                                var data = jQuery.parseJSON(data);
                                item.set("title", data.docs[0].sourceResource.title);
                                if (data.docs[0].object) {
                                    item.set("thumbnail", data.docs[0].object);
                                }
                                if ((!item.get("key_date") || item.get("key_date") == "" || item.get("key_date") == undefined) && self.current_tab == 6) {
                                    date = self.getDateFromSourceResource(data.docs[0].sourceResource);
                                    item.set("key_date", date);
                                }
                                if ((!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) && self.current_tab == 5) {
                                    coords = data.docs[0].sourceResource.spatial[0].name;
                                    if (data.docs[0].sourceResource.spatial[0].coordinates != "" && data.docs[0].sourceResource.spatial[0].coordinates != undefined) {
                                        coords = data.docs[0].sourceResource.spatial[0].coordinates;
                                    }
                                    item.set("coords", coords);
                                }
                                new_items.push(item.get("pid"));
                            });
                        } else if (repo == "local") {
                            jQuery.ajax({
                                url: item_admin_obj.ajax_url,
                                type: "POST",
                                data: {
                                    action: "get_post_meta",
                                    _ajax_nonce: item_admin_obj.item_admin_nonce,
                                    pid: item.get("pid"),
                                },
                                success: function(data) {
                                    item.set("title", data.post_title);
                                    if (!data.post_mime_type.includes("audio") && !data.post_mime_type.includes("video")) {
                                        item.set("thumbnail", data.guid);
                                    }
                                    if ((!item.get("key_date") || item.get("key_date") == "" || item.get("key_date") == undefined) && self.current_tab == 6) {
                                        jQuery.ajax({
                                            url: item_admin_obj.ajax_url,
                                            type: "POST",
                                            async: false,
                                            data: {
                                                action: "get_custom_meta",
                                                _ajax_nonce: item_admin_obj.item_admin_nonce,
                                                pid: item.get('pid'),
                                            },
                                            success: function(data) {
                                                item.set("key_date", data._timeline_date[0]);
                                            }
                                        });
                                    }
                                    if ((!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) && self.current_tab == 5) {
                                        jQuery.ajax({
                                            url: item_admin_obj.ajax_url,
                                            type: "POST",
                                            async: false,
                                            data: {
                                                action: "get_custom_meta",
                                                _ajax_nonce: item_admin_obj.item_admin_nonce,
                                                pid: item.get('pid'),
                                            },
                                            success: function(data) {
                                                item.set("coords", data._map_coords[0]);
                                            }
                                        });
                                    }
                                    new_items.push(item.get("pid"));
                                }
                            });
                        }
                    } else {
                        self.appendSingleItem(item);
                    }
                    //if its the last item then put it on a 1 second loop to see if all of the ajax calls have completed, then if they have, append the items so the order is preserved
                    var interval;
                    if (i === self.shortcode.items.models.length - 1) {
                        interval = setInterval(function() {
                            if (new_items.length === self.shortcode.items.models.length) {
                                clearInterval(interval);
                                jQuery(".selected-items").html("");
                                jQuery("#selected #sortable-" + tab_name + "-list").children("li").remove();
                                _.each(self.shortcode.items.models, function(item) {
                                    self.appendSingleItem(item);
                                });
                            } else {
                                //do nothing
                            }
                        }, 1000);
                    }
                });
            } else if (this.select_all == true) {
                jQuery(".selected-items").html("<div class='notice notice-warning'><p>Selected items are loading...</p></div>");
            } else {
                jQuery(".selected-items").html("<div class='notice notice-warning'><p>You haven't selected any items yet.</p></div>");
                jQuery("#selected #sortable-" + tab_name + "-list").children("li").remove();
            }
        } else if (this.select_all == true) {
            //if there are no items in the list yet, lets wait 2 seconds for the drs connections to try to finish before we refresh this tab
            jQuery(".selected-items").html("<div class='notice notice-warning'><p>Selected items are loading...</p></div>");
            if (jQuery("#selected #sortable-" + tab_name + "-list").children().length < 1) {
                var self = this;
                var interval;
                interval = setInterval(function() {
                    if (jQuery("#selected #sortable-" + tab_name + "-list").children().length < 1 || (jQuery("#selected #sortable-" + tab_name + "-list").children().length < self.result_count)) {
                        jQuery("#drs #drs-select-all-item").trigger('change'); //triggers SelectAllItem
                        jQuery(".nav-tab[href='#selected']").trigger('click'); //triggers navigateShortcode
                    } else {
                        clearInterval(interval);
                    }
                }, 2000);
            }
        } else {
            jQuery(".selected-items").html("<div class='notice notice-warning'><p>You haven't selected any items yet.</p></div>");
            jQuery("#selected #sortable-" + tab_name + "-list").children("li").remove();
        }
    },

    appendSingleItem: function(item) {
        tab_name = this.tabs[this.current_tab];
        var itemView = new drstk.ItemView({
            model: item
        });
        jQuery("#selected #sortable-" + tab_name + "-list").append(itemView.el);
        if (this.current_tab == 5 || this.current_tab == 6) {
            colors = "";
            var self = this;
            _.each(self.shortcode.get('colorsettings').models, function(color) {
                color = color.attributes.colorname;
                colors += "<option value='" + color + "'";
                if (self.options != undefined && self.options.settings != undefined) {
                    var preset_colors = self.options.settings[color + "_color_desc_id"];
                } else if (self.options != undefined) {
                    var preset_colors = self.options[color + "_id"] ? self.options[color + "_id"] : self.options[color];
                }
                if (preset_colors != undefined) {
                    preset_colors = preset_colors.split(",");
                    for (var i = 0; i < preset_colors.length; i++) {
                        preset_colors[i] = preset_colors[i].trim();
                    }
                }
                if (preset_colors != undefined && preset_colors.indexOf(item.attributes.pid) > -1) {
                    item.set("color", color);
                }
                if (item.attributes.color == color) {
                    colors += " selected='selected'";
                }
                colors += ">" + color.charAt(0).toUpperCase() + color.slice(1) + "</option>";
            });
            jQuery("#selected #sortable-" + tab_name + "-list").find("li:last-of-type label").append('<br/>Color label (see Settings tab):<br/> <select name="color"><option value="">Color Label</option>' + colors + '</select>');
        }
        if (this.shortcode.items.where({
                pid: item.attributes.pid
            }).length > 0) {
            jQuery("#selected #sortable-" + tab_name + "-list").find("li:last-of-type input").prop("checked", true);
        }
    },

    getSettings: function() {
        jQuery("#settings").html("<table />");
        _.each(this.shortcode.get('settings').models, function(setting, i) {
            var settingView = new drstk.SettingView({
                model: setting
            });
            jQuery("#settings table").append(settingView.el);
            jQuery("#settings table tr:last-of-type").addClass(setting.get('name'));
        });
        type = this.shortcode.get('type');
        if (type == 'map' || type == 'timeline') {
            jQuery('#settings').append("<div class='toolkitcolors'><h4>Color Settings</h4><button type='button' id ='addcolorbutton'>Add Color</button><br/><table class ='color-table striped'>" +
                "<tbody>" +
                "<tr class='colorheader'>" +
                "<td><h5>Description</h5></td>" +
                "<td><h5>Color Value</h5></td>" +
                "</tr></tbody>" +
                "</table></div>");
            _.each(this.shortcode.get('colorsettings').models, function(colorsetting, i) {
                var colorsettingView = new drstk.ColorSettingView({
                    model: colorsetting
                });
                jQuery("#settings .color-table").append(colorsettingView.el);
                jQuery(jQuery("#settings .color-table tr:last-of-type").addClass(colorsetting.get('name')));
            });
        }
    },

    settingsChange: function(e) {
        e.preventDefault();
        field_name = jQuery(e.currentTarget).attr("name");
        if (jQuery(e.currentTarget).attr("type") == "checkbox") {
            name = jQuery(e.currentTarget).parents("tr").attr("class");
            setting = this.shortcode.get('settings').where({
                name: name
            })[0];
            var vals = []
            jQuery(e.currentTarget).parents("td").find("input[type='checkbox']").each(function() {
                if (jQuery(this).is(":checked")) {
                    vals.push(jQuery(this).attr("name"));
                }
            });
            setting.set('value', vals);
        } else if (jQuery(e.currentTarget).attr("type") == "color") {
            var color = jQuery(e.currentTarget).val();
            name = jQuery(e.currentTarget).parents("td").prev("td").find("input").attr("name");
            colorsetting = this.shortcode.get('colorsettings').where({
                name: name
            })[0];
            colorsetting.set('colorHex', color);
        } else if (field_name.indexOf('label-text-') != -1) {
            name = jQuery(e.currentTarget).attr("name");
            colorsetting = this.shortcode.get('colorsettings').where({
                name: name
            })[0];
            val = jQuery(e.currentTarget).val();
            colorsetting.set('value', val);
            colorsetting.set('colorname', val);
        } else {
            name = jQuery(e.currentTarget).attr("name");
            setting = this.shortcode.get('settings').where({
                name: name
            })[0];
            val = jQuery(e.currentTarget).val();
            if (field_name == "end-date" || field_name == "start-date") {
                if (val == "") {
                    val = null;
                }
            }
            setting.set('value', [val]);
        }
    },

    validTime: function() {
        return_arr = [];
        no_year = [];
        key_date_list = [];
        _.each(_.clone(this.shortcode.items.where({
            repo: 'drs'
        })), function(item) {
            var key_date_year = item.get('key_date').split("/")[0];
            key_date_list.push({
                year: key_date_year,
                name: item.get('title')
            });
        });
        _.each(_.clone(this.shortcode.items.where({
            repo: 'local'
        })), function(item) {
            if (item.get('key_date') != undefined || item.get('key_date') != "" || item.get('key_date') != [] || !item.get('key_date')) {
                var key_date_year = item.get('key_date').split("/")[0];
                key_date_list.push({
                    year: key_date_year,
                    name: item.get('title')
                });
            } else {
                no_year.push(item.get('title'));
            }
        });
        _.each(_.clone(this.shortcode.items.where({
            repo: 'dpla'
        })), function(item) {
            if (!item.get("key_date") || item.get("key_date") == undefined || item.get("key_date") == "" || item.get("key_date") == []) {
                no_year.push(item.get('title'));
            } else {
                key_date_list.push({
                    year: item.get("key_date"),
                    name: item.get('title')
                });
            }
        });
        var self = this;
        key_date_list.forEach(function(each_key) {
            start_date = self.shortcode.get('settings').where({
                name: 'start-date'
            })[0];
            start_date = start_date.attributes.value[0];
            end_date = self.shortcode.get('settings').where({
                name: 'end-date'
            })[0];
            end_date = end_date.attributes.value[0];
            if (typeof each_key.year == "array") {
                if (each_key.year[0] < start_date && each_key.year[1] > end_date) {
                    return_arr.push(each_key.name);
                }
            } else {
                if (each_key.year < start_date || each_key.year > end_date) {
                    return_arr.push(each_key.name);
                }
            }
        });
        if (return_arr.length > 0 || no_year.length > 0) {
            return return_arr.concat(no_year);
        } else {
            return true;
        }
    },

    validMap: function() {
        no_map = [];
        key_date_list = [];
        _.each(_.clone(this.shortcode.items.where({
            repo: 'drs'
        })), function(item) {
            if (!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) {
                no_map.push(item.get('title'));
            }
        });
        _.each(_.clone(this.shortcode.items.where({
            repo: 'local'
        })), function(item) {
            if (!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) {
                no_map.push(item.get('title'));
            }
        });
        _.each(_.clone(this.shortcode.items.where({
            repo: 'dpla'
        })), function(item) {
            if (!item.get("coords") || item.get("coords") == "" || item.get("coords") == undefined) {
                no_map.push(item.get('title'));
            }
        });
        if (no_map.length > 0) {
            return no_map;
        } else {
            return true;
        }
    },

    changeColor: function(e) {
        color = jQuery(e.currentTarget).val();
        if (color != "") {
            pid = jQuery(e.currentTarget).siblings(".tile").val();
            item = this.shortcode.items.where({
                pid: pid
            });
            item[0].set({
                'color': color
            });
        }
    },

    getMediaitems: function() {
        jQuery("#local").html("<a class='button' id='wp_media'>Add or Browse Local Items</a><br/>");
        if (this.shortcode.items != undefined && this.shortcode.items.where({
                repo: 'local'
            }).length > 0) {
            var self = this;
            _.each(this.shortcode.items.where({
                repo: 'local'
            }), function(item) {
                pid = item.get('pid');
                thumbnail = item.get('thumbnail');
                repo = "local";
                title = item.get('title');
                this_item = new drstk.Item;
                this_item.set("pid", pid).set("thumbnail", thumbnail).set("repo", repo).set("title", title).set("coords", item.get("coords")).set("key_date", item.get("key_date"));
                if ((self.current_tab == 6 && (this_item.get("key_date") == undefined)) || (self.current_tab == 5 && (this_item.get("coords") == undefined))) {
                    jQuery.ajax({
                        url: item_admin_obj.ajax_url,
                        type: "POST",
                        async: false,
                        data: {
                            action: "get_custom_meta",
                            _ajax_nonce: item_admin_obj.item_admin_nonce,
                            pid: item.get('pid'),
                        },
                        success: function(data) {
                            if (self.current_tab == 5) {
                                this_item.set("coords", data._map_coords[0]);
                                item.set("coords", data._map_coords[0]);
                            }
                            if (self.current_tab == 6) {
                                this_item.set("key_date", data._timeline_date[0]);
                                item.set("key_date", data._timeline_date[0]);
                            }
                        }
                    });
                }
                view = new drstk.ItemView({
                    model: this_item
                });
                jQuery("#local").append(view.el);
                if (self.current_tab == 6) {
                    jQuery("#local").find("li:last-of-type").append("<p>Date: <span class='key_date'>" + this_item.get("key_date") + "</span></p>");
                }
                if (self.current_tab == 5) {
                    jQuery("#local").find("li:last-of-type").append("<p>Map Info: <span class='coords'>" + this_item.get("coords") + "</span></p>");
                }
                if (self.shortcode.items != undefined && self.shortcode.items.where({
                        pid: pid
                    }).length > 0) {
                    jQuery("#local").find("li:last-of-type input").prop("checked", true);
                }
            });

        }
    },

    addMediaItems: function(e) {
        if (typeof(frame) !== 'undefined') frame.close();
        if (this.current_tab == 1) {
            multiple = false;
        } else {
            multiple = true;
        }
        var self = this;
        frame = wp.media.frames.drstk_frame = wp.media({
            title: "Select Images",
            button: {
                text: "Add Selected Images"
            },
            multiple: multiple
        });
        frame.on('select', function() {
            var files = frame.state().get('selection').toJSON();
            jQuery.each(files, function(i) {
                pid = this.id.toString();
                title = this.title;
                thumbnail = (this.sizes != undefined) ? this.sizes.thumbnail.url : this.image.src;
                repo = "local";
                if (self.shortcode.items === undefined || self.shortcode.items.where({
                        pid: pid
                    }).length == 0) {
                    this_item = new drstk.Item;
                    this_item.set("pid", pid).set("thumbnail", thumbnail).set("repo", repo).set("title", title);
                    if (self.current_tab == 5 || self.current_tab == 6) {
                        jQuery.ajax({
                            url: item_admin_obj.ajax_url,
                            type: "POST",
                            async: false,
                            data: {
                                action: "get_custom_meta",
                                _ajax_nonce: item_admin_obj.item_admin_nonce,
                                pid: this_item.get('pid'),
                            },
                            success: function(data) {
                                if (self.current_tab == 6) {
                                    this_item.set("key_date", data._timeline_date[0]);
                                }
                                if (self.current_tab == 5) {
                                    this_item.set("coords", data._map_coords[0]);
                                }
                            }
                        });
                    }
                    if (self.shortcode.items === undefined) {
                        self.shortcode.items = new drstk.Items(this_item);
                    } else if (self.shortcode.items.where({
                            pid: pid
                        }).length == 0) {
                        self.shortcode.items.add(this_item);
                    }
                    view = new drstk.ItemView({
                        model: this_item
                    });
                    jQuery("#local").append(view.el);
                    jQuery("#local").find("li:last-of-type input").prop("checked", true);
                    if (self.current_tab == 6) {
                        jQuery("#local").find("li:last-of-type").append("<p>Date: <span class='key_date'>" + this_item.get("key_date") + "</span></p>");
                    }
                    if (self.current_tab == 5) {
                        jQuery("#local").find("li:last-of-type").append("<p>Map Info: <span class='coords'>" + this_item.get("coords") + "</span></p>");
                    }
                }
                if (self.current_tab == 1) {
                    jQuery.ajax({
                        url: item_admin_obj.ajax_url,
                        type: "POST",
                        data: {
                            action: "get_post_meta",
                            _ajax_nonce: item_admin_obj.item_admin_nonce,
                            pid: pid,
                        },
                        success: function(data) {
                            choices = {}
                            settings = self.shortcode.get('settings');
                            if (data.post_title) {
                                choices["title"] = "Title"
                            }
                            if (data.post_excerpt) {
                                choices["caption"] = "Caption"
                            }
                            oldmeta = settings.where({
                                name: 'metadata'
                            });
                            settings.remove(oldmeta);
                            if (Object.keys(choices).length > 0) {
                                settings.add({
                                    'name': 'metadata',
                                    'label': 'Metadata to Display',
                                    'tag': 'checkbox',
                                    'value': [],
                                    'choices': choices,
                                });
                                self.shortcode.set('settings', settings);
                            }
                        }
                    });
                }
            });
        }).open();
    },

    dplaSort: function(e) {
        e.preventDefault();
        this.search_params.sort = jQuery("select[name='dpla-sort']").val();
        this.getDPLAitems();
    },

    dplaFacet: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        if (this.search_params.facets[link.data("facet-name")] == undefined) {
            this.search_params.facets[link.data("facet-name")] = link.data("facet-val");
        } else if (this.search_params.facets[link.data("facet-name")].length > 0) {
            orig_value = this.search_params.facets[link.data("facet-name")]
            if (typeof orig_value == "string") {
                this.search_params.facets[link.data("facet-name")] = [orig_value, link.data("facet-val")];
            } else {
                this.search_params.facets[link.data("facet-name")].push(link.data("facet-val"));
            }
        }
        this.getDPLAitems();
    },

    dplaFacetToggle: function(e) {
        e.preventDefault();
        jQuery(".dpla-facets").toggleClass("hidden");
        jQuery("#dpla ol").toggleClass("fullwidth");
        if (!jQuery(".dpla-facets").hasClass("hidden")) {
            jQuery(".dpla-facets-button").addClass("hidden");
        } else {
            jQuery(".dpla-facets-button").removeClass("hidden");
        }
    },

    dplaUpdateDate: function(e) {
        e.preventDefault();
        this.getDPLAitems();
    },

    dplaFacetRemove: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        values = this.search_params.facets[link.data("facet-name")];
        if (link.data("facet-name") != "date") {
            new_values = [];
            if (typeof values != "string") {
                _.each(values, function(val) {
                    if (val != link.data("facet-val")) {
                        new_values.push(val);
                    }
                });
            }
            if (new_values.length == 0) {
                delete this.search_params.facets[link.data("facet-name")];
            } else {
                this.search_params.facets[link.data("facet-name")] = new_values;
            }
            this.getDPLAitems();
        }
    },

    dplaFacetExpand: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        facet_name = link.data("facet-name");
        jQuery(".dpla-expanded-facet-" + facet_name).toggleClass("hidden");
        if (!jQuery(".dpla-expanded-facet-" + facet_name).hasClass("hidden")) {
            link.text("View Less");
        } else {
            link.text("View More");
        }
    },

    drsSort: function(e) {
        e.preventDefault();
        this.search_params.sort = jQuery("select[name='drs-sort']").val();
        this.getDRSitems();
    },

    drsFacet: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        if (this.search_params.facets[link.data("facet-name")] == undefined) {
            this.search_params.facets[link.data("facet-name")] = link.data("facet-val");
        } else if (this.search_params.facets[link.data("facet-name")].length > 0) {
            orig_value = this.search_params.facets[link.data("facet-name")]
            if (typeof orig_value == "string") {
                this.search_params.facets[link.data("facet-name")] = [orig_value, link.data("facet-val")];
            } else {
                this.search_params.facets[link.data("facet-name")].push(link.data("facet-val"));
            }
        }
        this.getDRSitems();
    },

    drsFacetToggle: function(e) {
        e.preventDefault();
        jQuery(".drs-facets").toggleClass("hidden");
        jQuery("#drs ol").toggleClass("fullwidth");
        if (!jQuery(".drs-facets").hasClass("hidden")) {
            jQuery(".drs-facets-button").addClass("hidden");
        } else {
            jQuery(".drs-facets-button").removeClass("hidden");
        }
    },

    drsFacetRemove: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        values = this.search_params.facets[link.data("facet-name")];
        new_values = [];
        if (typeof values != "string") {
            _.each(values, function(val) {
                if (val != link.data("facet-val")) {
                    new_values.push(val);
                }
            });
        }
        if (new_values.length == 0) {
            delete this.search_params.facets[link.data("facet-name")];
        } else {
            this.search_params.facets[link.data("facet-name")] = new_values;
        }
        this.getDRSitems();
    },

    drsFacetExpand: function(e) {
        e.preventDefault();
        link = jQuery(e.currentTarget);
        facet_name = link.data("facet-name");
        jQuery(".drs-expanded-facet-" + facet_name).toggleClass("hidden");
        if (!jQuery(".drs-expanded-facet-" + facet_name).hasClass("hidden")) {
            link.text("View Less");
        } else {
            link.text("View More");
        }
    }
});

jQuery(function($) {
    "use strict";
    /**
     * Attach a click event to the meta-box button that instantiates the Application object, if it's not already open.
     */
    $("body").on('click', "#drs-backbone_modal", function(e) {
        e.preventDefault();
        if (drstk.backbone_modal.__instance === undefined) {
            drstk.backbone_modal.__instance = new drstk.backbone_modal.Application();
        }
    });

});
