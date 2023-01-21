<?php
ob_start();
set_time_limit(1800);

function split_name($name)
{
    $name = trim($name);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));
    return array($first_name, $last_name);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Upload Student Excel</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once('../classes/class.helpers.php');
    require_once('../classes/class.db_connect.php');

    require_once('../classes/school_administration/class.students.php');
    require_once('../classes/school_administration/class.student_details.php');
    require_once('../classes/school_administration/class.parent_details.php');

    $Clean = array();

    $Clean['StartFromRow'] = 1;
    $Clean['UploadingFileName'] = '';

    $Clean['Process'] = 0;

    if (isset($_POST['hdnProcess'])) {
        $Clean['Process'] = (int) $_POST['hdnProcess'];
    }

    switch ($Clean['Process']) {
        case 1:

            if (isset($_POST['txtStartFromRow'])) {
                $Clean['StartFromRow'] = (int) $_POST['txtStartFromRow'];
            }

            $UploadedFile = array();

            if (isset($_FILES['fileExcel'])) {
                $UploadedFile = $_FILES['fileExcel'];
            }

            if ($UploadedFile['error'] != 0) {
                $MSG = 'file not uploaded';
                break;
            }

            require_once("../includes/PHPExcel/PHPExcel/IOFactory.php");

            try {
                $inputFileType = PHPExcel_IOFactory::identify($UploadedFile['tmp_name']);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($UploadedFile['tmp_name']);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($UploadedFile['tmp_name'], PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            //  Get worksheet dimensions
            $CurrentSheet = $objPHPExcel->getSheet(0);
            $highestRow = $CurrentSheet->getHighestRow();
            $highestColumn = $CurrentSheet->getHighestColumn();

            $InvalidData = array();

            $Counter = 0;
            $InsertedRecordCounter = 1;

            $DatabaseName = 'addedschools_lucknowips_testing-23-24';

            //  Loop through each row of the worksheet in turn
            for ($row = $Clean['StartFromRow']; $row <= $highestRow; $row++) {
                //  Read a row of data into an array
                //$rowData = $CurrentSheet->rangeToArray('A' . $row . ':' . 'S' . $row, NULL, TRUE, FALSE);
                $rowData = $CurrentSheet->rangeToArray('A' . $row . ':' . 'L' . $row, NULL, TRUE, FALSE);

                foreach ($rowData as $key => $Details) {
                    $StudentData = array();

                    $StudentData['AdmissionDate'] = date('Y-m-d');
                    $StudentData['StudentName'] = '';
                    $StudentData['Gender'] = '';
                    $StudentData['Category'] = '';
                    $StudentData['DOB'] = '';
                    $StudentData['StudentAdharNumber'] = '';
                    $StudentData['Address'] = '';
                    $StudentData['PIN'] = '';
                    $StudentData['FatherName'] = '';
                    $StudentData['MotherName'] = '';
                    $StudentData['FatherMobileNumber'] = '';
                    $StudentData['MotherMobileNumber'] = '';
                    $StudentData['ParentAdharNumber'] = '';
                    $StudentData['RollNo'] = '';
                    $StudentData['FeeCode'] = '';
                    $StudentData['SRNO'] = '';
                    $StudentData['ClassSectionID'] = 0;
                    $StudentData['HouseID'] = 0;
                    $StudentData['BloodGroup'] = '';
                    $StudentData['AcademicYearID'] = 2;

                    // echo '<pre>';
                    // print_r($Details);exit;
                    
                    if (isset($Details[0])) {
                        $StudentData['FeeCode'] = $Details[0];
                    }
                    if (isset($Details[1])) {
                        $StudentData['StudentName'] = $Details[1];
                    }
                    if (isset($Details[2])) {
                        $StudentData['ClassSectionID'] = $Details[2];
                    }
                    if (isset($Details[3])) {
                        $StudentData['Gender'] = 'Female';

                        if ($Details[3] == 'MALE' || $Details[3] == 'M')
                        {
                            $StudentData['Gender'] = 'Male';
                        }
                    }
                    if (isset($Details[4])) {
                        $StudentData['FatherName'] = $Details[4];
                    }
                    if (isset($Details[5])) {
                        $StudentData['MotherName'] = $Details[5];
                    }
                    if (isset($Details[6])) {
                        $StudentData['Address'] = $Details[6];
                    }
                    if (isset($Details[7])) {
                        $StudentData['FatherMobileNumber'] = $Details[7];
                    }
                    if (isset($Details[8])) {
                        $StudentData['MotherMobileNumber'] = $Details[8];
                    }
                    if (isset($Details[9])) {
                        if ($Details[9] != 'General' && $Details[9] != 'OBC' && $Details[9] != 'SC' && $Details[9] != 'ST'){
                            $StudentData['Category'] = 'General';
                        }
                        else{
                            $StudentData['Category'] = $Details[9];
                        }
                    }
                    
                    if (isset($Details[10])) {
                        $StudentData['DOB'] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($Details[10]));
                    }
                    if (isset($Details[11])) {
                        $StudentData['SRNO'] = $Details[11];
                    }
                    
                    // if (isset($Details[5])) {
                    //     $StudentData['StudentAdharNumber'] = $Details[5];
                    // }
                    // if (isset($Details[7])) {
                    //     $StudentData['PIN'] = $Details[7];
                    // }
                    // if (isset($Details[12])) {
                    //     $StudentData['ParentAdharNumber'] = $Details[12];
                    // }
                    // if (isset($Details[13])) {
                    //     $StudentData['RollNo'] = $Details[13];
                    // }
                    // if (isset($Details[17])) {
                    //     $StudentData['HouseID'] = $Details[17];
                    // }
                    // if (isset($Details[18])) {
                    //     $StudentData['BloodGroup'] = $Details[18];
                    // }

                    /************************/
                    $StudentNameSplitResult = split_name($StudentData['StudentName']);

                    $StudentData['StudentFirstName'] = $StudentNameSplitResult[0];
                    $StudentData['StudentLastName'] = $StudentNameSplitResult[1];

                    $FatherNameSplitResult = split_name($StudentData['FatherName']);

                    $StudentData['FatherFirstName'] = $FatherNameSplitResult[0];
                    $StudentData['FatherLastName'] = $FatherNameSplitResult[1];

                    $MotherNameSplitResult = split_name($StudentData['MotherName']);

                    $StudentData['MotherFirstName'] = $MotherNameSplitResult[0];
                    $StudentData['MotherLastName'] = $MotherNameSplitResult[1];
                    
                    
                    if (empty($StudentData['StudentFirstName'])) {
                        continue;
                    }

                    $Counter++;

                    $NewStudentDetail = new StudentDetail();

                    $NewStudentDetail->SetAdmissionDate($StudentData['AdmissionDate']);
                    $NewStudentDetail->SetClassSectionID($StudentData['ClassSectionID']);
                    $NewStudentDetail->SetColourHouseID($StudentData['HouseID']);
                    $NewStudentDetail->SetRollNumber($StudentData['RollNo']);
                    $NewStudentDetail->SetEnrollmentID($StudentData['SRNO']);

                    $NewStudentDetail->SetFirstName($StudentData['StudentFirstName']);
                    $NewStudentDetail->SetLastName($StudentData['StudentLastName']);

                    $NewStudentDetail->SetAadharNumber($StudentData['StudentAdharNumber']);
                    $NewStudentDetail->SetDOB($StudentData['DOB']);

                    $NewStudentDetail->SetAddress1($StudentData['Address']);
                    $NewStudentDetail->SetPinCode($StudentData['PIN']);

                    $NewStudentDetail->SetGender($StudentData['Gender']);
                    $NewStudentDetail->SetCategory($StudentData['Category']);
                    $NewStudentDetail->SetStatus('Active');

                    $NewStudentDetail->SetMobileNumber($StudentData['FatherMobileNumber']);

                    $NewStudentDetail->SetCreateUserID('1000005');

                    // echo '<pre>';
                    // print_r($NewStudentDetail);exit;

                    //Parent Details

                    $NewParentDetail = new ParentDetail();

                    $NewParentDetail->SetFatherFirstName($StudentData['FatherFirstName']);
                    $NewParentDetail->SetFatherLastName($StudentData['FatherLastName']);
                    $NewParentDetail->SetMotherFirstName($StudentData['MotherFirstName']);
                    $NewParentDetail->SetMotherLastName($StudentData['MotherLastName']);

                    $NewParentDetail->SetFatherMobileNumber($StudentData['FatherMobileNumber']);
                    $NewParentDetail->SetAadharNumber($StudentData['ParentAdharNumber']);
                    $NewParentDetail->SetFeeCode($StudentData['FeeCode']);
                    $NewParentDetail->SetFatherMobileNumber($StudentData['FatherMobileNumber']);

                    $NewParentDetail->SetIsActive(1);

                    if (!$NewStudentDetail->Save($NewParentDetail)) {
                        error_log('Criticle Error: Error in saving at row = ' . $InsertedRecordCounter);
                    }

                    if ($StudentData['FatherFirstName'] == '' && $StudentData['FatherLastName'] == '') {
                        $StudentData['FatherFirstName'] = 'Default';
                    }

                    $UniqueID = Helpers::GenerateUniqueAddedID($StudentData['FatherFirstName'] . $StudentData['FatherLastName'], date('Y-m-d', strtotime($StudentData['DOB'])));

                    if ($UniqueID != '') {
                        $NewParentDetail->SetUserName($UniqueID);

                        if ($NewStudentDetail->Save($NewParentDetail)) {
                            if (!Helpers::SaveUniqueID($UniqueID, '')) {
                                error_log('Criticle Error: Generated Unique ID could not be saved into Added Central DB. StudentID: ' . $NewStudentDetail->GetStudentID() . ' UniqueID: ' . $UniqueID);
                            }
                        } else {
                            $NewRecordValidator->AttachTextError(ProcessErrors($NewStudentDetail->GetLastErrorCode()));
                            $HasErrors = true;

                            break;
                        }
                    } else {
                        error_log('Criticle Error: Generated Unique ID found balnk. StudentID: ' . $NewStudentDetail->GetStudentID());
                    }

                    $InsertedRecordCounter++;
                }
            }

            echo 'Uploaded Successfully student count =' . $Counter;
            exit;
            break;
    }
    ?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</head>

<body>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Upload Student Excel</h2>
            </div>
            <div class="panel-body">
                <form action="" class="form-horizontal" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="BusinessMonth" class="col-lg-2 control-label">Start From Row: </label>
                        <div class="col-lg-4">
                            <input type="number" class="form-control" name="txtStartFromRow" value="<?php echo $Clean['StartFromRow']; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="BusinessMonth" class="col-lg-2 control-label">Upload Excel: </label>
                        <div class="col-lg-4">
                            <input type="file" name="fileExcel" />
                        </div>
                    </div>
                    <div class="col-sm-offset-2 col-lg-10">
                        <div class="form-group">
                            <input type="hidden" name="hdnProcess" value="1" />
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>