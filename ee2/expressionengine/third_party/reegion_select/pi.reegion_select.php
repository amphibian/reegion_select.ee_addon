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
	'pi_version'		=> '2.2',
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
		$this->EE->load->helper('form');
		$this->EE->lang->loadfile('reegion_select');		
	}
	
	
	/**
	 * Display the dropdown menu.
	 *
	 * Displays a list of countries, provinces, states or counties, in either a dropdown list or custom template.
	 *
	 * @param string $list Name of the data array to use when building the list.
	 * @param string $name The default string for the "name" attribute on the <select> menu (in the case that one is not supplied).
	 */
	
	function _display($list, $name)
	{		
		include PATH_THIRD.'reegion_select/libraries/regions.php';
		
		$style = (empty($this->EE->TMPL->tagdata)) ? 'dropdown' : 'linear';
		$show = $this->EE->TMPL->fetch_param('show', FALSE);
		$hide = $this->EE->TMPL->fetch_param('hide', FALSE);
		$title = $this->EE->TMPL->fetch_param('title', $this->EE->lang->line('rs_select').' '.$this->EE->lang->line('rs_'.$name));

		$vars = array();
		$i = 0;
		$options = array('' => $title);
				
		if($style == 'dropdown')
		{
			$type = $this->EE->TMPL->fetch_param('type', 'name');
			$select_name = $this->EE->TMPL->fetch_param('name', $name);
			$id = $this->EE->TMPL->fetch_param('id', FALSE);
			$class = $this->EE->TMPL->fetch_param('class', 'reegion_select');
			$tabindex = $this->EE->TMPL->fetch_param('tabindex', FALSE);
			$required = $this->EE->TMPL->fetch_param('required', FALSE);
			$selected = $this->EE->TMPL->fetch_param('selected', '');
			$null_divider = $this->EE->TMPL->fetch_param('null_divider', 'y');
			
			$extra = 'class="'.trim($class).'"';
			if($id)
			{
				$extra .= ' id="'.trim($id).'"';
			}
			if($tabindex)
			{
				$extra .= ' tabindex="'.intval($tabindex).'"';
			}
			if($required)
			{
				$extra .= ' required="required"';
			}
			
			// Check for data- params
			foreach($this->EE->TMPL->tagparams as $param => $value)
			{
				if(substr($param, 0, 5) == 'data-')
				{
					$extra .= ' '.$param.'="'.$value.'"';
				}
			}
						
			if($null_divider == 'y')
			{
				$options[] = '--------------------';
			}		
		
		}
				
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
				$regions[$this->EE->lang->line('rs_provinces')] = $provinces;
				$regions[$this->EE->lang->line('rs_states')] = $states;
				break;
		 	case 'states_provinces':
				$regions[$this->EE->lang->line('rs_states')] = $states;
				$regions[$this->EE->lang->line('rs_provinces')] = $provinces;
				break;
			case 'ukcounties' :
				$regions = $ukcounties;
				break;				
		}
			
		foreach($regions as $k => $label)
		{
			if(is_array($label))
			{
				// States and provinces are different
				// (multidimensional array so we get optgroups)
				foreach($label as $sp_k => $sp_label)
				{
					$val = ((isset($type) && $type == 'alpha2') || $style == 'linear') ? $sp_k : $sp_label;
					if(
						($show == FALSE || in_array($val, explode('|', $show))) && 
						($hide == FALSE || !in_array($val, explode('|', $hide)))
					)
					{
						$options[$k][$val] = $sp_label;
						$vars[$i]['region_name'] = $sp_label;
						$vars[$i]['region_alpha2'] = $sp_k;
						$vars[$i]['region_alpha3'] = '';
						$i++;
					}					
				}
			}	
			else
			{
				if($style == 'dropdown')
				{
					$val = $label;
					switch($type)
					{
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
				}
				else
				{
					$val = $k;
				}
				
				if(
					($show == FALSE || in_array($val, explode('|', $show))) && 
					($hide == FALSE || !in_array($val, explode('|', $hide)))
				)
				{
					$options[$val] = $label;
					$vars[$i]['region_name'] = $label;
					$vars[$i]['region_alpha2'] = ($list == 'ukcounties') ? '' : $k;
					$vars[$i]['region_alpha3'] = ($list == 'countries') ? $countries_alpha3[$k] : '';
					$i++;
				}
			}
		}
				
		return ($style == 'dropdown') ? 
			form_dropdown($select_name, $options, $selected, $extra) : 
			$this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
	}


	function countries()
	{
		return $this->_display('countries','country');
	}

	
	function states()
	{
		return $this->_display('states','state');		
	}

	
	function provinces()
	{
		return $this->_display('provinces','province');
	}
	
	
	function ukcounties()
	{
		return $this->_display('ukcounties','county');	
	}
	
	
	function provinces_states()
	{
		return $this->_display('provinces_states','province_state');
	}


	function states_provinces()
	{
		return $this->_display('states_provinces','state_province');
	}	
	
	
	static function usage()
	{
		ob_start(); 
	?>
	
		REEgion Select will display a list of:
		
		- countries (based on the ISO 3166-1 list of countries, dependent territories, and special areas of geographical interest)
		- US states (based on the USPS official list of US states and possessions)
		- Canadian provinces and territories
		- UK counties
		- Canadian provinces and US states together
		
		
		Use the following EE tags to generate each type of list as a dropdown:
		
		{exp:reegion_select:countries}
		
		{exp:reegion_select:states}
		
		{exp:reegion_select:provinces}
		
		{exp:reegion_select:ukcounties}
		
		{exp:reegion_select:provinces_states}
		
		
		Or, use a tag pair to customize the display of regions with your own markup:
		
		{exp:reegion_select:countries}
			{region_name}
			{region_alpha2}
			{region_alpha3}
		{/exp:reegion_select:countries}
		
		(And likewise for the other region types.)
		
		You can also use the {count} and {total_results} variables within the tag pair.
		
		
		REEgion Select accepts ten optional parameters:
				
		show="" - a pipe-delimited list of values to show, if you don't want all of the default values to display. (e.g., show="CA|NY|OH|MI")
		
		hide="" - a pipe-delimited list of values to hide, if you don't want all of the default values to display. (e.g., hide="Canada|United States|Mexico")
				
		name="" - value for the "name" attribute of the <select> menu. Defaults: "country", "state", "province", "county", "province_state".
		
		type="" - "alpha2" will use use the ISO 3166-2 abbreviation as the <option> value for countries, states, and provinces. "alpha3" will use use the ISO 3166-1 abbreviation as the <option> value for countries. "name" will use the region name as the value. Default: "name".
		
		title="" - a title or heading for the <select> menu. Defaults to "Select a (Country/State/Province/etc)".

		id="" - value for the "id" attribute of the <select> menu.
		
		class="" - value for the "class" attribute of the <select> menu.
		
		tabindex="" - value for the "tabindex" attribute of the <select> menu.

		required="" - whether to add the HTML5 "required" attribute to the <select> menu.

		data-[value]="" - any "data-" values passed as individual parameters will be added verbatim to the <select> menu.
		
		selected="" - value of the <option> element that should be selected by default.
		
		null_divider="false" - whether or not to include a divider option with a null value. Defaults to "true". 
		
	<?php
		$buffer = ob_get_contents();
		ob_end_clean(); 
		return $buffer;
	}

}
?>