<?php 
	class C_Patroli extends CI_Controller {
		private $table = 'patroli';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			$this->load->library('Ciqrcode');
		}

		public function Read()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array(),'Penyelesaian'=> 0);

			$KodeCheckPoint = $this->input->post('KodeCheckPoint');
			$KodeLokasi = $this->input->post('KodeLokasi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$TanggalPatroli = $this->input->post('TanggalPatroli');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
			
			$SQL = "
				SELECT 
					a.KodeCheckPoint,
					a.NamaCheckPoint,
					a.Keterangan,
					c.NamaArea,
					COUNT(b.id)				'JumlahCheckin',
					CASE WHEN COUNT(b.id) >= 1 THEN 1 ELSE 0 END sts
				FROM tcheckpoint a
				LEFT JOIN patroli b on a.KodeCheckPoint = b.KodeCheckPoint AND a.LocationID = b.LocationID AND a.RecordOwnerID = b.RecordOwnerID 
				AND DATE(b.TanggalPatroli) = DATE('".$TanggalPatroli."') 
				AND b.KodeKaryawan = '".$KodeKaryawan."'
				LEFT JOIN tlokasipatroli c on a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
				LEFT JOIN tshift d on b.shift = d.id and b.LocationID = d.LocationID and b.RecordOwnerID = d.RecordOwnerID
				where a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.LocationID = '".$KodeLokasi."'
			";
			// CASE WHEN (SELECT x.GantiHari FROM tshift x where x.id = b.Shift and x.RecordOwnerID = b.RecordOwnerID AND x.LocationID = b.LocationID) = 1 THEN DATE_ADD(b.TanggalPatroli, INTERVAL -1 DAY) ELSE b.TanggalPatroli END
			if ($KodeCheckPoint != "") {
				$SQL .= " AND a.KodeCheckPoint = '".$KodeCheckPoint."' ";
			}

			$SQL.= " GROUP BY a.KodeCheckPoint, a.NamaCheckPoint,a.Keterangan, c.NamaArea ";
			
			$rs = $this->db->query($SQL);

			$SQL2 = "
				SELECT 
					a.LocationID,
					COUNT(DISTINCT a.KodeCheckPoint) JumlahRencanaPatroli,
					COUNT(b.id) JumlahPatroliAktual
				FROM tcheckpoint a
				LEFT JOIN patroli b on a.KodeCheckPoint = b.KodeCheckPoint AND a.LocationID = b.LocationID AND a.RecordOwnerID = b.RecordOwnerID 
				AND DATE(b.TanggalPatroli) = DATE('".$TanggalPatroli."') 
				AND b.KodeKaryawan = '".$KodeKaryawan."'
				LEFT JOIN tshift d on b.shift = d.id and b.LocationID = d.LocationID and b.RecordOwnerID = d.RecordOwnerID
				where a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.LocationID = '".$KodeLokasi."'
				GROUP BY a.LocationID
			";
			// CASE WHEN (SELECT x.GantiHari FROM tshift x where x.id = b.Shift and x.RecordOwnerID = b.RecordOwnerID AND x.LocationID = b.LocationID) = 1 THEN DATE_ADD(b.TanggalPatroli, INTERVAL -1 DAY) ELSE b.TanggalPatroli END

			// (TIMESTAMPDIFF(MINUTE, d.MulaiBekerja, d.SelesaiBekerja) * COUNT(DISTINCT a.KodeCheckPoint)) / (d.IntervalPatroli * 60) JumlahRencanaPatroli,
			$xRS = $this->db->query($SQL2);
			if ($rs) {
				// var_dump($xRS->num_rows());
				$data['success'] = true;
				$data['data'] = $rs->result();
				if ($xRS->num_rows() > 0) {
					if($xRS->row()->JumlahRencanaPatroli > 0){
						$data['Penyelesaian'] = Round(($xRS->row()->JumlahPatroliAktual / $xRS->row()->JumlahRencanaPatroli) * 100, 2);
					}
				}
				else{
					$data['Penyelesaian'] = 0;
				}
			}
			echo json_encode($data);
		}
		public function CRUD()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$RecordOwnerID 		= $this->input->post('RecordOwnerID');
			$KodeCheckPoint 	= $this->input->post('KodeCheckPoint');
			$LocationID 		= $this->input->post('LocationID');
			$TanggalPatroli 	= $this->input->post('TanggalPatroli');
			$KodeKaryawan 		= $this->input->post('KodeKaryawan');
			$Koordinat 			= $this->input->post('Koordinat');
			$Image 				= $this->input->post('Image');
			$ImageName 			= $this->input->post('ImageName');
			$Catatan 			= $this->input->post('Catatan');
			$Shift 				= $this->input->post('Shift');


			$baseDir = FCPATH.'Assets/images/patroli/'.$ImageName;

			// Validasi Subscription
			$SubscriptionID = $this->input->post('SubscriptionID');
			$Subscription = $this->ModelsExecuteMaster->FindData(array('KodePartner' => $RecordOwnerID), 'tcompany');

			if ($Subscription->num_rows() == 0) {
				$data['message'] = "Invalid Subscription";
				goto jump;
			}
			else{
				$EndSubscription = $Subscription->row()->EndSubs;
				if (strtotime($EndSubscription) < time()) {
					$data['message'] = "Subscription Expired";
					goto jump;
				}
			}

			try {
				file_put_contents($baseDir,base64_decode($Image));
				$oParam = array(
					'RecordOwnerID'	=> $RecordOwnerID,
					'LocationID'	=> $LocationID,
					'KodeCheckPoint'=> $KodeCheckPoint,
					'KodeKaryawan'	=> $KodeKaryawan,
					'TanggalPatroli'=> $TanggalPatroli
				);

				$xSQL = "
					SELECT a.* FROM patroli a
					LEFT JOIN tshift b on a.shift = b.id and a.LocationID = b.LocationID and a.RecordOwnerID = b.RecordOwnerID
					WHERE a.RecordOwnerID = '".$RecordOwnerID."'
					AND a.LocationID = '".$LocationID."'
					AND a.KodeCheckPoint = '".$KodeCheckPoint."'
					AND a.KodeKaryawan = '".$KodeKaryawan."'
					AND DATE(CASE WHEN (SELECT x.GantiHari FROM tshift x where x.id = a.Shift and x.RecordOwnerID = a.RecordOwnerID AND x.LocationID = a.LocationID) = 1 THEN DATE_ADD(a.TanggalPatroli, INTERVAL -1 DAY) ELSE a.TanggalPatroli END) = DATE('".$TanggalPatroli."') 
				";

				// $max = $this->ModelsExecuteMaster->FindData($oParam, 'patroli');
				$max = $this->db->query($xSQL);
				$Rank = $max->num_rows();

				// Get Shift

				$oShiftWhere = array(
					'RecordOwnerID' => $RecordOwnerID,
					'LocationID'    => $LocationID
				);
	
				$shifts = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
		
				$current_datetime = new DateTime($TanggalPatroli);
				$current_time_only = $current_datetime->format('H:i:s');
		
				$Shift = "";
		
				foreach ($shifts as $key) {
					$shiftstart = $key->MulaiBekerja;
					$shiftend = $key->SelesaiBekerja;
		
					// $absenstart > $absenend
		
					if ($key->GantiHari == 1) {
						if ($current_time_only >= $shiftstart || $current_time_only <= $shiftend) {
							$Shift = $key->id;
						}
					} else {
						if ($current_time_only >= $shiftstart && $current_time_only <= $shiftend) {
							$Shift = $key->id;
							
						}
					}
				}

				$param = array(
					'RecordOwnerID' 	=> $RecordOwnerID,
					'KodeCheckPoint' 	=> $KodeCheckPoint,
					'LocationID' 		=> $LocationID,
					'TanggalPatroli' 	=> $TanggalPatroli,
					'KodeKaryawan' 		=> $KodeKaryawan,
					'Koordinat'			=> $Koordinat,
					'Image' 			=> $ImageName,
					'Catatan' 			=> $Catatan,
					'Rank' 				=> $Rank,
					'Shift'				=> $Shift
				);

				$rs = $this->ModelsExecuteMaster->ExecInsert($param,$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['success'] = false;
					$data['message'] = "Gagal Simpan CheckPoint";
				}
			} catch (Exception $e) {
				$data['success'] = false;
				$data['message'] = "Gagal Simpan CheckPoint : ".$e->getMessage();
			}

			jump:
			echo json_encode($data);
		}

		public function TestGetShift() {

			$TanggalPatroli 	= $this->input->post('TanggalPatroli');
			$RecordOwnerID 		= $this->input->post('RecordOwnerID');
			$LocationID 		= $this->input->post('LocationID');

			$oShiftWhere = array(
				'RecordOwnerID' => $RecordOwnerID,
				'LocationID'    => $LocationID
			);

			$shifts = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
	
			$current_datetime = new DateTime($TanggalPatroli);
			$current_time_only = $current_datetime->format('H:i:s');
	
			$Shift = "";
	
			foreach ($shifts as $key) {
				$shiftstart = $key->MulaiBekerja;
				$shiftend = $key->SelesaiBekerja;
	
				// $absenstart > $absenend
	
				if ($key->GantiHari == 1) {
					if ($current_time_only >= $shiftstart || $current_time_only <= $shiftend) {
						$Shift = $key->NamaShift;
					}
				} else {
					if ($current_time_only >= $shiftstart && $current_time_only <= $shiftend) {
						$Shift = $key->NamaShift;
						
					}
				}
			}
	
			var_dump($Shift);
		}

		public function move_uploaded_file()
		{
			$sourcePath = FCPATH . 'Assets/images/patroli/';
			$archiveRoot = FCPATH . 'Assets/images/patroli_archive/';
			

			$Bulan = $this->input->post('Bulan');
			$Tahun = $this->input->post('Tahun');

			$start_date = "{$Tahun}-{$Bulan}-01";
			$end_date   = date("Y-m-t", strtotime($start_date));

			$oPatrolData = $this->db->where("TanggalPatroli >=", $start_date)
							->where("TanggalPatroli <=", $end_date)
							// ->where("Image = ","scaled_9186e2e4-4978-4d63-a59f-d114350b2bb18143695232328591163.jpg")
							->get("patroli")
							->result();

			$targetFolder = $archiveRoot . $Tahun.$Bulan . '/';
			// var_dump($targetFolder);
			if (!is_dir($targetFolder)) {
				mkdir($targetFolder);
			}
			echo "Target folder: " . $targetFolder . "<br>";
			echo count($oPatrolData)." records found.<br>";

			foreach ($oPatrolData as $patrol) {
				$Image = $patrol->Image;

				if (rename($sourcePath.$Image, $targetFolder.$Image)) {
					echo "Moved: {$Image}<br>";
				} else {
					echo "FAILED: {$Image}<br>";
				}
			}

			$zipFile = $archiveRoot . $Tahun.$Bulan . '.zip';

			$zip = new ZipArchive();
			if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
				$filesToZip = glob($targetFolder . '*.*');

				foreach ($filesToZip as $f) {
					$zip->addFile($f, basename($f));
				}

				$zip->close();
				echo "ZIP berhasil dibuat<br>";
			} else {
				echo "Gagal membuat zip.";
				return;
			}

			foreach (glob($targetFolder . '*') as $f) {
				unlink($f);
			}
			rmdir($targetFolder);

			echo "Folder berhasil dihapus";
		}
	}

	
?>