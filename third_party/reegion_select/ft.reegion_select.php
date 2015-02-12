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
		'version'	=> '2.2'
	);
	
	public $has_array_data = TRUE;
	public $regions = array();
	public $countries_alpha3 = array();
 			
	function __construct()
	{
		$this->EE =& get_instance();
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
		$this->EE->table->add_row(
			$this->EE->lang->line('rs_multiselect', 'multiselect'),
			form_label(form_checkbox('multiselect', 'y', (isset($settings['multiselect']) && $settings['multiselect'] == 'y') ? true : false).' '.lang('yes'))
		);

	}
	
	
	// Matrix support
	function display_cell_settings($settings)
	{
		$types = $this->_get_types();
		return array(
		    array(
		    	$this->EE->lang->line('rs_region_type', 'region_type'),
				form_dropdown('region_type', $types, (isset($settings['region_type'])) ? $settings['region_type'] : '')
			),
			array(
				$this->EE->lang->line('rs_multiselect', 'multiselect'),
				form_label(form_checkbox('multiselect', 'y', (isset($settings['multiselect']) && $settings['multiselect'] == 'y') ? true : false).' '.lang('yes'))
			)
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
			),
			$this->grid_checkbox_row(
				$this->EE->lang->line('rs_multiselect'),
				'multiselect',
				'y',
				(isset($settings['multiselect']) && $settings['multiselect'] == 'y') ? true : false
			),
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
			'region_type' => $this->EE->input->post('region_type'),
			'multiselect' => $this->EE->input->post('multiselect', 'n')
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
	
	function save($data)
	{
		if(is_array($data))
		{
			$data = implode('|', $data);
		}
		return $data;
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
		$this->_fetch_regions();
		
		if(!empty($this->settings['multiselect']) && $this->settings['multiselect'] == 'y' && ! is_array($data))
		{
			$data = explode('|', $data);
		}
		
		$regions = array_merge(array('' => '--'), $this->regions);
	
		return (!empty($this->settings['multiselect']) && $this->settings['multiselect'] == 'y') ? 
			form_multiselect($name.'[]', $regions, $data, 'size="10"') : 
			form_dropdown($name, $regions, $data);
	}
	
	
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if(!empty($tagdata) && !empty($data))
		{
			// We're outputting (potentially) multiple values
			$this->_fetch_regions();
			$exploded = explode('|', $data);
			$array = array();
			
			foreach($exploded as $k => $region)
			{
				$array[$k] = array(
					'region_name' => $this->regions[$region],
					'region_alpha2' => $region
				);
				if($this->settings['region_type'] == 'countries')
				{
					$array[$k]['region_alpha3'] = $this->countries_alpha3[$region];
				}
			}
			$r = ee()->TMPL->parse_variables($tagdata, $array);
			if(!empty($params['backspace']))
			{
				$r = substr($r, 0, - $params['backspace']);
			}
			return $r;
		}
		else
		{
			return $this->replace_name($data);		
		}
	}
	
	
	function replace_name($data, $params = array(), $tagdata = FALSE, $lv_settings = array())
	{
		if(!empty($data))
		{
			$this->_fetch_regions();
			return $this->regions[$data];
		}
	}
	
	
	function replace_alpha2($data, $params = array(), $tagdata = FALSE)
	{
		// Alpha-2 is what we store in the database, so spit it out
		return $data;
	}


	function replace_alpha3($data, $params = array(), $tagdata = FALSE, $lv_settings = array())
	{
		if(!empty($data) && $this->settings['region_type'] == 'countries')
		{
			$this->_fetch_regions();
			return $this->countries_alpha3[$data];
		}
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
		$this->_fetch_regions();
		$data = explode('|', $data);
		$r = '';
		foreach($data as $region)
		{
			$r .= $region.' '.$this->regions[$region];
			if($this->settings['region_type'] == 'countries')
			{
				$r .= ' '.$this->countries_alpha3[$region];
			}
		}
		return $r;
	}
	
	function _fetch_regions()
	{
		include PATH_THIRD.'reegion_select/libraries/regions.php';		
		switch($this->settings['region_type'])
		{
			case 'countries':
				$this->regions = $countries;
				$this->countries_alpha3 = $countries_alpha3;
				break;
			case 'states':
				$this->regions = $states;
				break;
			case 'provinces':
				$this->regions = $provinces;
				break;
		 	case 'provinces_states':
				$this->regions = array_merge($provinces, $states);
				break;
		 	case 'states_provinces':
				$this->regions = array_merge($states, $provinces);
				break;
			case 'ukcounties':
				// Counties array has no keys,
				// so we need to explicitly set them to match the values.
				$regions = array();
				foreach($ukcounties as $v)
				{
					$regions[$v] = $v;
				}
				$this->regions = $regions;
				break;				
		}
	}

}
