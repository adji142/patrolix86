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
			$Status = $this->input->post('Status');
			
			$SQL = "
				SELECT 
					a.*,
					b.NamaArea,
					COALESCE(d1.NamaShift, e.Nama) NamaShift
				FROM tsecurity a
				LEFT JOIN tlokasipatroli b on a.LocationID = b.id
				LEFT JOIN tshift c on c.RecordOwnerID = a.RecordOwnerID AND c.LocationID = b.id AND c.id = a.Shift
				LEFT JOIN tjadwal d on a.NIK = d.NIK and date(now()) = d.Tanggal
				LEFT JOIN tshift d1 on d.Jadwal = d1.id AND d.RecordOwnerID = d1.RecordOwnerID 
				LEFT JOIN tstatuskehadiran e on d.StatusKehadiran = e.id
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
			";

			if ($NIK != "") {
				$SQL .= " AND a.NIK = '".$NIK."' ";
			}

			if ($LocationID != "") {
				$SQL .= " AND a.LocationID = '".$LocationID."' ";	
			}

			if ($Status != "") {
				$SQL .= " AND a.Status = '".$Status."' ";	
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

			if ($formtype == "delete") {
				$oParam = array(
					'KodeKaryawan' 	=> $NIK,
					'RecordOwnerID'	=> $RecordOwnerID
				);

				$count = $this->ModelsExecuteMaster->FindData($oParam, 'patroli')->num_rows();

				if ($count > 0) {
					$data['success'] = false;
					$data['message'] = "Data Security sudah Pernah melakukan patroli";
					goto jump;
				}

				$oParam = array(
					'NIK' 			=> $NIK,
					'RecordOwnerID'	=> $RecordOwnerID
				);

				$count = $this->ModelsExecuteMaster->FindData($oParam, 'tjadwal')->num_rows();

				if ($count > 0) {
					$data['success'] = false;
					$data['message'] = "Security masih ada jadwal";
					goto jump;
				}
			}

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