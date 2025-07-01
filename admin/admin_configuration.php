<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST))
{
	$cfgId = array();
	$newSettings = $_POST['settings'];
	$newSettings = json_decode($newSettings);
	//var_dump($newSettings);
	//echo $newSettings[1]."<br>".$newSettings[2]."<br>".$newSettings[3]."<br>".$newSettings[4]."<br>".$newSettings[5]."<br>".$newSettings[6]."<br>".$newSettings[7]."<br>".$newSettings[8];
	//echo "Currency-> ".$newSettings[0]." <-<br><br>";
	//file_put_contents("cake.txt", $newSettings[2]);
	//Validate new site name
	if ($newSettings[1] != $websiteName) {
		$newWebsiteName = $newSettings[1];
		if(minMaxRange(1,150,$newWebsiteName))
		{
			$errors[] = lang("CONFIG_NAME_CHAR_LIMIT",array(1,150));
		}
		else if (count($errors) == 0) {
			$cfgId[] = 1;
			$cfgValue[1] = $newWebsiteName;
			$websiteName = $newWebsiteName;
		}
	}
	
	//Validate new URL
	if ($newSettings[2] != $websiteUrl) {
		$newWebsiteUrl = $newSettings[2];
		if(minMaxRange(1,150,$newWebsiteUrl))
		{
			$errors[] = lang("CONFIG_URL_CHAR_LIMIT",array(1,150));
		}
		else if (substr($newWebsiteUrl, -1) != "/"){
			$errors[] = lang("CONFIG_INVALID_URL_END");
		}
		else if (count($errors) == 0) {
			$cfgId[] = 2;
			$cfgValue[2] = $newWebsiteUrl;
			$websiteUrl = $newWebsiteUrl;
		}
	}
	
	//Validate new site email address
	if ($newSettings[3] != $emailAddress) {
		$newEmail = $newSettings[3];
		if(minMaxRange(1,150,$newEmail))
		{
			$errors[] = lang("CONFIG_EMAIL_CHAR_LIMIT",array(1,150));
		}
		elseif(!isValidEmail($newEmail))
		{
			$errors[] = lang("CONFIG_EMAIL_INVALID");
		}
		else if (count($errors) == 0) {
			$cfgId[] = 3;
			$cfgValue[3] = $newEmail;
			$emailAddress = $newEmail;
		}
	}
	
	//Validate email activation selection
	if ($newSettings[4] != $emailActivation) {
		$newActivation = $newSettings[4];
		if($newActivation != "true" AND $newActivation != "false")
		{
			$errors[] = lang("CONFIG_ACTIVATION_TRUE_FALSE");
		}
		else if (count($errors) == 0) {
			$cfgId[] = 4;
			$cfgValue[4] = $newActivation;
			$emailActivation = $newActivation;
		}
	}
	
	//Validate new email activation resend threshold
	if ($newSettings[5] != $resend_activation_threshold) {
		$newResend_activation_threshold = $newSettings[5];
		if($newResend_activation_threshold > 72 OR $newResend_activation_threshold < 0)
		{
			$errors[] = lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72));
		}
		else if (count($errors) == 0) {
			$cfgId[] = 5;
			$cfgValue[5] = $newResend_activation_threshold;
			$resend_activation_threshold = $newResend_activation_threshold;
		}
	}
	
	//Validate new language selection
	if ($newSettings[6] != $language) {
		$newLanguage = $newSettings[6];
		if(minMaxRange(1,150,$language))
		{
			$errors[] = lang("CONFIG_LANGUAGE_CHAR_LIMIT",array(1,150));
		}
		elseif (!file_exists($newLanguage)) {
			$errors[] = lang("CONFIG_LANGUAGE_INVALID",array($newLanguage));				
		}
		else if (count($errors) == 0) {
			$cfgId[] = 6;
			$cfgValue[6] = $newLanguage;
			$language = $newLanguage;
		}
	}
	
	//Validate new template selection
	if ($newSettings[7] != $template) {
		$newTemplate = $newSettings[7];
		if(minMaxRange(1,150,$template))
		{
			$errors[] = lang("CONFIG_TEMPLATE_CHAR_LIMIT",array(1,150));
		}
		elseif (!file_exists($newTemplate)) {
			$errors[] = lang("CONFIG_TEMPLATE_INVALID",array($newTemplate));				
		}
		else if (count($errors) == 0) {
			$cfgId[] = 7;
			$cfgValue[7] = $newTemplate;
			$template = $newTemplate;
		}
	}
	//echo "Currency: ".$newSettings[8];
	//Validate new currency
	if ($newSettings[8] != $currency) {
		$newCurrency = $newSettings[8];
		if(minMaxRange(1,150,$newCurrency))
		{
			$errors[] = lang("CONFIG_CURRENCY_CHAR_LIMIT",array(1,20));
		}
		else if (count($errors) == 0) {
			$cfgId[] = 8;
			$cfgValue[8] = $newCurrency;
			$currency = $newCurrency;
		}
	}
	
	if ($newSettings[9] != $finYearStart) {
		$newFinYearStart = $newSettings[9];
		if(minMaxRange(1,150,$newFinYearStart))
		{
			$errors[] = lang("CONFIG_CURRENCY_CHAR_LIMIT",array(1,20));
		}
		else if (count($errors) == 0) {
			$cfgId[] = 9;
			$cfgValue[9] = $newFinYearStart;
			$finYearStart = $newFinYearStart;
		}
	}
	
	//Update configuration table with new settings
	if (count($errors) == 0 AND count($cfgId) > 0) {
		updateConfig($cfgId, $cfgValue);
		$successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
	}
}

