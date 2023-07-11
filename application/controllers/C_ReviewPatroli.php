<?php 
	class C_ReviewPatroli extends CI_Controller {
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

			$TglAwal = $this->input->post('TglAwal');
			$TglAkhir = $this->input->post('TglAkhir');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
			$LocationID = $this->input->post('LocationID');
			
			$SQL = "
				SELECT 
					a.id,
					c.NamaArea				AS NamaLokasi,
					d.NamaSecurity,
					b.NamaCheckPoint,
					a.Koordinat,
					a.Image,
					a.Catatan,
					DATE_FORMAT(a.TanggalPatroli,'%d/%m/%Y %H:%i:%S') Tanggal
				FROM patroli a
				LEFT JOIN tcheckpoint b on a.KodeCheckPoint = b.KodeCheckPoint and a.RecordOwnerID = b.RecordOwnerID AND a.LocationID = b.LocationID
				LEFT JOIN tlokasipatroli c on a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
				LEFT JOIN tsecurity d on a.KodeKaryawan = d.NIK = a.RecordOwnerID = d.RecordOwnerID
				WHERE DATE(a.TanggalPatroli) BETWEEN '".$TglAwal."' AND '".$TglAkhir."'
				AND a.RecordOwnerID = '".$RecordOwnerID."'
			";

			if ($KodeKaryawan != "") {
				$SQL .= " AND a.KodeKaryawan = '".$KodeKaryawan."' ";
			}

			if ($LocationID != "") {
				$SQL .= " AND a.LocationID = '".$LocationID."' ";
			}

			$SQL.= " ORDER BY a.TanggalPatroli";

			$rs = $this->db->query($SQL);
			if ($rs) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			echo json_encode($data);
		}
	}
?>