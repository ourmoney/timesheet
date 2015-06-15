<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Dashboard
 * 
 * @author    Ashish Tailor ashishgtailor@hotmail.com
 * Main Dashboard for user and 
 * provide different functions within application.
 * 
*/

class Dashboard extends CI_Controller {

	/**
	 * Default controller after logged in.
	 * This controller need tow library @tank_auth@, @Filemaker_helper@
	 */
	function __construct()
	{
		parent::__construct();		
		
		$this->load->model("dashboardmodel");
		$this->load->library('tank_auth');
		
	}
	/**
	 * Dashboard
	 * where u can add time, archive time etc.
	 * @shows dashboard view
	 */
	function index()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else 
		{
			$this->filemaker_helper->fillJobsContainer('Open');
			$user_id= $this->session->userdata('user_id');
			$nav=($this->session->userdata('isadmin') == 1) ? "admin_nav" : "nav";
			
			$data['title']="Dashboard";
			$data['page_header']="Dashboard <small>Timesheet Management</small>";
			$data['breadcrum']="Dashboard/timesheet";
			$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";
			$data['users']=$this->dashboardmodel->getUsers(null);
			//$data['clients']=$this->dashboardmodel->getClients();
			$data['clients']=$this->filemaker_helper->getClients();
			$data['timesheet']=$this->dashboardmodel->getTimeSheet($user_id,$week_beginning=null);
			
			$this->load->view('templates/header',$data);
			$this->load->view('templates/myfunctions');
			$this->load->view('templates/'.$nav);
			$this->load->view('pages/page_header', $data);
			$this->load->view('pages/dashboard', $data);
			$this->load->view('templates/footer');
		}
	}
	/**
	 * Dashboard - edit
	 * where u can edit time, archive time etc.
	 * @shows edit view
	 */
	function edit($id)
	{
		$user_id= $this->session->userdata('user_id');
		
		$data['title']="Dashboard - Edit time";
		$data['page_header']="Dashboard <small>Timesheet Management</small>";
		$data['breadcrum']="Dashboard/edittime";
		$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";
		$data['clients']=$this->dashboardmodel->getClients();
		$data['timesheet']=$this->dashboardmodel->getTimeSheet($user_id,$week_beginning=null);
		$data['time']=$this->dashboardmodel->getTime($id);
		$client=$this->dashboardmodel->getTime($id);
		$client_id=$client['client_id'];
		//$data['job_options']=$this->dashboardmodel->getJobList($client_id);
		$data['job_options']=$this->getJobList($client_id);
		
		$this->load->view('templates/header',$data);
		$this->load->view('templates/myfunctions');
		$this->load->view('templates/nav');
		$this->load->view('pages/page_header', $data);
		$this->load->view('pages/edittime', $data);
		$this->load->view('templates/footer');
		
	}
	/**
	 * Dashboard - archive timesheet
	 * 
	 * @shows archive view
	 */
	public function archive_timesheets()
	{
		$this->filemaker_helper->fillJobsContainer();
		$user_id= $this->session->userdata('user_id');
		
		$data['title']="Archived Timesheets";
		$data['page_header']="Dashboard <small>Timesheet Management</small>";
		$data['breadcrum']="Dashboard/archive_timesheets";
		$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";		
		$data['week_beginning']=$this->dashboardmodel->getArchiveWeeks($user_id);
		$data['days']=$this->dashboardmodel->getDays($user_id,$week_beginning=null);
		
		$this->load->view('templates/header',$data);
		$this->load->view('templates/myfunctions');
		$this->load->view('templates/nav');
		$this->load->view('pages/page_header', $data);
		$this->load->view('pages/archive_timesheets', $data);
		$this->load->view('templates/footer');
	}
	/**
	 * Dashboard - Admin level extra dashbord bar view
	 * admin can view users times and uploaded to Filemaker
	 * @shows admin dashboard view
	 */
	public function timesheets($user_id)
	{
		$this->filemaker_helper->fillJobsContainer();
		$user_id= ($user_id == null) ? $this->session->userdata('user_id') : $user_id ;
		
		$nav=($this->session->userdata('isadmin') == 1) ? "admin_nav" : "nav";
		
		$data['title']="Archived Timesheets";
		$data['page_header']="Dashboard <small>Timesheet Management</small>";
		$data['breadcrum']="Dashboard/archive_timesheets";
		$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";
		$data['username']=$this->dashboardmodel->getUsers($user_id);
		$data['users']=$this->dashboardmodel->getUsers(null);		
		$data['week_beginning']=$this->dashboardmodel->getTimeWeeks($user_id);
		$data['days']=$this->dashboardmodel->getDays($user_id,$week_beginning=null);
		
		$this->load->view('templates/header',$data);
		$this->load->view('templates/myfunctions');
		$this->load->view('templates/'.$nav);
		$this->load->view('pages/page_header', $data);
		$this->load->view('pages/timesheets', $data);
		$this->load->view('templates/footer');
	}
	/**
	 * Dashboard 
	 * Get jobs from Filemaker using Filemaker api
	 * Also use ajax to fill selcet box.
	 * @retrun to ajax and shoes joblist according to choosen client.
	 */
	public function joblist()
	{
		$this->filemaker_helper->fillJobsContainer('Open');
		$clientid=($this->input->post('client_id') == '0') ? NULL : $this->input->post('client_id');
		
		$data['title']="Jobs";
		$data['page_header']="Dashboard <small>Timesheet Management</small>";
		$data['breadcrum']="Dashboard/Jobs";
		$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";
		
		$config = array();
      	$config["base_url"] = site_url() . "/dashboard/joblist/";
      	//$config["total_rows"] = $this->dashboardmodel->record_count('jobs',$clientid);
		$config["total_rows"] = $this->filemaker_helper->record_count('web-jobs',$clientid);
		
      	$config["per_page"] = 100;
     	$config["uri_segment"] = 3;	
      
      	$this->pagination->initialize($config);
        
      	$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
      	//$data["jobs"] = $this->dashboardmodel->getJobs($config["per_page"], $page,$clientid);
		$data["jobs"] = $this->filemaker_helper->showJobList($config["per_page"], $page,$clientid);
		
      	$data["links"] = $this->pagination->create_links();
		//$data["clients"]=$this->dashboardmodel->getClients();  
		$data['clients']=$this->filemaker_helper->getClients();
	  
		$this->load->view('templates/header',$data);
		$this->load->view('templates/myfunctions');
		$this->load->view('templates/nav');
		$this->load->view('pages/page_header', $data);
		$this->load->view('pages/joblist', $data);
		$this->load->view('templates/footer');
	}
	/**
	 * Dashboard 
	 * Get jobs from Filemaker using Google Map api - US and UK calander	 
	 * @shows calander view for US and UK office.
	 */
	public function calendar()
	{
		$data['title']="Calendar";
		$data['page_header']="Dashboard <small>Calender</small>";
		$data['breadcrum']="Dashboard/Calender";
		$data['page_message']="<strong>Using Timesheet Admin System !!!</strong> Try to fill daily times on Timesheet please.";
		//$data['timesheet']=$this->dashboardmodel->getArchiveTimesheet($user_id);
		
		$this->load->view('templates/header',$data);
		$this->load->view('templates/myfunctions');
		$this->load->view('templates/nav');
		$this->load->view('pages/page_header', $data);
		$this->load->view('pages/calendar', $data);
		$this->load->view('templates/footer');
	}
	/**
	 * NOT USED AT THE MOMENT 
	 * 	 
	 * @using ajax filling job list from local data base.
	 */
	public function job_list()
	{
		$client_id=($this->input->post('clients')) ? $this->input->post('clients') : 0 ;
		$job_list=$this->showJob($client_id);
		//$job_list["job_list"]='1';
		
		$attributes = 'class="form-control"';
		echo form_dropdown('jobs', $job_list, "TRUE", $attributes);
		
	}
	/**
	 * NOT USED AT THE MOMENT 
	 * 	 
	 * @using ajax filling job list from local data base.
	 */
	public function getJobList($client_id)
	{
		$job_list=$this->showJob($client_id);
		//$job_list["job_list"]='1';
		
		//$attributes = 'class="form-control"';
		//echo form_dropdown('jobs', $job_list, "TRUE", $attributes);
		return $job_list;
		
	}
	public function addtime()
	{
		$data=$this->input->post();
		
		$datainserted=$this->dashboardmodel->insertTime($data);
		if($datainserted)
			redirect('dashboard', 'refresh');
		else
		{
			echo "Error";
		}
		
	}
	public function edittime()
	{
		$data=$this->input->post();
		
		$datainserted=$this->dashboardmodel->editTime($data);
		if($datainserted)
			redirect('dashboard', 'refresh');
		else
		{
			redirect('');		}
		
	}
	public function deletetime($id)
	{
		$updatetable=$this->dashboardmodel->deleteTime($id);
		if($updatetable)
			redirect('dashboard', 'refresh');
		else
		{
			redirect('');	
		}
		
	}
