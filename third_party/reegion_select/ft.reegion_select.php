<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class Reegion_select_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'REEgion Select',
		'version'	=> '2.1'
	);
 
 			
	function __construct()
	{
		EE_Fieldtype::__construct();
		$this->EE->lang->loadfile('reegion_select');
	}
		

	function accepts_content_type($name)
	{
		return ($name == 'channel' || $name == 'grid');
	}
		
	function display_settings($settings)
	{
		$types = $this->_get_types();
		$this->EE->table->add_row(
			$this->EE->lang->line('rs_region_type', 'region_type'),
			form_dropdown('region_type', $types, (isset($settings['region_type'])) ? $settings['region_type'] : '', 'id="region_type"')
		);

	}
	
	
	// Matrix support
	function display_cell_settings($settings)
	{
		$types = $this->_get_types();
		return array(
		    array($this->EE->lang->line('rs_region_type', 'region_type'),
		    form_dropdown('region_type', $types, (isset($settings['region_type'])) ? $settings['region_type'] : ''))
		  );
		
	}
	

	function grid_display_settings($settings)
	{
		$types = $this->_get_types();
		return array(
			$this->grid_dropdown_row(
				$this->EE->lang->line('rs_region_type'),
				'region_type',
				$types,
				(isset($settings['region_type'])) ? $settings['region_type'] : ''
			)
		);
	}	
	
	
	// Low Variables support
	function display_var_settings($settings)
	{
		return $this->display_cell_settings($settings);
	}
	
	
	function _get_types()
	{		
		return array(
			'countries' => $this->EE->lang->line('rs_countries'),
			'states' => $this->EE->lang->line('rs_states'),
			'provinces' => $this->EE->lang->line('rs_provinces'),
			'provinces_states' => $this->EE->lang->line('rs_provinces_states'),
			'states_provinces' => $this->EE->lang->line('rs_states_provinces'),
			'ukcounties' => $this->EE->lang->line('rs_ukcounties')
		);
	}
	
	
	function save_settings($data)
	{
		return array(
			'region_type' => $this->EE->input->post('region_type')
		);
	}
	
	
	function grid_save_settings($data)
	{
		return $data;
	}	
	
	// Low Variables support
	function save_var_settings($data)
	{
		return $this->save_settings($data);
	}


	function display_field($data)
	{

		return $this->_display($data, $this->field_name);
	}
	
	
	// Matrix support
	function display_cell($data)
	{

		return $this->_display($data, $this->cell_name);
	}
	
	
	// Low Variables support
	function display_var_field($data)
	{
		return $this->_display($data, $this->field_name);
	}
	
	
	function _display($data, $name)
	{
		include PATH_THIRD.'reegion_select/libraries/regions.php';

		switch($this->settings['region_type'])
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
				$regions = array();
				$regions[$this->EE->lang->line('rs_provinces')] = $provinces;
				$regions[$this->EE->lang->line('rs_states')] = $states;
				break;
		 	case 'states_provinces':
				$regions = array();
				$regions[$this->EE->lang->line('rs_states')] = $states;
				$regions[$this->EE->lang->line('rs_provinces')] = $provinces;
				break;
			case 'ukcounties':
				// Counties array has no keys,
				// so we need to explicitly set them to match the values.
				$regions = array();
				foreach($ukcounties as $v)
				{
					$regions[$v] = $v;
				}
				break;				
		}
		
		return form_dropdown($name, array_merge(array('' => '--'), $regions), $data);
	}
	
	
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_name($data);
	}
	
	
	function replace_name($data, $params = array(), $tagdata = FALSE, $lv_settings = array())
	{
		include PATH_THIRD.'reegion_select/libraries/regions.php';		
		switch($this->settings['region_type'])
		{
			case 'countries':
				return $countries[$data];
				break;
			case 'states':
				return $states[$data];
				break;
			case 'provinces':
				return $provinces[$data];
				break;
		 	case 'provinces_states': case 'states_provinces':
				$regions = array_merge($provinces, $states);
				return $regions[$data];
				break;
			case 'ukcounties' :
				return $data;
				break;				
		}
	}
	
	
	function replace_alpha2($data, $params = array(), $tagdata = FALSE)
	{
		// Alpha-2 is what we store in the database, so spit it out
		return $data;
	}


	function replace_alpha3($data, $params = array(), $tagdata = FALSE, $lv_settings = array())
	{
		// Applies to Countries only
		if($this->settings['region_type'] == 'countries')
		{
			include PATH_THIRD.'reegion_select/libraries/regions.php';
			$data = $countries_alpha3[$data];
		}
		return $data;
	}
	

	// Low Variables support
	function display_var_tag($data, $params = array(), $tagdata = FALSE)
	{
		if(isset($params['type']))
		{
			switch($params['type'])
			{
				case 'alpha2' :
					return $data;
					break;
				case 'alpha3':	
					return $this->replace_alpha3($data, null, null, $params);
					break;
				default :
					return $this->replace_name($data, null, null, $params);
			}		
		}
		else
		{
			return $this->replace_name($data, null, null, $params);
		}
	}	
	
	
	// Low Search support
	function third_party_search_index($data)
	{
		if(empty($data))
		{
			return $data;
		}
		
		// Make both codes and names searchable
		$r = $data;
		include PATH_THIRD.'reegion_select/libraries/regions.php';
		switch($this->settings['region_type'])
		{
			case 'countries':
				$r .= ' ' . $countries_alpha3[$data];
				$r .= ' ' . $countries[$data];
				break;
			case 'states':
				$r .= ' ' . $states[$data];
				break;
			case 'provinces':
				$r .= ' ' . $provinces[$data];
				break;
			case 'provinces_states':
			case 'states_provinces':
				$regions = array_merge($provinces, $states);
				$r .= ' ' . $regions[$data];
				break;
		}
		return $r;
	}

}