$languages = getLanguageFiles(); //Retrieve list of language files
$templates = getTemplateFiles(); //Retrieve list of template files
$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
require_once("models/header.php");

echo resultBlock($errors,$successes);

//echo "settings[".$settings['website_name']['id']."]";

echo "
<table>
<tr>
	<td>Website Name:</td>
	<td><input type='text' id='settings[".$settings['website_name']['id']."]' name='settings[".$settings['website_name']['id']."]' value='".$websiteName."' /></td>
</tr>
<tr>
	<td>Website URL:</td>
	<td><input type='text' id='settings[".$settings['website_url']['id']."]' name='settings[".$settings['website_url']['id']."]' value='".$websiteUrl."' /></td>
</tr>
<tr>
	<td>Email:</td>
	<td><input type='text' id='settings[".$settings['email']['id']."]' name='settings[".$settings['email']['id']."]' value='".$emailAddress."' /></td>
</tr>
<tr>
	<td>Activation Threshold:</td>
	<td><input type='text' name='settings[".$settings['resend_activation_threshold']['id']."]' id='settings[".$settings['resend_activation_threshold']['id']."]' value='".$resend_activation_threshold."' /></td>
</tr>
<tr>
	<td>Language:</td>
	<td><select name='settings[".$settings['language']['id']."]' id='settings[".$settings['language']['id']."]'>";
	//Display language options
	foreach ($languages as $optLang){
		if ($optLang == $language){
			echo "<option value='".$optLang."' selected>$optLang</option>";
		}
		else {
			echo "<option value='".$optLang."'>$optLang</option>";
		}
	}
	echo "
	</select></td>
</tr>
<tr>
	<td>Email Activation:</td>
	<td><select name='settings[".$settings['activation']['id']."]' id='settings[".$settings['activation']['id']."]'>";
	//Display email activation options
	if ($emailActivation == "true"){
		echo "
		<option value='true' selected>True</option>
		<option value='false'>False</option>
		</select>";
	}
	else {
		echo "
		<option value='true'>True</option>
		<option value='false' selected>False</option>
		</select>";
	}
echo "</td>
</tr>
<tr>
	<td>Template:</td>
	<td><select name='settings[".$settings['template']['id']."]' id='settings[".$settings['template']['id']."]'>";
	//Display template options
	foreach ($templates as $temp){
		if ($temp == $template){
			echo "<option value='".$temp."' selected>$temp</option>";
		}
		else {
			echo "<option value='".$temp."'>$temp</option>";
		}
	}
echo "</td>
</tr>

<tr>
	<td>Currency:</td>
	<td><input type='text' name='settings[".$settings['currency']['id']."]' id='settings[".$settings['currency']['id']."]' value='".$currency."'/></td></tr>
	
<tr>
	<td>Financial Year Starting Month:</td>
	<td><input type='text' name='settings[".$settings['fyStartMonth']['id']."]' id='settings[".$settings['fyStartMonth']['id']."]' value='".$finYearStart."'/></td></tr>
	
<tr>
	<td><input type='submit' name='Submit' value='Submit' onclick='adminConfigUpdate()' /></td>
</tr>
</table>";
?>