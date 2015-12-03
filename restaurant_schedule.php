<?php
/**
* 
*/
class restaurant_schedule extends REST
{
	private $mysqli ='';
	private $obj='';
	
	function __construct()
	{
		$this->obj = new DbConnect();
		$this->mysqli = $this->obj->db_connect();
	}	

	public function r_scheduler()
	{
			$shift = $_REQUEST['shift'];
			switch ($shift) {
			case 1:					
			self::TimeProcess1();
			break;						
			case 2:
				self::TimeProcess2();
			break;
							}
	}


			private function TimeProcess1()
			{
				
				date_default_timezone_set('Europe/London');
				$json_arr = array();
				$str="";
				$opening1 = new DateTime($_REQUEST['opening']);
				$closing1 = new DateTime($_REQUEST['closing']);
				$current_time = time();	
				$crr_time = new DateTime();
				$policy_time =$_REQUEST['policy_time'];

				if ($crr_time>=$opening1 AND $crr_time<=$closing1) 
				{
					if (strtolower($_REQUEST["order_option"])=="delivery")
					 {
					 	$delivery_time = $current_time+($policy_time*60);
					 	$arr = array('status' =>1,"delivery_time"=>date("h:i: A",$delivery_time),"delivery_area"=>self::isRetaurantDeliverThisPostcode());
						echo json_encode($arr);
					 }
					 else
					 {
					 	$delivery_time = $current_time+($policy_time*60);
						$arr = array('status' =>1,"delivery_time"=>date("h:i: A",$delivery_time));
						echo json_encode($arr);
					 }
				}
				else
				{
					if(strtolower($_REQUEST["order_option"])=="delivery")	
					{
							
						$arr = array('status' =>0,"delivery_time"=>self::preOrderListOftime(),"delivery_area"=>self::isRetaurantDeliverThisPostcode());
						echo json_encode($arr);
					}
					else
					{
						$delivery_time = $current_time+($policy_time*60);
						$arr = array('status' =>0,"delivery_time"=>self::preOrderListOftime());
						echo json_encode($arr);
					}
				}
				
			}

		private function preOrderListOftime()
		{
			$str="";
			if (strtolower($_REQUEST["order_option"])=="delivery")
			{
				$policy_time =$_REQUEST['policy_time'];
				$start_time = strtotime($_REQUEST['opening'])+($policy_time*60);
				$end_time   = strtotime($_REQUEST['closing']);
				$arr =array();
				for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
				{ 
					$str1=strtotime(date('H:i:s', $i));
					$str2=date('h:i A', $i);
					$arr[] = array("time1"=>$str1,"time2"=>$str2);	
				}
				return $arr;
			}

			if (strtolower($_REQUEST["order_option"])=="collection")
			{
				$policy_time =$_REQUEST['policy_time'];
				$start_time = strtotime($_REQUEST['opening'])+($policy_time*60);
				$end_time   = strtotime($_REQUEST['closing'])-(3*60);
				$arr =array();
				for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
				{ 
					$str1=strtotime(date('H:i:s', $i));
					$str2=date('h:i A', $i);
					$arr[] = array("time1"=>$str1,"time2"=>$str2);	
				}
				return $arr;
			}
		}


