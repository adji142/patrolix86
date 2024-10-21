<?php 
	class C_GuestLog extends CI_Controller {
		private $table = 'guestlog';

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

			$where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'Tanggal >='        => $TglAwal,
                'Tanggal <='		=> $TglAkhir
            );

            if ($KodeLokasi != "") {
            	// 'LocationID'        => $KodeLokasi,
            	$where['LocationID'] = $KodeLokasi;
            }

            $rs = $this->ModelsExecuteMaster->FindDataWithOrder($where, 'guestlog', 'Tanggal', 'DESC');

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
                'id'            => $id
            );

            $rs = $this->ModelsExecuteMaster->FindData($where, 'guestlog');

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
			$TglMasuk = $this->input->post('TglMasuk');
			$TglKeluar = $this->input->post('TglKeluar');
			$ImageIn = $this->input->post('ImageIn');
			$ImageOut = $this->input->post('ImageOut');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$LocationID = $this->input->post('LocationID');
			$Keterangan = $this->input->post('Keterangan');
			$CreatedAt = date('Y-m-d H:i:s');
			$LastUpdatedAt = date('Y-m-d H:i:s');
			$NamaTamu = $this->input->post('NamaTamu');
			$NamaYangDicari = $this->input->post('NamaYangDicari');
			$Tujuan = $this->input->post('Tujuan');

			$formtype = $this->input->post('formtype');

			$oParam = array();
			$rs = array();

			if ($formtype == "add") {
				$getDate = str_replace(":", "", str_replace("-", "", $TglMasuk));
                // 2023-09-06 16:11:38.658465
                $ImageNameIN = "IN".$id."-".$RecordOwnerID.substr($getDate, 0,14).".png";
                $baseDir = FCPATH.'Assets/images/guestlog/'.$ImageNameIN;
                $ifp = fopen( $baseDir, 'wb' ); 
                fwrite($ifp, base64_decode($ImageIn));
                fclose($ifp);

                $ImageNameOUT = "";

                if ($ImageOut != "") {
                	$getDate = str_replace(":", "", str_replace("-", "", $TglKeluar));
	                $ImageNameOUT = "OUT".$id."-".$RecordOwnerID.substr($getDate, 0,14).".png";
	                $baseDir = FCPATH.'Assets/images/guestlog/'.$ImageNameOUT;

	                file_put_contents($baseDir,base64_decode($ImageOut));
                }

				$oParam = array(
					'Tanggal' => $Tanggal,
					'TglMasuk' => $TglMasuk,
					'ImageIn' => $ImageNameIN,
					'RecordOwnerID' => $RecordOwnerID,
					'LocationID' => $LocationID,
					'CreatedAt' => $CreatedAt,
					'TglKeluar' => $TglKeluar,
					'ImageOut' => $ImageNameOUT,
					'Keterangan' => $Keterangan,
					'LastUpdatedAt' => $LastUpdatedAt,
					'NamaTamu' => $NamaTamu,
					'NamaYangDicari' => $NamaYangDicari,
					'Tujuan' => $Tujuan
				);

				$rs = $this->ModelsExecuteMaster->ExecInsert($oParam, 'guestlog');

				if ($rs) {
					$data["success"] = true;
				}

			}
			elseif ($formtype == "edit") {
				$getDate = str_replace(":", "", str_replace("-", "", $TglMasuk));
                // 2023-09-06 16:11:38.658465
                $ImageNameIN = "IN".$id."-".$RecordOwnerID.substr($getDate, 0,14).".png";
                $baseDir = FCPATH.'Assets/images/guestlog/'.$ImageNameIN;
                $ifp = fopen( $baseDir, 'wb' ); 
                fwrite($ifp, base64_decode($ImageIn));
                fclose($ifp);

                $ImageNameOUT = "";

                if ($ImageOut != "") {
                	$getDate = str_replace(":", "", str_replace("-", "", $TglKeluar));
	                $ImageNameOUT = "OUT".$id."-".$RecordOwnerID.substr($getDate, 0,14).".png";
	                $baseDir = FCPATH.'Assets/images/guestlog/'.$ImageNameOUT;

	                file_put_contents($baseDir,base64_decode($ImageOut));
                }

				$oParam = array(
					'Tanggal' => $Tanggal,
					'TglMasuk' => $TglMasuk,
					'ImageIn' => $ImageNameIN,
					'RecordOwnerID' => $RecordOwnerID,
					'LocationID' => $LocationID,
					'CreatedAt' => $CreatedAt,
					'TglKeluar' => $TglKeluar,
					'ImageOut' => $ImageNameOUT,
					'Keterangan' => $Keterangan,
					'LastUpdatedAt' => $LastUpdatedAt,
					'NamaTamu' => $NamaTamu,
					'NamaYangDicari' => $NamaYangDicari,
					'Tujuan' => $Tujuan
				);

				$rs = $this->ModelsExecuteMaster->ExecUpdate($oParam, array('id'=>$id) , 'guestlog');
				if ($rs) {
					$data["success"] = true;
				}
			}
			else{
				$data['message'] = "Form Type Tidak Valid";
				goto jump;
			}


			jump:

			echo json_encode($data);
		}
	}
?>