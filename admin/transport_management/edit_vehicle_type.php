<?php
require_once("../../classes/class.users.php");
require_once("../../classes/class.validation.php");
require_once("../../classes/class.authentication.php");

require_once("../../classes/transport_management/class.vehicle_type.php");

require_once("../../includes/global_defaults.inc.php");

//1. RECHECK IF THE USER IS VALID //
try
{
    $AuthObject = new ApplicationAuthentication;
    $LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e)
{
    header('location:../unauthorized_login_admin.php');
    exit;
}
catch (Exception $e)
{
    header('location:../unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

if ($LoggedUser->HasPermissionForTask(TASK_EDIT_VEHICLE_TYPE) !== true)
{
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}

$UserMenusArray = array();
$UserMenusArray = $LoggedUser->GetUserMenus();

if (!is_array($UserMenusArray) || count($UserMenusArray) <= 0)
{
    //header('location:../logout.php');
    //exit;
}

if (isset($_POST['btnCancel']))
{
    header('location:vehicle_type_list.php');
    exit;
}

$Clean = array();

$Clean['VehicleTypeID'] = 0;

if (isset($_GET['VehicleTypeID']))
{
    $Clean['VehicleTypeID'] = (int) $_GET['VehicleTypeID'];
}
else if (isset($_POST['hdnVehicleTypeID']))
{
    $Clean['VehicleTypeID'] = (int) $_POST['hdnVehicleTypeID'];
}

if ($Clean['VehicleTypeID'] <= 0)
{
    header('location:../error.php');
    exit;
}   

try
{
    $VehicleTypeToEdit = new VehicleType($Clean['VehicleTypeID']);
}
catch (ApplicationDBException $e)
{
    header('location:../error.php');
    exit;
}
catch (Exception $e)
{
    header('location:../error.php');
    exit;
}

$HasErrors = false;

$Clean['Process'] = 0;

$Clean['VehicleType'] = '';

$Clean['IsActive'] = 1;

if (isset($_POST['hdnProcess']))
{
    $Clean['Process'] = (int) $_POST['hdnProcess'];
}
else if (isset($_GET['Process']))
{
    $Clean['Process'] = (int) $_GET['Process'];
}
switch ($Clean['Process'])
{
    case 3:     
        if (isset($_POST['txtVehicleType']))
        {
            $Clean['VehicleType'] = strip_tags(trim($_POST['txtVehicleType']));
        }

        if (!isset($_POST['chkIsActive']))
        {
            $Clean['IsActive'] = 0;
        }
                
        $NewRecordValidator = new Validator();
        $NewRecordValidator->ValidateStrings($Clean['VehicleType'], 'Vehicle Type  is required and should be between 3 and 50 characters.', 3, 50);

        if ($NewRecordValidator->HasNotifications())
        {
            $HasErrors = true;
            break;
        }
                
        $VehicleTypeToEdit->SetVehicleType($Clean['VehicleType']);
        $VehicleTypeToEdit->SetIsActive($Clean['IsActive']);

        if (!$VehicleTypeToEdit->Save())
        {
            $NewRecordValidator->AttachTextError(ProcessErrors($VehicleTypeToEdit->GetLastErrorCode()));
            $HasErrors = true;

            break;
        }
        
        header('location:vehicle_type_list.php?Mode=UD');
        exit;
    break;

    case 2:
        $Clean['VehicleType'] = $VehicleTypeToEdit->GetVehicleType();
        $Clean['IsActive'] = $VehicleTypeToEdit->GetIsActive();

    break;
}

require_once('../html_header.php');
?>
<title>Edit Vehicle Type</title>
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
                    <h1 class="page-header">Edit Vehicle Type</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
             <form class="form-horizontal" name="EditFeeGroup" action="edit_vehicle_type.php" method="post">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Enter Vehicle Type Details
                    </div>
                    <div class="panel-body">
<?php
                        if ($HasErrors == true)
                        {
                            echo $NewRecordValidator->DisplayErrors();
                        }
?>                    
                        <div class="form-group">
                            <label for="VehicleType" class="col-lg-2 control-label">Vehicle Type</label>
                            <div class="col-lg-4">
                                <input class="form-control" type="text" maxlength="50" id="VehicleType" name="txtVehicleType" value="<?php echo $Clean['VehicleType']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="IsActive" class="col-lg-2 control-label">Is Active</label>
                            <div class="col-lg-4">
                                <input type="checkbox" id="IsActive" name="chkIsActive" <?php echo ($Clean['IsActive'] == 1) ? 'checked="checked"' : ''; ?> value="1" />
                            </div>
                        </div>
                        <div class="form-group">
                        <div class="col-sm-offset-2 col-lg-10">
                            <input type="hidden" name="hdnProcess" value="3"/>
                            <input type="hidden" name="hdnVehicleTypeID" value="<?php echo $Clean['VehicleTypeID']; ?>" />
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="submit" class="btn btn-primary" name="btnCancel">Cancel</button>
                        </div>
                      </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
<?php
require_once('../footer.php');
?>
</body>
</html>