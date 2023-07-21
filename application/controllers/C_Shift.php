<?php 
	class C_Shift extends CI_Controller {
		private $table = 'tshift';

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
			$LocationID = $this->input->post('LocationID');
			
			$SQL = "
				SELECT 
					a.*
				FROM tshift a
				WHERE a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.LocationID = '".$LocationID."'
			";

			if ($id != "") {
				$SQL .= " AND a.id = '".$id."' ";
			}

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
			$NamaShift = $this->input->post('NamaShift');
			$MulaiBekerja = $this->input->post('MulaiBekerja');
			$SelesaiBekerja = $this->input->post('SelesaiBekerja');
			$IntervalPatroli = $this->input->post('IntervalPatroli');
			$IntervalType = $this->input->post('IntervalType');
			$Toleransi = $this->input->post('Toleransi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$LocationID = $this->input->post('LocationID');
			$GantiHari = $this->input->post('GantiHari');

			$formtype = $this->input->post('formtype');

			$param = array(
				'NamaShift' => $NamaShift,
				'MulaiBekerja' => $MulaiBekerja,
				'SelesaiBekerja' => $SelesaiBekerja,
				'IntervalPatroli' => $IntervalPatroli,
				'IntervalType' => $IntervalType,
				'Toleransi' => $Toleransi,
				'RecordOwnerID' => $RecordOwnerID,
				'LocationID' => $LocationID,
				'GantiHari' => $GantiHari
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
					$data['message'] = "Gagal Tambah data Shift";
				}
			}
			elseif ($formtype == 'edit') {
				$oWhere = array(
					'id' => $id,
					'RecordOwnerID' => $RecordOwnerID,
					'LocationID' => $LocationID
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
					'LocationID' => $LocationID
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