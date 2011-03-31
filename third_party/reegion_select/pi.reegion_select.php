<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
    This file is part of REEgion Select add-on for ExpressionEngine.

    REEgion Select is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    REEgion Select is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    Read the terms of the GNU General Public License
    at <http://www.gnu.org/licenses/>.
    
    Copyright 2011 Derek Hogue
*/

$plugin_info = array(
	'pi_name'			=> 'REEgion Select',
	'pi_version'		=> '2.0.1',
	'pi_author'			=> 'Derek Hogue',
	'pi_author_url'		=> 'http://github.com/amphibian/reegion_select.ee2_addon',
	'pi_description'	=> 'Displays a drop down select menu of countries, US states, Canadian provinces, or UK counties.',
	'pi_usage'			=> Reegion_select::usage()
);

class Reegion_select {
	
	/**
	 * Constructor
	*/
	
	function Reegion_select()
	{
		$this->EE =& get_instance();
	
	}
	/**
	 * Display the dropdown menu.
	 *
	 * Displays a <select> menu of countries, provinces, states or counties.
	 *
	 * @param string $list Name of the data array to use when building the <select> menu.
	 * @param string $name The default string for the "name" attribute on the <select> menu (in the case that one is not supplied).
	 * @param string $label Text to be appended to the phrase "Select a" as the first option of the <select> menu.
	 */
	
	function dropdown_builder($list, $name, $label)
	{
		include PATH_THIRD.'reegion_select/libraries/regions.php';
		
		$type = $this->EE->TMPL->fetch_param('type', 'name');
		$name = $this->EE->TMPL->fetch_param('name', $name);
		$id = ' id="'.$this->EE->TMPL->fetch_param('id', 'reegion_select').'"';
		$class = ' class="'.$this->EE->TMPL->fetch_param('class', 'reegion_select').'"';
		$selected = $this->EE->TMPL->fetch_param('selected', '');
		$show = $this->EE->TMPL->fetch_param('show', '');
		$null_divider = $this->EE->TMPL->fetch_param('null_divider', 'true');
		
		$r = '<select name="' . $name . '"' . $id . $class . '>
	<option value="">Select a ' . $label . '</option>
	';
		$r .= ($null_divider == 'true') ? '<option value="">--------------------</option>
		' : '';
		
		switch($list)
		{
			case 'countries':
				$regions = $countries;
				break;
			case 'states':
				$regions = $states;
				break;
			case 'provinces':
				$regions = $provinces;
				break;
		 	case 'provinces_states':
				$regions = array_merge($provinces, $states);
				break;
			case 'ukcounties' :
				$regions = $ukcounties;
				break;				
		}
			
		foreach ($regions as $k => $v)
		{
			$val = $v;
			switch($type) {
				case 'alpha2':
					if(!is_numeric($k))
					{
						$val = $k;
					}
					break;
				case 'alpha3':
					if(!is_numeric($k) && $list == 'countries')
					{
						$val = $countries_alpha3[$k];
					}
					break;
			}
			if($show == '' || in_array($val, explode('|', $show)))
			{
				$sel = ($val == $selected) ? ' selected="selected"' : '';
				$r .= '<option value="' . $val . '"' . $sel . '>' . $v . '</option>
				';
			}
		}
		$r .= '</select>';
		
		return $r;	
	}


	function countries()
	{
		return $this->dropdown_builder("countries","country","country");
	}

	
	function states()
	{
		return $this->dropdown_builder("states","state","state");		
	}

	
	function provinces()
	{
		return $this->dropdown_builder("provinces","province","province");
	}
	
	
	function ukcounties()
	{
		return $this->dropdown_builder("ukcounties","county","county");	
	}
	
	
	function provinces_states()
	{
		return $this->dropdown_builder('provinces_states',"province_state","province or state");
	}
	

// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start(); 
?>

REEgion Select will display a dropdown <select> list of:

- countries (based on the ISO 3166-1 list of countries, dependent territories, and special areas of geographical interest)
- US states (based on the USPS official list of US states and possessions)
- Canadian provinces and territories
- UK counties
- Canadian provinces and US states together

Use the following EE tags to generate each type of dropdown:

{exp:reegion_select:countries}

{exp:reegion_select:states}

{exp:reegion_select:provinces}

{exp:reegion_select:ukcounties}

{exp:reegion_select:provinces_states}

REEgion Select accepts five optional parameters:

name="" - value for the "name" attribute of the <select> menu. Defaults: "country", "state", "province", "county", "province_state".

type="" - "alpha2" will use use the ISO 3166-2 abbreviation as the <option> value for countries, states, and provinces. "alpha3" will use use the ISO 3166-1 abbreviation as the <option> value for countries. "name" will use the region name as the value. Default: "name".

selected="" - value of the <option> element that should be selected by default.

id="" - value for the "id" attribute of the <select> menu.

class="" - value for the "class" attribute of the <select> menu.

show="" - a pipe-delimited list of values to show, if you don't want all of the default values to display. (i.e. show="CA|NY|OH|MI")

null_divider="false" - whether or not to include a divider option with a null value. Defaults to "true". 

<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}


}
?>