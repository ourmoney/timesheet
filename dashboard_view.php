                <!-- Page Heading -->
                
                <!-- /.row -->
                <!-- Timesheet enter Form -->
				<div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Time Sheet</h3>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <form role="form" id="frmTimeSheet" action="<? echo site_url() ?>/dashboard/addtime" method="post" >
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="10%">Date <? echo $this->session->userdata('isadmin'); ?></th>
                                                    <th width="20%">Client</th>
                                                    <th width="10%">Job Number</th>
                                                    <th width="10%">Activity</th>
                                                    <th width="20%">Additional Info</th>
                                                    <th width="10%">Hours</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <? 
													   $firstdayofweek=strtotime("monday this week");
													   $today=date('l jS',time()); 
													   $dbdate=date('Y-m-d');	
													   $weekstartdatedb=date('Y-m-d',$firstdayofweek);
													   //$date = date("Y-m-d",strtotime('monday this week')).'to'.date("Y-m-d",strtotime("friday this week")); 
													   for($i=0;$i < 5; $i++)
													   {
														   $day=date('l jS',$firstdayofweek);
													   	   $daydb=date('Y-m-d',$firstdayofweek);
														   $weekdays[$daydb]=$day;
														   $firstdayofweek=$firstdayofweek + 86400;
													   }
													   $slected_day=$dbdate;
													   
													?>
                                                    <td>
                                                    <input type="hidden" name="user_id" value="<? echo $this->session->userdata('user_id'); ?>" />
                                                    <?
                                                    echo form_dropdown('tdate', $weekdays,$slected_day,'class="form-control"');
													?>                                                    
                                                    <input type="hidden" name="week_beginning" value="<? echo $weekstartdatedb; ?>" />
                                                    </td>
                                                    <td>
                                                    <?
														$client_options[0]="Choose Client";
														/*foreach($clients as $client)
														{
															$client_options[$client->client_id]=$client->client;
														}*/
														foreach($clients->getRecords() as $client)
														{
															$client_options[$client->getField('Addresses::URN')]=$client->getField('Client');
														}
														//$client_options=array('small'  => 'Samsung','med'    => 'Apple','large'   => 'HTC','xlarge' => 'Nokia');
														
														//$attributes=array('onChange' => 'get_job_no()', 'class' =>'form-control');
														$attributes = 'onChange="get_job_no()" class="form-control"';
														//$css='class="form-control"';
														echo form_dropdown('clients', $client_options, "TRUE",$attributes);
													?>                                                    	
                                                    </td>
                                                    <td>
                                                    	<?
														/*foreach($job_list as $job)
														{
															$job_options[$job->job_no]=$job->job_no."-".$job->job_title;
														}*/
														$job_options['0']='none';
														$attributes = 'class="form-control"';
														echo "<span id='jobs'>". form_dropdown('jobs', $job_options, "TRUE",$attributes)."</span>"; 
														?>
                                                    </td>
                                                    <td>
                                                    	<select class="form-control" name="activity">
                                                            <option value="Admin">Admin</option>
                                                            <option value="Creative">Creative</option>
                                                            <option value="Re-touching">Re-touching</option>
                                                            <option value="Other"> Other</option>
                                                    	</select>
                                                     </td>
                                                    <td>
                                                    <textarea class="form-control" rows="3" placeholder="Enter Additional Information" name="info"></textarea> 
                                                   
                                                    </td>
                                                    <td>
                                                    	<div class="form-group">
                                                        <!-- 				<div class="form-group form-group-sm has-error has-feedback"> -->
                                                    	<? /*<span class="input-group-addon">hh:mm</span> */ ?>
                                                     	<select class="form-control" style="width:50%; float:left" name="hours" id="hours">
                                                        <? 
															for($i= 0; $i <16; $i++)
															{
																echo "<option value='$i'>$i</option>";
															}
														?>
                                                                                                           
                                                    	</select>
                                                                                                                
                                                        <select class="form-control" style="width:50%"  name="minutes" id="minutes">
                                                               <option value='00'>00</option>
                                                               <option value='25'>25</option>
                                                               <option value='50'>50</option>
                                                               <option value='75'>75</option>
                                                                                                                                                                   
                                                    	</select>
                                                    	<!-- <span class="glyphicon glyphicon-remove form-control-feedback"></span>  -->
                                                    	</div> 
                                                    </td>                                                
                                                </tr>                               
                                            </tbody>
                                        </table>
                                        <button type="submit" class="btn btn-default pull-right">Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <!-- Timesheet entered view -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-list fa-fw"></i> Time Sheet</h3>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                            	<th width="5%">Edit / Del</th>
                                                <th width="13%">Date</th>
                                                <th width="20%">Client</th>
                                                <th width="25%">Job Number</th>
                                                <th width="10%">Activity</th>
                                                <th width="17%">Additional Information</th>
                                                <th width="10%">Hours</th>                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?
											$edit_attributes=array(
                    							
                    							'title'=>'Edit');
											
											$del_attributes=array(
                    							'onclick'=>"return confirm('Are you sure?');",
                    							
                    							'title'=>'Delete');
											
											if(sizeof($timesheet['id']) > 0)
											{
												$totlatime=sizeof($timesheet['id']);
												$i=0;
												while($i < $totlatime)
												{
												echo "<tr>";
												?>
                                                	<td><? 
														echo anchor('dashboard/edit/'.$timesheet['id'][$i],'<i class="fa fa-pencil fa-fw"></i>',$edit_attributes);
														echo "<span class='right_margin'></span>";
														echo anchor('dashboard/deletetime/'.$timesheet['id'][$i],'<i class="fa fa-trash-o fa-fw"></i>',$del_attributes);
													?></td>
													<td><? echo displayShortDate($timesheet['tdate'][$i]); ?></td>
													<td><? echo $timesheet['client'][$i]; ?></td>
													<td><? echo $timesheet['job_no'][$i]." - ".$timesheet['job_title'][$i]; ?></td>
													<td><? echo $timesheet['activity'][$i]; ?></td>
													<td><? echo $timesheet['additional_info'][$i]; ?></td>
													<td class="righttext"><? echo $timesheet['hours'][$i]; ?></td> 
												<? 
												$i++;
												echo "</tr>";
												}
											}
										?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->