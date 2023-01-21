<?php
header("Content-Type:application/json");

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.users.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.validation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.authentication.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.date_processing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.fcm_send_notification.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/classes/class.json_response.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/includes/global_defaults.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/includes/process_errors.php');

//	Other Required Classes
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/academic_supervision/class.student_diaries.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.branch_staff.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/classes/class.app.branch_staff.php');

$Response = new JSONResponse();

$Clean = array();

$Clean['SchoolCode'] = '';
$Clean['UniqueToken'] = md5(uniqid(rand(), true));

//	Other Variables
$Clean['ClassSectionID'] = '';
$Clean['Heading'] = '';
$Clean['Details'] = '';

if (isset($_REQUEST['SchoolCode']))
{
	$Clean['SchoolCode'] = strip_tags(trim((string) $_REQUEST['SchoolCode']));
}

if (isset($_REQUEST['Token']))
{
	$Clean['UniqueToken'] = strip_tags(trim((string) $_REQUEST['Token']));
}

if (isset($_REQUEST['ClassSectionID']))
{
	$Clean['ClassSectionID'] = strip_tags(trim((string) $_REQUEST['ClassSectionID']));
}

if (isset($_REQUEST['Heading']))
{
	$Clean['Heading'] = strip_tags(trim((string) $_REQUEST['Heading']));
}

if (isset($_REQUEST['Details']))
{
	$Clean['Details'] = strip_tags(trim((string) $_REQUEST['Details']));
}

try
{
	$LoggedInBranchStaff = new AppBranchStaff($Clean['UniqueToken']);

	$AllClassSections = array();
	$AllClassSections = $LoggedInBranchStaff->GetApplicableClassSections();

	$RecordValidator = new Validator();
    
    if (!array_key_exists($Clean['ClassSectionID'], $AllClassSections)) 
	{
		$Response->SetError(1);
		$Response->SetErrorCode(0);
		$Response->SetMessage('Invalid class section.');

		echo json_encode($Response->GetResponseAsArray());
		exit;
	}   
    	
    if ($Clean['Heading'] != '')
    {
        if (!$RecordValidator->ValidateStrings($Clean['Heading'], 'Heading is required and should be between 1 and 150 characters.', 1, 150)) 
    	{
    		$Response->SetError(1);
    		$Response->SetErrorCode(0);
    		$Response->SetMessage('Heading should be between 1 and 150 characters.');
    
    		echo json_encode($Response->GetResponseAsArray());
    		exit;
    	}   
    }
    
    if ($Clean['Details'] != '')
    {
        if (!$RecordValidator->ValidateStrings($Clean['Details'], 'Details should be between 1 and 1000 characters.', 1, 1000)) 
    	{
    		$Response->SetError(1);
    		$Response->SetErrorCode(0);
    		$Response->SetMessage('Details should be between 1 and 1000 characters.');
    
    		echo json_encode($Response->GetResponseAsArray());
    		exit;
    	}   
    }
	
	$NewStudentDiary = new StudentDiary();
				
	$NewStudentDiary->SetClassSectionID($Clean['ClassSectionID']);
	$NewStudentDiary->SetHeading($Clean['Heading']);
    $NewStudentDiary->SetDetails($Clean['Details']);
    
	$NewStudentDiary->SetIsActive(1);

    $NewStudentDiary->SetCreateUserID($LoggedInBranchStaff->GetUserID());
    
	if (!$NewStudentDiary->Save())
	{
		$RecordValidator->AttachTextError(ProcessErrors($RecordValidator->GetLastErrorCode()));
		$HasErrors = true;
		exit;
	}
	
	$ApplicableFor = array();
	$ApplicableFor[$Clean['ClassSectionID']]['ApplicableFor'] = 'ClassSection';
	$ApplicableFor[$Clean['ClassSectionID']]['StaffOrClassID'] = $Clean['ClassSectionID'];
			
	FcmSendNotification::SendNoticeNotification($Clean['Heading'], $ApplicableFor);

	$Response->SetMessage(ProcessAppMessages(SAVED_SUCCESSFULLY));
}
catch (ApplicationDBException $e)
{
    $Response->SetError(1);
	$Response->SetErrorCode(UNKNOWN_ERROR);
	$Response->SetMessage(ProcessAppErrors(UNKNOWN_ERROR));

	echo json_encode($Response->GetResponseAsArray());
	exit;
}
catch (Exception $e)
{
	$Response->SetError(1);
	$Response->SetErrorCode(UNKNOWN_ERROR);
	$Response->SetMessage(ProcessAppErrors(UNKNOWN_ERROR));
	
	echo json_encode($Response->GetResponseAsArray());
	exit;
}

echo json_encode($Response->GetResponseAsArray());
exit;
?>