			public function TimeProcess2()
			{
				date_default_timezone_set('Europe/London');
				$json_arr["PreOrderTime"] = array();
				$str="";
				$opening1 = new DateTime($_REQUEST['opening1']);
				$closing1 = new DateTime($_REQUEST['closing1']);
				$opening2 = new DateTime($_REQUEST['opening2']);
				$closing2 = new DateTime($_REQUEST['closing2']);
				$crr_time = new DateTime();
				$current_time = time();	
				$policy_time =$_REQUEST['policy_time'];
				
				if($crr_time >= $opening1 && $crr_time<= $closing1)
				{
					$delivery_time = $current_time+($policy_time*60);
					$arr = array('status' =>1,"delivery_time"=>date("h:i: A",$delivery_time));
					echo json_encode($arr);

				}
				else
				{
				    $start_time  = strtotime($_REQUEST['opening'])+($policy_time*60);
					$end_time   = strtotime($_REQUEST['closing'])-($policy_time*60);
					for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
					{ 	
						$str.=date('h:i A', $i).",";
						
					}
					$json_arr["pre_time"] =$str;
					$json_arr["status"]=0;
					echo json_encode($json_arr);
				}

		 	    if($crr_time >= $opening2 && $crr_time<= $closing2)
				{
					$delivery_time = $current_time+($policy_time*60);
					$arr = array('status' =>1,"delivery_time"=>date("h:i: A",$delivery_time));
					echo json_encode($arr);
				}
				else
				{
				    $start_time  = strtotime($_REQUEST['opening'])+($policy_time*60);
					$end_time   = strtotime($_REQUEST['closing'])-($policy_time*60);
					for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
					{ 	
						$str.=date('h:i A', $i).",";
						
					}
					$json_arr["pre_time"] =$str;
					$json_arr["status"]=0;
					echo json_encode($json_arr);
				}
			}


public function isRetaurantDeliverThisPostcode()
    {
        $rest_id = mysqli_real_escape_string($this->mysqli, $_REQUEST['rest_id']);
        $user_id = mysqli_real_escape_string($this->mysqli, $_REQUEST['user_id']);

        if(is_numeric($rest_id) && is_numeric($user_id))
        {
        	$sql = $this->mysqli->query("select count(rsp.postcode) as postcode_found from restaurant_supply_postcode rsp where rsp.restaurant_id = '$rest_id' and rsp.postcode IN (select REPLACE(postcode, ' ', '') as postcode from user_profile where user_id = '$user_id')");
        	$result = $sql->fetch_assoc();	 
        	$datas["app"] = array(); 
        	if($result['postcode_found'] == 0)
        	{        
        		$arr = array();		
        		$arr['status'] = 'Failed';
        		$arr['msg'] = 'This restaurant not deliver in your postcode.';        		
        		array_push($datas['app'], $arr);
        		return $datas;
        	}   
        	else
        	{
        		$callback = array('status'=>'Success','msg'=>'This restaurant deliver in your postcode.');
        		array_push($datas['app'], $callback);
        		return $datas;
        	}     	
        }
        else
        {
        	return (array('status'=>'Failed','msg'=>'Please provide input in correct format'));
        }
    }

    public function isRestaurantOpen()
    {
    	$noShift = $_REQUEST['shift'];
    	switch ($noShift)
    	 {
    		case 1:
    			self::isRestaurantOpenForOneShift();
    		break;

    		case 2:
    			self::isRestaurantOpenForOneShift();
    		break;

    	}
    }
    private function isRestaurantOpenForOneShift()
    {
		date_default_timezone_set('Europe/London');
		$json_arr = array();
		$str="";
		$opening1 = new DateTime($_REQUEST['opening']);
		$closing1 = new DateTime($_REQUEST['closing']);
		$current_time = time();	
		$crr_time = new DateTime();
		$policy_time =$_REQUEST['policy_time'];

		$open =date('h:i A',strtotime($_REQUEST['opening']));
		$close = date('h:i A',strtotime($_REQUEST['closing']));
		if ($crr_time>=$opening1 AND $crr_time<=$closing1) 
		{
	 	$delivery_time = $current_time+($policy_time*60);
	 	$arr = array('status' =>1);
		echo json_encode($arr);		 
		}
		else
		{
			$arr = array('status' =>0,"msg"=>"Sorry we are closed, opening time ".$open." - ".$close." | PRE-ORDER NOW!");
			echo json_encode($arr);
		}
    }

