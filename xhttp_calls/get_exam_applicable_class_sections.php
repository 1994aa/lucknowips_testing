<?php
//header('Content-Type: application/json');

require_once('../classes/class.users.php');
require_once('../classes/class.authentication.php');

try
{
	$AuthObject = new ApplicationAuthentication;
	$LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e)
{
	echo 'error|*****|Unknown error, please try again.';
	exit;
}
catch (Exception $e)
{
	echo 'error|*****|Unknown error, please try again.';
	exit;
}

require_once('../classes/examination/class.exams.php');

$ExamTypeID = 0;
$ClassID = 0;
$AcademicYearID = 0;

if (isset($_POST['SelectedExamType']))
{
	$ExamTypeID = (int) $_POST['SelectedExamType'];
}
if (isset($_POST['SelectedClass']))
{
	$ClassID = (int) $_POST['SelectedClass'];
}
if (isset($_POST['SelectedAcademicYearID']))
{
	$AcademicYearID = (int) $_POST['SelectedAcademicYearID'];
}

if ($ExamTypeID <= 0 && $ClassID <= 0 && $AcademicYearID <= 0)
{
	echo 'error|*****|Unknown error, please try again.';
	exit;
}

$AllClassSections = array();
$AllClassSections = Exam::GetExamApplicableClassSections($ExamTypeID, $ClassID, $AcademicYearID);

if (count($AllClassSections) <= 0)
{
	echo 'error|*****|No records found.';
	exit;
}

echo 'success|*****|';

foreach ($AllClassSections as $ClassSectionID => $ClassSectionName)
{
	echo '<option value="'. $ClassSectionID .'">'. $ClassSectionName .'</option>';
}

exit;
?>