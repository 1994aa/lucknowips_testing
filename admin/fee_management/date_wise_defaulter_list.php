<?php
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

require_once('../../classes/fee_management/class.fee_heads.php');
require_once('../../classes/fee_management/class.fee_collection.php');

require_once('../../classes/class.global_settings.php');

require_once('../../includes/global_defaults.inc.php');

//1. RECHECK IF THE USER IS VALID //
try
{
    $AuthObject = new ApplicationAuthentication;
    $LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e)
{
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}
catch (Exception $e)
{
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

if ($LoggedUser->HasPermissionForTask(FEE_DEFAULTER) !== true)
{
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}

$StudentStatusList = array('Active' => 'Active', 'InActive' => 'InActive');

$Filters = array();

$ClassList =  array();
$ClassList = AddedClass::GetActiveClasses();

$FeeHeadList =  array();
$FeeHeadList = FeeHead::GetActiveFeeHeads();

$AcademicYears =  array();
$AcademicYears = AcademicYear::GetAllAcademicYears();


$GlobalSettingObject = new GlobalSetting();

$AcademicYearMonthID = 0;
$AcademicYearMonthID = AcademicYearMonth::GetMonthIDByMonthName(date('M'));

$ClassSectionsList =  array();
$StudentsList =  array();

$ActiveFeeHeads = array();
$ActiveFeeHeads = FeeHead::GetActiveFeeHeads();

$DefaulterList = array();

$DueFromDate = '';
$DueToDate = '';

$FeePriority = 0;

$HasErrors = false;
$TotalRecords = 0;

$Clean = array();
$Clean['Process'] = 0;

$Clean['AcademicYearID'] = 0;
$Clean['ClassID'] = 0;
$Clean['ClassSectionID'] = 0;
$Clean['StudentID'] = 0;
$Clean['StudentName'] = '';
$Clean['FeeHeadID'] = 0;
$Clean['MobileNumber'] = '';

$Clean['Status'] = 'Active';

$Clean['DueFromDate'] = '';
$Clean['DueToDate'] = '';

// paging and sorting variables start here  //

$Clean['AllRecords'] = '';
$Clean['CurrentPage'] = 1;
$TotalPages = 0;

$Start = 0;
$Limit = 50;

$DefaulterList = array();

// end of paging variables//

if (isset($_GET['hdnProcess']))
{
    $Clean['Process'] = (int) $_GET['hdnProcess'];
}
elseif (isset($_GET['Process']))
{
    $Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process'])
{
    case 7:
        
        if (isset($_GET['drdAcademicYear'])) 
		{
			$Clean['AcademicYearID'] = (int) $_GET['drdAcademicYear'];
		}
		else if (isset($_GET['AcademicYearID'])) 
		{
			$Clean['AcademicYearID'] = (int) $_GET['AcademicYearID'];
		}
		
        if (isset($_GET['drdClass']))
        {
            $Clean['ClassID'] = strip_tags(trim($_GET['drdClass']));
        }
        elseif (isset($_GET['ClassID']))
        {
            $Clean['ClassID'] = strip_tags(trim($_GET['ClassID']));
        }

        if (isset($_GET['drdClassSection']))
        {
            $Clean['ClassSectionID'] = strip_tags(trim($_GET['drdClassSection']));
        }
        elseif (isset($_GET['ClassSectionID']))
        {
            $Clean['ClassSectionID'] = strip_tags(trim($_GET['ClassSectionID']));
        }
        
        if (isset($_GET['drdStudent']))
        {
            $Clean['StudentID'] = strip_tags(trim($_GET['drdStudent']));
        }
        elseif (isset($_GET['StudentID']))
        {
            $Clean['StudentID'] = strip_tags(trim($_GET['StudentID']));
        }
        
        if (isset($_GET['drdFeeHead']))
        {
            $Clean['FeeHeadID'] = strip_tags(trim($_GET['drdFeeHead']));
        }
        elseif (isset($_GET['FeeHeadID']))
        {
            $Clean['FeeHeadID'] = strip_tags(trim($_GET['FeeHeadID']));
        }
        
        if (isset($_GET['optStatus']))
        {
            $Clean['Status'] =  strip_tags(trim( (string) $_GET['optStatus']));
        }
        elseif (isset($_GET['Status']))
        {
            $Clean['Status'] =  strip_tags(trim( (string) $_GET['Status']));
        }
        
        if (isset($_GET['txtStudentName']))
        {
            $Clean['StudentName'] = strip_tags(trim($_GET['txtStudentName']));
        }
        elseif (isset($_GET['StudentName']))
        {
            $Clean['StudentName'] = strip_tags(trim($_GET['StudentName']));
        }
        
        if (isset($_GET['txtMobileNumber']))
        {
            $Clean['MobileNumber'] = strip_tags(trim($_GET['txtMobileNumber']));
        }
        else if (isset($_GET['MobileNumber']))
        {
            $Clean['MobileNumber'] = strip_tags(trim( (string) $_GET['MobileNumber']));
        }
        
        if (isset($_GET['txtDueFromDate']))
		{
			$Clean['DueFromDate'] = strip_tags(trim((string) $_GET['txtDueFromDate']));
		}
		elseif (isset($_GET['txtDueFromDate']))
		{
			$Clean['DueFromDate'] = strip_tags(trim((string) $_GET['txtDueFromDate']));
		}
		
		if (isset($_GET['txtDueToDate']))
		{
			$Clean['DueToDate'] = strip_tags(trim((string) $_GET['txtDueToDate']));
		}
		elseif (isset($_GET['txtDueToDate']))
		{
			$Clean['DueToDate'] = strip_tags(trim((string) $_GET['txtDueToDate']));
		}
       
        $SearchValidator = new Validator();

        if ($Clean['ClassID'] > 0)
        {
            $SearchValidator->ValidateInSelect($Clean['ClassID'], $ClassList, 'Unknown error, please try again.');
            $ClassSectionsList = ClassSections::GetClassSections($Clean['ClassID']);
        }

        if ($Clean['ClassSectionID'] > 0)
        {
            $SearchValidator->ValidateInSelect($Clean['ClassSectionID'], $ClassSectionsList, 'Unknown error, please try again.');
            
            $StudentsList = StudentDetail::GetStudentsByClassSectionID($Clean['ClassSectionID'], 'Active', $Clean['AcademicYearID']);
            
            if ($Clean['StudentID'] > 0)
            {
                $SearchValidator->ValidateInSelect($Clean['StudentID'], $StudentsList, 'Please select a valid student.');   
            }
        }
        
        if ($Clean['FeeHeadID'] > 0)
        {
            $SearchValidator->ValidateInSelect($Clean['FeeHeadID'], $ActiveFeeHeads, 'Please select a valid fee head.');
        }
        
        $SearchValidator->ValidateInSelect($Clean['Status'], $StudentStatusList, 'Unknown Error in status, Please try again.');
        
        if ($Clean['StudentName'] != '')
        {
            $SearchValidator->ValidateStrings($Clean['StudentName'], 'Student name should be between 2 to 50.', 2, 50);
        }
        
        if ($Clean['MobileNumber'] != '')
        {
            $SearchValidator->ValidateStrings($Clean['MobileNumber'], 'Mobile number should be between 1 to 15.', 1, 15);
        }
        
        $SearchValidator->ValidateDate($Clean['DueFromDate'], 'Please enter valid due from date.');
		$SearchValidator->ValidateDate($Clean['DueToDate'], 'Please enter valid due to date.');
        
        if ($SearchValidator->HasNotifications())
        {
            $HasErrors = true;
            break;
        }
        
        $DueFromDate = date('Y-m-d', strtotime(DateProcessing::ToggleDateDayAndMonth(($Clean['DueFromDate']))));
		$DueToDate = date('Y-m-d', strtotime(DateProcessing::ToggleDateDayAndMonth(($Clean['DueToDate']))));

        //set record filters    
                
        $Filters['AcademicYearID'] = $Clean['AcademicYearID'];
        $Filters['ClassID'] = $Clean['ClassID'];
        $Filters['ClassSectionID'] = $Clean['ClassSectionID'];
        $Filters['FeeHeadID'] = $Clean['FeeHeadID'];
        $Filters['StudentID'] = $Clean['StudentID'];
        $Filters['StudentName'] = $Clean['StudentName'];
        $Filters['MobileNumber'] = $Clean['MobileNumber'];
        $Filters['Status'] = $Clean['Status'];
        
        $Filters['DueFromDate'] = $DueFromDate;
		$Filters['DueToDate'] = $DueToDate;
        
        //get records count
        
        if ($Clean['AcademicYearID'] == 1)
        {
            FeeCollection::SearchFeeDefaultersVishnu($TotalRecords, true, $Filters);
        }
        else
        {
            FeeCollection::SearchFeeDefaultersVishnuYear2($TotalRecords, true, $Filters);
        }

        if ($TotalRecords > 0)
        {
            // Paging and sorting calculations start here.
            $TotalPages = (($TotalRecords % $Limit) == 0) ? $TotalRecords / $Limit : floor($TotalRecords / $Limit) + 1;

            if (isset($_GET['CurrentPage']))
            {
                $Clean['CurrentPage'] = (int) $_GET['CurrentPage'];
            }
            
            if (isset($_GET['AllRecords']))
            {
                $Clean['AllRecords'] = (string) $_GET['AllRecords'];
            }

            if ($Clean['CurrentPage'] <= 0)
            {
                $Clean['CurrentPage'] = 1;
            }
            elseif ($Clean['CurrentPage'] > $TotalPages)
            {
                $Clean['CurrentPage'] = $TotalPages;
            }

            if ($Clean['CurrentPage'] > 1)
            {
                $Start = ($Clean['CurrentPage'] - 1) * $Limit;
            }
            // end of Paging and sorting calculations.
            // now get the actual  records
            
            if ($Clean['AllRecords'] == 'All') 
            {
                $TotalPages = 1;
                if ($Clean['AcademicYearID'] == 1)
                {
                    $DefaulterList = FeeCollection::SearchFeeDefaultersVishnu($TotalRecords, false, $Filters, 0, $TotalRecords);
                }
                else
                {
                    $DefaulterList = FeeCollection::SearchFeeDefaultersVishnuYear2($TotalRecords, false, $Filters, 0, $TotalRecords);
                }
            }
            else
            {
                if ($Clean['AcademicYearID'] == 1)
                {
                    $DefaulterList = FeeCollection::SearchFeeDefaultersVishnu($TotalRecords, false, $Filters, $Start, $Limit);
                }
                else
                {
                    $DefaulterList = FeeCollection::SearchFeeDefaultersVishnuYear2($TotalRecords, false, $Filters, $Start, $Limit);
                }
            }
        }
        break;
}

require_once('../html_header.php');
?>
<title>Date Wise Defaulter List</title>
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
                <div class="col-lg-6">
                    <h1 class="page-header">Date Wise Defaulter List</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <form class="form-horizontal" name="frmRoomReport" action="date_wise_defaulter_list.php" method="get">
                <div class="panel panel-default" id="accordion">
                    <div class="panel-heading">
                        <strong><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Filters</a></strong>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in">
                        <div class="panel-body">
<?php
                            if ($HasErrors == true)
                            {
                                echo $SearchValidator->DisplayErrors();
                            }
?>
                            
                            <div class="form-group">
                                <label for="AcademicYear" class="col-lg-2 control-label">Academic Year</label>
                                <div class="col-lg-3">
                                	<select class="form-control" name="drdAcademicYear" id="AcademicYearID">
    <?php
                                    if (is_array($AcademicYears) && count($AcademicYears) > 0)
                                    {
                                        foreach ($AcademicYears as $AcademicYearID => $AcademicYearDetails)
                                        {
                                            if ($AcademicYearID != 1)
                                            {
                                                continue;
                                            }
                                            
                                            if ($Clean['AcademicYearID'] == 0)
                                            {
                                                if ($AcademicYearDetails['IsCurrentYear'] == 1)
                                                {
                                                    $Clean['AcademicYearID'] = $AcademicYearID;   
                                                }
                                            }
                                            
                                            echo '<option ' . ($Clean['AcademicYearID'] == $AcademicYearID ? 'selected="selected"' : '') . ' value="' . $AcademicYearID . '" >' . date('Y', strtotime($AcademicYearDetails['StartDate'])) .' - '. date('Y', strtotime($AcademicYearDetails['EndDate'])) . '</option>';
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
                                        <option  value="0" >Select Class</option>
    <?php
                                        foreach ($ClassList as $ClassID => $ClassName)
                                        {
    ?>
                                            <option <?php echo (($ClassID == $Clean['ClassID']) ? 'selected="selected"' : ''); ?> value="<?php echo $ClassID; ?>"><?php echo $ClassName; ?></option>
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
                                            if (is_array($ClassSectionsList) && count($ClassSectionsList) > 0)
                                            {
                                                foreach ($ClassSectionsList as $ClassSectionID => $SectionName) 
                                                {
                                                    echo '<option ' . ($Clean['ClassSectionID'] == $ClassSectionID ? 'selected="selected"' : '') . ' value="' . $ClassSectionID . '">' . $SectionName . '</option>' ;
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
                                            if (is_array($StudentsList) && count($StudentsList) > 0)
                                            {
                                                foreach ($StudentsList as $StudentID=>$StudentDetails)
                                                {
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
                                        <option  value="0" >-- All Fee Head --</option>
    <?php
                                        foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHeadDetails)
                                        {
    ?>
                                            <option <?php echo ($FeeHeadID == $Clean['FeeHeadID'] ? 'selected="selected"' : ''); ?> value="<?php echo $FeeHeadID; ?>"><?php echo $FeeHeadDetails['FeeHead']; ?></option>
    <?php
                                        }
    ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Status" class="col-lg-2 control-label">Student Status</label>
                                <div class="col-lg-5">
    <?php
                                foreach ($StudentStatusList as $StatusKey => $StatusName)
                                {
    ?>
                                    <label class="col-sm-4"><input class="custom-radio" type="radio" id="<?php echo $StatusKey;?>" name="optStatus" value="<?php echo $StatusKey;?>" <?php echo (($Clean['Status'] == $StatusKey) ? 'checked="checked"' : ''); ?> >&nbsp;&nbsp;<?php echo $StatusName;?></label>
    <?php
                                }
    ?>
                                </div>
                            </div>
                            <div class="form-group">                            
                                <label for="DueFromDate" class="col-lg-2 control-label">Due Date Between</label>
                                <div class="col-lg-3">
                                    <input class="form-control select-date" type="text" maxlength="10" id="DueFromDate" name="txtDueFromDate" value="<?php echo $Clean['DueFromDate']; ?>" />
                                </div>
                                <label for="DueToDate" class="col-lg-1 control-label">to</label>
                                <div class="col-lg-3">
                                    <input class="form-control select-date" type="text" maxlength="10" id="DueToDate" name="txtDueToDate" value="<?php echo $Clean['DueToDate']; ?>" />
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
        if ($Clean['Process'] == 7 && $HasErrors == false)
        {
            $ReportHeaderText = '';
            
            if ($Clean['AcademicYearID'] != 0)
            {
                $ReportHeaderText .= ' Session: ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['StartDate'])) .' - '. date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['EndDate'])) . ',';
            }

            if ($Clean['ClassID'] > 0)
            {
                $ReportHeaderText .= ' Class : ' . $ClassList[$Clean['ClassID']] . ',';
            }

            if ($Clean['ClassSectionID'] > 0)
            {
                $ReportHeaderText .= ' Section : ' . $ClassSectionsList[$Clean['ClassSectionID']] . ',';
            }
            
            if ($Clean['StudentID'] > 0)
            {
                $ReportHeaderText .= ' Student : ' . $StudentsList[$Clean['StudentID']]['FirstName'] . ',';
            }
            
            if ($Clean['FeeHeadID'] > 0)
            {
                $ReportHeaderText .= ' Fee Head : ' . $ActiveFeeHeads[$Clean['FeeHeadID']]['FeeHead'] . ',';
            }

            if ($Clean['StudentName'] != '')
            {
                $ReportHeaderText .= ' Student Name : ' . $Clean['StudentName'] . ',';
            }
            
            if ($Clean['MobileNumber'] != '')
            {
                $ReportHeaderText .= ' Mobile Number : ' . $Clean['MobileNumber'] . ',';
            }
            
            if ($Clean['Status'] != '')
            {
                $ReportHeaderText .= ' Status: ' . $Clean['Status'] . ' students,';
            }

            if ($ReportHeaderText != '')
            {
                $ReportHeaderText = ' for' . rtrim($ReportHeaderText, ',');
            }
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Total Records Returned: <?php echo $TotalRecords; ?></strong>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div>
                                <div class="row">
                                    <div class="col-lg-6">
<?php
                                    if ($TotalPages > 1)
                                    {
                                        $AllParameters = array('Process' => '7', 'AcademicYearID' => $Clean['AcademicYearID'], 'ClassID' => $Clean['ClassID'], 'ClassSectionID' => $Clean['ClassSectionID'], 'StudentID' => $Clean['StudentID'], 'FeeHeadID' => $Clean['FeeHeadID'], 'StudentName' => $Clean['StudentName'], 'Status' => $Clean['Status'], 'txtDueFromDate' => $Clean['DueFromDate'], 'txtDueToDate' => $Clean['DueToDate']);
                                        echo UIHelpers::GetPager('date_wise_defaulter_list.php', $TotalPages, $Clean['CurrentPage'], $AllParameters);
                                    }
?>                                        
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="print-btn-container">
                                            <button id="PrintButton" type="submit" class="btn btn-primary">Print</button>
                                            <!--<button id="" onclick="$('#get_excel').val(2); $('#SubmitSearch').click();$('#get_excel').val(0);" type="submit" class="btn btn-primary">Export to Excel</button>-->
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTableHeading">
                                    <div class="col-lg-12">
                                        <div class="report-heading-container"><strong>Date Wise Defaulter List on <?php echo date('d-m-Y h:i A') . $ReportHeaderText; ?></strong></div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTable">
                                    <div class="col-lg-12">
                                        <table class="table table-responsive table-striped table-bordered" id="DataTableRecords" style="overflow: auto;">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Sr. No.</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Mobile No</th>
                                                    <th>Previous Due</th>
<?php
                                                foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) 
                                                {
                                                    $FeeHeadTotalDue[$FeeHeadID] = 0;
                                                    echo '<th>'. $FeeHeadDetails['FeeHead'] .'</th>';
                                                }
?>
                                                    <th>Total Due</th>
                                                    <th>Due Months</th>
                                                    <th class="print-hidden">Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
                                    $TotalDue = 0;
                                    $TotalPreviousYearDue = 0;
                                    if (is_array($DefaulterList) && count($DefaulterList) > 0)
                                    {
                                        $Counter = $Start;
                                        
                                        foreach ($DefaulterList as $StudentID => $Details)
                                        {
                                            $PreviousDefaultedFees = array();
                                            $PreviousDueAmount = 0;

                                            if ($Clean['AcademicYearID'] == 2) 
                                            {
                                                //echo '**************here**************';
                                                $PreviousDefaultedFees = FeeCollection::GetFeeDefaulterDues($StudentID, 120, 1, $PreviousYearDue, date('Y-m-d'));
                                                
                                                foreach ($PreviousDefaultedFees as $Month => $FeeDetails) 
                                                {
                                                    $PreviousDueAmount += array_sum(array_column($FeeDetails,'FeeHeadAmount'));
                                                }
                                                
                                                if ($PreviousDueAmount < 0) 
                                                {
                                                    $PreviousDueAmount = 0;
                                                }
                                            }
                                            
                                            $DueMonth = 0;

                                            $SelectedDueMonths = '';

                                            $FeeDefaulterDues = array();
                                            $FeeDefaulterDues = FeeCollection::GetFeeDefaulterDuesVishnu($StudentID, $FeePriority, $Clean['AcademicYearID'], $PreviousYearDue, $DueMonth, date('Y-m-d'), $SelectedDueMonths, $DueFromDate, $DueToDate);
                                            
                                            $RowTotalDue = 0;
                                            $TotalDue += $PreviousYearDue + $PreviousDueAmount;
                                            $RowTotalDue = $PreviousYearDue + $PreviousDueAmount;
                                            $TotalPreviousYearDue += $PreviousYearDue + $PreviousDueAmount;
?>
                                                <tr>
                                                    <td><?php echo ++$Counter; ?></td>
                                                    <td><?php echo $Details['EnrollmentID']; ?></td>
                                                    <td><?php echo $Details['StudentName']; ?></td>
                                                    <td><?php echo $Details['Class']; ?></td>
                                                    <td><?php echo $Details['FatherMobileNumber'] .'<br> '. $Details['MotherMobileNumber']; ?></td>
                                                    <td class="text-right"><?php echo number_format($PreviousYearDue + $PreviousDueAmount, 2); ?></td>
<?php
                                                    foreach ($FeeHeadList as $FeeHeadID => $FeeHeadDetails) 
                                                    {
                                                        $FeeHeadDueAmount = 0;
                                                        
                                                        if (isset($FeeDefaulterDues[$FeeHeadID]))
                                                        {
                                                            $RowTotalDue += $FeeHeadDueAmount;
                                                            $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
                                                        }
                                                        
                                                        foreach ($FeeDefaulterDues as $Month => $DefaulterDetails) 
                                                        {
                                                            if ($Clean['FeeHeadID'] > 0)
                                                            {
                                                                if ($Clean['FeeHeadID'] == $FeeHeadID  && isset($FeeDefaulterDues[$FeeHeadID]))
                                                                {
                                                                    $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
                                                                }
                                                            }
                                                            else 
                                                            {
                                                                if (isset($FeeDefaulterDues[$FeeHeadID]))
                                                                {
                                                                    $FeeHeadDueAmount = $FeeDefaulterDues[$FeeHeadID]['FeeHeadAmount'];
                                                                }
                                                            }
                                                        }

                                                        $TotalDue += $FeeHeadDueAmount;
                                                        
                                                        $RowTotalDue += $FeeHeadDueAmount;

                                                        echo '<td class="text-right">'. number_format($FeeHeadDueAmount, 2) .'</td>';

                                                        $FeeHeadTotalDue[$FeeHeadID] += $FeeHeadDueAmount;
                                                    }
?>
                                                    <td class="text-right"><?php echo number_format($RowTotalDue, 2); ?></td>
                                                    <td class="text-right"><?php echo $DueMonth . ' Month'; ?></td>
                                                    <td class="print-hidden"><button type="button" class="btn btn-info btn-sm DueDetails" data-toggle="modal" data-target="#ViewDueDetails" value="<?php echo $StudentID; ?>">Details &nbsp;<i class="fa fa-angle-double-right"></i></button></td>
                                                </tr>
<?php
                                        }
                                    }
?>
                                                <tr>
                                                    <th class="text-right"> </th>
                                                    <th class="text-right"> </th>
                                                    <th class="text-right"> </th>
                                                    <th class="text-right"> </th>
                                                    <th class="text-right">Grand Total : </th>
                                                    <th class="text-right"><?php echo number_format($TotalPreviousYearDue, 2) ?></th>
<?php
                                                    foreach ($FeeHeadTotalDue as $FeeHeadID => $FeeHeadDue) 
                                                    {
                                                        echo '<th class="text-right">'. number_format($FeeHeadDue, 2) .'</th>';
                                                    }
?>
                                                    <th class="text-right"><?php echo number_format($TotalDue, 2); ?></th>
                                                    <th class="print-hidden"></th>
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
    <!-- /#wrapper -->
<?php
require_once('../footer.php');
?>
<!-- DataTables JavaScript -->
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script> 
<script src="../vendor/jquery-ui/jquery-ui.min.js"></script>

<script src="https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js"></script>  
<link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css" rel="stylesheet">

<script type="text/javascript">
$(document).ready(function() {
    $(".select-date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd/mm/yy'
    });

    $('#Class').change(function(){

        var ClassID = parseInt($(this).val());
        
        if (ClassID <= 0)
        {
            $('#ClassSection').html('<option value="0">Select Section</option>');
            return;
        }
        
        $.post("/xhttp_calls/get_sections_by_classs.php", {SelectedClassID:ClassID}, function(data)
        {
            ResultArray = data.split("|*****|");
            
            if (ResultArray[0] == 'error')
            {
                alert (ResultArray[1]);
                return false;
            }
            else
            {
                $('#ClassSection').html('<option value="0">Select Section</option>' + ResultArray[1]);
            }
         });
    });
    
    $('#ClassSection').change(function(){

        var ClassSectionID = parseInt($(this).val());
        var AcademicYearID = parseInt($('#AcademicYearID').val());
        
        if (ClassSectionID <= 0)
        {
            $('#Student').html('<option value="0">Select Student</option>');
            return;
        }
        
        $.post("/xhttp_calls/get_students_by_class_section.php", {SelectedClassSectionID:ClassSectionID,SelectedAcademicYearID:AcademicYearID}, function(data)
        {
            ResultArray = data.split("|*****|");
            
            if (ResultArray[0] == 'error')
            {
                alert (ResultArray[1]);
                return false;
            }
            else
            {
                $('#Student').html('<option value="0">Select Student</option>' + ResultArray[1]);
            }
        });
    });

    $('.DueDetails').click(function(){

        var DueFromDate = '<?php echo $DueFromDate; ?>';
        var DueToDate = '<?php echo $DueToDate; ?>';
        
        var AcademicYearMonthID = '';
        
        var StudentID = 0;
        StudentID = parseInt($(this).val());
        AcademicYearID = parseInt($('#AcademicYearID').val());

        FeePriority = <?php echo $FeePriority;?>;

        if (StudentID <= 0 || AcademicYearID <= 0)
        {
            alert('Error! No record found.');
            return;
        }
        //pending for review
        $.post("/xhttp_calls/get_fee_defaulter_dues_vishnu.php", {SelectedStudentID:StudentID, AcademicYearID:AcademicYearID, FeePriority:FeePriority, AcademicYearMonthID:AcademicYearMonthID, DueFromDate:DueFromDate, DueToDate:DueToDate}, function(data)
        {
            ResultArray = data.split("|*****|");
            
            if (ResultArray[0] == 'error')
            {
                alert (ResultArray[1]);
                return false;
            }
            else
            {
                $('#DueDetails').html(ResultArray[1]);
            }
        });
    });
    
    $('#AcademicYearID').change();
});
</script>
<!-- JavaScript To Print A Report -->
<script src="../js/print-report.js"></script>
</body>
</html>