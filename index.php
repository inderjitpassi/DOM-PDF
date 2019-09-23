<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once 'includes/bootstrap.inc';
include('./includes/mail_function.php');
// include autoloader
require_once 'dompdf/autoload.inc.php';
// reference the Dompdf namespace
use Dompdf\Dompdf;
// instantiate and use the dompdf class
$dompdf = new Dompdf();
$userid = mysql_real_escape_string($_REQUEST['uid']);
$sql = "SELECT pv.value,pf.name FROM profile_values pv INNER JOIN profile_fields pf WHERE pv.fid=pf.fid and pv.uid='".$userid."'";
$esql = mysql_query($sql);
$user_array = array();
while($drows = mysql_fetch_array($esql)){
	$user_array[$drows['name']] = $drows['value'];
}


if(strpos($_REQUEST['vid'], 'DLP') !== false){
	$dlsq = "select Student_course_name,Student_order_id,Student_transaction_date  AS payment_date,Student_contact AS Student_Contact from onlinepayment where Student_order_id='".$_REQUEST['vid']."'";
	
	
	$desql = mysql_query($dlsq);
	$desarr = mysql_fetch_array($desql);
	$user_array[course_applied_for] = $desarr['Student_course_name'];
	$user_array[Student_order_id] = $desarr['Student_order_id'];	
	$user_array[payment_date] = $desarr['payment_date'];	
	$user_array[Student_contact] = $desarr['Student_Contact'];	
	
	
}else if(strpos($_REQUEST['vid'], 'AESPL2017') !== false){
   
   $dlsq = "SELECT cdn.course_title,user_reg_number_value AS Student_order_id,payment_date,(SELECT 
      `value` 
    FROM
      profile_values 
    WHERE uid = '".$_REQUEST['uid']."'
      AND fid = '68' 
    LIMIT 1) AS Student_Contact FROM content_type_payment_transactions_installment ctpti 
	INNER JOIN course_details_new cdn 
	WHERE ctpti.course_id =cdn.crs_desc_id AND ctpti.user_id ='".$_REQUEST['uid']."'  AND user_reg_number_value ='".$_REQUEST['vid']."'  AND user_reg_number_value IS NOT NULL ORDER BY created_at DESC LIMIT 1"; 
	
  /*  $dlsq = "SELECT cdn.course_title FROM content_type_payment_transactions_installment ctpti 
	INNER JOIN course_details_new cdn 
	WHERE ctpti.course_id =cdn.crs_desc_id AND ctpti.user_reg_number_value ='".."'"; */
	
	$desql = mysql_query($dlsq);
	$desarr = mysql_fetch_array($desql);
	$user_array[course_applied_for] = $desarr['course_title'];
	
	$user_array[Student_order_id] = $desarr['Student_order_id'];	
	$user_array[payment_date] = $desarr['payment_date'];	
	$user_array[Student_contact] = $desarr['Student_Contact'];	
	
}
$dob = '';

 

 if ( !strstr($user_array['profile_dob'], "{",true) ) { $dob = $user_array['profile_dob']; }
 

$pdf_content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<style>
table{
	width:100%;
	margin:0px auto;
	    border-spacing: 0;
    border-collapse: collapse;
	
}
table td {
border:1px solid #ddd;
border-spacing: 0;
border-collapse: collapse;
padding: 3px;
font-size: 13px;
font-family:Arial;
color: #4c4c4c;
  
		
}

.clear{
	
	clear:both;
}

