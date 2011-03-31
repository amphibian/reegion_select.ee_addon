REEgion Select is an EE2 plugin and fieldtype that displays a dropdown <select> list of:

- countries (based on the ISO 3166-1 list of countries, dependent territories, and special areas of geographical interest)
- US states (based on the USPS official list of US states and possessions)
- Canadian provinces and territories
- UK counties
- Canadian provinces and US states together

##Fieldtype Usage

To use the fieldtype, simply install and then choose REEgion Select from the fieldtype menu when creating a new custom field (or Matrix field), then choose which kind of regions you'd like to display.

In your templates, display your custom field using {field_name:name} ((or simply {field_name}) to display the name of the region (i.e., United States); {field_name:alpha2} to display the ISO 3166-2 code for the country, state, or province (i.e., US); or use {field_name:alpha3} to display the ISO 3166-1 code for the country (i.e., USA).

##Plugin Usage

Use the following EE tags to generate each type of dropdown in your templates:

`{exp:reegion_select:countries}`

`{exp:reegion_select:states}`

`{exp:reegion_select:provinces}`

`{exp:reegion_select:ukcounties}`

`{exp:reegion_select:provinces_states}`

##Plugin Parameters

REEgion Select accepts seven optional parameters:

- `name=""`

   Value for the "name" attribute of the <select> menu. Defaults: "country", "state", "province", "county", "province_state".

- `type=""`

   "alpha2" will use use the ISO 3166-2 abbreviation as the <option> value for countries, states, and provinces. "alpha3" will use use the ISO 3166-1 abbreviation as the <option> value for countries. "name" will use the region name as the value. Default: "name".

- `selected=""`

   Value of the <option> element that should be selected by default.

- `id=""`

   Value for the "id" attribute of the <select> menu.

- `class=""`

   Value for the "class" attribute of the <select> menu.

- `show=""`

   A pipe-delimited list of values to show, if you don't want all of the default values to display. (i.e. show="CA|NY|OH|MI")

- `null_divider="false"`

   Whether or not to include a divider option with a null value at the top of the menu. Defaults to "true". 
   
##Compatibility

This version of REEgion Select is only compatible with ExpressionEngine 2.1.3 or higher. The ExpressionEngine 1.6-compatible version [can be found here](http://github.com/amphibian/pi.reegion_select.ee_addon).