    private function isRestaurantOpenFor2ndShift()
    {
		date_default_timezone_set('Europe/London');
		$json_arr = array();
		$str="";

		$current_time = time();	
		$crr_time = new DateTime();
		$policy_time =$_REQUEST['policy_time'];
		if ($_REQUEST['opening1']!=null && $_REQUEST['closing1']!=null)
		 {

			$opening1 = new DateTime($_REQUEST['opening1']);
			$closing1 = new DateTime($_REQUEST['closing1']);

		 	$open =date('h:i A',strtotime($_REQUEST['opening1']));
			$close = date('h:i A',strtotime($_REQUEST['closing1']));
			if ($crr_time>=$opening1 AND $crr_time<=$closing1) 
			{
			 	$arr = array('status' =>1);
				echo json_encode($arr);		 
			}
			else
			{
				$arr = array('status' =>0,"msg"=>"Sorry we are closed, opening time ".$open." - ".$close." | PRE-ORDER NOW!");
				echo json_encode($arr);
			}
		}

		if ($_REQUEST['opening2']!=null && $_REQUEST['closing2']!=null)
		 {

			$opening2 = new DateTime($_REQUEST['opening2']);
			$closing2 = new DateTime($_REQUEST['closing2']);

		 	$open =date('h:i A',strtotime($_REQUEST['opening2']));
			$close = date('h:i A',strtotime($_REQUEST['closing2']));
			if ($crr_time>=$opening2 AND $crr_time<=$closing2) 
			{
			 	$arr = array('status' =>1);
				echo json_encode($arr);		 
			}
			else
			{
				$arr = array('status' =>0,"msg"=>"Sorry we are closed, opening time ".$open." - ".$close." | PRE-ORDER NOW!");
				echo json_encode($arr);
			}
		}

    }

    public function getReservationTime()
    {
    	date_default_timezone_set('Europe/London');
    	if ($_REQUEST['opening1']!=null && $_REQUEST['closing1']!=null)
 		{
	    	$opening1 = new DateTime($_REQUEST['opening1']);
			$closing1 = new DateTime($_REQUEST['closing1']);
			$crr_time = new DateTime("6:30 PM");
			$resTime = array();
			$start_time = strtotime($_REQUEST['opening1']);
			$end_time   = strtotime($_REQUEST['closing1']);
			if ($crr_time>=$opening1 AND $crr_time<=$closing1) 
			{		$d=new DateTime();
					$current_time=strtotime($d->format('h:i A'));	
					for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
					{ 
						if($i>$current_time)
						{
							$str2=date('h:i A', $i);
							$resTime[] = $str2;	
						}
					}
			}
			else
			{
				for ($i=$start_time; $i <=$end_time ; $i=$i+(15*60)) 
					{ 
						$str1=strtotime(date('H:i:s', $i));
						$str2=date('h:i A', $i);
						$resTime[] = $str2;						
					}
			}
	}

if ($_REQUEST['opening2']!=null && $_REQUEST['closing2']!=null)
 {
 		$crr_time = new DateTime();
 		$opening2 = new DateTime($_REQUEST['opening2']);
		$closing2 = new DateTime($_REQUEST['closing2']);
 		$start_time2 = strtotime($_REQUEST['opening2']);
		$end_time2   = strtotime($_REQUEST['closing2']);
		if ($crr_time>=$opening2 AND $crr_time<=$closing2) 
		{
				$d=new DateTime();
				$current_time=strtotime($d->format('h:i A'));	
				for ($i=$current_time; $i <=$end_time2 ; $i=$i+(15*60)) 
				{ 
					if($i>$current_time)
					{
						$str1=strtotime(date('H:i:s', $i));
						$str2=date('h:i A', $i);
						$resTime[] = $str2;	
					}	
				}
		}
		else
		{
			for ($i=$start_time2; $i <=$end_time2 ; $i=$i+(15*60)) 
				{ 
					$str1=strtotime(date('H:i:s', $i));
					$str2=date('h:i A', $i);
					$resTime[] = $str2;	
					
				}
		}
}
	  echo json_encode($resTime);

    }

}

?>