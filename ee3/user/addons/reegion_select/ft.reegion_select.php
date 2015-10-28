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
    
    Copyright 2015 Derek Hogue
*/

class Reegion_select_ft extends EE_Fieldtype {

	public $countries_alpha3 = array();
	public $has_array_data = true;
	public $info = array(
		'name' => 'Reegion Select',
		'version' => '2.3'
	);
	public $provinces = array();
	public $regions = array();
	public $region_types = array();
	public $states = array();
 			
	function __construct()
	{
		ee()->load->helper('form');
		ee()->lang->loadfile('reegion_select');
		$this->region_types = array(
			'countries' => lang('rs_countries'),
			'states' => lang('rs_states'),
			'provinces' => lang('rs_provinces'),
			'provinces_states' => lang('rs_provinces_states'),
			'states_provinces' => lang('rs_states_provinces'),
			'ukcounties' => lang('rs_ukcounties')
		);
	}	

	function accepts_content_type($name)
	{
		return true;
	}

	function display_settings($data)
	{
		$settings = array(
			'reegion_select' => array(
				'label' => $this->info['name'],
				'group' => 'reegion_select',
				'settings' => array(
					array(
						'title' => 'rs_region_type',
						'desc' => '',
						'fields' => array(
							'region_type' => array(
								'type' => 'select',
								'choices' => $this->region_types,
								'value' => (isset($data['region_type'])) ? $data['region_type'] : ''
							)
						)
					),
					array(
						'title' => 'rs_multiselect',
						'desc' => '',
						'fields' => array(
							'multiselect' => array(
								'type' => 'yes_no',
								'value' => (isset($data['multiselect']) && $data['multiselect'] == 'y') ? 'y' : 'n'
							)
						)
					)
				)
			)
		);
		return $settings;
	}

	function grid_display_settings($data)
	{
		$settings = $this->display_settings($data);
		$grid_settings = array();
		foreach ($settings as $value) {
			$grid_settings[$value['label']] = $value['settings'];
		}
		return $grid_settings;
	}
	
	function save_settings($data)
	{
		return array(
			'region_type' => ee('Request')->post('region_type'),
			'multiselect' =>  ee('Request')->post('multiselect', 'n')
		);
	}
	
	function grid_save_settings($data)
	{
		return $data;
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
	
	function _display($data, $name)
	{
		$this->_fetch_regions();
		
		if($this->settings['region_type'] == 'states_provinces')
		{
			$regions = array(lang('rs_states') => $this->states, lang('rs_provinces') => $this->provinces);
			$regions = array_merge(array('' => '--'), $regions);
		}
		elseif($this->settings['region_type'] == 'provinces_states')
		{
			$regions = array(lang('rs_provinces') => $this->provinces, lang('rs_states') => $this->states);
			$regions = array_merge(array('' => '--'), $regions);
		}
		else
		{
			$regions = array_merge(array('' => '--'), $this->regions);
		}

		if(!empty($this->settings['multiselect']) && $this->settings['multiselect'] == 'y' && ! is_array($data))
		{
			$data = explode('|', $data);
		}
			
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
					'region_count' => $k+1,
					'total_regions' => count($exploded),
					'region_name' => $this->regions[$region],
					'region_alpha2' => $region
				);
				if($this->settings['region_type'] == 'countries')
				{
					$array[$k]['region_alpha3'] = $this->countries_alpha3[$region];
				}
			}
			return ee()->TMPL->parse_variables($tagdata, $array);
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
		if(!empty($data) && ( $this->settings['region_type'] == 'countries' || $lv_settings['region_type'] == 'countries'))
		{
			$this->_fetch_regions();
			return $this->countries_alpha3[$data];
		}
	}

	
	/*
		Low Variables support
	*/
	function var_display_field($data)
	{
		return $this->_display($data, $this->name);
	}
	

	/*
		Low Search support
	*/
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
	
	
	/*
		Matrix support - these may change when the EE3 version of Matrix arrives (if it does)	
	*/
	function display_cell($data)
	{
		return $this->_display($data, $this->cell_name);
	}
	
	function display_cell_settings($settings)
	{
		return array(
		    array(
		    	lang('rs_region_type', 'region_type'),
				form_dropdown('region_type', $this->region_types, (isset($settings['region_type'])) ? $settings['region_type'] : '')
			),
			array(
				lang('rs_multiselect', 'multiselect'),
				form_label(form_checkbox('multiselect', 'y', (isset($settings['multiselect']) && $settings['multiselect'] == 'y') ? true : false).' '.lang('yes'))
			)
		);
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
				$this->provinces = $provinces;
				$this->states = $states;
				break;
		 	case 'states_provinces':
				$this->regions = array_merge($states, $provinces);
				$this->provinces = $provinces;
				$this->states = $states;
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
