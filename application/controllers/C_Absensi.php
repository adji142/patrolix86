<?php 
	class C_Absensi extends CI_Controller {
		private $table = 'absensi';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');

            date_default_timezone_set('Asia/Jakarta');
		}

        public function ReadNew()
        {
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $KodeLokasi = $this->input->post('KodeLokasi');
            $RecordOwnerID = $this->input->post('RecordOwnerID');
            $KodeKaryawan = $this->input->post('KodeKaryawan');
            $Tanggal = $this->input->post('Tanggal');


            $isGantiHari = 0;
            $KodeShift = -1;

            $datefrom = '';
            $dateTo = '';

            // $opreffAbs = $this->db->select("*")
            //                 ->from('absensi')
            //                 ->where("CheckOut",'0000-00-00 00:00:00.000000')
            //                 ->where("LocationID",$KodeLokasi)
            //                 ->where("RecordOwnerID",$RecordOwnerID)
            //                 ->where("KodeKaryawan",$KodeKaryawan)
            //                 ->order_By('Tanggal, CreatedOn', 'DESC')
            //                 ->limit(1)->get();

            // // var_dump($opreffAbs->result());
            // echo json_encode($opreffAbs->result());

            // if ($opreffAbs->num_rows() > 0) {
            //     $oAbsRow = $opreffAbs->row();

            //     $oShiftWhere = array(
            //         'RecordOwnerID' => $RecordOwnerID,
            //         'LocationID'    => $KodeLokasi,
            //         'id'            => $oAbsRow->Shift
            //     );
            
            //     $oShift = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
            

            //     // var_dump($oShiftWhere);
            //     $currentDate = new DateTime($Tanggal);
            //     foreach ($oShift as $key) {
            //         // var_dump($key);
            //         // echo json_encode($key);
            //         $paramDate = explode(" ", $Tanggal);
            //         // var_dump($paramDate);
            //         $datefrom = new DateTime($paramDate[0].' '.$key->MulaiBekerja);
            //         $dateTo = new DateTime($paramDate[0].' '.$key->SelesaiBekerja);

            //         if ($key->GantiHari == 1) {
            //             $datefrom->modify('-1 days');
            //         }
            //         $dateTo->modify('-30 minutes');

            //         if ($currentDate >= $datefrom && $currentDate <= $dateTo) {
            //             echo $key->NamaShift."<br>";
            //             $isGantiHari = $key->GantiHari;
            //             $KodeShift = $key->id;
            //             break;
            //         }
            //     }

            //     // if ($isGantiHari == 1) {
            //     //     $Tanggal = date('Y-m-d', strtotime($Tanggal . ' - 1 days'));
            //     // }

            //     $where = array(
            //         'RecordOwnerID'     => $RecordOwnerID,
            //         'LocationID'        => $KodeLokasi,
            //         'KodeKaryawan'      => $KodeKaryawan,
            //         'Tanggal'           => date('Y-m-d',strtotime($Tanggal)),
            //         'CheckOut'          => '0000-00-00 00:00:00.000000'
            //     );

            //     // var_dump($datefrom->format("Y-m-d H:i:s"));
            //     $sql = "SELECT a.* FROM absensi a ";
            //     $sql .= " LEFT JOIN tshift b on a.LocationID = b.LocationID and a.RecordOwnerID = b.RecordOwnerID and a.Shift = b.id ";
            //     $sql .= " WHERE a.RecordOwnerID = '".$RecordOwnerID."'";
            //     $sql .= " AND a.LocationID = ".$KodeLokasi;
            //     $sql .= " AND a.KodeKaryawan ='".$KodeKaryawan."'";
                
            //     $sql .= " AND ('".$Tanggal."' BETWEEN '".$datefrom->format("Y-m-d H:i:s")."' AND '".$dateTo->format("Y-m-d H:i:s")."' OR a.CheckOut = '0000-00-00 00:00:00.000000') ";
            //     $sql .= " AND a.id = ".$oAbsRow->id;
            //     // if ($isGantiHari == 1) {
            //     //      $sql .= " AND a.Tanggal = DATE_ADD('".date('Y-m-d',strtotime($Tanggal))."' ";
            //     // }
            //     //  else{
            //     //      $sql .= " and a.Tanggal = '".date('Y-m-d',strtotime($Tanggal))."'";
            //     // }
            //     $sql .= " ORDER BY CreatedOn DESC LIMIT 1 ";

            //     // var_dump($sql);
            //     $rs = $this->db->query($sql);

            //     // var_dump($where);

            //     // $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

            //     if($rs->num_rows() > 0){
            //         $data['success'] = true;
            //         $data['data'] = $rs->result();
            //     }
            // }

            $current_datetime = new DateTime($Tanggal);
            $current_time_only = $current_datetime->format('H:i:s');
            $midnight = new DateTime('23:59:59');

            $oShiftWhere = array(
                'RecordOwnerID' => $RecordOwnerID,
                'LocationID'    => $KodeLokasi
            );
            
            $oShift = [];
            $shifts = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();

            // echo json_encode($shifts);


            // end Validasi
            foreach ($shifts as $key) {
                $absenstart = $key->MulaiAbsen;
                $absenend = date("H:i:s", strtotime($key->MaxAbsen." +2 hours"));
                // var_dump($absenend);

                $shiftstart = $key->MulaiBekerja;
                $shiftend = $key->SelesaiBekerja;

                // $absenstart > $absenend

                // var_dump($current_time_only);
                // var_dump($midnight);
                if ($key->GantiHari == 1) {
                    if ($current_time_only ) {
                        # code...
                    }
                    if ($current_time_only >= $absenstart || $current_time_only <= $absenend) {
                        
                        $Shift = $key->id;
                    }
                } else {
                    if ($current_time_only >= $absenstart && $current_time_only <= $absenend) {
                        $Shift = $key->id;
                        // echo $current_time_only." > ".$absenstart." < ".$absenend."<br>";
                    }
                }
            }

            $oShiftWhere = array(
                'RecordOwnerID' => $RecordOwnerID,
                'LocationID'    => $KodeLokasi,
                'id'            => $Shift
            );

            // var_dump($oShiftWhere);
        
            $oShift = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
        

            // var_dump($oShiftWhere);
            $currentDate = new DateTime($Tanggal);
            foreach ($oShift as $key) {
                // var_dump($key);
                // echo json_encode($key);
                $paramDate = explode(" ", $Tanggal);
                // var_dump($paramDate);
                $datefrom = new DateTime($paramDate[0].' '.$key->MulaiBekerja);
                $dateTo = new DateTime($paramDate[0].' '.$key->SelesaiBekerja);



                if ($current_datetime > $midnight) {
                    $datefrom->modify('-1 days');
                }
                else{
                    $dateTo->modify('1 days');   
                }

                // if ($key->GantiHari == 1) {
                //     $datefrom->modify('-1 days');
                // }
                $dateTo->modify('-30 minutes');

                if ($currentDate >= $datefrom && $currentDate <= $dateTo) {
                    // echo $key->NamaShift."<br>";
                    $isGantiHari = $key->GantiHari;
                    $KodeShift = $key->id;
                    break;
                }
            }

            // if ($isGantiHari == 1) {
            //     $Tanggal = date('Y-m-d', strtotime($Tanggal . ' - 1 days'));
            // }

            $where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'LocationID'        => $KodeLokasi,
                'KodeKaryawan'      => $KodeKaryawan,
                'Tanggal'           => date('Y-m-d',strtotime($Tanggal)),
                'CheckOut'          => '0000-00-00 00:00:00.000000'
            );

            // var_dump($datefrom->format("Y-m-d H:i:s"));
            $sql = "SELECT a.* FROM absensi a ";
            $sql .= " LEFT JOIN tshift b on a.LocationID = b.LocationID and a.RecordOwnerID = b.RecordOwnerID and a.Shift = b.id ";
            $sql .= " WHERE a.RecordOwnerID = '".$RecordOwnerID."'";
            $sql .= " AND a.LocationID = ".$KodeLokasi;
            $sql .= " AND a.KodeKaryawan ='".$KodeKaryawan."'";
            
            // $sql .= " AND ('".$Tanggal."' BETWEEN '".$datefrom->format("Y-m-d H:i:s")."' AND '".$dateTo->format("Y-m-d H:i:s")."' OR a.CheckOut = '0000-00-00 00:00:00.000000') ";
            $sql .= " AND (a.Checkin BETWEEN '".$datefrom->format("Y-m-d H:i:s")."' AND '".$dateTo->format("Y-m-d H:i:s")."' OR a.CheckOut = '0000-00-00 00:00:00.000000') ";
            // $sql .= " AND a.Shift = ".$Shift;
            // $sql .= " AND a.CheckOut = '0000-00-00 00:00:00.000000' ";
            // if ($isGantiHari == 1) {
            //      $sql .= " AND a.Tanggal = DATE_ADD('".date('Y-m-d',strtotime($Tanggal))."' ";
            // }
            //  else{
            //      $sql .= " and a.Tanggal = '".date('Y-m-d',strtotime($Tanggal))."'";
            // }
            $sql .= " ORDER BY CreatedOn DESC LIMIT 1 ";

            // var_dump($sql);
            $rs = $this->db->query($sql);

            // var_dump($where);

            // $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }



            echo json_encode($data);
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
                'Tanggal'           => date_format(date_create($Tanggal),'Y-m-d')
            );
            $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

            // var_dump($rs->result());

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }
            else{
                // $data['success'] = true;
                // $data['message'] = "0 Records count";

                $where = array(
                    'RecordOwnerID'     => $RecordOwnerID,
                    'LocationID'        => $KodeLokasi,
                    'KodeKaryawan'      => $KodeKaryawan,
                    'Tanggal'           => date('Y-m-d', strtotime($Tanggal . ' - 1 days'))
                );

                $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

                // var_dump($rs->result());

                if ($rs->num_rows() > 0) {
                    $oParamShift = array(
                        'id' => $rs->row()->Shift,
                    );
                    // var_dump($oParamShift);
                    $oShift = $this->ModelsExecuteMaster->FindData($oParamShift, 'tshift');

                    if ($oShift->num_rows() >0) {
                        $xTanggalAwal = date('Y-m-d H:i:s', strtotime(date_format(date_create($Tanggal),'Y-m-d').' '.$oShift->row()->MulaiBekerja . ' - 1 days'));
                        $xTanggalAkhir = date_format(date_create(date_format(date_create($Tanggal),'Y-m-d').' '.$oShift->row()->SelesaiBekerja),'Y-m-d H:i:s');
                        // echo 'Mulai : '. $xTanggalAwal.'<br> Selesai : '.$xTanggalAkhir.'<br>'.$Tanggal.'<br>';

                        // $oShift->row()->GantiHari == "1"
                        // echo date("H:i", strtotime($oShift->row()->SelesaiBekerja));
                        // echo $oShift->row()->SelesaiBekerja;
                        // echo $oShift->row()->GantiHari;
                        if ($Tanggal >= $xTanggalAwal && $Tanggal <= $xTanggalAkhir) {
                            if ($oShift->row()->GantiHari == "1") {
                                $data['success'] = true;
                                $data['data'] = $rs->result();
                            }
                            else{
                                $data['success'] = true;
                                $data['message'] = "0 Records count";
                            }
                        }
                        else{
                            $data['success'] = true;
                            $data['message'] = "0 Records count";
                        }   
                    }
                    else{
                        $data['success'] = false;
                        $data['message'] = "Invalid Shift";
                    }
                }
                else{
                    $data['success'] = true;
                    $data['message'] = "0 Records count";
                }

                // Check Lompat Hari
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
                    DATE(a.Checkin) oriCheckinDate,
                    DATE_FORMAT(DATE(a.Checkin),'%d-%m-%Y') CheckinDate,
                    DATE_FORMAT(a.Checkin,'%T') CheckinTime,
                    a.KoordinatOUT,
                    a.ImageOUT,
                    a.CheckOut,
                    DATE(a.CheckOut) oriCheckoutDate,
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
                    // Get Shift

                    // $current_time = '2024-06-10 19:22:00';
                    $current_datetime = new DateTime($Checkin);
                    $current_time_only = $current_datetime->format('H:i:s');

                    $oShiftWhere = array(
                        'RecordOwnerID' => $RecordOwnerID,
                        'LocationID'    => $LocationID
                    );
                    
                    $oShift = [];
                    $shifts = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
                    
                    // Validasi
                    $oSQLValidation = "SELECT * FROM tshift where RecordOwnerID = '".$RecordOwnerID."' AND LocationID = ".$LocationID." AND '".$current_time_only."' BETWEEN MulaiAbsen and DATE_ADD(MulaiBekerja,INTERVAL 1 HOUR) ";

                    $oValidation = $this->db->query($oSQLValidation);

                    if ($oValidation->num_rows() == 0) {
                        $data['message'] = "Absensi Shift Belum dimulai";
                        goto jump;
                    }

                    // echo json_encode($oDataShift);


                    // end Validasi
                    foreach ($shifts as $key) {
                        $absenstart = $key->MulaiAbsen;
                        $absenend = $key->MaxAbsen;

                        $shiftstart = $key->MulaiBekerja;
                        $shiftend = $key->SelesaiBekerja;

                        // $absenstart > $absenend

                        if ($key->GantiHari == 1) {
                            if ($current_time_only >= $absenstart || $current_time_only <= $absenend) {
                                $Shift = $key->id;
                            }
                        } else {
                            if ($current_time_only >= $absenstart && $current_time_only <= $absenend) {
                                $Shift = $key->id;
                                
                            }
                        }
                    }


                    // echo $Shift;
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

        public function UpdateAbsensi()
        {
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $checkindate = $this->input->post('checkindate');
            $checkoutdata = $this->input->post('checkoutdata');
            $id = $this->input->post('id');

            $SQL = "UPDATE absensi SET Checkin = '".$checkindate."', CheckOut = '".$checkoutdata."' where id =".$id;

            $rs = $this->db->query($SQL);

            if($rs){
                $data['success'] = true;
            }
            else{
                $data['success'] = false;
                $data['message'] = "Sistem Gagal Memproses Data";
            }

            echo json_encode($data);
        }

        public function TestGetShift()
        {
            $RecordOwnerID = $this->input->post('RecordOwnerID');
            $LocationID = $this->input->post('LocationID');
            $Tanggal = $this->input->post('Tanggal');

            $oShiftWhere = array(
                'RecordOwnerID' => $RecordOwnerID,
                'LocationID'    => $LocationID
            );
            $shift = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();

            $currentDate = new DateTime($Tanggal.' '. date('H:i:s'));
            echo $currentDate->format('Y-m-d H:i:s')."<br>";
            foreach ($shift as $key) {
                // var_dump($key);
                $datefrom = new DateTime($Tanggal.' '.$key->MulaiBekerja);
                $dateTo = new DateTime($Tanggal.' '.$key->SelesaiBekerja);

                if ($key->GantiHari == 1) {
                    $dateTo->modify('1 days');
                }
                // $date->modify('-10 days');

                echo "Awal : ". $datefrom->format('Y-m-d H:i:s')." - Akhir : ".$dateTo->format('Y-m-d H:i:s')."<br>";

                if ($currentDate >= $datefrom && $currentDate <= $dateTo) {
                    echo $key->NamaShift."<br>";
                }
            }
        }

        public function TestRead()
        {
            $data = array('success' => false ,'message'=>array(),'data'=>array());

            $KodeLokasi = 58;
            $RecordOwnerID = 'CL0006';
            $KodeKaryawan = 'CL0006.TBS2022';
            $Tanggal = '2024-06-10 08:46:00';


            $isGantiHari = 0;
            $KodeShift = -1;

            $datefrom = '';
            $dateTo = '';

            $oShiftWhere = array(
                'RecordOwnerID' => $RecordOwnerID,
                'LocationID'    => $KodeLokasi
            );
	    
	    $sql = "select * from tshift where RecordOwnerID = '".$RecordOwnerID."' AND LocationID =".$KodeLokasi." Order By MulaiBekerja DESC ";
		$rs = $this->db->query($sql);
            $shift = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
		$shift = $rs->result();
		

            // var_dump($oShiftWhere);
            $currentDate = new DateTime($Tanggal);
            foreach ($shift as $key) {
                // var_dump($key);
                $paramDate = explode(" ", $Tanggal);
                // var_dump($paramDate);
                $datefrom = new DateTime($paramDate[0].' '.$key->MulaiBekerja);
                $dateTo = new DateTime($paramDate[0].' '.$key->SelesaiBekerja);

                if ($key->GantiHari == 1) {
                    $datefrom->modify('-1 days');
                }
                $dateTo->modify('-30 minutes');
		echo "CurentDate: ".$currentDate->format("Y-m-d H:i:s") ."<br>";
		    echo "Tgl Awal: ".$datefrom->format("Y-m-d H:i:s") ."<br>";
		    echo "Tgl Akhir: ".$dateTo->format("Y-m-d H:i:s") ."<br>";
                if ($currentDate >= $datefrom && $currentDate <= $dateTo) {
                    echo $key->NamaShift."<br>";
                    $isGantiHari = $key->GantiHari;
                    $KodeShift = $key->id;
                    break;
                }
            }

            // if ($isGantiHari == 1) {
            //     $Tanggal = date('Y-m-d', strtotime($Tanggal . ' - 1 days'));
            // }

            $where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'LocationID'        => $KodeLokasi,
                'KodeKaryawan'      => $KodeKaryawan,
                'Tanggal'           => date('Y-m-d',strtotime($Tanggal)),
                'CheckOut'          => '0000-00-00 00:00:00.000000'
            );

            // var_dump($datefrom->format("Y-m-d H:i:s"));
            $sql = "SELECT a.* FROM absensi a ";
            $sql .= " LEFT JOIN tshift b on a.LocationID = b.LocationID and a.RecordOwnerID = b.RecordOwnerID and a.Shift = b.id ";
            $sql .= " WHERE a.RecordOwnerID = '".$RecordOwnerID."'";
            $sql .= " AND a.LocationID = ".$KodeLokasi;
            $sql .= " AND a.KodeKaryawan ='".$KodeKaryawan."'";
            
            $sql .= " AND ('".$Tanggal."' BETWEEN '".$datefrom->format("Y-m-d H:i:s")."' AND '".$dateTo->format("Y-m-d H:i:s")."' OR a.CheckOut = '0000-00-00 00:00:00.000000') ";

 if ($isGantiHari == 1) {
                 $sql .= " AND a.Tanggal = CASE WHEN b.GantiHari = 1 THEN DATE_ADD('".date('Y-m-d',strtotime($Tanggal))."', INTERVAL -1 DAY) ELSE '".date('Y-m-d',strtotime($Tanggal))."' END ";
}
             else{
                 $sql .= "and a.Tanggal = '".date('Y-m-d',strtotime($Tanggal))."'";
}
            
            $sql .= " ORDER BY CreatedOn DESC LIMIT 1 ";

            var_dump($sql);
            $rs = $this->db->query($sql);

            // var_dump($where);

            // $rs = $this->ModelsExecuteMaster->FindData($where, 'absensi');

            if($rs->num_rows() > 0){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }

            echo json_encode($data);
        }

        public function is_current_shift($shift, $current_time_only){
            var_dump($shift);
            $shift_start = new DateTime($shift['MulaiBekerja']);
            $shift_end = new DateTime($shift['SelesaiBekerja']);

            if ($shift_start > $shift_end) {
                var_dump("lewat tenah malam");
                $midnight = new DateTime('00:00:00');
                $end_of_day = new DateTime('23:59:59');

                if (($current_time_only >= $shift['MulaiBekerja'] && $current_time_only <= $end_of_day->format('H:i:s')) ||
                    ($current_time_only >= $midnight->format('H:i:s') && $current_time_only <= $shift['SelesaiBekerja'])) {
                    return true;
                }
            }
            else{
                if ($current_time_only >= $shift['MulaiBekerja'] && $current_time_only <= $shift['SelesaiBekerja']) {
                    return true;
                }
            }
            return false;
        }

        function checkShift() {
            $overlaps = [];

            $current_time = '2024-06-10 19:22:00';
            $current_datetime = new DateTime($current_time);
            $current_time_only = $current_datetime->format('H:i:s');

            $oShiftWhere = array(
                'RecordOwnerID' => 'CL0006',
                'LocationID'    => 58
            );
            
            $oShift = [];
            $shifts = $this->ModelsExecuteMaster->FindData($oShiftWhere,'tshift')->result();
            
            foreach ($shifts as $key) {
                // $oshift[] = $key;
                $shift_start = $key->MulaiAbsen;
                $shift_end = $key->MaxAbsen;

                if ($current_time_only <= $shift_start) {
                    echo "Belum Mulai";
                    goto jump;
                }

                if ($shift_start > $shift_end) {
                    // Shift spans midnight
                    // var_dump("Masuk 1");
                    echo "Masuk 1 <br>";
                    if ($current_time_only >= $shift_start || $current_time_only <= $shift_end) {
                        echo json_encode($key);
                        // echo json_encode($key);
                    }
                } else {
                    echo "Masuk 2 <br>";
                    if ($current_time_only >= $shift_start && $current_time_only <= $shift_end) {
                        echo json_encode($key);
                    }
                }
            }

            jump:


            // echo json_encode($overlaps);
        }




    }
