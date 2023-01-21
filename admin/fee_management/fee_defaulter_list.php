<?php

set_time_limit(1800);

require_once('../../classes/class.users.php');
require_once('../../classes/class.validation.php');
require_once('../../classes/class.authentication.php');

require_once('../../classes/class.ui_helpers.php');
require_once('../../classes/class.sms_queue.php');

require_once('../../classes/school_administration/class.academic_years.php');
require_once('../../classes/school_administration/class.academic_year_months.php');
require_once('../../classes/school_administration/class.classes.php');
require_once('../../classes/school_administration/class.class_sections.php');

require_once('../../classes/school_administration/class.students.php');
require_once('../../classes/school_administration/class.student_details.php');

require_once('../../classes/school_administration/class.parent_details.php');
require_once('../../classes/fee_management/class.late_fee_rules.php');

require_once('../../classes/fee_management/class.fee_heads.php');
require_once('../../classes/fee_management/class.fee_collection.php');

require_once('../../classes/class.global_settings.php');

require_once('../../includes/global_defaults.inc.php');

//1. RECHECK IF THE USER IS VALID //
try {
    $AuthObject = new ApplicationAuthentication;
    $LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e) {
    header('location:/admin/unauthorized_login_admin.php');
    exit;
} catch (Exception $e) {
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

if ($LoggedUser->HasPermissionForTask(FEE_DEFAULTER) !== true) {
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}

$StudentStatusList = array('Active' => 'Active', 'InActive' => 'InActive');
$PaymentModeList = array(1 => 'Cash', 2 => 'Cheque', 3 => 'Net Transfer');

$Filters = array();

$ClassList = array();
$ClassList = AddedClass::GetActiveClasses();

$FeeHeadList = array();
$FeeHeadList = FeeHead::GetActiveFeeHeads();

$FeeHeads = [];
foreach ($FeeHeadList as $HeadID => $Head) {
    $FeeHeads[$HeadID] = $Head['FeeHead'];
}

$AcademicYears = array();
$AcademicYears = AcademicYear::GetAllAcademicYears();

$AcademicYearMonths = array();
$AcademicYearMonths = AcademicYearMonth::GetMonthsByFeePriority();

$GlobalSettingObject = new GlobalSetting();

$FeeSubmissionLastDate = '';
$FeeSubmissionFrequency = 0;
$FeeSubmissionType = '';

$FeeSubmissionLastDate = $GlobalSettingObject->GetFeeSubmissionLastDate();
$FeeSubmissionFrequency = $GlobalSettingObject->GetFeeSubmissionFrequency();
$FeeSubmissionType = $GlobalSettingObject->GetFeeSubmissionType();

$AcademicYearMonthID = 0;
$AcademicYearMonthID = AcademicYearMonth::GetMonthIDByMonthName(date('M'));

$FeePriority = 0;

// foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value)
// {
// 	if (array_key_exists($AcademicYearMonthID, $Value))
// 	{
// 		end($Value);
// 		$FeePriority = $Value[key($Value)]['FeePriority'];
// 	}
// }

$ClassSectionsList = array();
$StudentsList = array();

$ActiveFeeHeads = array();
$ActiveFeeHeads = FeeHead::GetActiveFeeHeads();

$DefaulterList = array();

$RecordDeletedSuccessfully = false;
$HasErrors = false;
$TotalRecords = 0;

$Clean = array();
$Clean['Process'] = 0;

$Clean['AcademicYearID'] = 0;
$Clean['ClassID'] = 0;
$Clean['ClassSectionID'] = 0;
$Clean['StudentID'] = 0;
$Clean['FeeHeadID'] = 0;
$Clean['MobileNumber'] = '';
$Clean['DueMonths'] = 0;

$Clean['Status'] = 'Active';
$Clean['StudentName'] = '';
$Clean['MonthList'] = array();

$SelectedMonths = '';

// paging and sorting variables start here  //

$Clean['AllRecords'] = '';
$Clean['CurrentPage'] = 1;
$TotalPages = 0;

$Start = 0;
$Limit = 50;

$DefaulterList = array();
$SMSSendSuccessfully = false;

// end of paging variables//

if (isset($_GET['hdnProcess'])) {
    $Clean['Process'] = (int) $_GET['hdnProcess'];
} elseif (isset($_GET['Process'])) {
    $Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process']) {
    case 7:

        if (isset($_GET['drdAcademicYear'])) {
            $Clean['AcademicYearID'] = (int) $_GET['drdAcademicYear'];
        } else if (isset($_GET['AcademicYearID'])) {
            $Clean['AcademicYearID'] = (int) $_GET['AcademicYearID'];
        }

        if (isset($_GET['drdClass'])) {
            $Clean['ClassID'] = strip_tags(trim($_GET['drdClass']));
        } elseif (isset($_GET['ClassID'])) {
            $Clean['ClassID'] = strip_tags(trim($_GET['ClassID']));
        }

        if (isset($_GET['drdClassSection'])) {
            $Clean['ClassSectionID'] = strip_tags(trim($_GET['drdClassSection']));
        } elseif (isset($_GET['ClassSectionID'])) {
            $Clean['ClassSectionID'] = strip_tags(trim($_GET['ClassSectionID']));
        }

        if (isset($_GET['drdStudent'])) {
            $Clean['StudentID'] = strip_tags(trim($_GET['drdStudent']));
        } elseif (isset($_GET['StudentID'])) {
            $Clean['StudentID'] = strip_tags(trim($_GET['StudentID']));
        }

        if (isset($_GET['drdFeeHead'])) {
            $Clean['FeeHeadID'] = strip_tags(trim($_GET['drdFeeHead']));
        } elseif (isset($_GET['FeeHeadID'])) {
            $Clean['FeeHeadID'] = strip_tags(trim($_GET['FeeHeadID']));
        }

        if (isset($_GET['optStatus'])) {
            $Clean['Status'] = strip_tags(trim((string) $_GET['optStatus']));
        } elseif (isset($_GET['Status'])) {
            $Clean['Status'] = strip_tags(trim((string) $_GET['Status']));
        }

        if (isset($_GET['txtStudentName'])) {
            $Clean['StudentName'] = strip_tags(trim($_GET['txtStudentName']));
        } elseif (isset($_GET['StudentName'])) {
            $Clean['StudentName'] = strip_tags(trim($_GET['StudentName']));
        }

        if (isset($_GET['txtMobileNumber'])) {
            $Clean['MobileNumber'] = strip_tags(trim($_GET['txtMobileNumber']));
        } else if (isset($_GET['MobileNumber'])) {
            $Clean['MobileNumber'] = strip_tags(trim((string) $_GET['MobileNumber']));
        }

        if (isset($_GET['chkMonth']) && is_array($_GET['chkMonth'])) {
            $Clean['MonthList'] = $_GET['chkMonth'];
        } elseif (isset($_GET['MonthList'])) {
            $SelectedMonths = $_GET['MonthList'];
        }

        if ($SelectedMonths != '') {
            $Clean['MonthList'] = explode(',', $SelectedMonths);
        }

        $SearchValidator = new Validator();

        if ($Clean['ClassID'] > 0) {
            $SearchValidator->ValidateInSelect($Clean['ClassID'], $ClassList, 'Unknown error, please try again.');
            $ClassSectionsList = ClassSections::GetClassSections($Clean['ClassID']);
        }

        if ($Clean['ClassSectionID'] > 0) {
            $SearchValidator->ValidateInSelect($Clean['ClassSectionID'], $ClassSectionsList, 'Unknown error, please try again.');

            $StudentsList = StudentDetail::GetStudentsByClassSectionID($Clean['ClassSectionID'], 'Active', $Clean['AcademicYearID']);

            if ($Clean['StudentID'] > 0) {
                $SearchValidator->ValidateInSelect($Clean['StudentID'], $StudentsList, 'Please select a valid student.');
            }
        }

        if ($Clean['FeeHeadID'] > 0) {
            $SearchValidator->ValidateInSelect($Clean['FeeHeadID'], $ActiveFeeHeads, 'Please select a valid fee head.');
        }

        if ($Clean['Status'] != '') {
            $SearchValidator->ValidateInSelect($Clean['Status'], $StudentStatusList, 'Unknown Error in status, Please try again.');
        }

        if ($Clean['StudentName'] != '') {
            $SearchValidator->ValidateStrings($Clean['StudentName'], 'Student name should be between 2 to 50.', 2, 50);
        }

        if ($Clean['MobileNumber'] != '') {
            $SearchValidator->ValidateStrings($Clean['MobileNumber'], 'Mobile number should be between 1 to 15.', 1, 15);
        }

        if (count($Clean['MonthList']) > 0) {
            $FeePriority = $AcademicYearMonths[end($Clean['MonthList'])]['FeePriority'];
        }

        //pending for review
        if ($FeePriority == 0 && $Clean['AcademicYearID'] == 2 && isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') {
            foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value) {
                if (array_key_exists($AcademicYearMonthID, $Value)) {
                    end($Value);
                    $FeePriority = $Value[key($Value)]['FeePriority'];
                }
            }
        }
        // else if ($FeePriority == 0 && $Clean['AcademicYearID'] == 3 && isset($_SESSION['DB']) && $_SESSION['DB'] == 'addedschools_lucknowips_testing-22-23') {
        //     foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value) {
        //         if (array_key_exists($AcademicYearMonthID, $Value)) {
        //             end($Value);
        //             $FeePriority = $Value[key($Value)]['FeePriority'];
        //         }
        //     }
        // } else {
        //     foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value) {
        //         if (array_key_exists($AcademicYearMonthID, $Value)) {
        //             end($Value);
        //             $FeePriority = $Value[key($Value)]['FeePriority'];
        //         }
        //     }
        // }

        $SelectedMonths = '';
        $SelectedMonths = implode(',', $Clean['MonthList']);

        if ($SearchValidator->HasNotifications()) {
            $HasErrors = true;
            break;
        }

        //set record filters    

        $Filters['AcademicYearID'] = $Clean['AcademicYearID'];
        $Filters['ClassID'] = $Clean['ClassID'];
        $Filters['ClassSectionID'] = $Clean['ClassSectionID'];
        $Filters['FeeHeadID'] = $Clean['FeeHeadID'];
        $Filters['StudentID'] = $Clean['StudentID'];
        $Filters['StudentName'] = $Clean['StudentName'];
        $Filters['MobileNumber'] = $Clean['MobileNumber'];
        $Filters['Status'] = $Clean['Status'];
        // $Filters['DueToDate'] = date('Y-m-d');
        $Filters['DueToDate'] = '';
        $Filters['AcademicYearMonthID'] = $SelectedMonths;


        // var_dump($Filters);exit;

        //get records count
        FeeCollection::SearchDateWiseDefaulter($TotalRecords, true, $Filters, $FeePriority);
        if ($TotalRecords > 0) {
            // Paging and sorting calculations start here.
            $TotalPages = (($TotalRecords % $Limit) == 0) ? $TotalRecords / $Limit : floor($TotalRecords / $Limit) + 1;

            if (isset($_GET['CurrentPage'])) {
                $Clean['CurrentPage'] = (int) $_GET['CurrentPage'];
            }

            if (isset($_GET['AllRecords'])) {
                $Clean['AllRecords'] = (string) $_GET['AllRecords'];
            }

            if ($Clean['CurrentPage'] <= 0) {
                $Clean['CurrentPage'] = 1;
            } elseif ($Clean['CurrentPage'] > $TotalPages) {
                $Clean['CurrentPage'] = $TotalPages;
            }

            if ($Clean['CurrentPage'] > 1) {
                $Start = ($Clean['CurrentPage'] - 1) * $Limit;
            }
            // end of Paging and sorting calculations.
            // now get the actual  records

            if (isset($_GET['report_submit']) && $_GET['report_submit'] == 2) {
                require_once('../excel/fee_defaulter_list_download_xls.php');
            }
            if ($Clean['AllRecords'] == 'All') {
                $DefaulterList = FeeCollection::SearchDateWiseDefaulter($TotalRecords, false, $Filters, $FeePriority, 0, $TotalRecords);
            } else {
                $DefaulterList = FeeCollection::SearchDateWiseDefaulter($TotalRecords, false, $Filters, $FeePriority, $Start, $Limit);
            }
        }
        break;
}

