<?php 
	class C_LokasiPatroli extends CI_Controller {
		private $table = 'tlokasipatroli';

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

			$id = $this->input->post('id');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			
			$SQL = "
				SELECT 
					a.*
				FROM tlokasipatroli a
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
			";

			if ($id != "") {
				$SQL .= " AND a.id = '".$id."' ";
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

			$id = $this->input->post('id');
			$NamaArea = $this->input->post('NamaArea');
			$AlamatArea = $this->input->post('AlamatArea');
			$Keterangan = $this->input->post('Keterangan');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$StartPatroli = $this->input->post('StartPatroli');
			$IntervalPatroli = $this->input->post('IntervalPatroli');
			$IntervalType = $this->input->post('IntervalType');
			$EndPatroli = $this->input->post('EndPatroli');
			$Toleransi = $this->input->post('Toleransi');

			$formtype = $this->input->post('formtype');

			$param = array(
				'NamaArea' => $NamaArea,
				'AlamatArea' => $AlamatArea,
				'Keterangan' => $Keterangan,
				'RecordOwnerID' => $RecordOwnerID,
				'StartPatroli' => $StartPatroli,
				'IntervalPatroli' => $IntervalPatroli,
				'IntervalType' => $IntervalType,
				'EndPatroli' => $EndPatroli,
				'Toleransi'	=> $Toleransi
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
				$rs = $this->ModelsExecuteMaster->ExecUpdate($param,array('id'=>$id,'RecordOwnerID'=>$RecordOwnerID),$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Edit data Jenis Tagihan";
				}
			}
			elseif ($formtype == 'delete') {
				$oCheckPoint = $this->ModelsExecuteMaster->FindData(array('LocationID'=>$id,'RecordOwnerID'=> $RecordOwnerID),'tcheckpoint');

				if ($oCheckPoint->num_rows() > 0) {
					$data['success'] = false;
					$data['message'] = "Data Lokasi Sudah Dipakai";
					goto jump;
				}
				$rs = $this->ModelsExecuteMaster->DeleteData(array('id'=>$id,'RecordOwnerID'=>$RecordOwnerID),$this->table);
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