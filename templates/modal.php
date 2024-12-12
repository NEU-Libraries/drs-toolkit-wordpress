<?php
/**
 * Backbone Templates
 * This file contains all of the HTML used in our modal and the workflow itself.
 *
 * Each template is wrapped in a script block ( note the type is set to "text/html" ) and given an ID prefixed with
 * 'tmpl'. The wp.template method retrieves the contents of the script block and converts these blocks into compiled
 * templates to be used and reused in your application.
 */


/**
 * The Modal Window, including sidebar and content area.
 * Add menu items to ".navigation-bar nav ul"
 * Add content to ".backbone_modal-main article"
 */
?>
<script type="text/html" id='tmpl-drstk-modal-window'>
	<div class="backbone_modal">
		<a class="backbone_modal-close dashicons dashicons-no" href="#"
		   title="<?php echo __( 'Close', 'backbone_modal' ); ?>"><span
				class="screen-reader-text"><?php echo __( 'Close', 'backbone_modal' ); ?></span></a>

		<div class="backbone_modal-content">
			<div class="navigation-bar">
				<nav>
					<ul></ul>
				</nav>
			</div>
			<section class="backbone_modal-main" role="main">
				<header><h1><?php echo __( 'Add Toolkit Shortcodes', 'backbone_modal' ); ?></h1></header>
				<article></article>
				<footer>
					<div class="inner text-right">
						<button id="btn-cancel"
						        class="button button-large"><?php echo __( 'Cancel', 'backbone_modal' ); ?></button>
						<button id="btn-ok"
						        class="button button-primary button-large"><?php echo __( 'Insert Shortcode', 'backbone_modal' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
</script>

<?php
/**
 * The Modal Backdrop
 */
?>
<script type="text/html" id='tmpl-drstk-modal-backdrop'>
	<div class="backbone_modal-backdrop">&nbsp;</div>
</script>
<?php
/**
 * Base template for a navigation-bar menu item ( and the only *real* template in the file ).
 */
?>
<script type="text/html" id='tmpl-drstk-modal-menu-item'>
	<li class="nav-item"><a href="{{ data.url }}">{{ data.name }}</a></li>
</script>
<?php
/**
 * A menu item separator.
 */
?>
<script type="text/html" id='tmpl-drstk-modal-menu-item-separator'>
	<li class="separator">&nbsp;</li>
</script>
<?php
/**
* A template for tab content
*/
?>
<script type='text/html' id='tmpl-drstk-modal-tab-content'>
	<div class="wrap">
		<h1 class="title">{{data.title}}</h1>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href="#drs">DRS Items</a>
			<a class="nav-tab" href="#dpla">DPLA Items</a>
			<a class="nav-tab" href="#local">Local Items</a>
			<a class="nav-tab" href="#selected">Selected Items</a>
			<a class="nav-tab" href="#settings">Settings</a>
		</h2>
		<br/>
		<div class="pane" id="drs">
			<label for="search">Search for an item: </label>
      <input type="text" name="search" id="search-{{data.type}}" class='drstk-search-input'  />
      <button class="themebutton search-button">Search</button>
			<br/>
			<div class="drs-chosen"></div>
			<br/>
			<button class="drs-facets-button">Show Filtering Options</button>

			<!-- Code to add Select all checkbox only for map and timeline -->
			<# if(data.title == "Map" || data.title == "Timeline"){ #>
	      <label id="select-all-label">
	        <input id="drs-select-all-item" type="checkbox"> Select All </input>
	      </label>
      <# } #>

			<div class="drs-items">Loading...</div>
			<div class="drs-facets hidden">
				<b class="drs-facet-title">Filters <a href='' class="drs-close-facets"><span class="dashicons dashicons-no"> </span></a></b>
				<div class="drs-sort"><label for="drs-sort">Sort By: </label><select name="drs-sort"><option value="">Relevance</option><option value="title">Title</option><option value="creator">Creator</option><option value="date">Date Created</option></select></div>
				<div class="drs-date">
				</div>
				<div class="drs-subject">
				</div>
				<div class="drs-creator">
				</div>
				<div class="drs-type">
				</div>
			</div>
			<ol id="sortable-{{data.type}}-list" class="fullwidth"></ol><div class="drs-pagination"></div>
		</div>
		<div class="pane" id="dpla">
			<label for="search">Search for an item: </label>
      <input type="text" name="search" id="search-{{data.type}}" class='drstk-search-input' />
      <button class="themebutton search-button">Search</button>
			<br/>
			<div class="dpla-chosen"></div>
			<br/>
			<button class="dpla-facets-button hidden">Show Filtering Options</button>
			<div class="dpla-items">Loading...</div>
			<div class="dpla-facets hidden">
				<b class="dpla-facet-title">Filters <a href='' class="dpla-close-facets"><span class="dashicons dashicons-no"> </span></a></b>
				<div class="dpla-sort"><label for="dpla-sort">Sort By: </label><select name="dpla-sort"><option value="">Relevance</option><option value="title">Title</option><option value="creator">Creator</option><option value="date">Date Created</option></select></div>
				<div class="dpla-date">
				</div>
				<div class="dpla-subject">
				</div>
				<div class="dpla-creator">
				</div>
				<div class="dpla-type">
				</div>
			</div>
			<ol id="sortable-{{data.type}}-list" class="fullwidth"></ol><div id="dpla-pagination"><span class="tablenav"></span></div>
		</div>
		<div class="pane" id="local">
		</div>
		<div class="pane" id="selected">
			<div class="selected-items">Loading...</div><ol id="sortable-{{data.type}}-list"></ol><div class="selected-pagination"></div>
		</div>
		<div class="pane" id="settings">
		</div>
	</div>
</script>
<?php
/* a template for select settings */
?>
<script type='text/html' id='tmpl-drstk-setting-select'>
	<td>
		<label for='{{data.name}}'>
			{{data.label}}
		</label>
	</td>
	<td>
		<select name='{{data.name}}'>
			<# _.each(data.choices, function(choice, key) { #>
			    <option value='{{key}}' <# if (data.value.indexOf(key) > -1) { #> selected="selected" <# } #>>{{ choice }}</option>
			<# }); #>
		</select>
	</td>
	<td>{{data.helper}}</td>
</script>

<?php
/* a template for checkbox settings */
?>
<script type='text/html' id='tmpl-drstk-setting-checkbox'>
	<td><h5>{{data.label}}</h5></td>
	<td>
		<# if (_.size(data.choices) == 1) { #>
				<# if (jQuery.isNumeric(Object.keys(data.choices)[0])) { #>
					<label><input type="checkbox" name="{{data.choices[0]}}" <# if (data.value == data.choices[0]) { #> checked="checked" <# } #>/> </label><br/>
				<# } else { #>
					<label><input type="checkbox" name="{{Object.keys(data.choices)[0]}}" <# if (data.value == Object.keys(data.choices)[0]) { #> checked="checked" <# } #>/> {{data.value}} </label><br/>
				<# } #>
		<# } else { #>
			<# _.each(data.choices, function(choice, key) {
				var key_array = key.split(",");
				#>
				<label><input type="checkbox" name="{{key}}" <# if (data.value.indexOf(key) > -1 || (key_array && data.value.indexOf(key_array[0]) > -1) || (key_array.length > 1 &&  data.value.indexOf(key_array[1]) > -1)) { #> checked="checked" <# } #>/> {{choice}} </label><br/>
			<# }); #>
		<# } #>
	</td>
	<td>{{data.helper}}</td>
</script>

<?php
/* a template for number settings */
?>
<script type='text/html' id='tmpl-drstk-setting-number'>
	<td>
		<label for="{{data.name}}">{{data.label}}</label>
	</td>
	<td>
		<input type="number" value="{{data.value[0]}}" name="{{data.name}}"/>
	</td>
	<td>{{data.helper}}</td>
</script>
<?php
/* a template for text settings */
?>
<script type='text/html' id='tmpl-drstk-setting-text'>
	<td>
		<label for="{{data.name}}">{{data.label}}</label>
	</td>
	<td>
		<# if (Array.isArray(data.value)){ #>
			<input type="text" value="{{data.value[0]}}" name="{{data.name}}"/>
		<# } else { #>
			<input type="text" value="{{data.value}}" name="{{data.name}}"/>
		<# } #>
	</td>
	<td>{{data.helper}}</td>
</script>




<?php
/* a template for button settings */
?>
<script type='text/html' id='tmpl-drstk-setting-button'>
	<td><h5>{{data.label}}</h5></td>
	<td>
		<button type="button" id ="{{data.name}}">{{data.value}} </button>
		<button type="button" id ="save-button">Save </button>
	</td>
</script>

<?php
/* a template for Color headers settings */
// TODO - is this even being used?
?>
<script type='text/html' id='tmpl-drstk-setting-colorheader'>
	<td><h5>Description</h5></td>
	<td><h5>Color Value</h5></td>

</script>


<?php
/* a template for color input */
?>
<script type='text/html' id='tmpl-drstk-setting-colorinput'>
	<td><input type='text' name="{{data.name}}"  value="{{data.colorname}}" />&nbsp;&nbsp;
	</td>+
	<td><input type='color' name="{{data.colorname}}" value='{{data.colorHex}}' /></td>
	<td><div style="cursor:pointer;font-weight:bold" class="delete-color-row" id ='delete-{{data.name}}'>X</div></td>
</script>