public function showL()
	{
		$layouts=$this->filemaker_helper->showLayout();
		echo"<pre>";
			print_r($layouts);
		echo "</pre>";
			
	}
	
	public function showJob($clientid)
	{
		
		$result=$this->filemaker_helper->showJobs($clientid);
		
		// Check for errors
		if (FileMaker::isError($result)) 
		{
			//die($result->getMessage());
			$jobs['0']="No open Jobs";
		}
		else
		{
		
		// Loop over the records 
			foreach ($result->getRecords() as $record) {
			   
			// Get the title of the record
			$JobNumber = $record->getField('JobNumber');
			$JobDescription = $record->getField('JobDescription');
			/*$JobStatus = $record->getField('JobStatus');
			$Client = $record->getField('Client');
			$URN = $record->getField('URN');*/
			//$ClientId = $record->getField('Addresses::URN');
			 
			// Show a link to the record
			//echo $JobNumber."-".$JobDescription."-".$JobStatus."-".$Client."-".$ClientId;
			//echo "<br>";
				$jobs['0']="Job No Required";
				$jobs[$JobNumber]=$JobNumber." - ".$JobDescription;
	 
			}
		}
		return $jobs;
			
	}
	/**
	 * Before upload to Filmaker for Freelancer account manager approval needs.
	 * Its approve time for Freelancer
	 * 
	 * @retrun result .
	 */
	public function approvetime()
	{
		$id=$_GET['id'];
		$userid=$_GET['userid'];
		
		$updatetable=$this->dashboardmodel->approveTime($id,$userid);
		
		if($updatetable)
			redirect('','refresh');
		else
		{
			//redirect('');	
			echo "Error";
		}
		
		
	}
	/**
	 * Comunicate with Filmaker and upload time to Filmaker server 
	 *	 
	 * @return result
	 */
	public function uploadtime()
	{
		$id=$_GET['id'];
		$userid=$_GET['userid'];
		
		$data=$this->dashboardmodel->getTime($id);
		$recorduploaded = $this->filemaker_helper->uploadTime($data,$userid);
		
		if($recorduploaded > 0)
		{
			$timeuploaded=$this->dashboardmodel->uploadTime($id);
		}
		
		if($timeuploaded)
			redirect('','refresh');
		else
		{
			//redirect('');	
			echo "Please delete record from filemaker and try again !!!";
		}
		//print $recordcount;
		//$this->load->view('test',$data);
	}
	public function test()
	{
		$clientid='44';
		$recordcount = $this->filemaker_helper->record_count('web-jobs',$clientid);
		
		echo $recordcount;
		//$this->load->view('test',$data);
	}
	
}
// END dashboard controller

/* End of file dashboard.php */
/* Location: ./application/controller/dashboard.php */ 