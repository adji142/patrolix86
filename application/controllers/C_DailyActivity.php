<?php 
	class C_DailyActivity extends CI_Controller {
		private $table = 'dailyactivity';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
		}

		public function Read()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$TglAwal = $this->input->post('TglAwal');
			$TglAkhir = $this->input->post('TglAkhir');
			$KodeLokasi = $this->input->post('KodeLokasi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');

			$where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'CAST(Tanggal AS DATE) >='        => $TglAwal,
                'CAST(Tanggal AS DATE) <='		=> $TglAkhir
            );

            if ($KodeLokasi != "") {
            	$where['LocationID'] = $KodeLokasi;
            }

            if ($KodeKaryawan != "") {
            	$where['KodeKaryawan'] = $KodeKaryawan;
            }

            $rs = $this->ModelsExecuteMaster->FindData($where, $this->table);

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }else{
            	$data['message'] = "0 Record Count";
            }

            echo json_encode($data);
		}

		public function Find()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array());

			$id = $this->input->post('id');
			$KodeLokasi = $this->input->post('KodeLokasi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');

			$where = array(
                'RecordOwnerID' => $RecordOwnerID,
                'LocationID'    => $KodeLokasi,
                'id'            => $id
            );

            $rs = $this->ModelsExecuteMaster->FindData($where, $this->table);

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }else{
            	$data['message'] = "0 Record Count";
            }

            echo json_encode($data);
		}

		public function CRUD()
		{
			$data = array('success' => false ,'message'=>"",'data'=>array());

			$id = $this->input->post('id');
			$Tanggal = $this->input->post('Tanggal');
			$DeskripsiAktifitas = $this->input->post('DeskripsiAktifitas');
			$Gambar1 = $this->input->post('Gambar1');
			$Gambar2 = $this->input->post('Gambar2');
			$Gambar3 = $this->input->post('Gambar3');
			$CreatedOn = date('Y-m-d H:i:s');
			$UpdatedOn = date('Y-m-d H:i:s');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$LocationID = $this->input->post('LocationID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
			$formtype = $this->input->post('formtype');

			try {
				$getDate = str_replace(":", "", str_replace("-", "", $Tanggal));

				$ImageName1 = "";
				$ImageName2 = "";
				$ImageName3 = "";
                

                if ($Gambar1 != "") {
                	$ImageName1 = "IMG1".$RecordOwnerID.substr($getDate, 0,14).".png";
                	$baseDir1 = FCPATH.'Assets/images/activity/'.$ImageName1;
                	file_put_contents($baseDir1,base64_decode($Gambar1));
                }

                if ($Gambar2 != "") {
                	$ImageName2 = "IMG2".$RecordOwnerID.substr($getDate, 0,14).".png";
                	$baseDir2 = FCPATH.'Assets/images/activity/'.$ImageName2;
                	file_put_contents($baseDir2,base64_decode($Gambar2));
                }

                if ($Gambar3 != "") {
                	$ImageName3 = "IMG3".$RecordOwnerID.substr($getDate, 0,14).".png";
                	$baseDir3 = FCPATH.'Assets/images/activity/'.$ImageName3;
                	file_put_contents($baseDir3,base64_decode($Gambar3));
                }

                $oParam = array(
					'Tanggal' => $Tanggal,
					'DeskripsiAktifitas' => $DeskripsiAktifitas,
					'Gambar1' => $ImageName1,
					'Gambar2' => $ImageName2,
					'Gambar3' => $ImageName3,
					'RecordOwnerID' => $RecordOwnerID,
					'LocationID' => $LocationID,
					'KodeKaryawan' => $KodeKaryawan
				);

				// var_dump($oParam);

				$oKaryawan = $this->ModelsExecuteMaster->FindData(array('NIK'=>$KodeKaryawan, 'RecordOwnerID'=>$RecordOwnerID), 'tsecurity');

				if ($oKaryawan->num_rows() > 0) {
					$oParam['NamaKaryawan'] = $oKaryawan->row()->NamaSecurity;
				}

				if ($formtype == "add") {
					$oParam['CreatedOn'] = $CreatedOn;

					$rs = $this->ModelsExecuteMaster->ExecInsert($oParam, $this->table);

					if ($rs) {
						$data["success"] = true;
					}
				}
				elseif ($formtype == "edit") {
					$oParam['UpdatedOn'] = $UpdatedOn;

					$rs = $this->ModelsExecuteMaster->ExecUpdate($oParam, array('id'=>$id) , $this->table);
					if ($rs) {
						$data["success"] = true;
					}
				}

			} catch (Exception $e) {
				$data['success'] = false;
				$data['message'] = $e->getMessage();
			}

			echo json_encode($data);

		}

	}

?>