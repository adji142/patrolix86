<?php 
	class C_Pricing extends CI_Controller {
		private $table = 'patroli';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			$this->load->library('Ciqrcode');
		}

		public function ReadPricingTable()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$rs = $this->ModelsExecuteMaster->FindData(array('Status'=> 1), 'pricingtable');

			if ($rs->num_rows()>0) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			else{
				$data['success'] = true;
				$data['message'] = 'No Data Found';
			}

			echo json_encode($data)
		}

		public function ReadPricingTerm()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$PriceID = $this->input->post('PriceID');

			$rs = $this->ModelsExecuteMaster->FindData(array('PriceID'=> $PriceID), 'pricingterm');

			if ($rs->num_rows()>0) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			else{
				$data['success'] = true;
				$data['message'] = 'No Data Found';
			}

			echo json_encode($data)
		}
	}
?>