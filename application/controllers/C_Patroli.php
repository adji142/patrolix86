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
					(TIMESTAMPDIFF(MINUTE, StartPatroli, EndPatroli) * COUNT(a.KodeCheckPoint)) / (c.IntervalPatroli * 60) JumlahRencanaPatroli,
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
				var_dump($xRS->num_rows());
				$data['success'] = true;
				$data['data'] = $rs->result();
				if ($xRS) {
					$data['Penyelesaian'] = Round(($xRS->row()->JumlahPatroliAktual / $xRS->row()->JumlahRencanaPatroli) * 100, 2);
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

			$baseDir = FCPATH.'Assets/images/patroli/'.$ImageName;

			try {
				file_put_contents($baseDir,base64_decode($Image));
					$oParam = array(
					'RecordOwnerID'	=> $RecordOwnerID,
					'LocationID'	=> $LocationID,
					'KodeCheckPoint'=> $KodeCheckPoint,
					'KodeKaryawan'	=> $KodeKaryawan,
					'TanggalPatroli'=> $TanggalPatroli
				);
				$max = $this->ModelsExecuteMaster->FindData($oParam, 'patroli');

				$Rank = $max->num_rows();

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
			echo json_encode($data);
		}
	}
?>