<?php
require_once("../classes/class.users.php");
require_once("../classes/class.validation.php");
require_once("../classes/class.authentication.php");

require_once("../classes/school_administration/class.parent_details.php");
require_once("../classes/school_administration/class.branch_staff.php");

require_once("../includes/global_defaults.inc.php");

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

if ($LoggedUser->HasPermissionForTask(TASK_CHANGE_PASSWORD) !== true) {
	header('location:unauthorized_login_admin.php');
	exit;
}

$HasErrors = false;

$SelectedUserType = '';
$SelectedUserName = '';
$SelectedUserMobile = '';

$Clean['Process'] = 0;

$Clean['UserName'] = '';
$Clean['Password'] = '';

if (isset($_POST['hdnProcess'])) {
	$Clean['Process'] = (int) $_POST['hdnProcess'];
} elseif (isset($_GET['Process'])) {
	$Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process']) {
	case 1:
		if (isset($_POST['hdnUserName'])) {
			$Clean['UserName'] = strip_tags(trim($_POST['hdnUserName']));
		}
		if (isset($_POST['txtNewPassword'])) {
			$Clean['Password'] = strip_tags(trim($_POST['txtNewPassword']));
		}

		$RecordValidator = new Validator();

		if (!$RecordValidator->ValidateStrings($Clean['UserName'], 'Please enter username.', 4, 250)) {
			header('location:/admin/error.php');
			exit;
		}

		$RecordValidator->ValidateStringsSpecialChar($Clean['Password'], "0-9_.@-", 'Please enter a valid new password. It should be between 4 and 12 chars.', 4, 12);

		try {
			$SelectedUser = new User(0, $Clean['UserName']);
		} catch (ApplicationDBException $e) {
			header('location:/admin/error.php');
			exit;
		} catch (Exception $e) {
			header('location:/admin/error.php');
			exit;
		}

		if ($RecordValidator->HasNotifications()) {
			$HasErrors = true;
			break;
		}

		if ($SelectedUser->GetRoleID() != 13 && $SelectedUser->GetRoleID() != 14 && $SelectedUser->GetRoleID() != 15) {
			header('location:/admin/error.php');
			exit;
		}
		
		if ($LoggedUser->ChangePasswordByAdmin($Clean['Password'], $Clean['UserName'])) {
			header('location:change_user_password.php?POMode=RU');
			exit;
		} else {
			$RecordValidator->AttachTextError("The password could not be changed. There was an error.");
			$HasErrors = true;
			break;
		}
		break;

	case 7:
		$RecordValidator = new Validator();

		if (isset($_POST['txtUserName'])) {
			$Clean['UserName'] = strip_tags(trim($_POST['txtUserName']));
		} else if (isset($_GET['UserName'])) {
			$Clean['UserName'] = strip_tags(trim($_GET['UserName']));
		}

		if (!$RecordValidator->ValidateStrings($Clean['UserName'], "Please enter the user name which should be between 4 and 250 characters.", 4, 250)) {
			$HasErrors = true;
			break;
		}

		try {
			$SelectedUser = new User(0, $Clean['UserName']);

			if ($SelectedUser->GetRoleID() == '13')
			{
				$SelectedUserType = 'Faculty';
			}
			else if ($SelectedUser->GetRoleID() == '14')
			{
				$SelectedUserType = 'Management Staff';
			}
			else if ($SelectedUser->GetRoleID() == '15')
			{
				$SelectedUserType = 'Parent';
			}
			else
			{
				$RecordValidator->AttachTextError("This username is not present.");
				$HasErrors = true;
				break;
			}
			
			if ($SelectedUser->GetRoleID() == 15)
			{
				$UserDetails = new ParentDetail(0, $Clean['UserName']);
				
				$SelectedUserName = $UserDetails->GetFatherFirstName().' '.$UserDetails->GetFatherLastName();
				$SelectedUserMobile = $UserDetails->GetFatherMobileNumber();
			}
			else
			{
				$UserDetails = new BranchStaff(0, $Clean['UserName']);

				$SelectedUserName = $UserDetails->GetFirstName().' '.$UserDetails->GetLastName();
				$SelectedUserMobile = $UserDetails->GetMobileNumber1();
			}

		} catch (ApplicationDBException $e) {
			$RecordValidator->AttachTextError("This username is not present.");
		} catch (Exception $e) {
			$RecordValidator->AttachTextError("This username is not present.");
		}

		if ($RecordValidator->HasNotifications()) {
			$HasErrors = true;
			break;
		}

		if ($SelectedUser->GetRoleID() != 13 && $SelectedUser->GetRoleID() != 14 && $SelectedUser->GetRoleID() != 15) {
			$RecordValidator->AttachTextError('Only passsword of a Faculty, Management Staff or Parent can be changed from here.');
			unset($SelectedUser);
			$HasErrors = true;
			break;
		}
		break;
}