$LandingPageMode = '';
if (isset($_GET['Mode'])) {
    $LandingPageMode = $_GET['Mode'];
}

require_once('../html_header.php');
?>
<title>Defaulter List New</title>
<!-- DataTables CSS -->
<link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
<link href="../vendor/jquery-ui/jquery-ui.min.css" rel="stylesheet">
</head>

<body>

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <?php
            require_once('../site_header.php');
            require_once('../left_navigation_menu.php');
            ?>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Defaulter List New</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <form class="form-horizontal" name="frmRoomReport" action="fee_defaulter_list.php" method="get">
                <div class="panel panel-default" id="accordion">
                    <div class="panel-heading">
                        <strong><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Filters</a></strong>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <?php
                            if ($HasErrors == true) {
                                echo $SearchValidator->DisplayErrors();
                            } else if ($LandingPageMode == 'AS') {
                                echo '<div class="alert alert-success">Record saved successfully.</div>';
                            } else if ($SMSSendSuccessfully == true) {
                                echo '<div class="alert alert-danger">SMS Send successfully.</div>';
                            } else if ($LandingPageMode == 'UD') {
                                echo '<div class="alert alert-success">Record updated successfully.</div>';
                            }
                            ?>

                            <div class="form-group">
                                <label for="AcademicYear" class="col-lg-2 control-label">Academic Year</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="drdAcademicYear" id="AcademicYearID">
                                        <?php
                                        if (is_array($AcademicYears) && count($AcademicYears) > 0) {
                                            foreach ($AcademicYears as $AcademicYearID => $AcademicYearDetails) {
                                                if ($Clean['AcademicYearID'] == 0) {
                                                    if ($AcademicYearDetails['IsCurrentYear'] == 1) {
                                                        $Clean['AcademicYearID'] = $AcademicYearID;
                                                    }
                                                }

                                                echo '<option ' . ($Clean['AcademicYearID'] == $AcademicYearID ? 'selected="selected"' : '') . ' value="' . $AcademicYearID . '" >' . date('Y', strtotime($AcademicYearDetails['StartDate'])) . ' - ' . date('Y', strtotime($AcademicYearDetails['EndDate'])) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ClassList" class="col-lg-2 control-label">Class</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="drdClass" id="Class">
                                        <option value="0">Select Class</option>
                                        <?php
                                        foreach ($ClassList as $ClassID => $ClassName) {
                                        ?>
                                            <option <?php echo ($ClassID == $Clean['ClassID'] ? 'selected="selected"' : ''); ?> value="<?php echo $ClassID; ?>"><?php echo $ClassName; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <label for="ClassSection" class="col-lg-1 control-label">Section</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="drdClassSection" id="ClassSection">
                                        <option value="0">Select Section</option>
                                        <?php
                                        if (is_array($ClassSectionsList) && count($ClassSectionsList) > 0) {
                                            foreach ($ClassSectionsList as $ClassSectionID => $SectionName) {
                                                echo '<option ' . ($Clean['ClassSectionID'] == $ClassSectionID ? 'selected="selected"' : '') . ' value="' . $ClassSectionID . '">' . $SectionName . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Student" class="col-lg-2 control-label">Select Student</label>
                                <div class="col-lg-7">
                                    <select class="form-control" name="drdStudent" id="Student">
                                        <option value="0">Select Student</option>
                                        <?php
                                        if (is_array($StudentsList) && count($StudentsList) > 0) {
                                            foreach ($StudentsList as $StudentID => $StudentDetails) {
                                                echo '<option ' . ($Clean['StudentID'] == $StudentID ? 'selected="selected"' : '') . ' value="' . $StudentID . '">' . $StudentDetails['FirstName'] . ' ' . $StudentDetails['LastName'] . '(' . $StudentDetails['RollNumber'] . ')</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="StudentName" class="col-lg-2 control-label">Student Name</label>
                                <div class="col-lg-7">
                                    <input class="form-control" type="text" maxlength="50" id="StudentName" name="txtStudentName" value="<?php echo $Clean['StudentName']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="MobileNumber" class="col-lg-2 control-label">Mobile Number</label>
                                <div class="col-lg-7">
                                    <input class="form-control" type="text" maxlength="50" id="MobileNumber" name="txtMobileNumber" value="<?php echo $Clean['MobileNumber']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="FeeHeadList" class="col-lg-2 control-label">Fee Head</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="drdFeeHead" id="FeeHead">
                                        <option value="0">-- All Fee Head --</option>
                                        <?php
                                        foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHeadDetails) {
                                        ?>
                                            <option <?php echo ($FeeHeadID == $Clean['FeeHeadID'] ? 'selected="selected"' : ''); ?> value="<?php echo $FeeHeadID; ?>"><?php echo $FeeHeadDetails['FeeHead']; ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Month" class="col-lg-2 control-label">By Month</label>
                                <div class="col-lg-9">
                                    <?php
                                    foreach ($AcademicYearMonths as $AcademicYearMonthID => $MonthDetails) {
                                    ?>
                                        <label class="checkbox-inline">
                                            <input class="custom-radio chkAllMonth" type="checkbox" <?php echo (in_array($AcademicYearMonthID, $Clean['MonthList']) ? 'checked="checked"' : ''); ?> name="chkMonth[<?php echo $AcademicYearMonthID; ?>]" value="<?php echo $AcademicYearMonthID; ?>" />
                                            <?php echo $MonthDetails['MonthShortName']; ?>
                                        </label>
                                    <?php
                                    }
                                    ?>
                                    <label class="checkbox-inline">
                                        <input class="custom-radio " id="chkAllMonth" type="checkbox" <?php echo (count($Clean['MonthList']) == count($AcademicYearMonths) ? 'checked="checked"' : ''); ?> name="chkAllMonth" value="" />
                                        All
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Status" class="col-lg-2 control-label">Student Status</label>
                                <div class="col-lg-5">
                                    <?php
                                    foreach ($StudentStatusList as $StatusKey => $StatusName) {
                                    ?>
                                        <label class="col-sm-4"><input class="custom-radio" type="radio" id="<?php echo $StatusKey; ?>" name="optStatus" value="<?php echo $StatusKey; ?>" <?php echo (($Clean['Status'] == $StatusKey) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;<?php echo $StatusName; ?></label>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="hdnProcess" value="7" />
                                    <input type="hidden" name="report_submit" id="get_excel" value="0" />
                                    <button type="submit" class="btn btn-primary" id="SubmitSearch">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- /.row -->
            <?php
            if ($Clean['Process'] == 7 && $HasErrors == false) {
                $ReportHeaderText = '';

                if ($Clean['AcademicYearID'] != 0) {
                    $ReportHeaderText .= ' Session: ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['StartDate'])) . ' - ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['EndDate'])) . ',';
                }

                if ($Clean['ClassID'] > 0) {
                    $ReportHeaderText .= ' Class : ' . $ClassList[$Clean['ClassID']] . ',';
                }

                if ($Clean['ClassSectionID'] > 0) {
                    $ReportHeaderText .= ' Section : ' . $ClassSectionsList[$Clean['ClassSectionID']] . ',';
                }

                if ($Clean['StudentID'] > 0) {
                    $ReportHeaderText .= ' Student : ' . $StudentsList[$Clean['StudentID']]['FirstName'] . ',';
                }

                if ($Clean['FeeHeadID'] > 0) {
                    $ReportHeaderText .= ' Fee Head : ' . $ActiveFeeHeads[$Clean['FeeHeadID']]['FeeHead'] . ',';
                }

                if ($Clean['StudentName'] != '') {
                    $ReportHeaderText .= ' Student Name : ' . $Clean['StudentName'] . ',';
                }

                if ($Clean['MobileNumber'] != '') {
                    $ReportHeaderText .= ' Mobile Number : ' . $Clean['MobileNumber'] . ',';
                }

                if ($Clean['Status'] != '') {
                    $ReportHeaderText .= ' Status: ' . $Clean['Status'] . ' students,';
                }

                if (count($Clean['MonthList']) > 1) {
                    $ReportHeaderText .= ' Months ';
                }

                foreach ($Clean['MonthList'] as $Key => $MonthID) {
                    $ReportHeaderText .= ' ' . $AcademicYearMonths[$MonthID]['MonthName'] . ', ';
                }

                if ($ReportHeaderText != '') {
                    $ReportHeaderText = ' for' . rtrim($ReportHeaderText, ',');
                }
            ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong>Total Records Returned: <span id="totalRecordCount"></span></strong>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="print-btn-container">
                                                <button id="PrintButton" type="submit" class="btn btn-primary">Print</button>
                                                <button id="" onclick="$('#get_excel').val(2); $('#SubmitSearch').click();$('#get_excel').val(0);" type="submit" class="btn btn-primary">Export to Excel</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="RecordTableHeading">
                                        <div class="col-lg-12">
                                            <div class="report-heading-container"><strong>Defaulter List on
                                                    <?php echo date('d-m-Y h:i A') . $ReportHeaderText; ?></strong></div>
                                        </div>
                                    </div>
                                    <div class="row" id="RecordTable">
                                        <div class="col-lg-12">
                                            <table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
                                                <thead>
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Sr.No</th>
                                                        <th>Student Name</th>
                                                        <th>Class</th>
                                                        <th>Mobile No</th>
                                                        <th>Previous Year Due</th>
                                                        <?php
                                                        foreach ($FeeHeads as $FeeID => $FeeName) {
                                                            echo '<th>' . $FeeName . '</th>';
                                                        }
                                                        ?>
                                                        <th>Late Fee</th>
                                                        <th>Current Due</th>
                                                        <th>Total Due</th>
                                                        <th class="print-hidden">Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $TotalLateFee = 0;
                                                    $TotalPrevDueAmount = 0;
                                                    $FeeHeadDueAmount = [];
                                                    $TotalCurrentDueAmount = 0;
                                                    $TotalDueAmount = 0;

                                                    $Counter = 0;
                                                    foreach ($DefaulterList as $StudentID => $StudentDetails) {
                                                    ?>
                                                        <tr>
                                                            <td><?php echo ++$Counter; ?></td>
                                                            <td><?php echo $StudentDetails['EnrollmentID']; ?></td>
                                                            <td><?php echo $StudentDetails['StudentName']; ?></td>
                                                            <td><?php echo $StudentDetails['Class']; ?></td>
                                                            <td><?php echo $StudentDetails['FatherMobileNumber'] . '<br>' . $StudentDetails['MotherMobileNumber']; ?>
                                                            </td>
                                                            <td class="text-right">
                                                                <?php echo $StudentDetails['PreviousYearDueAmount']; ?></td>
                                                            <?php
                                                            foreach ($FeeHeads as $FeeID => $FeeName) {
                                                                if (!isset($FeeHeadDueAmount[$FeeID])) {
                                                                    $FeeHeadDueAmount[$FeeID] = 0;
                                                                }
                                                                $FeeHeadDueAmount[$FeeID] += (isset($StudentDetails['FeeHeadDetails'][$FeeID]) ? $StudentDetails['FeeHeadDetails'][$FeeID] : 0);
                                                            ?>
                                                                <td class="text-right">
                                                                    <?php echo (isset($StudentDetails['FeeHeadDetails'][$FeeID]) ? $StudentDetails['FeeHeadDetails'][$FeeID] : 0); ?>
                                                                </td>
                                                            <?php
                                                            }
                                                            $AcademicYearID = 0;
                                                            $AcademicYearName = '';

                                                            $AcademicYearID = AcademicYear::GetCurrentAcademicYear($AcademicYearName);

                                                            $AcademicYearExplode = explode('-', $AcademicYearName);

                                                            $MonthWiseLateDays = array();
                                                            $LateFeeRules = array();

                                                            $LateCharge = 0;
                                                            $ChargeMethod = '';
                                                            $FeeMonths = array();
                                                            $DueFeeMonths = array();
                                                            $PreviousYearDue = array();
                                                            $StudentAdvanceFee = 0;

                                                            try {
                                                                $StudentDetailObject = new StudentDetail($StudentID);

                                                                $ClassSectionObj = new ClassSections($StudentDetailObject->GetClassSectionID());
                                                            } catch (ApplicationDBException $e) {
                                                                header('location:/admin/error.php');
                                                                exit;
                                                            } catch (Exception $e) {
                                                                header('location:/admin/error.php');
                                                                exit;
                                                            }

                                                            $FeeMonths[$StudentID] = $StudentDetailObject->GetStudentFeeMonths();

                                                            $DueFeeMonths[$StudentID] = $StudentDetailObject->GetStudentDueFeeMonths();

                                                            $PreviousYearDue[$StudentID] = $StudentDetailObject->GetStudentPreviousYearDue();

                                                            $StudentAdvanceFee = $StudentDetailObject->GetStudentAdvanceFee();

                                                            // Late Fee Calculation
                                                            foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value) {
                                                                if (array_key_exists($AcademicYearMonthID, $Value)) {
                                                                    end($Value);
                                                                    $DefaultedLastFeeMonthPriority = $Value[key($Value)]['FeePriority'];
                                                                }
                                                            }

                                                            $DefaultedFeeMonths = array();
                                                            $DefaultedFeeMonths[$StudentID] = FeeCollection::GetDefaultedFeeMonths($StudentID, $DefaultedLastFeeMonthPriority);

                                                            $CurrentDate = time();
                                                            $LateDays = 0;
                                                            $Clean['LateFeeAmount'] = 0;

                                                            foreach (array_chunk($AcademicYearMonths, $FeeSubmissionFrequency, true) as $Key => $Value) {
                                                                $FeeSubmissionMonthID = key($Value);

                                                                foreach ($Value as $MonthID => $Details) {
                                                                    if (array_key_exists($MonthID, $DefaultedFeeMonths[$StudentID])) {
                                                                        if ($Value[$FeeSubmissionMonthID]['MonthName'] == 'January' || $Value[$FeeSubmissionMonthID]['MonthName'] == 'February' || $Value[$FeeSubmissionMonthID]['MonthName'] == 'March') {
                                                                            $FeeSubmissionDate = $FeeSubmissionLastDate . ' ' . $Value[$FeeSubmissionMonthID]['MonthName'] . ' ' . $AcademicYearExplode[1];
                                                                        } else {
                                                                            $FeeSubmissionDate = $FeeSubmissionLastDate . ' ' . $Value[$FeeSubmissionMonthID]['MonthName'] . ' ' . $AcademicYearExplode[0];
                                                                        }

                                                                        $LateDays = intval(($CurrentDate - strtotime($FeeSubmissionDate)) / 86400);
                                                                        if ($LateDays > 0) {
                                                                            if ($Details["MonthName"] != 'April' && $Details["MonthName"] != 'May' && $Details["MonthName"] != 'June'  && $Details["MonthName"] != 'July'  && $Details["MonthName"] != 'August') {
                                                                                $MonthWiseLateDays[$MonthID] = $LateDays;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            $TotalLateCharge = 0;

                                                            $LateFeeRules = LateFeeRule::GetAllLateFeeRules();

                                                            $TotalLateCharge = 0;
                                                            foreach ($MonthWiseLateDays as $MonthID => $LateDays) {
                                                                foreach ($LateFeeRules as $Key => $Details) {
                                                                    if ($LateDays >= $Details['RangeFromDay']) {
                                                                        $LateCharge = $Details['LateFeeAmount'];
                                                                        $ChargeMethod = $Details['ChargeMethod'];
                                                                        break;
                                                                    } else {
                                                                        end($LateFeeRules);
                                                                        $LateCharge = $LateFeeRules[key($LateFeeRules)]['LateFeeAmount'];
                                                                        $ChargeMethod = $LateFeeRules[key($LateFeeRules)]['ChargeMethod'];
                                                                    }
                                                                }

                                                                $TotalCharges = 0;
                                                                if ($ChargeMethod == 'PerDay') {
                                                                    $ReamianingDays = $LateDays - 20;
                                                                    $ReamianingDay = $LateDays - $ReamianingDays;
                                                                    $ForTwentyDays = $ReamianingDay * 10;
                                                                    $AfterTwentyDays = $ReamianingDays * 50;
                                                                    $TotalCharges = $AfterTwentyDays + $ForTwentyDays;
                                                                    if ($LateDays <= 20) {
                                                                        $TotalCharges = $LateCharge * $LateDays;
                                                                    }
                                                                } else {
                                                                    $TotalLateCharge += $LateCharge;
                                                                }

                                                                if ($TotalCharges > 0) {
                                                                    FeeCollection::SaveLateFee($StudentID, $MonthID, $TotalCharges);
                                                                }
                                                            }

                                                            $ShowLateFeeDetails = array();
                                                            $ShowLateFeeDetails = FeeCollection::SearchLateFee($StudentID);

                                                            $Clean['LateFeeAmount'] = 0;
                                                            foreach ($ShowLateFeeDetails as $LateFeeID => $CurrentLateFeeDetails) {
                                                                $Clean['LateFeeAmount'] += $CurrentLateFeeDetails['Amount'];
                                                            }

                                                            $TotalLateFee += $Clean['LateFeeAmount'];
                                                            $TotalPrevDueAmount += $StudentDetails['PreviousYearDueAmount'];
                                                            // $TotalCurrentDueAmount += (($StudentDetails['Due']) - $StudentDetails['PreviousYearDueAmount']);
                                                            $TotalCurrentDueAmount += (($StudentDetails['Due'] + $Clean['LateFeeAmount']) - $StudentDetails['PreviousYearDueAmount']);
                                                            // $TotalDueAmount += $StudentDetails['Due'];
                                                            $TotalDueAmount += $StudentDetails['Due'] + $Clean['LateFeeAmount'];
                                                            ?>
                                                            <td class="text-right">
                                                                <?php echo $Clean['LateFeeAmount'] ? $Clean['LateFeeAmount'] : 0; ?>
                                                            </td>
                                                            <td class="text-right">
                                                                <?php //echo ($StudentDetails['Due']) - $StudentDetails['PreviousYearDueAmount']; 
                                                                ?>
                                                                <?php echo ($StudentDetails['Due'] + $Clean['LateFeeAmount']) - $StudentDetails['PreviousYearDueAmount']; ?>
                                                            </td>
                                                            <td class="text-right">
                                                                <?php echo $StudentDetails['Due'] + $Clean['LateFeeAmount']; ?>
                                                                <?php //echo $StudentDetails['Due']; 
                                                                ?>
                                                            </td>
                                                            <td class="print-hidden">
                                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#student_<?php echo $StudentID; ?>">
                                                                    Details
                                                                </button>

                                                                <!-- Modal -->
                                                                <div class="modal fade" id="student_<?php echo $StudentID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                                <h4 class="modal-title" id="myModalLabel">
                                                                                    Transaction Details For Student:
                                                                                    <?php echo $StudentDetails['StudentName']; ?>
                                                                                </h4>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <?php
                                                                                $totalDueAmount = 0;
                                                                                foreach ($StudentDetails['FeeDetails'] as $monthName => $FeeMonthDetails) {
                                                                                ?>
                                                                                    <table width="100%" class="table table-striped table-bordered">
                                                                                        <style>
                                                                                            .table tr.primary {
                                                                                                background-color: #337ab7 !important;
                                                                                                color: white;
                                                                                            }

                                                                                            .list-group-item>.badgewa {
                                                                                                float: right;
                                                                                                font-weight: bold;
                                                                                            }
                                                                                        </style>
                                                                                        <thead>
                                                                                            <tr class="primary">
                                                                                                <?php
                                                                                                $FY_string = '[2020-21]';
                                                                                                if ($_SESSION['DB'] != 'addedschools_lucknowips_testing') {
                                                                                                    $FY_string = '[2021-22]';
                                                                                                }
                                                                                                ?>
                                                                                                <th colspan="6">
                                                                                                    <?php echo $monthName . ' ' . $FY_string; ?>:
                                                                                                </th>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Sr.No.</th>
                                                                                                <th>Fee Head</th>
                                                                                                <th>Fee Amount</th>
                                                                                                <th>Due Amount</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php
                                                                                            $CounterFeeHead = 0;
                                                                                            foreach ($FeeMonthDetails as $FeeDetails) {
                                                                                                if ($FeeDetails['Due'] <= 0) {
                                                                                                    continue;
                                                                                                }
                                                                                                $totalDueAmount += $FeeDetails['Due'];
                                                                                            ?>
                                                                                                <tr>
                                                                                                    <td><?php echo ++$CounterFeeHead; ?>
                                                                                                    </td>
                                                                                                    <td><?php echo $FeeDetails['Head']; ?>
                                                                                                    </td>
                                                                                                    <td class="text-right">
                                                                                                        <?php echo $FeeDetails['Amount']; ?>
                                                                                                    </td>
                                                                                                    <td class="text-right">
                                                                                                        <?php echo $FeeDetails['Due']; ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            <?php
                                                                                            }
                                                                                            ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                <?php
                                                                                }
                                                                                if ($StudentDetails['PreviousYearDueAmount'] > 0) {
                                                                                ?>
                                                                                    <ul class="list-group">
                                                                                        <li class="list-group-item">
                                                                                            <span class="badgewa"><?php echo $StudentDetails['PreviousYearDueAmount']; ?></span>
                                                                                            Previous Year Due
                                                                                        </li>
                                                                                    </ul>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                                <ul class="list-group">
                                                                                    <li class="list-group-item">
                                                                                        <span class="badgewa"><?php echo $StudentDetails['Due']; ?></span>
                                                                                        Total Due
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>
                                                    <tr>
                                                        <th colspan="5" class="text-center">Total</th>
                                                        <th class="text-right">
                                                            <?php echo $TotalPrevDueAmount; ?>
                                                        </th>
                                                        <?php
                                                        if (count($DefaulterList) > 0) {
                                                            foreach ($FeeHeads as $FeeID => $FeeName) {
                                                                echo '<th class="text-right">' . $FeeHeadDueAmount[$FeeID] . '</th>';
                                                            }
                                                        }
                                                        ?>
                                                        <th>
                                                            <?php echo $TotalLateFee; ?>
                                                        </th>
                                                        <th class="text-right"><?php echo $TotalCurrentDueAmount; ?></th>
                                                        <th class="text-right"><?php echo $TotalDueAmount; ?></th>
                                                        <th class="print-hidden"></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.table-responsive -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
            <?php
            }
            ?>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <div id="ViewDueDetails" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header btn-info">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Transaction Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12" id="DueDetails"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="SendSmsModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header btn-info">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">SMS Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12" id="SMSDetails">
                            <form class="form-horizontal" name="AddFeedStudentmarks" action="fee_defaulter_list.php" method="get">
                                <div class="form-group">
                                    <label for="Message" class="col-lg-2 control-label">Message</label>
                                    <div class="col-lg-8">
                                        <textarea name="txtMessage" class="form-control" rows="7" id="Message">

Regards,
Lucknow International Public School
										</textarea>
                                        <small>(This message will autometically send particular student due amount and
                                            month.)</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-lg-10">
                                        <input type="hidden" name="hdnProcess" value="1" />
                                        <input type="hidden" name="hdnAcademicYearID" value="<?php echo $Clean['AcademicYearID']; ?>" />
                                        <input type="hidden" name="hdnClassID" value="<?php echo $Clean['ClassID']; ?>" />
                                        <input type="hidden" name="hdnClassSectionID" value="<?php echo $Clean['ClassSectionID']; ?>" />
                                        <input type="hidden" name="hdnStudentID" value="<?php echo $Clean['StudentID']; ?>" />
                                        <input type="hidden" name="hdnStudentName" value="<?php echo $Clean['StudentName']; ?>" />
                                        <input type="hidden" name="hdnStatus" value="<?php echo $Clean['Status']; ?>" />
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Send SMS</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /#wrapper -->
    <?php
    require_once('../footer.php');
    ?>
    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
    <script src="../vendor/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var totalRecordCount = '<?php echo (isset($Counter) ? $Counter : 0); ?>';

            $(".select-date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'dd/mm/yy'
            });

            // $('#DataTableRecords').DataTable({
            //     responsive: true,
            //     bPaginate: false,
            //     bSort: false,
            //     searching: false, 
            //     info: false
            // });

            $('#Class').change(function() {

                var ClassID = parseInt($(this).val());

                if (ClassID <= 0) {
                    $('#ClassSection').html('<option value="0">Select Section</option>');
                    return;
                }

                $.post("/xhttp_calls/get_sections_by_classs.php", {
                    SelectedClassID: ClassID
                }, function(data) {
                    ResultArray = data.split("|*****|");

                    if (ResultArray[0] == 'error') {
                        alert(ResultArray[1]);
                        return false;
                    } else {
                        $('#ClassSection').html('<option value="0">Select Section</option>' +
                            ResultArray[1]);
                    }
                });
            });

            $('#ClassSection').change(function() {

                var ClassSectionID = parseInt($(this).val());
                var AcademicYearID = parseInt($('#AcademicYearID').val());

                if (ClassSectionID <= 0) {
                    $('#Student').html('<option value="0">Select Student</option>');
                    return;
                }

                $.post("/xhttp_calls/get_students_by_class_section.php", {
                    SelectedClassSectionID: ClassSectionID,
                    SelectedAcademicYearID: AcademicYearID
                }, function(data) {
                    ResultArray = data.split("|*****|");

                    if (ResultArray[0] == 'error') {
                        alert(ResultArray[1]);
                        return false;
                    } else {
                        $('#Student').html('<option value="0">Select Student</option>' +
                            ResultArray[1]);
                    }
                });
            });

            $('.DueDetails').click(function() {

                var StudentID = 0;
                StudentID = parseInt($(this).val());
                AcademicYearID = parseInt($('#AcademicYearID').val());

                FeePriority = <?php echo $FeePriority; ?>;

                if (StudentID <= 0 || AcademicYearID <= 0) {
                    alert('Error! No record found.');
                    return;
                }

                $.post("/xhttp_calls/get_fee_defaulter_dues.php", {
                    SelectedStudentID: StudentID,
                    AcademicYearID: AcademicYearID,
                    FeePriority: FeePriority,
                }, function(data) {
                    ResultArray = data.split("|*****|");

                    if (ResultArray[0] == 'error') {
                        alert(ResultArray[1]);
                        return false;
                    } else {
                        $('#DueDetails').html(ResultArray[1]);
                    }
                });
            });

            $('#chkAllMonth').change(function() {

                if ($(this).prop("checked") == true) {
                    $('.chkAllMonth').prop('checked', true);
                } else {
                    $('.chkAllMonth').prop('checked', false);
                }
            });

            $('.chkAllMonth').change(function() {

                var Counter = <?php echo count($AcademicYearMonths); ?>;

                if ($('input.chkAllMonth:checked').length == Counter) {
                    $('#chkAllMonth').prop('checked', true);
                } else {
                    $('#chkAllMonth').prop('checked', false);
                }
            });

            $('#AcademicYearID').change(function() {

                $('#Class').val(0);
                $('#ClassSection').html('<option value="0">Select Section</option>');
                $('#Student').html('<option value="0">Select Student</option>');
            });

            $('#totalRecordCount').text(totalRecordCount);
        });
    </script>
    <!-- JavaScript To Print A Report -->
    <script src="../js/print-report.js"></script>
</body>

</html>