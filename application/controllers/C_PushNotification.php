<?php 
	class C_PushNotification extends CI_Controller {
		private $table = 'tcheckpoint';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			// $this->load->library('Ciqrcode');
		}
        
        

	}

?>