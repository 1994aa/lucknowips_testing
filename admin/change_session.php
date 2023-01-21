<?php
$no_url = true;
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

$Process = 0;

if (isset($_POST['hdnProcess'])) {
    $Process = $_POST['hdnProcess'];
}
switch ($Process) {
    case 1:
        if (isset($_POST['optSession'])) {

            $session = $_POST['optSession'];

            if ($session == 'Old') {
                $_SESSION['DB'] = 'addedschools_lucknowips_testing';
            } else if ($session == 'New') {
                $_SESSION['DB'] = 'addedschools_lucknowips_testing-21-22';
            } else if ($session == 'NewNew') {
                $_SESSION['DB'] = 'addedschools_lucknowips_testing-22-23';
            }else if ($session == 'NewNewNew') {
                $_SESSION['DB'] = 'addedschools_lucknowips_testing-23-24';
            }

            //header('location:admin_default.php?Msg=AC');
            header('location:' . $_SESSION['redirect_url']);
            exit;
        }

        break;
}
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
                <div class="col-lg-12">
                    <h1 class="page-header">Academic Year</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <!-- /.row -->
            <hr style="clear:both;" />

            <!-- module-small-panel -->
            <div class="row">
                <form action="change_session.php" method="post">
                    <div class="form-group">
                        <label for="" class="col-lg-2 control-label">Current Academic Year</label>
                        <div class="col-lg-8">
                            <label class="radio-inline">
                                <input class="" type="radio" <?php echo (($_SESSION['DB'] != 'addedschools_lucknowips_testing-21-22') ? 'checked="checked"' : ''); ?> name="optSession" value="Old">Before 21-22
                            </label>
                            <label class="radio-inline">
                                <input class="" type="radio" <?php echo (($_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') ? 'checked="checked"' : ''); ?> name="optSession" value="New">Session 21-22
                            </label>
                            <label class="radio-inline">
                                <input class="" type="radio" <?php echo (($_SESSION['DB'] == 'addedschools_lucknowips_testing-22-23') ? 'checked="checked"' : ''); ?> name="optSession" value="NewNew">Session 22-23
                            </label>
                            <label class="radio-inline">
                                <input class="" type="radio" <?php echo (($_SESSION['DB'] == 'addedschools_lucknowips_testing-23-24') ? 'checked="checked"' : ''); ?> name="optSession" value="NewNewNew">Session 23-24
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            &nbsp;
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-lg-10">
                            <input type="hidden" name="hdnProcess" value="1">
                            <button type="submit" class="btn btn-primary">Switch</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /module-small-panel -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
    <?php
    require_once('footer.php');
    ?>
</body>

</html>