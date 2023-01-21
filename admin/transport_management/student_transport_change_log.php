<?php
require_once("../../classes/class.users.php");
require_once('../../classes/class.validation.php');
require_once("../../classes/class.authentication.php");

require_once("../../classes/school_administration/class.students.php");
require_once("../../classes/school_administration/class.student_details.php");
require_once("../../classes/transport_management/class.student_vehicle.php");

require_once("../../includes/global_defaults.inc.php");
require_once("../../includes/helpers.inc.php");

//1. RECHECK IF THE USER IS VALID //
try {
    $AuthObject = new ApplicationAuthentication;
    $LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e) {
    header('location:../unauthorized_login_admin.php');
    exit;
} catch (Exception $e) {
    header('location:../unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

// if ($LoggedUser->HasPermissionForTask(TASK_LIST_SECTION) !== true) {
//     header('location:/admin/unauthorized_login_admin.php');
//     exit;
// }

$HasErrors = false;

$Clean = array();

$Clean['StudentID'] = 0;

if (isset($_GET['StudentID'])) {
    $Clean['StudentID'] = (int) $_GET['StudentID'];
}

$StudentTransportChangeLog = array();
$StudentTransportChangeLog = StudentVehicle::GetStudentTransportChangeLogByID($Clean['StudentID']);

require_once('../html_header.php');
?>
<title>Student Transport Change Log</title>
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
                    <h1 class="page-header">Student Transport Change Log</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Total Records Returned: <?php echo count($StudentTransportChangeLog); ?></strong>
                            <div class="pull-right">
                                <button id="PrintButton" type="submit" class="btn btn-primary btn-xs">Print</button>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="text-center">Student Name:- <strong><?php echo $StudentTransportChangeLog[key($StudentTransportChangeLog)]['FirstName'] . ' ' . $StudentTransportChangeLog[key($StudentTransportChangeLog)]['LastName']; ?></strong></div>

                                    </div>
                                </div>
                                <?php
                                if ($HasErrors == true) {
                                    echo $RecordValidator->DisplayErrorsInTable();
                                }
                                ?>
                                <div class="row" id="RecordTableHeading">
                                    <div class="col-lg-12">
                                        <div class="report-heading-container"><strong>Student Transport Change Log on <?php echo date('d-m-Y h:i A'); ?></strong></div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTable">
                                    <div class="col-lg-12">
                                        <table width="100%" class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S. No</th>
                                                    <th>New Amount</th>
                                                    <th>Old Amount</th>
                                                    <th>Changed By</th>
                                                    <th>Date</th>
                                                    <th>Create Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (is_array($StudentTransportChangeLog) && count($StudentTransportChangeLog) > 0) {
                                                    $Counter = 0;
                                                    foreach ($StudentTransportChangeLog as $StudentID => $StudentTransportChangeLogDetails) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo ++$Counter; ?></td>
                                                            <td><?php echo $StudentTransportChangeLogDetails['NewAmount']; ?></td>
                                                            <td><?php echo $StudentTransportChangeLogDetails['OldAmount']; ?></td>
                                                            <td><?php echo $StudentTransportChangeLogDetails['UserName']; ?></td>
                                                            <td><?php echo date('d/m/Y', strtotime($StudentTransportChangeLogDetails['CurrentDate'])); ?></td>
                                                            <td><?php echo date('d/m/Y h:i A', strtotime($StudentTransportChangeLogDetails['CreateDateTime'])); ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="6">No Records</td>
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
    <!-- /#wrapper -->
    <?php
    require_once('../footer.php');
    if (PrintMessage($_GET, $Message)) {
    ?>
        <script type="text/javascript">
            alert('<?php echo $Message; ?>');
        </script>
    <?php
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $(".delete-record").click(function() {
                if (!confirm("Are you sure you want to delete this section?")) {
                    return false;
                }
            });
        });
    </script>
    <!-- JavaScript To Print A Report -->
    <script src="js/print-report.js"></script>
    <script src="/admin/js/print-report.js"></script>
</body>

</html>