</style>



			</head>
			<body>
			  <table style="table-layout: fixed">
			    <tr><td colspan="6" align="center" style=" font-family:arial;  font-weight:bold; font-size: 20px;">ADMISSION FORM</td></tr>
				<tr><td colspan="3" style="color: #0f9acf;font-size: 16px; font-family:arial;font-weight:bold" >Personal Details</td><td colspan="3">Voucher Id:'.$_REQUEST['vid'].'';
				
				if($desarr['payment_date'] !=""){
					$pdf_content .="<br/>Voucher Txn Date : ".date('d-m-Y',strtotime($desarr['payment_date']));
				}
				if($user_array[Student_contact] !=""){
					$pdf_content .="<br/>Voucher Contact No : ".$user_array[Student_contact];
				}
				
				$pdf_content .='</td></tr>
				<tr>
				   <td>Course Applied For</td>
				   <td colspan="5" style=" font-family:arial;  font-weight:bold; font-size: 14px;"><input type="text" value="'.$user_array[course_applied_for].'" style="width:100%; border:none;"></td>
				 </tr>
				  <tr><td colspan="6"><b>Details (As per school record)</b></td></tr>
				  <tr>
				    <td>First Name:</td><td colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['profile_first_name'].'</td>
					<td >Student Class Category:</td><td colspan="2" style="width: 230px;word-wrap:break-word;" >'.$user_array['student_class_category'].'</td>
				  </tr>
				  
				  <tr>
				    <td>Last Name:</td><td  colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['profile_last_name'].'</td>
					<td rowspan="3" colspan="3" style="width: 230px;word-wrap:break-word;"> <img src="sites/default/files/student_document/'.$user_array['photo'].'" width="100px;" height="100px;" readonly></td>
				  </tr>
				  
				  <tr>
						<td>Father\'s/Guardian\'s Name:</td><td colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['profile_father_guardian_name'].'</td>
				</tr>
					
						<tr>
						<td>Occupation of Father/Guardian:</td><td colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['occupation_of_gardian'].'</td></tr>
						
				  
				  
				  
				  <tr>				  
					  <td>Mother\'s Name:</td><td colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['mother_name'].'</td>
					  <td >Date of Birth:</td><td colspan="2" style="width: 180px;word-wrap:break-word;">'.$dob.'</td>	
				  </tr>
				  <tr>				  
					  <td>Occupation of Mother:</td><td colspan="2" style="width: 230px;word-wrap:break-word;">'.$user_array['occupation_of_mother'].'</td>
					  <td >Blood Group:</td><td colspan="2">'.$user_array['blood_group'].'</td>	
				  </tr>
				   <tr>				  
					  <td>Gender</td><td colspan="2">'.$user_array['gender'].'</td>';
					  
					  $esql = "select id,country_name from country_list where id=$user_array[nationality] order by country_name asc";
					  $esql =mysql_query($esql);
					  $natonality = mysql_fetch_array($esql);
					  
				$pdf_content .='<td >Nationality:</td><td colspan="2">'.$natonality['country_name'].'</td>	
				  </tr>
				  <tr> <td>Category:</td><td colspan="2">'.$user_array['category'].'</td>';
					  
					 
				$pdf_content .='<td ></td><td colspan="2"></td>	
				  </tr>
				  <tr>
				    <td colspan="6" style="color: #0f9acf;font-size: 16px; font-family:arial;font-weight:bold" >Correspondence Address</td>
				  </tr>
				  <tr>				 
					  <td>Residence Address:</td><td colspan="2" style="width: 180px;word-wrap:break-word;">'. $user_array['profile_address'].'</td>';
					  $sql = "SELECT id,city_name FROM city_list WHERE id ='".$user_array['city']."'";
					  $esql = mysql_query($sql);
					  $fetcharr = mysql_fetch_array($esql);
				$pdf_content .='<td >City:</td><td colspan="2">'.$fetcharr['city_name'].'</td>	
				  </tr>
				  <tr>';
					$sql ="SELECT state_id,state_name FROM state_list where state_id='".$user_array['state']."' ORDER BY state_name ASC";
					$esql = mysql_query($sql);
                    $state = mysql_fetch_array($esql);					
					$pdf_content .='<td>State:</td><td colspan="2">'.$state['state_name'].'</td>
					  <td >Pin Code:</td><td colspan="2">'.$user_array['pin'].'</td>	
				  </tr>
				   <tr>				  
					  <td>Phone(Residence):</td><td colspan="2">'.$user_array['phone_residance'].'</td>
					  <td>Mobile(Parent\'s):</td><td colspan="2">'.$user_array['mobile_parents'].'</td>	
				  </tr>
				  <tr>				  
					  <td>Email id(Parent\'s):</td> <td colspan="2">'.$user_array['email_id'].'</td>
					  <td>Contact(for SMS):</td> <td colspan="2">'.$user_array['sms_contact_no'].'</td>
					  
				  </tr>
				  
				  
				  <tr>
				    <td colspan="6" style="color: #0f9acf;font-size: 16px; font-family:arial;font-weight:bold" >Education Details</td>
				  </tr>
				   <tr>				  
					  <td colspan="2">School/College Name Address:</td><td colspan="4" style="width: 180px;word-wrap:break-word;">'.$user_array['school_collage_address'].'</td>
					  <!--<td colspan="2"></td><td colspan="2"></td>-->	
				  </tr>
				  <tr>				  
					  <td colspan="6" style=" font-family:arial;  font-weight:bold; font-size: 15px;">Marks Obtained</td>
				  </tr>';
				  
				  $sql = "SELECT exams_board,class,marks_in_percent,marksheet 
						   FROM student_education_details WHERE uid='".$userid."' 
						   AND record_type='Marks Obtained'";
						   
				    $esql = mysql_query($sql);
				  while($mrows = mysql_fetch_array($esql)){
				$pdf_content .='<tr>				  
					  <td>Exams Board:</td><td>'.$mrows['exams_board'].'</td>
					  <td>Class:</td><td>'.$mrows['class'].'</td>
						<td>%/Grade: '.$mrows['marks_in_percent'].' </td><td> 
						Marksheet: <a href="/sites/default/files/student_document/'.$mrows['marksheet'].'" target="_blank">View</a></td>
                         						
				  </tr>';
				  }
				$pdf_content .='<tr>
				    <td colspan="6" style=" font-family:arial;  font-weight:bold; font-size: 15px;">Grades Obtained</td>
				  </tr>';
				  $sql = "SELECT exams_board,class,marks_in_percent,grad 
			   FROM student_education_details WHERE uid='".$userid."' 
			   AND record_type='Marks grade'";
				$esql = mysql_query($sql);
				while($grows = mysql_fetch_array($esql)){
			$pdf_content .='<tr>				  
					  <td>Exams Board:</td><td>'.$grows['exams_board'].'</td>
					  <td>Class:</td><td>'.$grows['class'].'</td>
					  <td>Grad:</td><td>'.$grows['grad'].'</td>						  
				  </tr>';
				}
				
	$pdf_content .='<tr>				  
		  <td >Eligibility for Scholarship</td><td colspan="">'.$user_array['eligibility_for_scholarship'].'%</td>
		  <td colspan="4"></td>
	  </tr>
				  <tr>				  
					  <td colspan="2">If ex Aakashian,please mention your Aakash PSID</td><td colspan="4">'.$user_array['aakashian_psid'].'</td>
				
				  </tr>
				  <tr>				  
					  <td colspan="2">How did you come to know about Aakash ?</td><td colspan="4">'.$user_array['profile_source'].'</td>
					    
				  </tr>
				  <tr>				  
					  <td>AIATS Mode</td><td>'.ucfirst($user_array['aiats_mode']).'</td>';
					  if($user_array['aiats_mode'] =='offline'){
						 
						 /*$ssql ="SELECT Student_centre_location FROM onlinepayment WHERE user_id ='".$userid."'";
						 $esql = mysql_query($ssql);
						 $rowsv = mysql_fetch_array($esql);
						 
						 if($rowsv['Student_centre_location']==''){
							 $aits_center = $user_array['aits_center'];
						 }else{
							 $aits_center = $rowsv['Student_centre_location'];
						 }*/
						    $aits_center = $user_array['aits_center'];
						   $aits_center = '<b>Selected Center: </b>'.$aits_center;
					  }else{
						 $aits_center = ''; 
					  }
				$pdf_content .='<td colspan="4">'.$aits_center.'</td>
				  </tr>
				<tr>				  
					  <td>Medium</td><td>'.ucfirst($user_array['medium']).'</td>
					  <td  colspan="4"></td>
				  </tr>
				  <!--<tr>				  
					  <td>Contact</td><td>'.$user_array['sms_contact_no'].'</td>
					  <td colspan="4"><p>Please enter contact no. for SMS Communication</p></td>
				  </tr>-->
				<tr>
				  <td colspan="6" style=" font-family:arial;  font-weight:bold; font-size: 14px;">If you are enrolling for mock text then please enter your rollno. of NEET/JEE Mains/JEE Advance.</td>
				</tr>
				<tr>				  
					  <td>Mock Type:</td><td>'.$user_array['mock_type'].'</td>
					  <td colspan="2">RollNo.:</td><td colspan="2">'.$user_array['roll_no'].'</td>
				  </tr>	
			  
<tr>
 <td colspan="6" >
   <input type="checkbox" name="termsandcondition" id="termsandcondition" value="accepted" checked width="20px;">
   I agree to the terms of service.  
</td></tr>';
$sql = "select luid,suid from onlinepayment where user_id ='".$userid."'";
			  $esql = mysql_query($sql);
			 $paydetail = mysql_fetch_array($esql);

		$pdf_content .='<tr>				  
					  <td>USID:</td><td>'.$paydetail['luid'].'</td>
					  <td colspan="2">SDID:</td><td colspan="2">'.$paydetail['suid'].'</td>
				  </tr></table>';
			
					//$pdf_content .= '</body></html>';
					
					$sql3 = "SELECT exams_board,class,marks_in_percent,marksheet 
					FROM student_education_details WHERE uid='".$userid."' 
					AND record_type='Marks Obtained'";
					
			 $esql3 = mysql_query($sql3);
			 $pdf_content .= '<table style="margin-top:100%;width:100%;"> <tr><td align="center" colspan="2" style=" font-family:arial;  font-weight:bold; font-size: 20px; padding: 7px 0; background: #e8e8e8;">MARKSHEET</td></tr>';
		 	while($mrows = mysql_fetch_assoc($esql3)){
			  
   			 	if($mrows['marksheet'] !=""){
							$pdf_content .='<tr><td colspan="2" align="center" style="padding: 7px 0;"> <img src="sites/default/files/student_document/'.$mrows['marksheet'].'" style="width: 600px;height:700px"></td></tr>';
				 }
			} 
			$pdf_content .= '</table>';

		$pdf_content .='<table style="margin-top:60%;clear:both;"> <tr><td align="center" style=" font-family:arial;  font-weight:bold; font-size: 20px;padding: 5px 0; background: #e8e8e8;">PHOTOGRAPH</td></tr><tr>		
		<td align="center" style="padding: 10px 0;"><img src="sites/default/files/student_document/'.$user_array['photo'].'" readonly style="width: 419px;height:419px;"></td></tr></table>';


	
$pdf_content .= '</body></html>';

//echo $pdf_content;die;

$dompdf->loadHtml($pdf_content);

// (Optional) Setup the paper size and orientation

$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF

$dompdf->render();

// Output the generated PDF to Browser
// $dompdf->stream();
// Output the generated PDF (1 = download and 0 = preview)

$fileName = $_REQUEST['vid'].'_'.$user_array['profile_first_name'].'_'.$user_array['profile_last_name'];

$dompdf->stream($fileName,array("Attachment"=>0));


?>