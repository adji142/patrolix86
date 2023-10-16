<?php
    class C_SOS extends CI_Controller {
        private $table = 'sos';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			$this->load->library('Ciqrcode');
		}

        public function Read(){
            $data = array('success' => false ,'message'=>array(),'data'=>array(),'Penyelesaian'=> 0);
            $KodeLokasi = $this->input->post('KodeLokasi');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
            $id = $this->input->post('id');

            $where = array(
                'RecordOwnerID'     => $RecordOwnerID,
                'LocationID'        => $KodeLokasi,
                'id'                => $id
            );

            $rs = $this->ModelsExecuteMaster->FindData($where, 'sos');

            if($rs){
                $data['success'] = true;
                $data['data'] = $rs->result();
            }
            else{
                $data['success'] = false;
                $data['message'] = 'No Data Found';
            }

            echo json_encode($data);
        }
        public function Create()
        {
            $data = array('success' => false ,'message'=>array(),'data'=>array(), 'fcmNotif' => array());

            $id = $this->input->post('id');
            $RecordOwnerID = $this->input->post('RecordOwnerID');
            $LocationID = $this->input->post('LocationID');
            $KodeKaryawan = $this->input->post('KodeKaryawan');
            $Comment = $this->input->post('Comment');
            $Image1 = $this->input->post('Image1');
            $Image2 = $this->input->post('Image2');
            $Image3 = $this->input->post('Image3');
            $Koordinat = $this->input->post('Koordinat');
            $SubmitDate = $this->input->post('SubmitDate');
            $VoiceNote = $this->input->post('VoiceNote');
            $formMode = $this->input->post('formMode');

            $Image_Base64_1 = $this->input->post('Image_Base64_1');
            $Image_Base64_2 = $this->input->post('Image_Base64_2');
            $Image_Base64_3 = $this->input->post('Image_Base64_3');

            $Voice_Base64 = $this->input->post('Voice_Base64');

            $ImagebaseDir_1 = FCPATH.'Assets/images/SOS/'.$Image1;
            $ImagebaseDir_2 = FCPATH.'Assets/images/SOS/'.$Image2;
            $ImagebaseDir_3 = FCPATH.'Assets/images/SOS/'.$Image3;

            $VoicebaseDir = FCPATH.'Assets/voice/'.$VoiceNote;
            
            try {
                if ($Image_Base64_1 <> ""){
                    file_put_contents($ImagebaseDir_1,base64_decode($Image_Base64_1)); // Image
                }
                if($Image_Base64_2 <> ""){
                    file_put_contents($ImagebaseDir_2,base64_decode($Image_Base64_2)); // Image
                }
                if($Image_Base64_3 <> ""){
                    file_put_contents($ImagebaseDir_3,base64_decode($Image_Base64_3)); // Image
                }
                if($Voice_Base64 <> ""){
                    file_put_contents($VoicebaseDir,base64_decode($Voice_Base64)); // Voice
                }

                $dataKarywan = $this->ModelsExecuteMaster->FindData(array('NIK' => $KodeKaryawan), 'tsecurity');

                $param = array(
                    'id'            => $id,
                    'RecordOwnerID' => $RecordOwnerID,
                    'LocationID'    => $LocationID,
                    'KodeKaryawan'  => $KodeKaryawan,
                    'Comment'       => $Comment,
                    'Image1'        => $Image1,
                    'Image2'        => $Image2,
                    'Image3'        => $Image3,
                    'Koordinat'     => $Koordinat,
                    'SubmitDate'    => $SubmitDate,
                    'VoiceNote'     => $VoiceNote
                );

                $Message = "";
                $returnType = "";

                
                if($formMode == "add"){
                    $rs = $this->ModelsExecuteMaster->ExecInsert($param,$this->table);
                    $Message = $dataKarywan->row()->NamaSecurity." Sedang Dalam Kondisi Darurat. Detail Kondisi akan segera kami kirim";
                    $returnType = "notif";
                }
                else{
                    $rs = $this->ModelsExecuteMaster->ExecUpdate($param,array('id'=>$id),$this->table);
                    $Message = "Detail Kondisi " . $dataKarywan->row()->NamaSecurity. " Sudah Diketahui, Ketuk Untuk Melihat";
                    $returnType = "callback";
                }

                if ($rs) {
                    $data['success'] = true;
                    $data['message'] = "Data Berhasil Disimpan";
                }
                else{
                    $data['success'] = false;
                    $data['message'] = "Gagal Simpan SOS";
                }

                // echo 'topics/SOSTopic';
                $fcmNotif = array(
                    'to'            => '/topics/SOSTopic'.$LocationID,
                    'notification'  => array(
                        'title'     => 'SOS ! Keadaan Darurat ! SOS',
                        'body'      => $Message,
                    ),
                    'data'              => array(
                        'title'         => 'SOS ID',
                        'ID'            => $id,
                        'type'          => $returnType,
                        'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
                    )
                );

                $notif = $this->ModelsExecuteMaster->PushNotification($fcmNotif);
                $data['fcmNotif'] = $notif;
            } catch (Exception $e) {
                $data['success'] = false;
                $data['message'] = "Gagal Simpan CheckPoint : ".$e->getMessage();
            }

            echo json_encode($data);
        }
    }
?>