# List Filters 

List Filters adds advanced filtering options for page lists to Cotonti. A list 
of available filter types is below.

## Installation

Simply install the plugin through the admin panel as you would with any other 
plugin. No configuration is required, just add the filters to your page.list.tpl 
file. Implementation details are below. You may modify the plugin rights to 
disable it for certain groups, by default all guests and members can use it.

## Filter types

* eq	Equals (page_$field = $value) Value can be any string or number.
* ne	Not Equal (page_$field != $value) Value can be any string or number.
* lt	Less Than (page_$field < $value) Value must be numeric.
* lte	Less Than or Equal (page_$field <= $value) Value must be numeric.
* gt	Greater Than (page_$field > $value) Value must be numeric.
* gte	Greater Than or Equal (page_$field >= $value) Value must be numeric.
* in	SQL IN operator (page_$field IN ($value1, $value2, $value3)) Values must be comma seperated.
* rng	SQL BETWEEN operator (page_$field BETWEEN $value1 AND $value2) Values must be seperated with a tilde (1~2). Strings are supported.

## Implementation

Implementation of List Filters is a matter of modifying your page.list.tpl to 
include the filters. There are two ways in which you can display the filters: as
plain text links or as a form. Which you choose depends on your use case. More 
complex filtering systems are easier implemented using forms, while plain links 
are more user friendly and quicker because there's no need to submit a form. The
plugin provides helper functions for both implementation methods.

### Using links

For links the plugin provides the helper function listfilter_url(). This 
function takes three arguments and will return a URL to the list with the 
correct parameters for the filter. The arguments are filter type (see above), 
field name (database column name without 'page_') and the value to apply. An 
example:

    listfilter_url('eq', 'exttype', 'modules')

Assuming we're displaying the 'extensions' category, this will return:

    index.php?e=page&c=extensions&filters[eq][exttype]=modules

It's also possible to leave out the last argument (the value). This will return 
a URL for the current list without this filter, effectively disabling it. Any 
other filters will still be included in the link.

Another function that is convenient is listfilter_active() which returns TRUE or
FALSE depending on whether or not the filter is currently active. It takes the 
exact same arguments as listfilter_url().

Here's a complete example that uses these functions as CoTemplate callbacks:

    <li><a href="{PHP|listfilter_url('eq', 'exttype')}"<!-- IF {PHP|listfilter_active('eq', 'exttype')} --> class="selected"<!-- ENDIF -->>{PHP.L.All}</a></li>
    <li><a href="{PHP|listfilter_url('eq', 'exttype', 'modules')}"<!-- IF {PHP|listfilter_active('eq', 'exttype', 'modules')} --> class="selected"<!-- ENDIF -->>{PHP.L.Modules}</a></li>
    <li><a href="{PHP|listfilter_url('eq', 'exttype', 'plugins')}"<!-- IF {PHP|listfilter_active('eq', 'exttype', 'plugins')} --> class="selected"<!-- ENDIF -->>{PHP.L.Plugins}</a></li>

As you see this filter has three options: All, Modules and Plugins. In this case 
'All' simply disables the filter. It's not a requirement to include a 'disable' 
link, because clicking an active filter will disable it too (like a toggle 
switch).

### Using a form

An alternative to using plain text links is to use a form. This can be more
convenient in complex situations. To simplify creating the form, the plugin 
provides several helper functions for generating form elements. These functions 
are effectively wrappers for the functions included in the Cotonti Forms API 
(system/forms.php). They return complete HTML form fields. Provided functions 
are:

* listfilter_form_checkbox($type, $field, $value, $default = 0, $title = NULL)
* listfilter_form_inputbox($type, $field, $default = '')
* listfilter_form_radiobox($type, $field, $options, $default = '', $titles = NULL)
* listfilter_form_selectbox($type, $field, $options, $default = '', $titles = NULL)

Here's the arguments explained:

* $type (string) One of the filter types listed above.
* $field (string) Field name without 'page_' prefix.
* $value (string) Value which the filter will check for.
* $title (string) Text that will be displayed as a label (optional, defaults to $value).
* $default (string) Default text to display in the input field (optional) or value that is selected by default or in case of a checkbox: 0 (not checked) or 1 (checked).
* $options (string) Comma-seperated list of options.
* $titles (string) Comma-seperated list of titles (optional, defaults to $values).

### Other helpers

listfilter_urlparam()

Returns the URL query parameter for all currently active filters. Example:

    filters[eq][a]=b&filters[eq][c]=d&filters[ne][e]=f