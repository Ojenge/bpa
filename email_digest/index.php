<?php
require_once("../config/config.php");
require_once("../scorecards/measures/get-kpi-trend-two-months.php");
/****************************************
Presidential Directives
*****************************************/
$previousPeriod = mysqli_query($connect, "SELECT progress, COUNT(progress) AS count
FROM directives_trend d1
WHERE d1.updated = (SELECT d2.updated
                 FROM directives_trend d2
                 WHERE d2.updated <= '2016-09%' 
				 AND d2.directives_id = d1.directives_id 
				 ORDER BY d2.updated DESC LIMIT 1)
GROUP BY progress");
$total = 0;
while($previousPeriodData = mysqli_fetch_array($previousPeriod))
{
		$data[$previousPeriodData["progress"]] = $previousPeriodData["count"];
		$total = $total + $previousPeriodData["count"];
}
$previousPercentCompletion = ($data["Implemented"]/$total)*100;

$currentPeriod = mysqli_query($connect, "SELECT progress, COUNT(progress) AS count
FROM directives_trend d1
WHERE d1.updated = (SELECT d2.updated
                 FROM directives_trend d2
                 WHERE d2.updated <= '2016-11%' 
				 AND d2.directives_id = d1.directives_id 
				 ORDER BY d2.updated DESC LIMIT 1)
GROUP BY progress");
$total = 0;
while($currentPeriodData = mysqli_fetch_array($currentPeriod))
{
		$currentData[$currentPeriodData["progress"]] = $currentPeriodData["count"];
		$total = $total + $currentPeriodData["count"];
}
$currentPercentCompletion = ($currentData["Implemented"]/$total)*100;

$improvement = $currentPercentCompletion - $previousPercentCompletion;
if($improvement > 0) {
	$colorDirectives = "#093"; 
	$improvement = floor($improvement)."% improvement from previous month";
}
else {
	$colorDirectives = "red";
		$improvement = floor($improvement)."% drop from previous month";
}

/****************************************
Ministerial Projects
*****************************************/
$previousPeriodProjects = mysqli_query($connect, "SELECT achievement, COUNT(achievement) AS count
FROM ministry_projects_county_trend d1
WHERE d1.updated = (SELECT d2.updated
                 FROM ministry_projects_county_trend d2
                 WHERE d2.updated <= '2016-09%' 
				 AND d2.excel_id = d1.excel_id
				 AND d2.agency = d1.agency
				 ORDER BY d2.updated DESC LIMIT 1)
GROUP BY achievement");
$totalProjects = 0;
while($previousPeriodDataProjects = mysqli_fetch_array($previousPeriodProjects))
{
		$dataProjects[$previousPeriodDataProjects["achievement"]] = $previousPeriodDataProjects["count"];
		$totalProjects = $totalProjects + $previousPeriodDataProjects["count"];
}
$previousPercentCompletionProjects = ($dataProjects["Achieved"]/$totalProjects)*100;

$currentPeriodProjects = mysqli_query($connect, "SELECT achievement, COUNT(achievement) AS count
FROM ministry_projects_county_trend d1
WHERE d1.updated = (SELECT d2.updated
                 FROM ministry_projects_county_trend d2
                 WHERE d2.updated <= '2016-11%' 
				 AND d2.excel_id = d1.excel_id
				 AND d2.agency = d1.agency 
				 ORDER BY d2.updated DESC LIMIT 1)
GROUP BY achievement");
$totalProjects = 0;
while($currentPeriodDataProjects = mysqli_fetch_array($currentPeriodProjects))
{
		$currentDataProjects[$currentPeriodDataProjects["achievement"]] = $currentPeriodDataProjects["count"];
		$totalProjects = $totalProjects + $currentPeriodDataProjects["count"];
}
$currentPercentCompletionProjects = ($currentDataProjects["Achieved"]/$totalProjects)*100;


$improvementProjects = $currentPercentCompletionProjects - $previousPercentCompletionProjects;
if($improvementProjects > 0) {
	$colorProjects = "#093"; 
	$improvementProjects = floor($improvementProjects)."% improvement from previous month";
}
else {
	$colorProjects = "red";
		$improvementProjects = floor($improvementProjects)."% drop from previous month";
}

/****************************************
Cabinet Projects
*****************************************/
$previousCabinet = mysqli_query($connect, "SELECT * FROM cabinet_projects_audit WHERE date <= '2016-09-%'");
$noChange = 0;
$positiveChange = 0;
$negativeChange = 0;
while($previousDataCabinet = mysqli_fetch_array($previousCabinet))
{
	$id = $previousDataCabinet["id"];
	$baselineTemp = $previousDataCabinet['baseline'];
	$baselineTemp = @reset(array_filter(preg_split("/\D+/", $baselineTemp)));
	$currentTemp = $previousDataCabinet['current'];
	$currentTemp = @reset(array_filter(preg_split("/\D+/", $currentTemp)));
	
	if($id == 41 || $id == 1175 || $id == 1248 || $id == 1243) $noChange++;
	else if($currentTemp == '2011' || $currentTemp == '2012' || $currentTemp == '2013' || $currentTemp == '2014' || $currentTemp == '2015' || $currentTemp == '2016') $noChange++;
	else if($baselineTemp == $currentTemp)
	{
		if($baselineTemp == '' && $currentTemp == '') $noChange++;
		else if($baselineTemp == '47' && $currentTemp == '47') $noChange++;
		else $noChange++;	
	}
	else if($currentTemp > $baselineTemp || $baselineTemp == 0 || $baselineTemp == '' || $baselineTemp == NULL)
	{
		$positiveChange++;	
	}
	else if($baselineTemp > $currentTemp)
	{//file_put_contents("milestoneArrow.txt", "\t\n baselineTemp = $baselineTemp; currentTemp = $currentTemp", FILE_APPEND);
		$negativeChange++;	
	}
}

$currentCabinet = mysqli_query($connect, "SELECT * FROM cabinet_projects");
$totalCabinet = mysqli_num_rows($currentCabinet);
$noChangeCurrent = 0;
$positiveChangeCurrent = 0;
$negativeChangeCurrent = 0;
while($currentDataCabinet = mysqli_fetch_array($currentCabinet))
{
	$baselineTemp = $currentDataCabinet['baseline'];
	$baselineTemp = @reset(array_filter(preg_split("/\D+/", $baselineTemp)));
	$currentTemp = $currentDataCabinet['current'];
	$currentTemp = @reset(array_filter(preg_split("/\D+/", $currentTemp)));
	
	if($id == 41 || $id == 1175 || $id == 1248 || $id == 1243) $noChangeCurrent++;
	else if($currentTemp == '2011' || $currentTemp == '2012' || $currentTemp == '2013' || $currentTemp == '2014' || $currentTemp == '2015' || $currentTemp == '2016') $noChangeCurrent++;
	else if($baselineTemp == $currentTemp)
	{
		if($baselineTemp == '' && $currentTemp == '') $noChangeCurrent++;
		else if($baselineTemp == '47' && $currentTemp == '47') $noChangeCurrent++;
		else $noChangeCurrent++;	
	}
	else if($currentTemp > $baselineTemp || $baselineTemp == 0 || $baselineTemp == '' || $baselineTemp == NULL)
	{
		$positiveChangeCurrent++;	
	}
	else if($baselineTemp > $currentTemp)
	{//file_put_contents("milestoneArrow.txt", "\t\n baselineTemp = $baselineTemp; currentTemp = $currentTemp", FILE_APPEND);
		$negativeChangeCurrent++;
	}
}

$cabinetChange = (($positiveChangeCurrent - $positiveChange)/$positiveChange)*100;
if($cabinetChange > 0) 
{
	$colorCabinet = "#093"; 
	$cabinetChange = floor($cabinetChange)."% improvement from previous month";
}
else 
{
	$colorCabinet = "red";
	$cabinetChange = floor($cabinetChange)."% drop from previous month";
}

$to = 'lee.kyonze@president.go.ke';

$subject = 'Delivery Updates';

$headers = "From: " . strip_tags("admin@gprs.report") . "\r\n";
$headers .= "Reply-To: ". strip_tags("admin@gprs.report") . "\r\n";
$headers .= "CC: lkyonze@gmail.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
$message = '
<html><body>
<!-- Start of preheader -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="preheader" >
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 440px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 440px!important;text-align:center!important;">
                           <tbody>
                                 <!-- Spacing -->
                              <tr>
                                 <td width="100%" height="10"></td>
                              </tr>
                              <!-- Spacing -->
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of preheader --> ';
?>
<?php
$message .= '     
<!-- Start of header -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="header">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 440px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#d41b29" width="600px" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <!-- Spacing -->
                              <tr>
                                 <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td>
                                    <!-- logo -->
                                    <table width="140" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 140px !important;text-align:center !important;">
                                       <tbody>
                                          <tr>
                                             <td width="140" height="60" align="center">
                                                <div class="imgpop">
                                                   <a target="_blank" href="#">
                                                   <img src="email_digest/img/logo.png" alt="" border="0" width="60" height="60" style="display:block; border:none; outline:none; text-decoration:none;">
                                                   </a>
                                                </div>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!-- end of logo -->
                                    <!-- start of menu -->
                                    <table width="400" border="0" align="right" valign="middle" cellpadding="0" cellspacing="0" border="0" style="width: 440px!important;text-align:center!important;">
                                       <tbody>
                                          <tr>
                                             <td align="center" style="font-family: Helvetica, arial, sans-serif; font-size: 18px;color: #ffffff" st-content="phone"  height="60">
                                                Government of Kenya
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!-- end of menu -->
                                 </td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                              </tr>
                              <!-- Spacing -->
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of Header -->';
?>
<?php
$message .= '<!-- Start of main-banner -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="banner">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 440px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 440px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <!-- start of image -->
                                 <td align="center" st-image="banner-image">
                                    <div class="imgpop">
                                       <a target="_blank" href="#"><img width="600" border="0" height="300" alt="" border="0" style="display:block; border:none; outline:none; text-decoration:none;" src="email_digest/img/banner.jpg" style="width: 440px!important;height:220px!important;"></a>
                                    </div>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                        <!-- end of image -->
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of main-banner -->  
';
$message .= '<!-- Start of seperator -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="seperator">
   <tbody>
      <tr>
         <td>
            <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 440px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td align="center" height="10" style="font-size:1px; line-height:1px;">&nbsp;</td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of seperator -->   
<!-- Start of heading -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="seperator">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 440px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <td align="center" style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #ffffff; padding: 15px 0;" st-content="heading" bgcolor="#d41b29" align="center">
                                    Delivery Updates
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of heading --> ';

$message .= 
'<!-- 2columns -->
<table width="100%" bgcolor="#272a2d" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <td>
                                    <table width="290" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 290px!important;text-align:center!important;">
                                       <tbody>
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="20"></td>
                                          </tr>
                                          <!-- Spacing -->
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 270px!important;text-align:center!important;">
                                                   <tbody>
                                                   <!-- title -->
                                                      <tr>
                                                         <td width="270" bgcolor="#d41b29" height="50">
                                                            <table width="218" align="left" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #d41b29;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff; padding-left: 10px;" align="left" height="50">
                                                                        President\'s Directives
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                            <table width="48" align="right" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #000000;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff;" bgcolor="#000000" align="center" height="50" >
                                                                        '.$total.'
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="270" align="center" style="border:1px solid red; width:270; height:150px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left;">
                                                           <table style="width:90%; "><tr><td colspan="2" style="font-weight:bold">Status</td></tr> 
                                                           <tr><td>Implemented</td><td align="center">'.$currentData["Implemented"].'</td></tr>
                                                           <tr><td>In Progress</td><td align="center">'.$currentData["In Progress"].'</td></tr>
                                                           <tr><td>Not Started</td><td align="center">'.$currentData["Not Started"].'</td></tr></table>
                                                       <br> <span style="color:'.$colorDirectives.'; font-weight:bold;">'.$improvement.'</span>
                                                         </td>
                                                      </tr>
                                                      
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                          <!-- end of text content table -->
                                       </tbody>
                                    </table>
                                    <!-- end of left column -->
                                    <!-- start of right column -->
                                    <table width="290" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 290px!important;text-align:center!important;">
                                       <tbody>
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="20"></td>
                                          </tr>
                                          <!-- Spacing -->
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 270px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- title -->
                                                      <tr>
                                                         <td width="270" bgcolor="#d41b29" height="50">
                                                            <table width="218" align="left" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #d41b29;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff; padding-left: 10px;" height="50" align="left">
                                                                        Jubilee Milestones
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                            <table  width="48" align="right" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #000000;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff;" bgcolor="#000000" height="50" align="center">
                                                                        '.$totalCabinet.'
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="270" align="center" style="border:1px solid red; width:270; height:150px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left;">
                                                           <table style="width:90%; "><tr><td colspan="2" style="font-weight:bold">Status</td></tr> 
                                                           <tr><td>Milestones showing improved progress</td><td align="center" valign="top">'.$positiveChangeCurrent.'</td></tr>
                                                           </table>
                                                        <br><span style="color:'.$colorCabinet.'; font-weight:bold;">'.$cabinetChange.'</span>
                                                         </td>
                                                      </tr>
                                                      
                                                      <!-- end of content -->
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                          <!-- end of text content table -->
                                       </tbody>
                                    </table>
                                    <!-- end of right column -->
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- end of 2 columns -->';
$message .= 
'<!-- 2columns -->
<table width="100%" bgcolor="#272a2d" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <td>
                                    <table width="290" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 290px!important;text-align:center!important;">
                                       <tbody>
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="10"></td>
                                          </tr>
                                          <!-- Spacing -->
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 270px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- title -->
                                                      <tr>
                                                         <td width="270" bgcolor="#d41b29" height="50">
                                                            <table width="218" align="left" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #d41b29;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff; padding-left: 10px;" align="left" height="50">
                                                                        Ministry Projects
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                            <table width="48" align="right" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #000000;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff;" bgcolor="#000000" align="center" height="50" >
                                                                        '.$totalProjects.'
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="270" align="center" style="border:1px solid red; width:270; height:150px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left;">
                                                           <table style="width:90%; "><tr><td colspan="2" style="font-weight:bold">Status</td></tr> 
                                                           <tr><td>Project Objectives Achieved</td><td align="center">'.$currentDataProjects["Achieved"].'</td></tr>
                                                           <tr><td>Project Objectives Not Achieved</td><td align="center">'.$currentDataProjects["Not Achieved"].'</td></tr>
                                                           </table>
                                                        <br><span style=" color:'.$colorProjects.'; font-weight:bold;">'.$improvementProjects.'</span>
                                                         </td>
                                                      </tr>
                                                      
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                          <!-- end of text content table -->
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="10"></td>
                                          </tr>
                                          <!-- Spacing -->
                                       </tbody>
                                    </table>
                                    <!-- end of left column -->
                                    <!-- start of right column -->
                                    <table width="290" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 290px!important;text-align:center!important;">
                                       <tbody>
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="10"></td>
                                          </tr>
                                          <!-- Spacing -->
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 270px!important;text-align:center!important;">
                                                   <tbody>
                                                   <!-- title -->
                                                      <tr>
                                                         <td width="270" bgcolor="#d41b29" height="50">
                                                            <table width="218" align="left" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #d41b29;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff; padding-left: 10px;" height="50" align="left">
                                                                        Performance Contract Indicators
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                            <table  width="48" align="right" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #000000;">
                                                               <tbody>
                                                                  <tr>
                                                                     <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #ffffff;" bgcolor="#000000" height="50" align="center">
                                                                        '.$kpi_total.'
                                                                     </td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="270" align="center" style="border:1px solid red; width:270; height:150px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left;">
                                                           <table style="width:90%; "><tr><td colspan="2" style="font-weight:bold">Status</td></tr> 
                                                           <tr><td>KPIs Meeting Set Targets</td><td align="center">'.$kpi_green.'</td></tr>
                                                           <tr><td>KPIs Performing Below Set Targets</td><td align="center" valign="top">'.$kpi_red.'</td></tr></table><br />
                                                           <span style="color:#F00; font-weight:bold;">'.$kpi_difference.'</span>
                                                         </td>
                                                      </tr>
                                                      
                                                      <!-- end of content -->
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                          <!-- end of text content table -->
                                          <!-- Spacing -->
                                          <tr>
                                             <td width="100%" height="20"></td>
                                          </tr>
                                          <!-- Spacing -->
                                       </tbody>
                                    </table>
                                    <!-- end of right column -->
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- end of 2 columns -->';
$message .= '<!-- Start of seperator -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="seperator">
   <tbody>
      <tr>
         <td>
            <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td align="center" height="10" style="font-size:1px; line-height:1px;">&nbsp;</td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of seperator --> 
<!-- Start of heading -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="seperator">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#d41b29" width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <td align="center" style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #ffffff; padding: 15px 0;" st-content="heading" bgcolor="#d41b29" align="center">
                                    Latest from PDU
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of heading --> 
<!-- article -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <!-- Spacing -->
                              <tr>
                                 <td height="20"></td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td>
                                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" style="width: 560px!important;text-align:center!important;">
                                       <tbody>
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="140" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 140px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="140" height="90" align="center">
                                                            <img src="email_digest/img/milestones.jpg" alt="" border="0" width="90" height="90" style="display:block; border:none; outline:none; text-decoration:none;" label="articleimage">
                                                         </td>
                                                      </tr>
                                                   </tbody>
                                                </table>
                                                <!-- start of right column -->
                                                <table width="400" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 400px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- title -->
                                                      <tr>
                                                         <td style="font-family: Helvetica, arial, sans-serif; font-size: 16px; color: #262626; text-align:left; line-height: 20px;" style="padding-top:15px!important;">
                                                            Jubilee Milestones
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- Spacing -->
                                                      <tr>
                                                         <td width="100%" height="10"></td>
                                                      </tr>
                                                      <!-- Spacing -->
                                                      <!-- content -->
                                                      <tr>
                                                         <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left; line-height: 20px;">
                                                            Your Ministry\'s achievements since 2013 are now available online. Please take time to upload or share with us photos of the same if available.
                                                           
                                                       </td>
                                                      </tr>
                                                      <!-- end of content -->
                                                   </tbody>
                                                </table>
                                                <!-- end of right column -->
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td height="20"></td>
                              </tr>
                              <!-- Spacing -->
                              <!-- bottom-border -->
                              <tr>
                                 <td width="100%" bgcolor="#d41b29" height="3" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
                              </tr>
                              <!-- /bottom-border -->
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- end of article -->
';
$message .= '
<!-- article -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <!-- Spacing -->
                              <tr>
                                 <td height="20"></td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td>
                                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" style="width: 560px!important;text-align:center!important;">
                                       <tbody>
                                          <tr>
                                             <td>
                                                <!-- start of text content table -->
                                                <table width="140" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 140px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- image -->
                                                      <tr>
                                                         <td width="140" height="90" align="center">
                                                            <img src="email_digest/img/loginScreen.png" alt="" border="0" width="90" height="90" style="display:block; border:none; outline:none; text-decoration:none;" label="articleimage">
                                                         </td>
                                                      </tr>
                                                   </tbody>
                                                </table>
                                                <!-- start of right column -->
                                                <table width="400" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 400px!important;text-align:center!important;">
                                                   <tbody>
                                                      <!-- title -->
                                                      <tr>
                                                         <td style="font-family: Helvetica, arial, sans-serif; font-size: 16px; color: #262626; text-align:left; line-height: 20px;" style="padding-top:15px!important;">
                                                            Keeping Data Up to Date
                                                         </td>
                                                      </tr>
                                                      <!-- end of title -->
                                                      <!-- Spacing -->
                                                      <tr>
                                                         <td width="100%" height="10"></td>
                                                      </tr>
                                                      <!-- Spacing -->
                                                      <!-- content -->
                                                      <tr>
                                                         <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left; line-height: 20px;">
                                                           In order to reflect the latest status of the items above, appointed Ministerial Contacts need to ensure updates and progress made on projects and directives are kept up to date through the provided online channels.
                                                         </td>
                                                      </tr>
                                                      <!-- end of content -->
                                                   </tbody>
                                                </table>
                                                <!-- end of right column -->
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td height="20"></td>
                              </tr>
                              <!-- Spacing -->
                              <!-- bottom-border -->
                              <tr>
                                 <td width="100%" bgcolor="#d41b29" height="3" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
                              </tr>
                              <!-- /bottom-border -->
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- end of article -->
<!-- Start of seperator -->
<table width="100%" bgcolor="#2a2a2a" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;" st-sortable="seperator">
   <tbody>
      <tr>
         <td>
            <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td align="center" height="30" style="font-size:1px; line-height:1px;">&nbsp;</td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- End of seperator -->
';
$message .= '   
<!-- footer -->
<table width="100%" bgcolor="#d41b29" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; width:100% !important; line-height: 100% !important;">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="#d41b29" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width: 600px!important;text-align:center!important;">
                           <tbody>
                              <tr>
                                 <td>
                                    
                                    <!-- end of left column -->
                                    <!-- start of right column -->
                                    <table width="200" align="right" border="0" cellpadding="0" cellspacing="0" style="width: 200px!important;text-align:center!important;">
                                       <tbody>
                                           <tr>
                                             <td width="100%" height="10"></td>
                                          </tr>
                                          <!-- Spacing -->
                                         
                                        </tbody>
                                    </table>
                                    <!-- end of right column -->
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
<!-- end of footer -->
<!-- Start of Postfooter -->

<!-- End of postfooter -->   
   </body>
   </html>';
echo $message;
//mail($to, $subject, $message, $headers);
?>