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
    
    Copyright 2015 Derek Hogue
*/

class Reegion_select {

	function __construct()
	{
		ee()->load->helper('form');
		ee()->lang->loadfile('reegion_select');
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
		
		$style = (empty(ee()->TMPL->tagdata)) ? 'dropdown' : 'linear';
		$show = ee()->TMPL->fetch_param('show', FALSE);
		$hide = ee()->TMPL->fetch_param('hide', FALSE);
		$title = ee()->TMPL->fetch_param('title', lang('rs_select').' '.lang('rs_'.$name));

		$vars = array();
		$i = 0;
		$options = array('' => $title);
				
		if($style == 'dropdown')
		{
			$type = ee()->TMPL->fetch_param('type', 'name');
			$select_name = ee()->TMPL->fetch_param('name', $name);
			$id = ee()->TMPL->fetch_param('id', FALSE);
			$class = ee()->TMPL->fetch_param('class', 'reegion_select');
			$tabindex = ee()->TMPL->fetch_param('tabindex', FALSE);
			$required = ee()->TMPL->fetch_param('required', FALSE);
			$selected = ee()->TMPL->fetch_param('selected', '');
			$null_divider = ee()->TMPL->fetch_param('null_divider', 'y');
			
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
			if(!empty(ee()->TMPL->tagparams))
			{
				foreach(ee()->TMPL->tagparams as $param => $value)
				{
					if(substr($param, 0, 5) == 'data-')
					{
						$extra .= ' '.$param.'="'.$value.'"';
					}
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
				$regions[lang('rs_provinces')] = $provinces;
				$regions[lang('rs_states')] = $states;
				break;
		 	case 'states_provinces':
				$regions[lang('rs_states')] = $states;
				$regions[lang('rs_provinces')] = $provinces;
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
			ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $vars);
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

}