require_once('html_header.php');
?>
<title>Change User Password</title>
<link href="/admin/vendor/jquery-ui/jquery-ui.min.css" rel="stylesheet">
<style>
.parent-category {
    font-weight: bold;
}

.view-password {
	cursor: pointer;
}
</style>
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
                    <h1 class="page-header">Change User Password</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>

            <form class="form-horizontal" name="SearchUser" action="change_user_password.php" method="post" autocomplete="off">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Enter User Name
                    </div>
                    <div class="panel-body">
<?php
                        if ($HasErrors == true)
                        {
                            echo $RecordValidator->DisplayErrors();
                        }
?>                      
                        <div class="form-group">
                            <label for="UserName" class="col-lg-2 control-label">User Name</label>
                            <div class="col-lg-4">
                                <input class="form-control" type="text" required="required" name="txtUserName" maxlength="100" placeholder="Enter Name" id="UserName"  value="<?php echo $Clean['UserName']; ?>" />
                            </div>
                        </div> 

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-lg-10">
                                <input type="hidden" name="hdnProcess" value="7"/>
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
<?php
            if (($Clean['Process'] == 7 || $Clean['Process'] == 1) && isset($SelectedUser) && $HasErrors == false)
            {
?>
                <form class="form-horizontal" name="ChangePassword" action="change_user_password.php" method="post" autocomplete="off">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="Name" class="col-lg-2 control-label">Name</label>
                                <div class="col-lg-4">
                                    <p class="form-control-static"><?php echo $SelectedUserName; ?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Name" class="col-lg-2 control-label">User Type</label>
                                <div class="col-lg-4">
                                    <p class="form-control-static"><?php echo $SelectedUserType; ?></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Name" class="col-lg-2 control-label">Mobile #</label>
                                <div class="col-lg-4">
                                    <p class="form-control-static"><?php echo $SelectedUserMobile; ?></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Password" class="col-lg-2 control-label">New Password</label>
                                <div class="col-lg-4">
									<div class="input-group">
										<input class="form-control" type="password" required="required" name="txtNewPassword" maxlength="12" placeholder="Enter New Password" id="Password" />
										<span class="input-group-addon view-password" title="View Password"><i class="fa fa-eye" aria-hidden="true"></i></span>
									</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-lg-10">
                                <input type="hidden" name="hdnUserName" value="<?php echo $Clean['UserName']; ?>" />
                                    <input type="hidden" name="hdnProcess" value="1"/>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
<?php
            }
?>                    
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
<?php
require_once('footer.php');
?>
<script type="text/javascript">
<?php
    if (isset($_GET['POMode']))
    {
        if ($_GET['POMode'] == 'RU')
        {
            echo 'alert("Record Updated Successfully.");';
        }
    }
?>
		$(function () {
			$('.view-password').click(function() {
				var current_type = $('#Password').attr('type');
				
				if (current_type == 'password')
				{
					$('#Password').attr('type', 'text');
				}
				else
				{
					$('#Password').attr('type', 'password');
				}
			});
		});
    </script>
</body>
</html>