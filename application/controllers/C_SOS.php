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

        public function Create()
        {
            $data = array('success' => false ,'message'=>array(),'data'=>array());

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

            $Image_Base64_1 = $this->input->post('Image_Base64_1');
            $Image_Base64_2 = $this->input->post('Image_Base64_2');
            $Image_Base64_3 = $this->input->post('Image_Base64_3');

            $Voice_Base64 = $this->input->post('Voice_Base64');

            $ImagebaseDir_1 = FCPATH.'Assets/images/SOS/'.$Image1;
            $ImagebaseDir_2 = FCPATH.'Assets/images/SOS/'.$Image2;
            $ImagebaseDir_3 = FCPATH.'Assets/images/SOS/'.$Image3;

            $VoicebaseDir = FCPATH.'Assets/voice/'.$VoiceNote;
            
            try {
                file_put_contents($ImagebaseDir_1,base64_decode($Image_Base64_1)); // Image
                file_put_contents($ImagebaseDir_2,base64_decode($Image_Base64_2)); // Image
                file_put_contents($ImagebaseDir_3,base64_decode($Image_Base64_3)); // Image
                file_put_contents($VoicebaseDir,base64_decode($Voice_Base64)); // Voice

                $param = array(
                    'RecordOwnerID' => $RecordOwnerID,
                    'LocationID' => $LocationID,
                    'KodeKaryawan' => $KodeKaryawan,
                    'Comment' => $Comment,
                    'Image' => $Image,
                    'Koordinat' => $Koordinat,
                    'SubmitDate' => $SubmitDate,
                    'VoiceNote' => $VoiceNote
                );

                $rs = $this->ModelsExecuteMaster->ExecInsert($param,$this->table);
                if ($rs) {
                    $data['success'] = true;
                    $data['message'] = "Data Berhasil Disimpan";
                }
                else{
                    $data['success'] = false;
                    $data['message'] = "Gagal Simpan SOS";
                }
            } catch (Exception $e) {
                $data['success'] = false;
                $data['message'] = "Gagal Simpan CheckPoint : ".$e->getMessage();
            }

            echo json_encode($data);
        }
    }
?>