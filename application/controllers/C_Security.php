<?php 
	class C_Security extends CI_Controller {
		private $table = 'tsecurity';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
		}

		public function Read()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$NIK = $this->input->post('NIK');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			
			$SQL = "
				SELECT 
					a.*,
					b.NamaArea
				FROM tsecurity a
				LEFT JOIN tlokasipatroli b on a.LocationID = b.id
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
			";

			if ($NIK != "") {
				$SQL .= " AND a.NIK = '".$NIK."' ";
			}

			$rs = $this->db->query($SQL);

			if ($rs) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			echo json_encode($data);
		}
		public function CRUD()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$NIK = $this->input->post('NIK');
			$NamaSecurity = $this->input->post('NamaSecurity');
			$JoinDate = $this->input->post('JoinDate');
			$LocationID = $this->input->post('LocationID');
			$Status = $this->input->post('Status');
			$RecordOwnerID = $this->input->post('RecordOwnerID');

			$formtype = $this->input->post('formtype');

			$param = array(
				'NIK' => $NIK,
				'NamaSecurity' => $NamaSecurity,
				'JoinDate' => $JoinDate,
				'LocationID' => $LocationID,
				'Status' => $Status,
				'RecordOwnerID' => $RecordOwnerID,
				'tempEncrypt' => $this->encryption->encrypt($NIK)
			);

			$rs;
			$errormessage = '';
			if ($formtype == 'add') {
				$rs = $this->ModelsExecuteMaster->ExecInsert($param,$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Tambah data Jenis Tagihan";
				}
			}
			elseif ($formtype == 'edit') {
				$rs = $this->ModelsExecuteMaster->ExecUpdate($param,array('NIK'=>$NIK,'RecordOwnerID'=>$RecordOwnerID),$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Edit data Jenis Tagihan";
				}
			}
			elseif ($formtype == 'delete') {
				// $oCheckPoint = $this->ModelsExecuteMaster->FindData(array('LocationID'=>$id,'RecordOwnerID'=> $RecordOwnerID),'tcheckpoint');

				// if ($oCheckPoint->num_rows() > 0) {
				// 	$data['success'] = false;
				// 	$data['message'] = "Data Lokasi Sudah Dipakai";
				// 	goto jump;
				// }
				$rs = $this->ModelsExecuteMaster->DeleteData(array('NIK'=>$NIK,'RecordOwnerID'=>$RecordOwnerID),$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Delete data Jenis Tagihan";
				}
			}
			else{
				$data['message'] = "Invalid Form Type";
			}
			jump:
			echo json_encode($data);
		}
	}
?>