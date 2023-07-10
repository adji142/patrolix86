<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class home extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('ModelsExecuteMaster');
		$this->load->model('GlobalVar');
		// $this->load->model('deficeinfo');
		// require APPPATH.'libraries/phpmailer/src/Exception.php';
  //       require APPPATH.'libraries/phpmailer/src/PHPMailer.php';
  //       require APPPATH.'libraries/phpmailer/src/SMTP.php';
	}
	public function test()
	{
		echo $this->GlobalVar->systemInfo();
	}
	public function index()
	{
		$this->load->view('Dashboard');
	}
	public function generatePascode()
	{
		$data = array('success' => false ,'message'=>array(),'data'=>array());
		$passcode = $this->input->post('passcode');
		$validTo = $this->input->post('validTo');

		$nowDate = date("y-m-d h:i:s");
		$validDate = date("y-m-d h:i:s",strtotime($nowDate. ' + '.$validTo.' days'));
		$oParam = array(
			'Passcode'	=> $this->encryption->encrypt($passcode),
			'ValidTo'	=> $validDate,
			'Status'	=> 1
		);

		$rs = $this->ModelsExecuteMaster->ExecInsert($oParam,'pascode');

		if ($rs) {
			$data['success'] = true;
			$data['message'] = 'This Passcode Valid Until : '.$validDate;
		}
		else{
			$data['success'] = false;
			$data['message'] = 'failed to create passcode';	
		}

		echo json_encode($data);
	}
	public function verifikasiPascode()
	{
		$data = array('success' => false ,'message'=>'','data'=>array());
		$passcode = $this->input->post('passcode');

		$nowDate = date("y-m-d h:i:s");

		$SQL = "SELECT * FROM pascode WHERE ValidTo > '".$nowDate."' AND Status = 1 ";

		$rs = $this->db->query($SQL);
		// var_dump($rs->result());
		$message = '';
		if ($rs->num_rows() > 0) {
			foreach ($rs->result() as $key) {
				if ($this->encryption->decrypt($key->Passcode) == $passcode) {
					$data['success'] = true;
					$message = 'Passcode Valid';

					$this->ModelsExecuteMaster->ExecUpdate(array('Status'=>'0'),array('id'=>$key->id),'pascode');

					// Set Session
					$sess_data['passcode'] = $key->id;
					$this->session->set_userdata($sess_data);
					goto jump;
				}
			}
		}
		else{
			$data['success'] = false;
			$data['message'] = 'Pascode Expired';
		}

		if ($message == '') {
			$data['success'] = false;
			$message = 'Invalid Passcode';
			// var_dump($message.' - - - ');
		}

		jump:
		$data['message'] = $message;

		echo json_encode($data);
	}
	public function register()
	{
		$this->load->view('passcode');
	}
	public function complateregister()
	{
		$this->load->view('applyDocument');
	}
	// --------------------------------------- Master ----------------------------------------------------
	public function permission()
	{
		$this->load->view('V_Auth/permission');
	}
	public function role()
	{
		$this->load->view('V_Auth/roles');
	}
	public function permissionrole($value)
	{
		$rs = $this->ModelsExecuteMaster->FindData(array('id'=>$value),'roles');
		$data['roleid'] = $value;
		$data['rolename'] = $rs->row()->rolename;
		$this->load->view('V_Auth/permissionrole',$data);	
	}
	public function user()
	{
		$this->load->view('V_Auth/users');
	}
	public function login()
	{
		$this->load->view('Index');
	}
	public function changePass()
	{
		$this->load->view('changepassword');
	}

	// Master
	public function lokasipatroli()
	{
		$this->load->view('V_Master/LokasiPatroli');
	}
	public function checkpoint()
	{
		$this->load->view('V_Master/CheckPoint');
	}
	public function security()
	{
		$this->load->view('V_Master/Security');
	}
}