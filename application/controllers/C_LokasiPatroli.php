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

			if ($formtype == "delete") {
				$oParam = array(
					'LocationID' 	=> $id,
					'RecordOwnerID'	=> $RecordOwnerID
				);

				$count = $this->ModelsExecuteMaster->FindData($oParam, 'tcheckpoint')->num_rows();

				if ($count > 0) {
					$data['success'] = false;
					$data['message'] = "Data Lokasi sudah dipakai di Data Checkpoint";
					goto jump;
				}

				$oParam = array(
					'LocationID' 	=> $id,
					'RecordOwnerID'	=> $RecordOwnerID,
					'Status' => 1
				);
				$count = $this->ModelsExecuteMaster->FindData($oParam, 'tsecurity')->num_rows();

				if ($count > 0) {
					$data['success'] = false;
					$data['message'] = "Data Lokasi sudah dipakai di Data Security";
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

		public function DeleteLokasi()
		{
			$response = ['success' => false, 'message' => ''];

			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$LocationID = $this->input->post('LocationID');

			if (!$RecordOwnerID || !$LocationID) {
				$response['message'] = 'Parameter RecordOwnerID dan LocationID wajib diisi';
				echo json_encode($response);
				return;
			}

			// === LOG SETUP ===
			$logFile = FCPATH . 'application/logs/delete_lokasi_' . date('Ymd') . '.txt';
			$log = function($message) use ($logFile) {
				$timestamp = date('[Y-m-d H:i:s] ');
				file_put_contents($logFile, $timestamp . $message . PHP_EOL, FILE_APPEND);
			};

			$log("=== Mulai proses hapus lokasi: RecordOwnerID={$RecordOwnerID}, LocationID={$LocationID} ===");

			// === MULAI TRANSAKSI ===
			$this->db->trans_start();

			try {
				// === 1. Hapus gambar dari tabel patroli ===
				$patroli = $this->db
					->where('RecordOwnerID', $RecordOwnerID)
					->where('LocationID', $LocationID)
					->get('patroli_backup');

				foreach ($patroli->result() as $row) {
					if (!empty($row->Image)) {
						$filePath = FCPATH . 'Assets/images/patroli/' . $row->Image;
						if (file_exists($filePath)) {
							unlink($filePath);
							$log("Hapus file patroli: {$filePath}");
						} else {
							$log("File patroli tidak ditemukan: {$filePath}");
						}
					}
				}

				// === 2. Hapus record patroli ===
				$this->db->where('RecordOwnerID', $RecordOwnerID)
						->where('LocationID', $LocationID)
						->delete('patroli_backup');
				$log("Hapus record dari tabel patroli");

				// === 3. Ambil data absensi untuk hapus gambar ===
				$absensi = $this->db
					->where('RecordOwnerID', $RecordOwnerID)
					->where('LocationID', $LocationID)
					->get('absensi');

				foreach ($absensi->result() as $row) {
					if (!empty($row->ImageIN)) {
						$imgIn = FCPATH . 'Assets/images/Absensi/' . $row->ImageIN;
						if (file_exists($imgIn)) {
							unlink($imgIn);
							$log("Hapus file absensi IN: {$imgIn}");
						} else {
							$log("File absensi IN tidak ditemukan: {$imgIn}");
						}
					}
					if (!empty($row->ImageOut)) {
						$imgOut = FCPATH . 'Assets/images/Absensi/' . $row->ImageOut;
						if (file_exists($imgOut)) {
							unlink($imgOut);
							$log("Hapus file absensi OUT: {$imgOut}");
						} else {
							$log("File absensi OUT tidak ditemukan: {$imgOut}");
						}
					}
				}

				// === 5. Hapus record absensi ===
				$this->db->where('RecordOwnerID', $RecordOwnerID)
						->where('LocationID', $LocationID)
						->delete('absensi');
				$log("Hapus record dari tabel absensi");

				// === 6. Hapus checkpoint ===
				$this->db->where('RecordOwnerID', $RecordOwnerID)
						->where('LocationID', $LocationID)
						->delete('tcheckpoint');
				$log("Hapus record dari tabel tcheckpoint");

				// === 7. Hapus jadwal ===
				// $this->db->where('RecordOwnerID', $RecordOwnerID)
				// 		->delete('tjadwal');
				// $log("Hapus record dari tabel tjadwal");

				// === 8. Hapus security ===
				$this->db->where('RecordOwnerID', $RecordOwnerID)
						->where('LocationID', $LocationID)
						->delete('tsecurity');
				$log("Hapus record dari tabel tsecurity");

				// === 9. Terakhir hapus lokasi patroli ===
				$this->db->where('id', $LocationID)
						->where('RecordOwnerID', $RecordOwnerID)
						->delete('tlokasipatroli');
				$log("Hapus record dari tabel tlokasipatroli");

				// === SELESAI ===
				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE) {
					throw new Exception('Transaksi gagal, semua perubahan dibatalkan');
				}

				$log("=== Transaksi berhasil, semua data terhapus ===");

				$response['success'] = true;
				$response['message'] = 'Data lokasi dan seluruh data terkait berhasil dihapus';
			} catch (Exception $e) {
				$this->db->trans_rollback();
				$log("!!! ERROR: " . $e->getMessage());
				$response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
			}

			echo json_encode($response);
		}

	}
?>