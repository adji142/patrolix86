<?php 
	class C_Patroli extends CI_Controller {
		private $table = 'tcheckpoint';

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
				LEFT JOIN patroli b on a.KodeCheckPoint = b.KodeCheckPoint AND a.LocationID = b.LocationID AND a.RecordOwnerID = b.RecordOwnerID AND DATE(b.TanggalPatroli) = DATE('".$TanggalPatroli."') AND b.KodeKaryawan = '".$KodeKaryawan."'
				LEFT JOIN tlokasipatroli c on a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
				where a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.LocationID = '".$KodeLokasi."'
			";

			if ($KodeCheckPoint != "") {
				$SQL .= " AND a.KodeCheckPoint = '".$KodeCheckPoint."' ";
			}

			$SQL.= " GROUP BY a.KodeCheckPoint, a.NamaCheckPoint,a.Keterangan, c.NamaArea ";

			$rs = $this->db->query($SQL);

			$SQL2 = "
				SELECT 
					a.LocationID,
					(TIMESTAMPDIFF(MINUTE, StartPatroli, EndPatroli) * COUNT(a.KodeCheckPoint)) / (C.IntervalPatroli * 60) JumlahRencanaPatroli,
					COUNT(b.id) JumlahPatroliAktual
				FROM tcheckpoint a
				LEFT JOIN patroli b on a.KodeCheckPoint = b.KodeCheckPoint AND a.LocationID = b.LocationID AND a.RecordOwnerID = b.RecordOwnerID AND DATE(b.TanggalPatroli) = DATE('".$TanggalPatroli."') AND b.KodeKaryawan = '".$KodeKaryawan."'
				LEFT JOIN tlokasipatroli c on a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
				where a.RecordOwnerID = '".$RecordOwnerID."'
				AND a.LocationID = '".$KodeLokasi."'
				GROUP BY a.LocationID
			";

			$xRS = $this->db->query($SQL2);
			if ($rs) {
				$data['success'] = true;
				$data['data'] = $rs->result();
				$data['Penyelesaian'] = Round(($xRS->row()->JumlahPatroliAktual / $xRS->row()->JumlahRencanaPatroli) * 100, 2);
			}
			echo json_encode($data);
		}
		public function CRUD()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$KodeCheckPoint = $this->input->post('KodeCheckPoint');
			$NamaCheckPoint = $this->input->post('NamaCheckPoint');
			$Keterangan = $this->input->post('Keterangan');
			$LocationID = $this->input->post('LocationID');
			$RecordOwnerID = $this->input->post('RecordOwnerID');

			$formtype = $this->input->post('formtype');

			$param = array(
				'KodeCheckPoint' => $KodeCheckPoint,
				'NamaCheckPoint' => $NamaCheckPoint,
				'Keterangan' => $Keterangan,
				'LocationID' => $LocationID,
				'RecordOwnerID' => $RecordOwnerID,
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
				$rs = $this->ModelsExecuteMaster->ExecUpdate($param,array('KodeCheckPoint'=>$KodeCheckPoint,'RecordOwnerID'=>$RecordOwnerID),$this->table);
				if ($rs) {
					$data['success'] = true;
					$data['message'] = "Data Berhasil Disimpan";
				}
				else{
					$data['message'] = "Gagal Edit data Jenis Tagihan";
				}
			}
			elseif ($formtype == 'delete') {
				$rs = $this->ModelsExecuteMaster->DeleteData(array('KodeCheckPoint'=>$KodeCheckPoint,'RecordOwnerID'=>$RecordOwnerID),$this->table);
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
			echo json_encode($data);
		}
		public function generateQRCode()
		{
			$this->load->library('zip');
			$data = array('success' => false ,'message'=>array(),'data'=>array(), 'DownloadLink'=>'');

			$RecordOwnerID = $this->session->userdata('RecordOwnerID');
			$DateCreateion = date("Ymd h:i:s");

			// var_dump($DateCreateion);

			try {
				$rs = $this->ModelsExecuteMaster->FindData(array('RecordOwnerID'=>$RecordOwnerID),$this->table)->result();

				$baseDir = FCPATH.'Assets/images/QRCode/';

				if (!is_dir($baseDir.$RecordOwnerID)) {
				    mkdir($baseDir.$RecordOwnerID.'/', 0777, TRUE);
				}

				delete_files($baseDir.$RecordOwnerID.'/');
				// var_dump($RecordOwnerID);
				foreach ($rs as $key) {
					$fileName = $baseDir.$RecordOwnerID.'/'.$key->KodeCheckPoint.'-'.$key->NamaCheckPoint.'.png';
					$params['data'] = $key->KodeCheckPoint;
					$params['level'] = 'H';
					$params['size'] = 10;
					$params['savename'] = $fileName;
					$this->ciqrcode->generate($params);
					// var_dump($key->RecordOwnerID);
					$this->zip->read_file($fileName);

				}

				// Append to zip
				$filename = $RecordOwnerID.'.zip';
				// var_dump($baseDir.$filename);
				// $this->zip->read_dir($baseDir.$RecordOwnerID);
				$rs = $this->zip->archive($baseDir.'/'.$RecordOwnerID.'/'.$filename);
				// var_dump($rs);
				// $this->zip->download($baseDir.'/'.$RecordOwnerID.'/'.$filename);	
				if ($rs) {
					$data['success'] = true;
					$data['DownloadLink'] = base_url().'Assets/images/QRCode/'.$RecordOwnerID.'/'.$RecordOwnerID.'.zip';
				}
			} catch (Exception $e) {
				$data['success'] =false;
				$data['message'] = $e->getMessage();
			}

			echo json_encode($data);
		}
	}
?>