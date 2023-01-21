<?php

require_once("../../classes/class.users.php");
require_once("../../classes/class.validation.php");
require_once("../../classes/class.authentication.php");

require_once("../../classes/class.ui_helpers.php");

require_once("../../classes/fee_management/class.fee_heads.php");
require_once('../../classes/fee_management/class.fee_transactions.php');
require_once('../../classes/fee_management/class.fee_collection.php');

require_once('../../classes/school_administration/class.classes.php');
require_once('../../classes/school_administration/class.class_sections.php');

require_once('../../classes/school_administration/class.students.php');
require_once('../../classes/school_administration/class.student_details.php');
require_once('../../classes/school_administration/class.academic_years.php');

require_once("../../includes/global_defaults.inc.php");

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
// if ($LoggedUser->HasPermissionForTask(TASK_student_fee_details.php) !== true) {
//     header('location:/admin/unauthorized_login_admin.php');
//     exit;
// }

$AcademicYears =  array();
$AcademicYears = AcademicYear::GetAllAcademicYears();

$ClassList =  array();
$ClassList = AddedClass::GetActiveClasses();

$ClassSectionsList = array();

$ActiveFeeHeads = array();
$ActiveFeeHeads = FeeHead::GetActiveFeeHeads('priority DESC');

$StudentsList =  array();

$Filters = array();

$FeeDetails = array();

$HasErrors = false;
$TotalRecords = 0;

$Clean = array();
$Clean['Process'] = 0;

$Clean['AcademicYearID'] = 0;
$Clean['ClassID'] = 0;
$Clean['ClassSectionID'] = 0;
$Clean['StudentID'] = 0;
$Clean['Status'] = 'Active';

if (isset($_GET['Status'])) {
    $Clean['Status'] = $_GET['Status'];
}

if (isset($_GET['Process'])) {
    $Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process']) {
    case 7:
        if (isset($_GET['AcademicYearID'])) {
            $Clean['AcademicYearID'] = (int) $_GET['AcademicYearID'];
        }

        if (isset($_GET['ClassID'])) {
            $Clean['ClassID'] = (int) $_GET['ClassID'];
        }

        if (isset($_GET['ClassSectionID'])) {
            $Clean['ClassSectionID'] = (int) $_GET['ClassSectionID'];
        }

        if (isset($_GET['StudentID'])) {
            $Clean['StudentID'] = (int) $_GET['StudentID'];
        }

        if (isset($_GET['Status'])) {
            $Clean['Status'] = $_GET['Status'];
        }

        $RecordValidator = new Validator();

        if ($RecordValidator->ValidateInSelect($Clean['ClassID'], $ClassList, 'Please select a valid class.')) {

            $ClassSectionsList = ClassSections::GetClassSections($Clean['ClassID']);
            if ($RecordValidator->ValidateInSelect($Clean['ClassSectionID'], $ClassSectionsList, 'Please select a valid section.')) {
                $StudentsList = StudentDetail::GetStudentsByClassSectionID($Clean['ClassSectionID'], $Clean['Status'], $Clean['AcademicYearID']);

                $RecordValidator->ValidateInSelect($Clean['StudentID'], $StudentsList, 'Please select a valid student.');
            }
        }

        if ($RecordValidator->HasNotifications()) {
            $HasErrors = true;
            break;
        }

        $Filters['ClassID'] = $Clean['ClassID'];
        $Filters['ClassSectionID'] = $Clean['ClassSectionID'];

        //$Filters['StudentName'] = $Clean['StudentName'];
        //$Filters['MobileNumber'] = $Clean['MobileNumber'];

        $FeeDetails = StudentDetail::GetStudentFeeDetails($Clean['StudentID'], $Clean['AcademicYearID'], $Filters);

        break;
}

