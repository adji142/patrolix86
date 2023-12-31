<?php 
	class C_Absensi extends CI_Controller {
		private $table = 'absensi';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
		}

        public function Read(){
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $KodeLokasi = $this->input->post('KodeLokasi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
            $Tanggal = $this->input->post('Tanggal');

            $where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'LocationID'        => $KodeLokasi,
                'KodeKaryawan'      => $KodeKaryawan,
                'Tanggal'           => $Tanggal
            );
            $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }
            else{
                $data['success'] = true;
                $data['message'] = "0 Records count";
            }

            echo json_encode($data);
        }

        public function ReadReport(){
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $id = $this->input->post('id');
            $KodeLokasi = $this->input->post('LocationID');
            $RecordOwnerID = $this->input->post('RecordOwnerID');
            $KodeKaryawan = $this->input->post('KodeKaryawan');
            $TglAwal = $this->input->post('TglAwal');
            $TglAkhir = $this->input->post('TglAkhir');

            $SQL = "
                SELECT 
                    a.id,
                    a.RecordOwnerID,
                    b.NamaPartner,
                    a.LocationID,
                    c.NamaArea,
                    a.KodeKaryawan,
                    d.NamaSecurity,
                    a.KoordinatIN,
                    a.ImageIN,
                    a.Checkin,
                    DATE_FORMAT(DATE(a.Checkin),'%d-%m-%Y') CheckinDate,
                    DATE_FORMAT(a.Checkin,'%T') CheckinTime,
                    a.KoordinatOUT,
                    a.ImageOUT,
                    a.CheckOut,
                    DATE_FORMAT(DATE(a.CheckOut),'%d-%m-%Y') CheckoutDate,
                    DATE_FORMAT(a.CheckOut,'%T') CheckoutTime,
                    a.Tanggal,
                    e.NamaShift
                FROM absensi a
                LEFT JOIN tcompany b on a.RecordOwnerID = b.KodePartner
                LEFT JOIN tlokasipatroli c on a.LocationID = c.id
                LEFT JOIN tsecurity d on a.KodeKaryawan = d.NIK
                LEFT JOIN tshift e on a.Shift = e.id AND a.LocationID = e.LocationID and a.RecordOwnerID = e.RecordOwnerID
                WHERE a.Tanggal BETWEEN '".$TglAwal."' AND '".$TglAkhir."'
                AND a.RecordOwnerID = '". $RecordOwnerID ."'
            ";
            if ($KodeLokasi != '') {
                $SQL .= "AND a.LocationID = '". $KodeLokasi ."'";
            }

            if ($KodeKaryawan != '') {
                $SQL .= "AND a.KodeKaryawan = '". $KodeKaryawan ."'";
            }

            if ($id != "") {
                $SQL .= "AND a.id = '". $id ."'";
            }

            $rs = $this->db->query($SQL);

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }
            else{
                $data['success'] = true;
                $data['message'] = "0 Records count";
            }

            echo json_encode($data);
        }

        public function CRUD(){
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $id = $this->input->post('id');
            $RecordOwnerID = $this->input->post('RecordOwnerID');
            $LocationID = $this->input->post('LocationID');
            $KodeKaryawan = $this->input->post('KodeKaryawan');
            $KoordinatIN = $this->input->post('KoordinatIN');
            $ImageIN = $this->input->post('ImageIN');
            // $ImageNameIN = $this->input->post('ImageNameIN');
            $KoordinatOUT = $this->input->post('KoordinatOUT');
            $ImageOUT = $this->input->post('ImageOUT');
            // $ImageNameOUT = $this->input->post('ImageNameOUT');
            $Tanggal = $this->input->post('Tanggal');
            $Shift = $this->input->post('Shift');
            $Checkin = $this->input->post('Checkin');
            $CheckOut = $this->input->post('CheckOut');
            $CreatedOn = $this->input->post('CreatedOn');
            $UpdatedOn = $this->input->post('UpdatedOn');
            $formMode = $this->input->post('formMode');

            $inputData = array();

            $rs = false;

            $baseDir = "";

            try {
                if($formMode == "in"){
                    $getDate = str_replace(":", "", str_replace("-", "", $Checkin));
                    // 2023-09-06 16:11:38.658465
                    $ImageNameIN = "IN".$KodeKaryawan."-".$RecordOwnerID.substr($getDate, 0,14).".png";
                    $baseDir = FCPATH.'Assets/images/Absensi/'.$ImageNameIN;
                    $ifp = fopen( $baseDir, 'wb' ); 
                    fwrite($ifp, base64_decode($ImageIN));
                    fclose($ifp);
                    // file_put_contents($baseDir,$ImageIN);

                    $inputData = array(
                        'RecordOwnerID'     => $RecordOwnerID,
                        'LocationID'        => $LocationID,
                        'KodeKaryawan'      => $KodeKaryawan,
                        'KoordinatIN'       => $KoordinatIN,
                        'ImageIN'           => $ImageNameIN,
                        'KoordinatOUT'      => '',
                        'ImageOUT'          => '',
                        'Tanggal'           => $Tanggal,
                        'Shift'             => $Shift,
                        'Checkin'           => $Checkin,
                        'CheckOut'          => '00:00:00',
                        'CreatedOn'         => $CreatedOn,
                    );
    
                    $rs = $this->ModelsExecuteMaster->ExecInsert($inputData,'absensi');
                }
                elseif ($formMode == "out") {
                    $getDate = str_replace(":", "", str_replace("-", "", $CheckOut));
                    $ImageNameOUT = "OUT".$KodeKaryawan."-".$RecordOwnerID.substr($getDate, 0,14).".png";
                    $baseDir = FCPATH.'Assets/images/Absensi/'.$ImageNameOUT;

                    file_put_contents($baseDir,base64_decode($ImageOUT));

                    $inputData = array(
                        'KoordinatOUT'      => $KoordinatOUT,
                        'ImageOUT'          => $ImageNameOUT,
                        'CheckOut'          => $CheckOut,
                        'UpdatedOn'         => $UpdatedOn,
                    );
    
                    $rs = $this->ModelsExecuteMaster->ExecUpdate($inputData,array('id'=>$id),'absensi');
                }
                else{
                    $data['success'] = false;
                    $data['message'] = "Invalid Form Mode";
                    goto jump;
                }
            } catch (Exception $e) {
                $data['success'] = false;
				$data['message'] = "Gagal Simpan CheckPoint : ".$e->getMessage();
                goto jump;
            }

            if($rs){
                $data['success'] = true;
            }
            else{
                $data['success'] = false;
                $data['message'] = "Sistem Gagal Memproses Data";
            }


            jump:

            echo json_encode($data);
        }



    }