<?php
class Mdl_observation extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	public function __api_saveobservation($data, $uid)
	{
		$data 		= json_decode(json_encode($data));
		$location 	= $data->location;
		$healthcare = $data->healthWorker;
		$moment 	= $data->moment;
		
		$moment->stepOne 	= $data->stepOne;
		$moment->stepTwo 	= $data->stepTwo;
		$moment->maskType 	= $data->maskType;
		$moment->type 		= $data->compliance;
		$moment->dateTime 	= $data->dateTime;
		$moment->glove 		= isset($data->gloveCompliance) ? $data->gloveCompliance : '';
		$moment->gown 		= isset($data->gownCompliance) ? $data->gownCompliance : '';
		$moment->mask 		= isset($data->maskCompliance) ? $data->maskCompliance : '';
		
		
		if(empty($uid))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'User ID is required.', 'result' => '');
		}
		elseif(empty($data))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'No data submitted.', 'result' => '');
		}
		elseif(!isset($healthcare))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Healthcare worker is required.', 'result' => '');
		}
		elseif(!isset($healthcare->type) || empty($healthcare->type))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Healthcare worker is required.', 'result' => '');
		}
		elseif(!isset($location))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Locations is required.', 'result' => '');
		}
		elseif(!isset($location->levelOne) || empty($location->levelOne))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 1 is required.', 'result' => '');
		}
		elseif(!isset($location->levelTwo) || empty($location->levelTwo))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 2 is required.', 'result' => '');
		}
		elseif(!isset($location->levelThree) || empty($location->levelThree))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 3 is required.', 'result' => '');
		}
		elseif(!isset($location->levelFour) || empty($location->levelFour))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 4 is required.', 'result' => '');
		}
		elseif(!isset($moment->type) || empty($moment->type))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Moment is required.', 'result' => '');
		}
		elseif(!isset($moment->stepOne) || empty($moment->stepOne))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Hand hygiene compliance is required.', 'result' => '');
		}
		elseif(isset($data->resend_id) && $this->validate_resend($data->resend_id) > 0)
		{
			$result = array( 'status' => 1, 'errortype' => 'warning', 'message' => 'This observation already saved to server.', 'result' => 'resend');
		}
		else
		{	
			if(!isset($moment->stepTwo) || $moment->stepTwo == 'NA')
			{
				$glove 				= 'NA';
				$gown 				= 'NA';
				$mask 				= 'NA';
				$moment->maskType 	= 'NA';
			}
			else
			{
				$prefix = ($moment->type == 'Before Contact' || $moment->type == 'Before Procedure') ? 'Don on' : 'Remove';
				
				$glove 	= (isset($moment->glove) && $moment->glove) ? 	$prefix.' glove / Yes' 	: $prefix.' glove / No';
				$gown 	= (isset($moment->gown) && $moment->gown) ? 	$prefix.' gown / Yes' 	: $prefix.' gown / No';
				$mask 	= (isset($moment->mask) && $moment->mask) ? 	$prefix.' mask / Yes' 	: $prefix.' mask / No';
			}
			
			if($moment->type == 'After Environment')
			{
				$glove 				= 'NA';
				$gown 				= 'NA';
				$mask 				= 'NA';
				$moment->maskType 	= 'NA';
				$moment->stepTwo	= 'NA';
			}
			
			$healthcare->type 		= ($healthcare->type == 'Others' && isset($healthcare->otherType) &&  $healthcare->otherType != '') ? 				$healthcare->otherType : 		$healthcare->type; 
			$location->levelTwo 	= ($location->levelTwo == 'Others' && isset($location->levelTwoOther) &&  $location->levelTwoOther != '') ? 		$location->levelTwoOther : 		$location->levelTwo; 
			$location->levelThree 	= ($location->levelThree == 'Others' && isset($location->levelThreeOther) &&  $location->levelThreeOther != '') ? 	$location->levelThreeOther : 	$location->levelThree; 
			$location->levelFour 	= ($location->levelFour == 'Others' && isset($location->levelFourOther) &&  $location->levelFourOther != '') ? 		$location->levelFourOther : 	$location->levelFour; 
			
			
			$user = $this->Mdl_users->get_user($uid);
			
			$this->db->insert('observations', array(
				'uid' 					=> $uid,
				'cid'					=> $user->cid,
				'resend_id'				=> isset($data->resend_id) ? $data->resend_id : '',
				'hcw_title' 			=> $healthcare->type->id,
				'hcw_name' 				=> isset($healthcare->name) ? $healthcare->name : '',
				'organization' 			=> isset($data->organizationName) ? $data->organizationName : '',
				'moment'				=> $moment->type,
				'note'					=> isset($moment->note) ? $moment->note : '',
				'location_level1'		=> $location->levelOne->id,
				'location_level2'		=> $location->levelTwo->id,
				'location_level3'		=> $location->levelThree->id,
				'location_level4'		=> $location->levelFour->id,
				'hh_compliance'			=> isset($moment->stepOne) ? $moment->stepOne : 'No',
				'hh_compliance_type'	=> isset($moment->stepTwo) ? $moment->stepTwo : 'NA',
				'glove_compliance'		=> $glove,
				'gown_compliance'		=> $gown,
				'mask_compliance'		=> $mask,
				'mask_type'				=> isset($moment->maskType) ? $moment->maskType : 'NA',
				'date_registered'		=> date('Y-m-d H:i:s', strtotime($moment->dateTime))
			));
			
			if($this->db->affected_rows())
			{
				$this->load->model('Mdl_users');
				
				$user = $this->Mdl_users->get_user($uid);
				$this->db->where('cid', $user->cid);
				$this->db->from('observations');
				$stats['companyRecords'] = $this->db->count_all_results();
				
				$this->db->where('uid', $uid);
				$this->db->from('observations');
				$stats['accountRecords'] = $this->db->count_all_results();
				
				$s_uid = $this->ion_auth->get_users_groups($uid)->row()->id != 1 ? $uid : '';
				
				$stats['momentRecords'] = $this->api_countobservation($user->cid, $s_uid);
				
				$result = array( 'status' => 1, 'errortype' => 'success', 'message' => 'New observation successfully save to server.', 'result' => $stats);
			}
			else
			{
				$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Server Error.', 'result' => '');
			}
		}
		
		return $result;
	}
	
	public function api_saveobservation($data, $uid)
	{
		$data = json_decode(json_encode($data));
		$user = $this->ion_auth->user($uid)->row();
		
		if(empty($data))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'No data submitted.', 'result' => '');
		}
		elseif(!isset($data->uid) || empty($data->uid))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'User ID is required.', 'result' => '');
		}
		elseif(!isset($data->hcw_title))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Healthcare worker is required.', 'result' => '');
		}
		elseif(!isset($data->location_level1) || empty($data->location_level1))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 1 is required.', 'result' => '');
		}
		elseif(!isset($data->location_level2) || empty($data->location_level2))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 2 is required.', 'result' => '');
		}
		elseif(!isset($data->location_level3) || empty($data->location_level3))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 3 is required.', 'result' => '');
		}
		elseif(!isset($data->location_level4) || empty($data->location_level4))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Location level 4 is required.', 'result' => '');
		}
		elseif(!isset($data->moment) || empty($data->moment))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Moment is required.', 'result' => '');
		}
		elseif(!isset($data->hh_compliance) || empty($data->hh_compliance))
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Hand hygiene compliance is required.', 'result' => '');
		}
		elseif(isset($data->resend_id) && $this->validate_resend($data->resend_id) > 0)
		{
			$result = array( 'status' => 1, 'errortype' => 'warning', 'message' => 'This observation already saved to server.', 'result' => 'resend');
		}
		else
		{	
			if(!isset($data->hh_compliance_type) || $data->hh_compliance_type == 'NA')
			{
				$data->glove_compliance = $data->gown_compliance = $data->mask_compliance = $data->mask_type = 'NA';
				$data->mask_type		= 'NA';
			}
			else
			{
				$prefix = ($data->moment ==  1 || $data->moment == 2) ? 'Don on' : 'Remove';
				$data->glove_compliance = (isset($data->glove_compliance) && $data->glove_compliance) ? $prefix.' glove / Yes' 	: $prefix.' glove / No';
				$data->gown_compliance 	= (isset($data->gown_compliance) && $data->gown_compliance) ? 	$prefix.' gown / Yes' 	: $prefix.' gown / No';
				$data->mask_compliance 	= (isset($data->mask_compliance) && $data->mask_compliance) ? 	$prefix.' mask / Yes' 	: $prefix.' mask / No';
				$data->mask_type		= ($data->mask_compliance == 'Don on mask / Yes' || $data->mask_compliance == 'Remove mask / Yes') ? $data->mask_type : 'NA';
			}
			
			$this->db->insert('observations', array(
				'uid' 					=> $data->uid,
				'cid'					=> $data->cid,
				'resend_id'				=> !isset($data->resend_id) ? '' : $data->resend_id,
				'hcw_title' 			=> $data->hcw_title,
				'hcw_name' 				=> !isset($data->hcw_name) 		? '' : $data->hcw_name,
				'organization' 			=> !isset($data->organization) 	? '' : $data->organization,
				'moment'				=> $data->moment,
				'note'					=> !isset($data->note) 			? '' : $data->note,
				'location_level1'		=> $data->location_level1,
				'location_level2'		=> $data->location_level2,
				'location_level3'		=> $data->location_level3,
				'location_level4'		=> $data->location_level4,
				'hh_compliance'			=> $data->hh_compliance,
				'hh_compliance_type'	=> !isset($data->hh_compliance_type) ? '' : $data->hh_compliance_type,
				'glove_compliance'		=> $data->glove_compliance,
				'gown_compliance'		=> $data->gown_compliance,
				'mask_compliance'		=> $data->mask_compliance,
				'mask_type'				=> $data->mask_type,
				'date_registered'		=> date('Y-m-d H:i:s', strtotime($data->date_registered))
			));
			
			if($this->db->affected_rows())
			{
				$this->db->where('cid', $user->cid);
				$this->db->from('observations');
				$stats['companyRecords'] = $this->db->count_all_results();
				
				$this->db->where('uid', $uid);
				$this->db->from('observations');
				$stats['accountRecords'] = $this->db->count_all_results();
				$stats['momentRecords'] = $this->api_countobservation($user->cid, $uid);
				
				$result = array( 'status' => 1, 'errortype' => 'success', 'message' => 'New observation successfully save to server.', 'result' => $stats);
			}
			else
			{
				$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'Server Error.', 'result' => '');
			}
		}
		
		return $result;
	}
	
	
	public function api_multiobservesend($data, $uid)
	{
		$data 	= json_decode(json_encode($data));
		$user 	= $this->ion_auth->user($uid)->row();
		$stats	= array();
		$this->db->insert('observations', $data);
		
		if($this->db->affected_rows())
		{
			$this->db->where('cid', $user->cid);
			$this->db->from('observations');
			$stats['companyRecords'] = $this->db->count_all_results();
			
			$this->db->where('uid', $uid);
			$this->db->from('observations');
			$stats['accountRecords'] 	= $this->db->count_all_results();
			$stats['momentRecords'] 	= $this->api_countobservation($user->cid, $uid);
			
			return array( 'status' => 1, 'errortype' => 'success', 'message' => 'New observation successfully save to server.', 'result' => $stats);
		}
		else
			return array( 'status' => 0, 'errortype' => 'error', 'message' => 'Server Error.', 'result' => '');
	}
	
	public function api_countobservation($cid, $uid = '')
	{
		$user 		= $this->ion_auth->user($uid)->row();
		$date_from 	= strtotime($this->input->get('dateFrom'));
		$date_to 	= strtotime($this->input->get('dateTo'));
		$group_id	= $this->ion_auth->get_users_groups($uid)->row()->id;
		
		if($this->input->get('department') > 0) 	$this->db->where('location_level1', $this->input->get('department'));
		if($this->input->get('subDepartment') > 0) 	$this->db->where('location_level2', $this->input->get('subDepartment'));
		if($this->input->get('ward') > 0) 			$this->db->where('location_level3', $this->input->get('ward'));
		if($this->input->get('patient') > 0) 		$this->db->where('location_level4', $this->input->get('patient'));
		if($this->input->get('healthcare') > 0) 	$this->db->where('hcw_title', $this->input->get('healthcare'));
		
		if($this->input->get('dateFrom') != '')
			$this->db->where('observations.date_registered >=',date('Y-m-d 00:00:00',$date_from));
			
		if($this->input->get('dateTo') != '')
			$this->db->where('observations.date_registered <=',date('Y-m-d 23:59:59',$date_to));
				
		if($group_id == 2)
			$this->db->where('uid', $uid);
		
		if($this->input->get('auditor') != '')	
			$this->db->where('uid', $this->input->get('auditor'));
		
		$this->db->where('cid', $cid);
		$this->db->select('moment1, moment2, moment3, moment4, moment5, hh_compliance');
		$query = $this->db->get('observations');
		
		$passedFailed 	= array('passed' => 0, 'failed' => 0);
		$momentRecords 	= array( 1 => $passedFailed, 2 => $passedFailed, 3 => $passedFailed, 4 => $passedFailed, 5 => $passedFailed);
		
		foreach($query->result() as $data)
		{
			for($x = 1; $x <=5; $x++){
				$moment = 'moment'.$x; 
				if($data->$moment == $x)
				{
					if($data->hh_compliance == 'missed')
						$momentRecords[$x]['failed']++;
					else
						$momentRecords[$x]['passed']++;
				}
			}
		}
		return $momentRecords;
	}
	
	public function validate_resend($id)
	{
		$this->db->where('resend_id',$id);
		$this->db->select('id');
		$this->db->from('observations');
		return $this->db->count_all_results();
	}
	
	public function api_getobservation($uid, $type)
	{
		$data 		= array();
		$user 		= $this->ion_auth->user($uid)->row();
		$date_from 	= strtotime($this->input->get('dateFrom'));
		$date_to 	= strtotime($this->input->get('dateTo'));
		$locations	= $this->get_all_locations($user->cid);
		
		if($this->input->get('department') > 0) 	$this->db->where('location_level1', $this->input->get('department'));
		if($this->input->get('subDepartment') > 0) 	$this->db->where('location_level2', $this->input->get('subDepartment'));
		if($this->input->get('ward') > 0) 			$this->db->where('location_level3', $this->input->get('ward'));
		if($this->input->get('patient') > 0) 		$this->db->where('location_level4', $this->input->get('patient'));
		if($this->input->get('healthcare') > 0) 	$this->db->where('hcw_title', $this->input->get('healthcare'));
		
		if($this->input->get('dateFrom') != '')
			$this->db->where('observations.date_registered >=',date('Y-m-d 00:00:00',$date_from));
			
		if($this->input->get('dateTo') != '')
			$this->db->where('observations.date_registered <=',date('Y-m-d 23:59:59',$date_to));
		
		if($this->input->get('auditor') != '')	
			$this->db->where('uid', $this->input->get('auditor'));
		
		if($type == 'user')			
			$this->db->where('uid', $uid);
		
		$this->db->join('users as u1', 'observations.uid = u1.id');
		
		$this->db->select("
			CONCAT(u1.first_name,' ',u1.last_name) AS full_name,
			observations.*, 
			DATE_FORMAT(observations.date_registered, '%b %d, %Y') AS date_registered,
			DATE_FORMAT(observations.date_registered, '%b %d, %Y %h:%i %p') AS datetime,
		",FALSE);
		$this->db->order_by('observations.date_registered', 'ASC');
		$query = $this->db->get('observations');
	
		
		if($query->num_rows())
		{
			$x = 0;
			$date = '';
			foreach($query->result() as $row)
			{
				$return_data[$x] 					= $row;
				$return_data[$x]->hcw_title 		= $locations[$row->hcw_title]['name'];
				$return_data[$x]->location_level1 	= $locations[$row->location_level1]['name'];
				$return_data[$x]->location_level2 	= $locations[$row->location_level2]['name'];
				$return_data[$x]->location_level3 	= $locations[$row->location_level3]['name'];
				$return_data[$x]->location_level4 	= $locations[$row->location_level4]['name'];
				
				if($date != $row->date_registered)
				{
					$return_data[$x]->header_date 	= date('M j, Y',strtotime($row->date_registered));
					$date							= $row->date_registered;
				}else
					$return_data[$x]->header_date = '';
					
				$x++;
			}
			
			$data['data'] 			= $return_data;
			$data['date']['from'] 	= date('M j, Y', $date_from);
			$data['date']['to'] 	= date('M j, Y', $date_to);
			
			if($this->input->get('download'))
			{
				return $this->api_sendmailreports($return_data, $uid, $this->input->get('download'));				
			}
		}
		else
		{
			$data['data'] 	= array();
			$data['date']['from'] 	= date('M j, Y', $date_from);
			$data['date']['to'] 	= date('M j, Y', $date_to);
		}
		
		return array( 'status' => 1, 'errortype' => 'success', 'message' => '', 'result' => $data);
	}
	
	
	public function get_all_locations($cid)
	{
		$this->db->where(array('cid' => $cid));
		$query = $this->db->get('locations');
		
		foreach($query->result() as $loc)
		{
			$result[$loc->id] = array('name' => $loc->name, 'id' => $loc->id);
		}
		
		return $result;
	}
	
	public function api_sendmailreports($data, $uid, $type)
	{
		if(count($data) == 0)
		{
			$result = array( 'status' => 0, 'errortype' => 'error', 'message' => 'No data available.', 'result' => '');
		}
		else
		{
			$this->load->library('email', array(
				'protocol'  => 'smtp',
				'smtp_host' => 'ssl://smtp.googlemail.com',
				'smtp_port' => '465',
				'smtp_user' => 'noreplytripoption@gmail.com',
				'smtp_pass' => 'Powercom888',
				'smtp_timeout' => '4',
				'mailtype'  => 'html',
			));
			
			if($type == 1 || $type == 3) $pdf 	= $this->api_pdfreports($data);
			if($type == 2 || $type == 3) $excel = $this->api_excelreports($data);
				
			$user = $this->ion_auth->user($uid)->row();
			
			$this->email->set_newline("\r\n");
			$this->email->from($this->site->info('support_email'), $this->site->info('business_title'));
			$this->email->to($user->email);
			$this->email->subject('Hand hygiene observations reports '.date('m/d/Y'));
			$this->email->message('Hand hygiene report');
	
			if(isset($pdf)) 	$this->email->attach($pdf);
			if(isset($excel)) 	$this->email->attach($excel);

			$this->email->send();
	
			if(isset($pdf) && file_exists($pdf)) 		unlink($pdf);
			if(isset($excel) && file_exists($excel)) 	unlink($excel);
			
			$result = array( 'status' => 1, 'errortype' => 'success', 'message' => 'Report successfully sent, please check your email address.', 'result' => '');
		}
		
		return $result;
	}
	
	public function api_pdfreports($data)
	{
		if(count($data) == 0) return false;
		
		$data_array 	= json_decode(json_encode($data));
		$param['data'] 	= $data_array;
		$date_from 	= date('m-d-Y', strtotime($this->input->get('dateFrom')));
		$date_to 	= date('m-d-Y', strtotime($this->input->get('dateTo')));
		
		$html = $this->load->view('report-table', $param, TRUE);
			
		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'cm', 'REPORT', true, 'UTF-8', true);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('B. Braun');
		$pdf->SetTitle('Compliance Data');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetFontSize(10);
		$pdf->AddPage('L', 'REPORT');
	
		$pdf->writeHTML($html, true, false, true, false, '');
		$file = FCPATH.'assets/uploads/HHAT_COMPLIANCE_DATA_'.rand(1000,9999).'_'.$date_from.' to '.$date_to.'.pdf';
		$pdf->Output($file, 'F');
		
		return $file;
	}
	
	public function api_excelreports($data)
	{
		if(count($data) == 0) return false;
		
		$arr_HcwType = array();
		$arr_Ward = array();
		$arr_Department = array();

		//Header	
		$contents['A1'] = '';
		$contents['B1'] = '';
		$contents['C1'] = 'LOCATION';
		
		$contents['D1'] = '';
		$contents['E1'] = '';
		$contents['F1'] = '';
		$contents['G1'] = '';
		$contents['H1'] = 'HEALTHCARE WORKER';
		
		$contents['I1'] = '';
		$contents['J1'] = 'HAND HYGIENE COMPLIANCE';
		$contents['K1'] = '';
		$contents['L1'] = '';
		$contents['M1'] = '';
		$contents['N1'] = '';
		
		$contents['O1'] = '';
		$contents['P1'] = '';
		$contents['Q1'] = 'Occupational';
		

		$contents['A2'] = 'Date & Time';
		$contents['B2'] = 'Auditor';
		$contents['C2'] = 'Branch';
		
		$contents['D2'] = 'Facility';
		$contents['E2'] = 'Department';
		$contents['F2'] = 'Ward';
		$contents['G2'] = 'Service';
		
		$contents['H2'] = 'Title';
		$contents['I2'] = 'Name';
		
		$contents['J2'] = 'Indication/s';
		
		$contents['K2'] = '';		
		$contents['L2'] = '';
		$contents['M2'] = '';
		$contents['N2'] = '';
		
		$contents['O2'] = 'Action';
		$contents['P2'] = 'Result';
		$contents['Q2'] = 'Exposure Risk';
		
		$contents['R2'] = 'GLOVES';
		$contents['S2'] = 'GOWN';
		$contents['T2'] = 'MASK';
		$contents['U2'] = 'Mask Type';
		$contents['V2'] = 'Notes';
		
		//Header
		$this->load->library('excel');
		$letters 	= range('A', 'Z');
		$i 			= 3;
		foreach($data as $items)
		{
			$contents['A'.$i] = $items->datetime;
			$contents['B'.$i] = $items->full_name;
			$contents['C'.$i] = $items->organization;
			$contents['D'.$i] = $items->location_level1;
			$contents['E'.$i] = $items->location_level2;
			$contents['F'.$i] = $items->location_level3;
			$contents['G'.$i] = $items->location_level4;
			$contents['H'.$i] = $items->hcw_title;
			$contents['I'.$i] = $items->hcw_name;
			$contents['J'.$i] = $items->moment1;
			$contents['K'.$i] = $items->moment2;
			$contents['L'.$i] = $items->moment3;
			$contents['M'.$i] = $items->moment4;
			$contents['N'.$i] = $items->moment5;
			$contents['O'.$i] = $items->hh_compliance;
			$contents['P'.$i] = $items->hh_compliance == 'missed' ? 'Failed' : 'Passed';
			$contents['Q'.$i] = $items->hh_compliance_type;
			$contents['R'.$i] = $items->glove_compliance;
			$contents['S'.$i] = $items->gown_compliance;
			$contents['T'.$i] = $items->mask_compliance;
			$contents['U'.$i] = $items->mask_type;
			$contents['V'.$i] = $items->note;
			
			if (!in_array($items->hcw_title, $arr_HcwType)) {
				array_push($arr_HcwType, $items->hcw_title);
			}
			if (!in_array($items->location_level3, $arr_Ward)) {
				array_push($arr_Ward, $items->location_level3);
			}
			if (!in_array($items->location_level2, $arr_Department)) {
				array_push($arr_Department, $items->location_level2);
			}

			$i++;
		}
		
		$this->excel->getProperties()->setCreator("Hand Hygiene Auditing Tool");
		$this->excel->getProperties()->setLastModifiedBy("RocketSpin.ph");
		$this->excel->getProperties()->setTitle("HHAT Compliance Data");
		$this->excel->getProperties()->setSubject("HHAT Reports");
		$this->excel->getProperties()->setDescription("System Generated Reports");

		$this->excel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11)->setName('Arial');
		$this->excel->getActiveSheet()->getStyle("A1:Z2")->getFont()->setBold(true);
		
		$this->excel->setActiveSheetIndex(0)->mergeCells('C1:G1');
		$this->excel->setActiveSheetIndex(0)->mergeCells('H1:I1');
		$this->excel->setActiveSheetIndex(0)->mergeCells('J1:P1');
		$this->excel->setActiveSheetIndex(0)->mergeCells('J2:N2');
		
		$this->excel->getActiveSheet()->getStyle('A1:Z2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('A1:V2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('2E8B57');
		$this->excel->getActiveSheet()->getStyle('A1:V2')->getFont()->applyFromArray(array('color' => array('rgb' => 'FFFFFF')));
		
		$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(19.33);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15.17);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(9.17);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(14.33);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10.17);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10.17);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(10.17);
		$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(10.17);
		$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(18.00);
		$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(4.17);
		$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(4.17);
		$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(4.17);
		$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(4.17);
		$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(4.17);
		$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(8.0);
		$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(7.17);
		$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(13.67);
		$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(7.67);
		$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(7.67);
		$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(7.67);
		$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(12.83);
		$this->excel->getActiveSheet()->getColumnDimension('V')->setWidth(29.17);
	
		foreach($contents as $key => $item)
		{
			$this->excel->getActiveSheet()->setCellValue($key, $item);	
		}
		
		$date_from 	= date('m-d-Y', strtotime($this->input->get('dateFrom')));
		$date_to 	= date('m-d-Y', strtotime($this->input->get('dateTo')));
		


		// START Ronald code here
		$ews2 = $this->excel->createSheet(0);
		$ews2->setTitle('Summary');

		$ews2->getColumnDimension('A')->setWidth(11);
		$ews2->getColumnDimension('B')->setWidth(11);
		$ews2->getColumnDimension('C')->setWidth(11);
		$ews2->getColumnDimension('D')->setWidth(11);
		$ews2->getColumnDimension('E')->setWidth(11);
		$ews2->getColumnDimension('F')->setWidth(11);
		$ews2->getColumnDimension('G')->setWidth(11);
		$ews2->getColumnDimension('H')->setWidth(11);
		$ews2->getColumnDimension('I')->setWidth(11);
		$ews2->getColumnDimension('J')->setWidth(11);
		$ews2->getColumnDimension('K')->setWidth(11);
		$ews2->getColumnDimension('L')->setWidth(11);
		$ews2->getColumnDimension('M')->setWidth(11);
		$ews2->getColumnDimension('N')->setWidth(11);
		$ews2->getColumnDimension('O')->setWidth(11);
		$ews2->getColumnDimension('P')->setWidth(11);
		$ews2->getColumnDimension('Q')->setWidth(11);
		$ews2->getColumnDimension('R')->setWidth(11);
		$ews2->getColumnDimension('S')->setWidth(11);
		$ews2->getColumnDimension('T')->setWidth(11);
		$ews2->getColumnDimension('U')->setWidth(11);
		$ews2->getColumnDimension('V')->setWidth(11);
		$ews2->getColumnDimension('W')->setWidth(11);
		$ews2->getColumnDimension('X')->setWidth(11);
		$ews2->getColumnDimension('Y')->setWidth(11);
		$ews2->getColumnDimension('Z')->setWidth(11);
		
		$ews2->setCellValue('a22', 'HCW Type');
		$ews2->setCellValue('b22', 'Total Passed');
		$ews2->setCellValue('c22', 'Total Submitted');
		$ews2->setCellValue('d22', 'Percentage');


		$ews2->setCellValue('f22', 'Indication');
		$ews2->setCellValue('f23', 'Indication 1');
		$ews2->setCellValue('f24', 'Indication 2');
		$ews2->setCellValue('f25', 'Indication 3');
		$ews2->setCellValue('f26', 'Indication 4');
		$ews2->setCellValue('f27', 'Indication 5');

	    $ews2->setCellValue('g22', 'Count');
	    $ews2->setCellValue('g23', '=COUNTIF(Worksheet!$J$3:$J$5000,1)');
		$ews2->setCellValue('g24', '=COUNTIF(Worksheet!$K$3:$K$5000,2)');
		$ews2->setCellValue('g25', '=COUNTIF(Worksheet!$L$3:$L$5000,3)');
		$ews2->setCellValue('g26', '=COUNTIF(Worksheet!$M$3:$M$5000,4)');
		$ews2->setCellValue('g27', '=COUNTIF(Worksheet!$N$3:$N$5000,5)');

		$ews2->setCellValue('K22', 'Department');
		$ews2->setCellValue('L22', 'Total Passed');
		$ews2->setCellValue('M22', 'Total Submitted');
		$ews2->setCellValue('N22', 'Percentage');

		$ews2->setCellValue('P22', 'Ward');
		$ews2->setCellValue('Q22', 'Total Passed');
		$ews2->setCellValue('R22', 'Total Submitted');
		$ews2->setCellValue('S22', 'Percentage');		


		//FOR HCW
		$ctr = 23;
		foreach ($arr_HcwType as $value) {
		    $ews2->setCellValue('a'.$ctr, $value);
			$ews2->setCellValue('b'.$ctr, '=COUNTIFS(Worksheet!$P$3:$P$5000,"Passed",Worksheet!$H$3:$H$5000,"'.$value.'")');
			$ews2->setCellValue('c'.$ctr, '=COUNTIF(Worksheet!$H$3:$H$5000, "'.$value.'")');
			$ews2->setCellValue('d'.$ctr, '=b'.$ctr.'/c'.$ctr);
			$ews2->getStyle('D'.$ctr)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$ctr++;
		}

		$dsl = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$D$22', null, 1),
		);
		$xal = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$A$23:$A$'.($ctr-1), null, ($ctr-23)),	//	Q1 to Q4
		);
		$dsv = array(
			new PHPExcel_Chart_DataSeriesValues('Number', 'Summary!$D$23:$D$'.($ctr-1), null, ($ctr-23)),
		);

		$ds = new PHPExcel_Chart_DataSeries(
                    PHPExcel_Chart_DataSeries::TYPE_BARCHART,
                    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
                    range(0, count($dsv)-1),
                    $dsl,
                    $xal,
                    $dsv
                    );
		$ds->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

		$pa = new PHPExcel_Chart_PlotArea(NULL, array($ds));
		$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
		$title = new PHPExcel_Chart_Title('Healthcare Worker Type compliance');
		$chart = new PHPExcel_Chart(
                    'chart1',
                    $title,
                    $legend,
                    $pa,
                    true,
                    0,
                    NULL, 
                    NULL
                    );

		$chart->setTopLeftPosition('A2');
		$chart->setBottomRightPosition('E20');

		$ews2->addChart($chart);





		$dslmoment = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$G22', null, 1),
		);
		$xalmoment = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$F23:$F27', null, 1),
		);
		$dsvmoment = array(
			new PHPExcel_Chart_DataSeriesValues('Number', 'Summary!$G23:$G27', null, 1),
		);

		$dsmoment = new PHPExcel_Chart_DataSeries(
                    PHPExcel_Chart_DataSeries::TYPE_BARCHART,
                    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
                    range(0, count($dsvmoment)-1),
                    $dslmoment,
                    $xalmoment,
                    $dsvmoment
                    );
		$dsmoment->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

		$pamoment = new PHPExcel_Chart_PlotArea(NULL, array($dsmoment));
		$legendmoment = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
		$titlemoment = new PHPExcel_Chart_Title('Number of Opportunities Between Diffirent Indications');
		$chartmoment = new PHPExcel_Chart(
                    'chart2',
                    $titlemoment,
                    $legendmoment,
                    $pamoment,
                    true,
                    0,
                    NULL, 
                    NULL
                    );

		$chartmoment->setTopLeftPosition('F2');
		$chartmoment->setBottomRightPosition('J20');

		$ews2->addChart($chartmoment);






		//FOR DEPARTMENT
		$dctr = 23;
		foreach ($arr_Department as $dvalue) {
		    $ews2->setCellValue('K'.$dctr, $dvalue);
			$ews2->setCellValue('L'.$dctr, '=COUNTIFS(Worksheet!$P$3:$P$5000,"Passed",Worksheet!$E$3:$E$5000,"'.$dvalue.'")');
			$ews2->setCellValue('M'.$dctr, '=COUNTIF(Worksheet!$E$3:$E$5000, "'.$dvalue.'")');
			$ews2->setCellValue('n'.$dctr, '=l'.$dctr.'/m'.$dctr);
			$ews2->getStyle('N'.$dctr)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$dctr++;
		}

		$dsldepartment = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$N$22', null, 1),
			//new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$D$22', null, 1),
		);
		$xaldepartment = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$K$23:$K$'.($dctr-1), null, ($dctr-23)),
			//new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$A$23:$A$'.($ctr-1), null, ($ctr-23)),
		);
		$dsvdepartment = array(
			new PHPExcel_Chart_DataSeriesValues('Number', 'Summary!$N$23:$N$'.($dctr-1), null, ($dctr-23)),
			//new PHPExcel_Chart_DataSeriesValues('Number', 'Summary!$N$23:$N$'.($dctr-1), null, ($dctr-23)),
		);

		$dsdepartment = new PHPExcel_Chart_DataSeries(
                    PHPExcel_Chart_DataSeries::TYPE_BARCHART,
                    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
                    range(0, count($dsvdepartment)-1),
                    $dsldepartment,
                    $xaldepartment,
                    $dsvdepartment
                    );
		$dsdepartment->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

		$padepartment = new PHPExcel_Chart_PlotArea(NULL, array($dsdepartment));
		$legenddepartment = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
		$titledepartment = new PHPExcel_Chart_Title('Department Compliance');
		$chartdepartment = new PHPExcel_Chart(
                    'chart3',
                    $titledepartment,
                    $legenddepartment,
                    $padepartment,
                    true,
                    0,
                    NULL, 
                    NULL
                    );

		$chartdepartment->setTopLeftPosition('K2');
		$chartdepartment->setBottomRightPosition('O20');

		$ews2->addChart($chartdepartment);




		//FOR WARD
		$wctr = 23;
		foreach ($arr_Ward as $wvalue) {
		    $ews2->setCellValue('P'.$wctr, $wvalue);
			$ews2->setCellValue('Q'.$wctr, '=COUNTIFS(Worksheet!$P$3:$P$5000,"Passed",Worksheet!$F$3:$F$5000,"'.$wvalue.'")');
			$ews2->setCellValue('R'.$wctr, '=COUNTIF(Worksheet!$F$3:$F$5000, "'.$wvalue.'")');
			$ews2->setCellValue('s'.$wctr, '=q'.$wctr.'/r'.$wctr);
			$ews2->getStyle('S'.$wctr)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$wctr++;
		}

		$dslward = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$S$22', null, 1),
		);
		$xalward = array(
			new PHPExcel_Chart_DataSeriesValues('String', 'Summary!$P$23:$P$'.($wctr-1), null, ($wctr-23)),	//	Q1 to Q4
		);
		$dsvward = array(
			new PHPExcel_Chart_DataSeriesValues('Number', 'Summary!$S$23:$S$'.($wctr-1), null, ($wctr-23)),
		);

		$dsward = new PHPExcel_Chart_DataSeries(
                    PHPExcel_Chart_DataSeries::TYPE_BARCHART,
                    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
                    range(0, count($dsvward)-1),
                    $dslward,
                    $xalward,
                    $dsvward
                    );
		$dsward->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

		$paward = new PHPExcel_Chart_PlotArea(NULL, array($dsward));
		$legendward = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
		$titleward = new PHPExcel_Chart_Title('Ward Compliance');
		$chartward = new PHPExcel_Chart(
                    'chart4',
                    $titleward,
                    $legendward,
                    $paward,
                    true,
                    0,
                    NULL, 
                    NULL
                    );

		$chartward->setTopLeftPosition('P2');
		$chartward->setBottomRightPosition('T20');

		$ews2->addChart($chartward);


		// END Ronald code here

		// Do your stuff here
		$writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
		$writer->setIncludeCharts(true); //Ronald code
		$file 	= FCPATH.'assets/uploads/HHAT_COMPLIANCE_DATA_'.rand(1000,9999).'_'.$date_from.' to '.$date_to.'.xlsx';
		$writer->save($file);
		return $file;
	}
		
	
	public function api_locations($cid, $uid)
	{
		$this->db->where(array('cid' => $cid, 'deleted' => 0));
		$query 	= $this->db->get('locations');
		$result = array();
		
		foreach($query->result() as $loc)
		{
			$sid = ($loc->sort == 0) ? $loc->id : $loc->sort;
			$result[$loc->category][$sid] = array('name' => $loc->name, 'id' => $loc->id);
		}
		
		if($this->input->get('type') == 'mobile')
		{
			$this->db->where('id', $uid);
			$this->db->update('users', array('data_update' => '0'));
		}
		return array( 'status' => 1, 'errortype' => 'success', 'message' => '', 'result' => $result);
	}
	
	
	
	public function api_statistics($uid)
	{
		$this->load->model('Mdl_users');
		$user 		= $this->ion_auth->user($uid)->row();
		$date_from 	= strtotime($this->input->get('dateFrom'));
		$date_to 	= strtotime($this->input->get('dateTo'));
		
		if($this->input->get('department') > 0) 	$this->db->where('location_level1', $this->input->get('department'));
		if($this->input->get('subDepartment') > 0) 	$this->db->where('location_level2', $this->input->get('subDepartment'));
		if($this->input->get('ward') > 0) 			$this->db->where('location_level3', $this->input->get('ward'));
		if($this->input->get('patient') > 0) 		$this->db->where('location_level4', $this->input->get('patient'));
		
		if($this->input->get('dateFrom') != '')
			$this->db->where('observations.date_registered >=',date('Y-m-d 00:00:00',$date_from));
			
		if($this->input->get('dateTo') != '')
			$this->db->where('observations.date_registered <=',date('Y-m-d 23:59:59',$date_to));
			
		$this->db->where('cid', $user->cid);
		$this->db->from('observations');
		$stats['companyRecords'] = $this->db->count_all_results();
		
		
		if($this->input->get('department') > 0) 	$this->db->where('location_level1', $this->input->get('department'));
		if($this->input->get('subDepartment') > 0) 	$this->db->where('location_level2', $this->input->get('subDepartment'));
		if($this->input->get('ward') > 0) 			$this->db->where('location_level3', $this->input->get('ward'));
		if($this->input->get('patient') > 0) 		$this->db->where('location_level4', $this->input->get('patient'));
		if($this->input->get('healthcare') > 0) 	$this->db->where('hcw_title', $this->input->get('healthcare'));
		
		if($this->input->get('dateFrom') != '')
			$this->db->where('observations.date_registered >=',date('Y-m-d 00:00:00',$date_from));
			
		if($this->input->get('dateTo') != '')
			$this->db->where('observations.date_registered <=',date('Y-m-d 23:59:59',$date_to));
		
		if($this->input->get('auditor') != '')	
			$this->db->where('uid', $this->input->get('auditor'));
		
		$this->db->where('uid', $uid);
		$this->db->from('observations');
		$stats['accountRecords'] 	= $this->db->count_all_results();
		$stats['momentRecords'] 	= $this->api_countobservation($user->cid, $uid);
		
		$stats['date']['from'] 		= date('M j, Y', $date_from);
		$stats['date']['to'] 		= date('M j, Y', $date_to);
		
		return array( 'status' => 1, 'errortype' => 'success', 'message' => $uid, 'result' => $stats);
	}
	
	public function search_locations($category, $keyword, $cid)
	{
		$this->db->where('id', $category);
		$this->db->where('cid', $cid);
		$this->db->like('name', $keyword);
		$this->db->select('id');
		$query 		= $this->db->get('locations');
		$response 	= false;
		
		if($query->num_rows())
		{
			$response = array();
			foreach($query->result() as $row)
			{
				$response[] = $row->id;
			}
		}
		
		return $response;
	}
}








