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
			$LocationID = $this->input->post('LocationID');
			
			$SQL = "
				SELECT 
					a.*,
					b.NamaArea,
					c.NamaShift
				FROM tsecurity a
				LEFT JOIN tlokasipatroli b on a.LocationID = b.id
				LEFT JOIN tshift c on c.RecordOwnerID = a.RecordOwnerID AND c.LocationID = b.id AND c.id = a.Shift
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
			";

			if ($NIK != "") {
				$SQL .= " AND a.NIK = '".$NIK."' ";
			}

			if ($LocationID != "") {
				$SQL .= " AND a.LocationID = '".$LocationID."' ";	
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
			$Shift = $this->input->post('Shift');

			$formtype = $this->input->post('formtype');

			$param = array(
				'NIK' => $NIK,
				'NamaSecurity' => $NamaSecurity,
				'JoinDate' => $JoinDate,
				'LocationID' => $LocationID,
				'Status' => $Status,
				'RecordOwnerID' => $RecordOwnerID,
				'tempEncrypt' => $this->encryption->encrypt($NIK),
				'Shift' => $Shift
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