require_once('../html_header.php');
?>
<title>Student Fee Details</title>
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
                    <h1 class="page-header">Student Fee Details</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <form class="form-horizontal" name="frmRoomReport" action="student_fee_details.php" method="get">
                <div class="panel panel-default" id="accordion">
                    <div class="panel-heading">
                        <strong><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Filters</a></strong>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <?php
                            if ($HasErrors == true) {
                                echo $RecordValidator->DisplayErrors();
                            }
                            ?>
                            <div class="form-group">
                                <label for="AcademicYear" class="col-lg-3 control-label">Academic Year</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="AcademicYearID" id="AcademicYearID">
                                        <?php
                                        if (is_array($AcademicYears) && count($AcademicYears) > 0) {
                                            foreach ($AcademicYears as $AcademicYearID => $AcademicYearDetails) {
                                                echo '<option ' . ($Clean['AcademicYearID'] == $AcademicYearID ? 'selected="selected"' : '') . ' value="' . $AcademicYearID . '" >' . date('Y', strtotime($AcademicYearDetails['StartDate'])) . ' - ' . date('Y', strtotime($AcademicYearDetails['EndDate'])) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ClassID" class="col-lg-3 control-label">Class</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="ClassID" id="ClassID">
                                        <option value="0">-- All Class --</option>
                                        <?php
                                        foreach ($ClassList as $ClassID => $ClassName) {
                                        ?>
                                            <option <?php echo (($ClassID == $Clean['ClassID']) ? 'selected="selected"' : ''); ?> value="<?php echo $ClassID; ?>"><?php echo $ClassName; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <label for="ClassSectionID" class="col-lg-1 control-label">Section</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="ClassSectionID" id="ClassSectionID">
                                        <option value="0">-- All Section --</option>
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
                                <label for="StudentID" class="col-lg-3 control-label">Select Student</label>
                                <div class="col-lg-7">
                                    <select class="form-control" name="StudentID" id="StudentID">
                                        <option value="0">-- All Student --</option>
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
                                <div class="col-lg-offset-3 col-lg-9">
                                    <input type="hidden" name="Process" value="7" />
                                    <input type="hidden" name="Status" value="<?php echo $Clean['Status']; ?>" />
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
                    $ReportHeaderText .= ' Fee Session: ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['StartDate'])) . ' - ' . date('Y', strtotime($AcademicYears[$Clean['AcademicYearID']]['EndDate'])) . ',';
                }

                if ($Clean['ClassID'] > 0) {
                    $ReportHeaderText .= ' Class : ' . $ClassList[$Clean['ClassID']] . ',';
                }

                if ($Clean['ClassSectionID'] > 0) {
                    $ReportHeaderText .= ' Section : ' . $ClassSectionsList[$Clean['ClassSectionID']] . ',';
                }

                if ($Clean['StudentID'] > 0) {
                    $ReportHeaderText .= ' Student : ' . $StudentsList[$Clean['StudentID']]['FirstName'] . ' ' . $StudentsList[$Clean['StudentID']]['LastName'] . '(' . $StudentsList[$Clean['StudentID']]['RollNumber'] . ')';
                }

                if ($ReportHeaderText != '') {
                    $ReportHeaderText = ' for' . rtrim($ReportHeaderText, ',');
                }
            ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div>
                                    <div class="row">
                                        <div class="col-lg-6"></div>
                                        <div class="col-lg-6">
                                            <div class="print-btn-container"><button id="PrintButton" type="submit" class="btn btn-primary">Print</button></div>
                                        </div>
                                    </div>
                                    <div class="row" id="RecordTableHeading">
                                        <div class="col-lg-12">
                                            <div class="report-heading-container"><strong>Student Fee Detail as on <?php echo date('d-m-Y h:i A') . $ReportHeaderText; ?></strong></div>
                                        </div>
                                    </div>
                                    <div class="row" id="RecordTable">
                                        <div class="col-lg-12">
                                            <table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
                                                <thead>
                                                    <tr>
                                                        <th>Month</th>
                                                        <th></th>
                                                        <?php
                                                        foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHead) {
                                                            echo '<th>' . $FeeHead['FeeHead'] . '</th>';
                                                        }
                                                        ?>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 12px;">
                                                    <?php
                                                    $BalanceAmount = array();
                                                    foreach ($FeeDetails as $Details) {
                                                    ?>
                                                        <tr>
                                                            <?php echo '<th rowspan="3">' . $Details['MonthName'] . '</th>'; ?>
                                                            <th>Payable</th>
                                                            <?php
                                                            $CurrentRowPayableTotal = 0;
                                                            foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHead) {
                                                                $CurrentFeeHeadPayable = ((isset($Details['FeeHeads']['Payable'][$FeeHeadID]) ? $Details['FeeHeads']['Payable'][$FeeHeadID] : 0));
                                                                $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Payable'] = $CurrentFeeHeadPayable;

                                                                $CurrentRowPayableTotal += $CurrentFeeHeadPayable;

                                                                echo '<td class="text-right">' . (($CurrentFeeHeadPayable) ? floatval($CurrentFeeHeadPayable) : '') . '</td>';
                                                            }
                                                            echo '<td class="text-right" style="font-size: 14px; font-weight: bold;">' . $CurrentRowPayableTotal . '</td>';
                                                            ?>
                                                        </tr>
                                                        <tr>
                                                            <th style="display: none;"></th>
                                                            <th>Paid</th>
                                                            <?php
                                                            $CurrentRowPaidTotal = 0;
                                                            foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHead) {
                                                                echo '<td class="text-right">';
                                                                if (!isset($BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Paid'])) {
                                                                    $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Paid'] = 0;
                                                                }

                                                                if (isset($Details['FeeHeads']['Paid'][$FeeHeadID])) {
                                                                    foreach ($Details['FeeHeads']['Paid'][$FeeHeadID] as $FeeCollectionID => $FeeCollectionDetails) {
                                                                        if ($FeeCollectionDetails['AmountPaid'] > 0) {
                                                                            $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Paid'] += $FeeCollectionDetails['AmountPaid'];

                                                                            $CurrentRowPaidTotal += $FeeCollectionDetails['AmountPaid'];

                                                                            echo '<strong>Fee Date: </strong> ' . date('d/m/Y', strtotime($FeeCollectionDetails['FeeDate'])) . '<br>';
                                                                            echo '<strong>Paid: </strong> ' . floatval($FeeCollectionDetails['AmountPaid']) . '<br>';
                                                                        }
                                                                    }
                                                                }

                                                                $Discount = isset($Details['FeeHeads']['Discount'][$FeeHeadID]) ? $Details['FeeHeads']['Discount'][$FeeHeadID] : 0;
                                                                $Concession = isset($Details['FeeHeads']['Concession'][$FeeHeadID]) ? $Details['FeeHeads']['Concession'][$FeeHeadID] : 0;
                                                                $WaveOff = isset($Details['FeeHeads']['WaveOff'][$FeeHeadID]) ? $Details['FeeHeads']['WaveOff'][$FeeHeadID] : 0;

                                                                $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Discount'] = $Discount;
                                                                $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Concession'] = $Concession;
                                                                $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['WaveOff'] = $WaveOff;

                                                                if ($Discount > 0) {
                                                                    echo '<strong>Discount: </strong>' . floatval($Discount) . '<br>';
                                                                }
                                                                if ($Concession > 0) {
                                                                    echo '<strong>Concession: </strong>' . floatval($Concession) . '<br>';
                                                                }
                                                                if ($WaveOff > 0) {
                                                                    echo '<strong>WaveOff: </strong>' . floatval($WaveOff) . '<br>';
                                                                }

                                                                $CurrentRowPaidTotal += $Discount;
                                                                $CurrentRowPaidTotal += $Concession;
                                                                $CurrentRowPaidTotal += $WaveOff;
                                                                echo '</td>';
                                                            }
                                                            echo '<td class="text-right" style="font-size: 14px; font-weight: bold;">' . $CurrentRowPaidTotal . '</td>';
                                                            ?>
                                                        </tr>
                                                        <tr style="background-color: #ecdada;">
                                                            <th style="display: none;"></th>
                                                            <th>Balance</th>
                                                            <?php
                                                            $CurrentRowBalanceTotal = 0;
                                                            foreach ($ActiveFeeHeads as $FeeHeadID => $FeeHead) {
                                                                echo '<td class="text-right">';
                                                                $Balance = $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Payable'] - ($BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Paid'] + $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Discount'] + $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['Concession'] + $BalanceAmount[$Details['AcademicYearMonthID']][$FeeHeadID]['WaveOff']);

                                                                $CurrentRowBalanceTotal += $Balance;

                                                                echo '<strong>' . (($Balance) ? $Balance : '--') . '</strong>';
                                                                echo '</td>';
                                                            }

                                                            echo '<td class="text-right" style="font-size: 16px; font-weight: bold; ' . (($CurrentRowBalanceTotal) ? 'color: red' : 'color: green') . '">' . (($CurrentRowBalanceTotal) ? $CurrentRowBalanceTotal : 'Paid') . '</td>';
                                                            ?>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4"></div>
                                            <div class="col-lg-4">
                                                <div class="panel panel-primary">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title">Student Fee Summary</h3>
                                                    </div>
                                                    <div class="panel-body text-right">
                                                        <?php
                                                        $TotalPayable = 0;
                                                        $TotalPaid = 0;
                                                        foreach ($BalanceAmount as $MonthID => $FeeHeadDetails) {
                                                            foreach ($FeeHeadDetails as $FeeHeadID => $FeeHeadAmount) {
                                                                $TotalPayable += $FeeHeadAmount['Payable'];
                                                                $TotalPaid += $FeeHeadAmount['Paid'] + $FeeHeadAmount['Discount'] + $FeeHeadAmount['Concession'] + $FeeHeadAmount['WaveOff'];
                                                            }
                                                        }
                                                        ?>
                                                        <div><strong>Total Payable: </strong> <?php echo number_format($TotalPayable, 2); ?></div>
                                                        <div><strong>Total Paid: </strong> <?php echo number_format($TotalPaid, 2); ?></div>
                                                        <div><strong>Total Due: </strong> <?php echo number_format($TotalPayable - $TotalPaid, 2); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4"></div>
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
            $('#DataTableRecords').DataTable({
                fixedHeader: true,
                responsive: true,
                bPaginate: false,
                bSort: false,
                searching: false,
                info: false
            });

            $('#ClassID').change(function() {

                $('#ClassSectionID').html('<option value="0">Select Section</option>');
                $('#StudentID').html('<option value="0">Select Student</option>');

                var ClassID = parseInt($(this).val());

                if (ClassID <= 0) {
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
                        $('#ClassSectionID').html('<option value="0">-- All Section --</option>' + ResultArray[1]);
                    }
                });
            });

            $('#ClassSectionID').change(function() {

                $('#StudentID').html('<option value="0">Select Student</option>');

                var ClassSectionID = parseInt($(this).val());
                var AcademicYearID = parseInt($('#AcademicYearID').val());

                if (ClassSectionID <= 0) {
                    $('#StudentID').html('<option value="0">-- All Student --</option>');
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
                        $('#StudentID').html('<option value="0">-- All Student --</option>' + ResultArray[1]);
                    }
                });
            });

            $('#AcademicYearID').change(function() {

                $('#ClassID').val(0);
                $('#ClassSectionID').html('<option value="0">Select Section</option>');
                $('#StudentID').html('<option value="0">Select Student</option>');
            });
        });
    </script>
    <!-- JavaScript To Print A Report -->
    <script src="../js/print-report.js"></script>
</body>

</html>