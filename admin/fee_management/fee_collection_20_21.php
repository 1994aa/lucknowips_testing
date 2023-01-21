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
if ($LoggedUser->HasPermissionForTask(TASK_FEE_COLLECTION_REPORT) !== true) {
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}

$PaymentModeList = array(1 => 'Cash', 2 => 'Cheque', 3 => 'Net Transfer', 4 => 'Bank Transfer', 5 => 'Card Payment');
$ChequeStatusList = array('All' => 'All', 'Pending' => 'Pending', 'Cleared' => 'Cleared', 'Bounced' => 'Bounced');

$StudentsList =  array();

$Filters = array();

$FeeCollectionDetails = array();

$RecordDeletedSuccessfully = false;
$HasErrors = false;
$TotalRecords = 0;

$Clean = array();
$Clean['Process'] = 7;

$Clean['FeeAcademicYearID'] = 0;

$Clean['TransactionDate'] = '';
$Clean['TransactionFromDate'] = '';
$Clean['TransactionToDate'] = '';
$Clean['StudentName'] = '';
$Clean['MobileNumber'] = '';


// paging and sorting variables start here  //
$Clean['AllRecords'] = '';
$Clean['CurrentPage'] = 1;
$TotalPages = 0;

$Start = 0;
$Limit = 50;
// end of paging variables//

if (isset($_GET['hdnProcess'])) {
    $Clean['Process'] = (int) $_GET['hdnProcess'];
} elseif (isset($_GET['Process'])) {
    $Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process']) {
    case 7:
        //set record filters   
        
        if (isset($_GET['txtTransactionDate'])) {
            $Clean['TransactionDate'] = strip_tags(trim($_GET['txtTransactionDate']));
        } elseif (isset($_GET['TransactionDate'])) {
            $Clean['TransactionDate'] = strip_tags(trim($_GET['TransactionDate']));
        }

        if (isset($_GET['txtTransactionFromDate'])) {
            $Clean['TransactionFromDate'] = strip_tags(trim($_GET['txtTransactionFromDate']));
        } elseif (isset($_GET['TransactionFromDate'])) {
            $Clean['TransactionFromDate'] = strip_tags(trim($_GET['TransactionFromDate']));
        }

        if (isset($_GET['txtTransactionToDate'])) {
            $Clean['TransactionToDate'] = strip_tags(trim($_GET['txtTransactionToDate']));
        } elseif (isset($_GET['TransactionToDate'])) {
            $Clean['TransactionToDate'] = strip_tags(trim($_GET['TransactionToDate']));
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
        
        $SearchValidator = new Validator();
        
        if ($Clean['TransactionDate'] != '') {
            $SearchValidator->ValidateDate($Clean['TransactionDate'], 'Please enter valid transaction date.');
        }

        if ($Clean['TransactionFromDate'] != '') {
            $SearchValidator->ValidateDate($Clean['TransactionFromDate'], 'Please enter valid transaction from date.');
            $SearchValidator->ValidateDate($Clean['TransactionToDate'], 'Please enter valid transaction to date.');
        }
        
        if ($Clean['StudentName'] != '') {
            $SearchValidator->ValidateStrings($Clean['StudentName'], 'Student name should be between 2 to 50.', 2, 50);
        }

        if ($Clean['MobileNumber'] != '') {
            $SearchValidator->ValidateStrings($Clean['MobileNumber'], 'Mobile number should be between 1 to 15.', 1, 15);
        }

        if ($SearchValidator->HasNotifications()) {
            $HasErrors = true;
            break;
        }
        
        if ($Clean['TransactionDate'] != '') {
            $Filters['TransactionDate'] = date('Y-m-d', strtotime(DateProcessing::ToggleDateDayAndMonth(($Clean['TransactionDate']))));
        }

        if ($Clean['TransactionFromDate'] != '') {
            $Filters['TransactionFromDate'] = date('Y-m-d', strtotime(DateProcessing::ToggleDateDayAndMonth(($Clean['TransactionFromDate']))));
        }

        if ($Clean['TransactionToDate'] != '') {
            $Filters['TransactionToDate'] = date('Y-m-d', strtotime(DateProcessing::ToggleDateDayAndMonth(($Clean['TransactionToDate']))));
        }

        $Filters['StudentName'] = $Clean['StudentName'];
        $Filters['MobileNumber'] = $Clean['MobileNumber'];
        $Filters['FeeAcademicYearID'] = 2;

        //get records count
        FeeCollection::SearchFeeCollectionDetailsNew_2021($TotalRecords, true, $Filters);

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
            if ($Clean['AllRecords'] == 'All') {
                $TotalPages = 1;
                $FeeCollectionDetails = FeeCollection::SearchFeeCollectionDetailsNew_2021($TotalRecords, false, $Filters, 0, $TotalRecords);
            } else {
                $FeeCollectionDetails = FeeCollection::SearchFeeCollectionDetailsNew_2021($TotalRecords, false, $Filters, $Start, $TotalRecords);
            }
        }
        // echo "<pre>";
        // var_dump($FeeCollectionDetails);exit;
        break;
}

