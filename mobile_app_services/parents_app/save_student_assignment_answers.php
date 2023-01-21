<?php
// error_log(__dir__);
// error_log(json_encode($_REQUEST));

// if (isset($_FILES))
// {
//     error_log(json_encode($_FILES));
// }

header("Content-Type:application/json");

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.users.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.validation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.authentication.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.date_processing.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/classes/class.json_response.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/includes/global_defaults.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/includes/process_errors.php');

//	Other Required Classess
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.classes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.class_sections.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/academic_supervision/class.chapters.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/academic_supervision/class.chapter_topics.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/academic_supervision/class.student_assignment.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.parent_details.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.students.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/school_administration/class.student_details.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/classes/class.app.student_details.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mobile_app_services/classes/class.app.parent_details.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/global_defaults.inc.php');

$Response = new JSONResponse();

$Clean = array();

$Clean['Token'] = 'd21e9fa63bf7597f95d88ee492d212d3';

$Clean['StudentID'] = 74;

//	Other Variables
$Clean['AssignmentID'] = 11;
$Clean['UploadFile'] = array();

$acceptable_extensions = array('jpeg', 'jpg', 'png', 'gif');

$acceptable_mime_types = array(
    'image/jpeg',
    'image/jpg', 
    'image/png', 
    'image/gif' 
);

if (isset($_REQUEST['Token']))
{
	$Clean['Token'] = strip_tags(trim((string) $_REQUEST['Token']));
}

if (isset($_REQUEST['StudentID']))
{
	$Clean['StudentID'] = strip_tags(trim((string) $_REQUEST['StudentID']));
}

if (isset($_REQUEST['AssignmentID']))
{
	$Clean['AssignmentID'] = strip_tags(trim((string) $_REQUEST['AssignmentID']));
}

if (isset($_FILES['AssignmentImage']) && is_array($_FILES['AssignmentImage']) )
{
    $Clean['UploadFile'] = $_FILES['AssignmentImage'];
}

try
{
	$LoggedInParent = new AppParentDetail($Clean['Token']);
	
	//var_dump($LoggedInParent);exit;

	$StudentDetails = new AppStudentDetail($Clean['StudentID']);
	
	$StudentAssignmentAnswer = new StudentAssignment($Clean['AssignmentID']);

	$RecordValidator = new Validator();

	$FileName = '';
    $FileExtension = '';

    if (count($Clean['UploadFile']) > 0 && $Clean['UploadFile']['error'] != 4) 
    {
        if ($Clean['UploadFile']['size'] > MAX_UPLOADED_FILE_SIZE || $Clean['UploadFile']['size'] <= 0) 
        {	
        	$Response->SetError(1);
			$Response->SetErrorCode(0);
			$Response->SetMessage('File size cannot be greater than ' . (MAX_UPLOADED_FILE_SIZE / 1024 /1024) . ' MB.');

			echo json_encode($Response->GetResponseAsArray());
			exit;
        }

        $FileExtension = strtolower(pathinfo($Clean['UploadFile']['name'], PATHINFO_EXTENSION));

        if ($FileExtension != 'pdf')
        {
            if (!in_array($Clean['UploadFile']['type'], $acceptable_mime_types) || !in_array($FileExtension, $acceptable_extensions))
            {	
            	if ($Clean['UploadFile']['size'] > MAX_UPLOADED_FILE_SIZE || $Clean['UploadFile']['size'] <= 0) 
    	        {	
    	        	$Response->SetError(1);
    				$Response->SetErrorCode(0);
    				$Response->SetMessage('File size cannot be greater than ' . (MAX_UPLOADED_FILE_SIZE / 1024 /1024) . ' MB.');
    
    				echo json_encode($Response->GetResponseAsArray());
    				exit;
    	        }
            }   
        }

        $FileName = $Clean['UploadFile']['name'];
    }
	
	if ($FileName != '') 
    {
        if (!is_dir(SITE_FS_PATH . '/site_images/student_assignment_answers'))
        {
            mkdir(SITE_FS_PATH . '/site_images/student_assignment_answers');
        }

        $UniqueUserFileUploadDirectory = SITE_FS_PATH . '/site_images/student_assignment_answers/' . $StudentAssignmentAnswer->GetAssignmentID().'/';

        if (!is_dir($UniqueUserFileUploadDirectory))
        {
            mkdir($UniqueUserFileUploadDirectory);
        }

        // variable for to get last inserted id
        $AssignmentAnswerID = 0;

        //insert image name into to the table
        $StudentAssignmentAnswer->SaveAssignmentAnswers($Clean['StudentID'], $FileName, 0, $AssignmentAnswerID);

        // Generate a Unique Name for the uploaded document
        $FileName = md5(uniqid(rand(), true) . $AssignmentAnswerID) . '.' . $FileExtension;

        //updating unique image name into to the table
        $StudentAssignmentAnswer->SaveAssignmentAnswers($Clean['StudentID'], $FileName, 0, $AssignmentAnswerID);
        
        if ($FileExtension != 'pdf')
        {
            #Compressing image code start
            $source = $Clean['UploadFile']['tmp_name'];
    		$imgInfo = getimagesize($Clean['UploadFile']['tmp_name']); 
    		$mime = $imgInfo['mime']; 
    
    		switch ($mime)
    		{
    			case 'image/jpeg':
    				$image = imagecreatefromjpeg($source);
    				break;
    			case 'image/png':
    				$image = imagecreatefrompng($source);
    				break;
    			case 'image/gif':
    				$image = imagecreatefromgif($source);
    				break;
    			default:
    				$image = imagecreatefromjpeg($source);
    		}
    		
    		#Compress and move uploaded file
    		imagejpeg($image, $UniqueUserFileUploadDirectory . $FileName, 75); 
    		#Compressing image code end
        }
        else
        {
            move_uploaded_file($Clean['UploadFile']['tmp_name'], $UniqueUserFileUploadDirectory . $FileName);
        }
    }

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