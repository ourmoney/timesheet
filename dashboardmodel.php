<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DashboardModel extends CI_Model {

	/**
	 * Dashboard model.
	 * This model serves Dashboard controller.
	 * Rgis model activated after logged in.
	 * 
	 */
	
	/**
	 * Function help for pagination purpose
	 *
	 * @return record count
	 */
	public function record_count($tablename,$id) {
		 if($id !=0 )
		   	 $query = $this->db->get_where($tablename,array('client_id' => $id, 'job_status' =>'1'));
		 else
		 	$query = $this->db->get_where($tablename,array('job_status'=>'1'));
    	 $counts=$query->num_rows();
		 
		 return   $counts;
         
    }
	/**
	 * AFTER FILEMAKER API ITS NOT IN USE.
	 *
	 * @return result (job list)
	 */
	public function getJobs($limit, $start,$filter)
	{
		$this->db->limit($limit, $start);
		
		if($filter != 0)
		{
		
		$query = $this->db->select('*',FALSE)
                      ->from('jobs')
                      ->join('clients', 'jobs.client_id = clients.client_id','left')
                      ->where(array('job_status'=> '1','clients.client_id'=> $filter))
					  ->order_by('client','ASC')  
					  ->order_by('jobs.job_no','DESC')  
                      ->get();
		}
		else
		{
			$query = $this->db->select('*',FALSE)
                      ->from('jobs')
                      ->join('clients', 'jobs.client_id = clients.client_id','left')
                      ->where(array('job_status'=> '1'))
					  ->order_by('client','ASC')  
					  ->order_by('jobs.job_no','DESC')  
                      ->get();
		}
		return $query->result();
	}
	/**
	 * AFTER FILEMAKER API ITS NOT IN USE.
	 *
	 * @return result (CLIENT list)
	 */
	public function getClients()
	{
		
		$query = $this->db->select('client_id,client',FALSE)
                      ->from('clients')
                      ->where('status', '1')
					  ->order_by('client','ASC')  
					  ->get();
					  
		return $query->result();
	}
	/**
	 * AFTER FILEMAKER API ITS NOT IN USE.
	 *
	 * @return result (job list)
	 */
	public function getJobList($client_id) // FOR AJAX 
	{
		$jobs='';
		$query = $this->db->select('job_no,job_title',FALSE)
                      ->from('jobs')
                      ->where('client_id', $client_id)
					  ->order_by('job_no','DESC')  
					  ->get();
		
		//return $query->result();
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			   	$jobs['0']="Job No Required";
				$jobs[$row->job_no]=$row->job_no." - ".$row->job_title;
		   }
		}
		else
			$jobs['0']="No open Jobs";
			
		return $jobs;
	}
	/**
	 * insert times in timesheet database
	 *
	 * @return success if inserted
	 */
	public function insertTime($data)
	{
		$user_id=$data['user_id'];
		$tdate=$data['tdate'];
		$client_id=$data['clients'];
		$job_no=$data['jobs'];
		$activity=$data['activity'];
		$additional_info=nl2br($data['info']);
		$hours=$data['hours'].".".$data['minutes'];
		$week_beginning=$data['week_beginning'];
		
	   $data = array(
		  'id' => '' ,
		  'user_id' => $user_id ,
		  'tdate' => $tdate ,
		  'client_id' => $client_id,
		  'job_no' => $job_no,
		  'activity' => $activity,
		  'additional_info' => $additional_info,
		  'hours' => $hours,
		  'week_beginning'=>$week_beginning
		);
		
		return $this->db->insert('timesheet', $data); 
	}
	/**
	 * Edit times in timesheet database
	 *
	 * @return success if edited
	 */
	public function editTime($data)
	{
		$id=$data['id'];
		$tdate=$data['tdate'];
		$client_id=$data['clients'];
		$job_no=$data['jobs'];
		$activity=$data['activity'];
		$additional_info=nl2br($data['info']);
		$hours=$data['hours'].".".$data['minutes'];
		//$week_beginning=$data['week_beginning'];
		
	   $data = array(
		  'tdate' => $tdate,
		  'client_id' => $client_id,
		  'job_no' => $job_no,
		  'activity' => $activity,
		  'additional_info' => $additional_info,
		  'hours' => $hours		  
		);
		$this->db->where('id',$id);
		return $this->db->update('timesheet', $data); 
	}
	/**
	 * delete times in timesheet database
	 *
	 * @return success if deleted
	 */
	public function deleteTime($id)
	{
		if($id)
		{
			$this->db->where('id',$id);
			return $this->db->delete('timesheet');
		}
	}
	/**
	 * This function is use for Admin level of access
	 *
	 * @return result
	 */
	public function getTimeSheet($user_id, $week_beginning)
	{
		//$jobs=$this->filemaker_helper->showJobsContainer();
		
		if($week_beginning == null)
			$week_beginning=date('Y-m-d',strtotime("monday this week"));
			
		
		$query=$this->db->select('id,tdate,client,timesheet.job_no,job_title,activity,additional_info,hours',FALSE)
					->from('timesheet')
					->join('clients', 'timesheet.client_id = clients.client_id','left')
					->join('jobs', 'timesheet.job_no = jobs.job_no','left')
					->where(array('user_id'=> $user_id,'week_beginning'=> $week_beginning))
					->order_by('id','DESC')
					->get();
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			   	$data['id'][]=$row->id;
			   	$data['tdate'][]=$row->tdate;		 		
				$data['client'][]=$row->client; 
				$data['job_no'][]=$row->job_no;
				$data['job_title'][]=$this->filemaker_helper->getJobTitle($row->job_no);//$this->filemaker_helper->showJobTitle($row->job_no); // FROM ARRAY
				$data['activity'][]=$row->activity;
				$data['additional_info'][]=$row->additional_info;
				$data['hours'][]=$row->hours;
		   }
		   return $data;
		}
					
	}
	/**
	 * get times for user
	 *
	 * @return user data
	 */
	public function getTime($id)
	{
		
		$query=$this->db->select('id,tdate,client_id,job_no,activity,additional_info,hours',FALSE)
					->from('timesheet')					
					->where(array('id'=> $id))					
					->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			
		  	$data['id']=$row->id;
			$data['tdate']=$row->tdate;		 		
			$data['client_id']=$row->client_id; 
			$data['job_no']=$row->job_no;
			$data['activity']=$row->activity;
			$data['additional_info']=$row->additional_info;
			
			$time=explode('.',$row->hours);
			$data['hours']=$time[0];
			$data['min']=$time[1];
			
		}
		
		return $data;
		
					
	}
	/**
	 * days for that week as weekley timesheet
	 *
	 * @return data
	 */
	public function getDays($user_id, $week_beginning)
	{
		//$jobs=$this->filemaker_helper->showJobsContainer();
		
		if($week_beginning == null)
			$week_beginning=date('Y-m-d',strtotime("monday this week"));

$query=$this->db->select('id,tdate,client,timesheet.job_no,job_title,activity,additional_info,hours,week_beginning,approved,uploaded',FALSE)
					->from('timesheet')
					->join('clients', 'timesheet.client_id = clients.client_id','left')
					->join('jobs', 'timesheet.job_no = jobs.job_no','left')
					->where(array('user_id'=> $user_id))
					->order_by('id','DESC')
					->get();
					if ($query->num_rows() > 0)
					{
					   foreach ($query->result() as $row)
					   {
						   	$data['week_beginning'][]=$row->week_beginning;
							$data['id'][]=$row->id;
							$data['tdate'][]=$row->tdate;		 		
							$data['client'][]=$row->client; 
							$data['job_no'][]=$row->job_no;
							$data['job_title'][]=$this->filemaker_helper->getJobTitle($row->job_no);//$this->filemaker_helper->showJobTitle($row->job_no); // FROM ARRAY
							$data['activity'][]=$row->activity;
							$data['additional_info'][]=$row->additional_info;
							$data['hours'][]=$row->hours;
							$data['aprroved'][]=$row->approved;
							$data['uploaded'][]=$row->uploaded;
					   }
					   return $data;
					}
										
	}
	/**
	 * get users for Admin dashboard left menu.
	 *
	 * @return list of user
	 */
	public function getUsers($user_id)
	{
		if($user_id == null)
		{
			$query=$this->db->select('id,username',FALSE)
					->from('users')
					->where(array('activated'=> '1'))
					->order_by('username','asc')
					->get();
			if ($query->num_rows() > 0)
			{
		   		return $query->result();
			}
		}
		else
		{
			$query=$this->db->select('users.id,username,user_job_type',FALSE)
					->from('users')
					->join('user_profiles', 'users.id = user_profiles.user_id','left')
					->where(array('activated'=> '1','users.id'=>$user_id))					
					->get();
			if ($query->num_rows() > 0)
			{
		   		return $query->result();
			}
		}		
	}
	
	/**
	 * For cron task
	 *
	 * @return user data
	 */
	public function getUsersEmail()
	{
		
			$query=$this->db->select('username,email',FALSE)
					->from('users')
					->where(array('activated'=> '1'))
					->order_by('username','asc')
					->get();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
		   		{
					$data['email'][]=$row->email;
					$data['username'][]=$row->username;
				}
				
		   		return $data;
			}
		
		
	}
	/**
	 * get archive timesheet for user.
	 *
	 * @return data
	 */
	public function getArchiveWeeks($user_id)
	{
		$wb=date('Y-m-d',strtotime("monday this week"));
		$query=$this->db->select('week_beginning',FALSE)
					->distinct()
					->from('timesheet')					
					->where(array('user_id'=> $user_id, 'week_beginning !=' => $wb))
					->order_by('week_beginning','DESC')
					->get();
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			   $week_beginning[]=$row->week_beginning;
		   }
		}
		else
			$week_beginning=null;
			
		return $week_beginning;
	}
	
	public function getTimeWeeks($user_id)
	{
		$wb=date('Y-m-d',strtotime("monday this week"));
		$query=$this->db->select('week_beginning',FALSE)
					->distinct()
					->from('timesheet')					
					->where(array('user_id'=> $user_id))
					->order_by('week_beginning','DESC')
					->get();
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			   $week_beginning[]=$row->week_beginning;
		   }
		}
		else
			$week_beginning=null;
			
		return $week_beginning;
	}
	/**
	 * admin level functionality - approve time for freelnacer
	 *
	 * @return result
	 */
	public function approveTime($id,$userid)
	{
		$aprrovedata = $this->getUsers($userid);
		$approvedname=$aprrovedata[0]->username;
		$firstcharacter=strtoupper($approvedname[0]);
		$data = array(
		  'approved' => $firstcharacter
		);
		
		$this->db->where('id',$id);
		return $this->db->update('timesheet', $data); 
	}
	/**
	 * admin level functionality - after updateing using filemaker
	 * status updated to 1
	 * @return result
	 */
	public function uploadTime($id)
	{	
	   $data = array(
		  'uploaded' => 1
		);
		
		$this->db->where('id',$id);
		
		return $this->db->update('timesheet', $data); 
	}
	
}

// END dashboard model

/* End of file dashboardmodel.php */
/* Location: ./application/models/dashboard.php */ 