$LandingPageMode = '';
if (isset($_GET['Mode'])) {
    $LandingPageMode = $_GET['Mode'];
}

require_once('../html_header.php');
?>
<title>Old Fee Collection Report 20-21</title>
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
                    <h1 class="page-header">Old Fee Collection Report 20-21</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <form class="form-horizontal" name="frmRoomReport" action="fee_collection_20_21.php" method="get">
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
                            } else if ($RecordDeletedSuccessfully == true) {
                                echo '<div class="alert alert-danger">Record deleted successfully.</div>';
                            } else if ($LandingPageMode == 'UD') {
                                echo '<div class="alert alert-success">Record updated successfully.</div>';
                            }
                            ?>

                            <div class="form-group">
                                <label for="TransactionDate" class="col-lg-3 control-label">Transaction Date</label>
                                <div class="col-lg-3">
                                    <input class="form-control select-date" type="text" maxlength="10" id="TransactionDate" name="txtTransactionDate" value="<?php echo $Clean['TransactionDate']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="TransactionFromDate" class="col-lg-3 control-label">Transaction Between</label>
                                <div class="col-lg-3">
                                    <input class="form-control select-date" type="text" maxlength="10" id="TransactionFromDate" name="txtTransactionFromDate" value="<?php echo $Clean['TransactionFromDate']; ?>" />
                                </div>
                                <label for="TransactionToDate" class="col-lg-1 control-label">to</label>
                                <div class="col-lg-3">
                                    <input class="form-control select-date" type="text" maxlength="10" id="TransactionToDate" name="txtTransactionToDate" value="<?php echo $Clean['TransactionToDate']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="StudentName" class="col-lg-3 control-label">Student Name</label>
                                <div class="col-lg-7">
                                    <input class="form-control" type="text" maxlength="50" id="StudentName" name="txtStudentName" value="<?php echo $Clean['StudentName']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="MobileNumber" class="col-lg-3 control-label">Mobile Number</label>
                                <div class="col-lg-7">
                                    <input class="form-control" type="text" maxlength="50" id="MobileNumber" name="txtMobileNumber" value="<?php echo $Clean['MobileNumber']; ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
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

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="print-btn-container"><button id="PrintButton" type="submit" class="btn btn-primary">Print</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">

                                    </div>
                                </div>
                                <div class="row" id="RecordTableHeading">
                                    <div class="col-lg-12">
                                        <div class="report-heading-container"><strong>Old Fee Collection Report 20-21 on <?php echo date('d-m-Y h:i A'); ?></strong></div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTable">
                                    <div class="col-lg-12">
                                        <table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Tr. ID</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>A.Y 19-20</th>
                                                    <th>A.Y 20-21</th>
                                                    <th>Paid Amt</th>
                                                    <th>Pmt. Mode</th>
                                                    <th>Fee Date</th>
                                                    <th class="print-hidden">Create User</th>
                                                    <th class="print-hidden">Create Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (is_array($FeeCollectionDetails) && count($FeeCollectionDetails) > 0) {
                                                    $Counter = $Start;
                                                    $TotalAmount = 0;
                                                    $TotalDiscount = 0;
                                                    $TotalAmountPaidAY1 = 0;
                                                    $TotalAmountPaidAY2 = 0;
                                                    $TotalAmountPaid = 0;

                                                    foreach ($FeeCollectionDetails as $FeeCollectionID => $Details) {
                                                        $TotalPayableAmount = 0;

                                                        if ($FeeCollectionID > 0) {
                                                            $TotalAmountPaidAY1 += $Details['AmountPaidForAYID1'];
                                                            $TotalAmountPaidAY2 += $Details['AmountPaidForAYID2'];

                                                            $TotalAmountPaid += $Details['AmountPaid']  + $Details['PreviousYearAmountPaid'];
                                                ?>
                                                            <tr>
                                                                <td><?php echo ++$Counter; ?></td>
                                                                <td><?php echo $Details['FeeTransactionID']; ?></td>
                                                                <td><?php echo $Details['StudentName'] ?></td>
                                                                <td><?php echo $Details['ClassName'] . '(' . $Details['SectionName'] . ')'; ?></td>
                                                                <td style="text-align: right;"><?php echo number_format($Details['AmountPaidForAYID1'], 2); ?></td>
                                                                <td style="text-align: right;"><?php echo number_format($Details['AmountPaidForAYID2'], 2); ?></td>
                                                                <td style="text-align: right;"><?php echo number_format($Details['AmountPaid'] + $Details['PreviousYearAmountPaid'], 2); ?></td>

                                                                <?php
                                                                if ($Details['PaymentMode'] == 2) {
                                                                    if ($Details['PaymentModeDetails'][2]['ChequeStatus'] == 'Bounced') {
                                                                ?>
                                                                        <td>
                                                                            <?php echo $PaymentModeList[$Details['PaymentMode']]; ?>
                                                                            <button type="button" class="btn btn-danger" style="padding-top: 0px; padding-bottom: 0px; padding-left: 2px; padding-right: 2px; font-size: 14px;">Bounced</button>
                                                                        </td>
                                                                    <?php
                                                                    } else if ($Details['PaymentModeDetails'][2]['ChequeStatus'] == 'Pending') {
                                                                    ?>
                                                                        <td>
                                                                            <?php echo $PaymentModeList[$Details['PaymentMode']]; ?>
                                                                            <button type="button" class="btn btn-info" style="padding-top: 0px; padding-bottom: 0px; padding-left: 2px; padding-right: 2px; font-size: 14px;">Pending</button>
                                                                        </td>
                                                                    <?php
                                                                    } else {
                                                                    ?>
                                                                        <td>
                                                                            <?php echo $PaymentModeList[$Details['PaymentMode']]; ?>
                                                                            <button type="button" class="btn btn-success" style="padding-top: 0px; padding-bottom: 0px; padding-left: 2px; padding-right: 2px; font-size: 14px;">Success</button>
                                                                        </td>
                                                                    <?php
                                                                    }
                                                                } else {
                                                                    ?>
                                                                    <td>
                                                                        <?php
                                                                        echo $PaymentModeList[$Details['PaymentMode']];
                                                                        ?>
                                                                    </td>
                                                                <?php
                                                                }
                                                                ?>

                                                                <td><?php echo date('d/m/Y', strtotime($Details['FeeDate'])); ?></td>
                                                                <td class="print-hidden"><?php echo $Details['CreateUserName']; ?></td>
                                                                <td class="print-hidden"><?php echo date('d/m/Y', strtotime($Details['CreateDate'])); ?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th>Grand Total : </th>
                                                        <th style="text-align: right;"><?php echo number_format($TotalAmountPaidAY1, 2); ?></th>
                                                        <th style="text-align: right;"><?php echo number_format($TotalAmountPaidAY2, 2); ?></th>
                                                        <th style="text-align: right;"><?php echo number_format($TotalAmountPaid, 2); ?></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th class="print-hidden"></th>
                                                        <th class="print-hidden"></th>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
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
        </div>
        <!-- /#page-wrapper -->

    </div>
    <div id="ViewFeeDetails" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header btn-info">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Transaction Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12" id="FeeDetails"></div>
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
            $('#ViewFeeDetails').on('show.bs.modal', function(e) {

                var feeCollectionID = $(e.relatedTarget).data('feecollectionid');

                var FeeAcademicYearID = 0;
                FeeAcademicYearID = $('#FeeAcademicYearID :selected').val();

                var FeeHeadID = 0;
                FeeHeadID = $('#FeeHead :selected').val();

                var FeeCollectionID = 0;
                FeeCollectionID = parseInt($(this).val());

                if (FeeCollectionID <= 0) {
                    alert('Error! No record found.');
                    return;
                }

                $.post("/xhttp_calls/get_fee_collection_details_by_transaction.php", {
                    SelectedFeeCollectionID: feeCollectionID,
                    PostAcademicYearID: FeeAcademicYearID,
                    PostFeeHeadID: FeeHeadID
                }, function(data) {
                    ResultArray = data.split("|*****|");

                    if (ResultArray[0] == 'error') {
                        alert(ResultArray[1]);
                        return false;
                    } else {
                        $('#FeeDetails').html(ResultArray[1]);
                    }
                });
            });

            $(".select-date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'dd/mm/yy'
            });

            $('#DataTableRecords').DataTable({
                fixedHeader: true,
                responsive: true,
                bPaginate: false,
                bSort: false,
                searching: false,
                info: false
            });

            $("body").on('click', '.delete-record', function() {
                if (!confirm("Are you sure you want to delete this Record?")) {
                    return false;
                }
            });

            $('#Class').change(function() {

                var ClassID = parseInt($(this).val());

                if (ClassID <= 0) {
                    $('#ClassSection').html('<option value="0">-- All Section --</option>');
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
                        $('#ClassSection').html('<option value="0">-- All Section --</option>' + ResultArray[1]);
                    }
                });
            });

            $('#ClassSection').change(function() {

                var ClassSectionID = parseInt($(this).val());
                var FeeAcademicYearID = parseInt($('#FeeAcademicYearID').val());

                if (ClassSectionID <= 0) {
                    $('#Student').html('<option value="0">-- All Student --</option>');
                    return;
                }

                $.post("/xhttp_calls/get_students_by_class_section.php", {
                    SelectedClassSectionID: ClassSectionID,
                    SelectedAcademicYearID: FeeAcademicYearID
                }, function(data) {
                    ResultArray = data.split("|*****|");

                    if (ResultArray[0] == 'error') {
                        alert(ResultArray[1]);
                        return false;
                    } else {
                        $('#Student').html('<option value="0">-- All Student --</option>' + ResultArray[1]);
                    }
                });
            });

            $('#FeeAcademicYearID').change(function() {

                $('#Class').val(0);
                $('#ClassSection').html('<option value="0">Select Section</option>');
                $('#Student').html('<option value="0">Select Student</option>');
            });

            $('#FeeAcademicYearID').change(function() {

                if ($(this).val() == 1) {
                    $('#AdmissionStatusBox').hide();
                } else {
                    $('#AdmissionStatusBox').show();
                }
            });

            $('#PreviousYearAmountPaid').click(function() {
                if ($(this).is(':checked')) {
                    $('#FeeHead option').prop('selected', false);
                }
            });

            $('#FeeAcademicYearID').change();
        });
    </script>
    <!-- JavaScript To Print A Report -->
    <script src="../js/print-report.js"></script>
</body>

</html>