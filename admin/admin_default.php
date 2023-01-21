<?php
require_once("../classes/class.users.php");
require_once("../classes/class.authentication.php");

require_once("../includes/global_defaults.inc.php");

require_once("../classes/class.helpers.php");

//1. RECHECK IF THE USER IS VALID //
try {
    $AuthObject = new ApplicationAuthentication;
    $LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e) {
    header('location:unauthorized_login_admin.php');
    exit;
} catch (Exception $e) {
    header('location:unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

if (isset($_SESSION['CurrentModuleID'])) {
    unset($_SESSION['CurrentModuleID']);
}

$ModuleList = array();
$ModuleList = Helpers::GetApplicableModules($LoggedUser->GetUserID());

$StudentDOBList = array();
$StudentDOBList = Helpers::GetStudentDOB();

$StudentsAbsentList = array();
$StudentsAbsentList = Helpers::GetStudentAbsentList(3);

$Summary = array();
$Summary = Helpers::GetSchoolSummary();

require_once('html_header.php');
?>
<title>Welcome To Admin Section</title>
</head>

<body>

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <?php
            require_once('site_header.php');
            require_once('left_navigation_menu.php');
            ?>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-4">
                    <h1 class="page-header">Dashboard</h1>
                </div>
                <div class="col-lg-6">
                    <h4 style="color:red;">Your service will be expired on 24<sup>th</sup> December, please contact to administrator.</h4>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="tile-header text-center">Students Strength</div>
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-users fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                        <?php echo (isset($Summary['TotalStudents']) ? $Summary['TotalStudents'] : 0); ?>
                                    </div>
                                    <div>Students and still counting...</div>
                                </div>
                            </div>
                        </div>
                        <a href="/admin/school_administration/students_list.php">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="tile-header text-center">Faculties Strength</div>
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                        <?php echo (isset($Summary['TotalFaculty']) ? $Summary['TotalFaculty'] : 0); ?>
                                    </div>
                                    <div>Faculties Working!</div>
                                </div>
                            </div>
                        </div>
                        <a href="/admin/school_administration/branch_staff_list.php">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="tile-header text-center">Other Staff Strength</div>
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-university fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                        <?php echo (isset($Summary['TotalNonTeachingStaff']) ? $Summary['TotalNonTeachingStaff'] : 0); ?>
                                    </div>
                                    <div>Other Staff!</div>
                                </div>
                            </div>
                        </div>
                        <a href="/admin/school_administration/branch_staff_list.php">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /.row -->
            <hr style="clear:both;" />
            <!-- -----------------------------------(modules and slider data)-26/11/2021 by prem kumar----------------------- -->
            <div class="row">
                <div class="col-md-8">

                    <div class="left-box">
                        <div class="left-box-in">
                            <table class="table table-bordered">
                                <thead style="background: gray; color:white;">
                                    <tr>
                                        <th scope="col" class="text-center">S.No.</th>
                                        <th scope="col" class="text-center">Name</th>
                                        <th scope="col" class="text-center">Action</th>
                                        <!-- <th scope="col">Handle</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $counter = 0;
                                    foreach ($ModuleList as $ModuleID => $ModuleName) {
                                        $counter++;
                                    ?>
                                        <tr>
                                            <th scope="row" class="text-center"><?php echo $counter; ?></th>
                                            <td class="text-center"> <?php echo $ModuleName; ?></td>
                                            <td class="text-center"> <a class="btn btn-default btn-success" href="module_default.php?CurrentModuleID=<?php echo $ModuleID; ?>">Open&nbsp;<i class="fa fa-arrow-circle-right"></i></a></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="right-box">
                        <div class="left-box-in">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="background: gray; color:white;" colspan="4">Today's Birthday !!</th>
                                    </tr>
                                    <tr>
                                        <th scope="col">S.No.</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">DOB</th>
                                        <th scope="col">Class</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="widget">
                                <div class="scrollPanel">
                                    <div class="data">
                                        <table class="dataTable table">
                                            <tbody>
                                                <?php
                                                if (count($StudentDOBList) > 0) {
                                                    $counter = 0;
                                                    foreach ($StudentDOBList as $StudentID => $StudentDOB) {
                                                ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo ++$counter; ?></td>
                                                            <td class="text-center"><?php echo $StudentDOB['FirstName'] . ' ' . $StudentDOB['LastName']; ?></td>
                                                            <td class="text-center"><?php echo date('d/m/Y', strtotime($StudentDOB['DOB'])); ?></td>
                                                            <td class="text-center"><?php echo $StudentDOB['ClassName']; ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td class="text-center" style="color: red;"><strong>Record not Found.</strong></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="left-box-in" id="right-down-box">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="background: gray; color:white;" colspan="4">Absent Students !!</th>
                                    </tr>
                                    <tr>
                                        <th scope="col">S.No.</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Absent</th>
                                        <th scope="col">Class</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="widget">
                                <div class="scrollPanel">
                                    <div class="data">
                                        <table class="dataTable table">
                                            <tbody>
                                                <?php
                                                if (count($StudentsAbsentList) > 0) {
                                                    $counter = 0;
                                                    foreach ($StudentsAbsentList as $StudentID => $StudentsAbsent) {
                                                ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo ++$counter; ?></td>
                                                            <td><?php echo $StudentsAbsent['FirstName'] . ' ' . $StudentsAbsent['LastName']; ?></td>
                                                            <td><?php echo $StudentsAbsent['Counter'] . ' days'; ?></td>
                                                            <td><?php echo $StudentsAbsent['ClassName']; ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td class="text-center" style="color: red;"><strong>Record not Found.</strong></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- -----------------------------------///(modules and slider data)-26/11/2021 by prem kumar----------------------- -->
                    </div>
                    <!-- /#page-wrapper -->

                </div>
            </div>
        </div>
    </div>
    <!-- /#wrapper -->
    <?php

    if (isset($_GET['Msg']) && $_GET['Msg'] == 'AC') {
    ?>
        <script>
            alert('Academic year changed successfully.')
        </script>
    <?php
    }

    require_once('footer.php');
    ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {

            rotateScrollWidgetDataTop($(".widget"));

            function rotateScrollWidgetDataTop(_widget) {
                var animationDuration = 10000;
                var scrollableData = _widget.find(".data");
                var destinationTopPosition = "-" + (scrollableData.height() + 10) + "px";
                var startingBottomPosition = (_widget.height() - 5) + "px";

                scrollableData.animate({
                    "top": destinationTopPosition
                }, {
                    duration: animationDuration,

                    complete: function() {
                        $(this).css("top", startingBottomPosition);
                        rotateScrollWidgetDataTop(_widget);
                    }
                });
            }

            $(".data").mouseover(function() {
                $(this).stop(); //Stop the animation when mouse in
            });

            $(".data").mouseleave(function() {
                rotateScrollWidgetDataTop($(".widget"));

                function rotateScrollWidgetDataTop(_widget) {
                    var animationDuration = 10000;
                    var scrollableData = _widget.find(".data");
                    var destinationTopPosition = "-" + (scrollableData.height() + 10) + "px";
                    var startingBottomPosition = (_widget.height() - 5) + "px";

                    scrollableData.animate({
                        "top": destinationTopPosition
                    }, {
                        duration: animationDuration,

                        complete: function() {
                            $(this).css("top", startingBottomPosition);
                            rotateScrollWidgetDataTop(_widget);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>