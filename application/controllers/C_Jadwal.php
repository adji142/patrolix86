<?php 
	class C_Jadwal extends CI_Controller {
		private $table = 'tjadwal';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			// $this->load->library('Ciqrcode');
		}

		public function Read()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$TglAwal = $this->input->post('TglAwal');
			$TglAkhir = $this->input->post('TglAkhir');

			$id = $this->input->post('id');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$NIK = $this->input->post('NIK');
			$Shift = $this->input->post('Shift');
			$Kehadiran = $this->input->post('Kehadiran');
			$source = $this->input->post('source');
			
			$SQL = "
				SELECT 
					a.id,
					fn_createIDDayName(a.Tanggal)		Hari,
					a.Tanggal,
					c.NamaShift,
					d.Nama 	StatusKehadiran,
					a.Keterangan,
					c.id ShiftID,
					b.Image
				FROM tjadwal a
				LEFT JOIN tsecurity b on a.NIK = b.NIK and a.RecordOwnerID = b.RecordOwnerID
				LEFT JOIN tshift c on a.Jadwal = c.id and a.RecordOwnerID = c.RecordOwnerID
				LEFT JOIN tstatuskehadiran d on a.StatusKehadiran = d.id
				WHERE a.Tanggal BETWEEN date('".$TglAwal."') AND date('".$TglAkhir."')
				AND a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.NIK = '".$NIK."'
			";

			if ($id != "") {
				$SQL .= " AND a.id = '".$id."' ";
			}

			if ($Shift != "") {
				$SQL .= " AND a.Jadwal = '".$Shift."' ";
			}

			if ($Kehadiran != "") {
				$SQL .= " AND a.StatusKehadiran = '".$Kehadiran."' ";
			}

			if ($source=="mobile") {
				$SQL .= " AND COALESCE(a.StatusKehadiran,'') = '' ";
			}


			// var_dump($SQL);

			$rs = $this->db->query($SQL);

			if ($rs) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			echo json_encode($data);
		}

		public function Find()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$id = $this->input->post('id');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$NIK = $this->input->post('NIK');
			
			$SQL = "
				SELECT 
					a.*
				FROM tjadwal a
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.NIK = '".$NIK."'
				AND a.id = $id
			";


			// var_dump($SQL);

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

			$id = $this->input->post('id');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$Tanggal = $this->input->post('Tanggal');
			$NIK = $this->input->post('NIK');
			$Jadwal = $this->input->post('Shift');
			$StatusKehadiran = $this->input->post('StatusKehadiran');
			$Keterangan = $this->input->post('Keterangan');

			$formtype = $this->input->post('formtype');

			// Validasi

			if ($formtype == "add") {
				$oParam = array(
					'Tanggal' 			=> $Tanggal,
					'RecordOwnerID'		=> $RecordOwnerID,
					'NIK'				=> $NIK
				);

				$count = $this->ModelsExecuteMaster->FindData($oParam, $this->table)->num_rows();

				if ($count > 0) {
					$data['success'] = false;
					$data['message'] = "Jadwal sudah dibuat";
					goto jump;
				}
			}

			$rs;
			$errormessage = '';
			if ($formtype == 'add') {
				$param = array(
					'RecordOwnerID' => $RecordOwnerID,
					'Tanggal' => $Tanggal,
					'NIK' => $NIK,
					'Jadwal' => $Jadwal,
					'StatusKehadiran' => $StatusKehadiran,
					'Keterangan' => $Keterangan,
					'CreatedBy' =>  $this->session->userdata('userid'),
					'CreatedOn' => date("y-m-d h:i:s")
				);
				$rs = $this->ModelsExecuteMaster->ExecInsert($param,$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Tambah data Shift";
				}
			}
			elseif ($formtype == 'edit') {
				$param = array(
					'RecordOwnerID' => $RecordOwnerID,
					'Tanggal' => $Tanggal,
					'NIK' => $NIK,
					'Jadwal' => $Jadwal,
					'StatusKehadiran' => $StatusKehadiran,
					'Keterangan' => $Keterangan,
					'LastUpdatedBy' =>  $this->session->userdata('userid'),
					'LastUpdatedOn' => date("y-m-d h:i:s")
				);
				$oWhere = array(
					'id' => $id,
					'RecordOwnerID' => $RecordOwnerID,
					'NIK' => $NIK
				);
				$rs = $this->ModelsExecuteMaster->ExecUpdate($param,$oWhere,$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Edit data Shift";
				}
			}
			elseif ($formtype == 'delete') {
				// $oCheckPoint = $this->ModelsExecuteMaster->FindData(array('LocationID'=>$id,'RecordOwnerID'=> $RecordOwnerID),'tcheckpoint');

				// if ($oCheckPoint->num_rows() > 0) {
				// 	$data['success'] = false;
				// 	$data['message'] = "Data Lokasi Sudah Dipakai";
				// 	goto jump;
				// }
				$oWhere = array(
					'id' => $id,
					'RecordOwnerID' => $RecordOwnerID,
					'NIK' => $NIK
				);
				$rs = $this->ModelsExecuteMaster->DeleteData($oWhere,$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Delete data Shift";
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