<?php
require_once("../../classes/class.users.php");
require_once("../../classes/class.validation.php");
require_once("../../classes/class.authentication.php");
require_once("../../includes/global_defaults.inc.php");
require_once("../../classes/class.date_processing.php");

require_once("../../classes/inventory_management/class.product_categories.php");
require_once("../../classes/inventory_management/class.products.php");
require_once("../../classes/inventory_management/class.departments.php");
require_once("../../classes/school_administration/class.branch_staff.php");
require_once("../../classes/inventory_management/class.stock_issue.php");
require_once("../../classes/inventory_management/class.stock_return.php");

//1. RECHECK IF THE USER IS VALID //
try
{
	$AuthObject = new ApplicationAuthentication;
	$LoggedUser = new User(0, $AuthObject->CheckValidUser());
}

// THIS CATCH BLOCK BUBBLES THE EXCEPTION TO THE BUILT IN 'Exception' CLASS IF THERE ARE ANY UNCAUGHT ERRORS //
catch (ApplicationAuthException $e)
{
	header('location:unauthorized_login_admin.php');
	exit;
}
catch (Exception $e)
{
	header('location:unauthorized_login_admin.php');
	exit;
}
// END OF 1. //

if ($LoggedUser->HasPermissionForTask(TASK_ADD_STOCK_ISSUE) !== true)
{
	header('location:/admin/unauthorized_login_admin.php');
	exit;
}

$StaffCategoryList = array('Teaching' => 'Teaching Staff', 'NonTeaching' => 'Non Teaching Staff');

$AllProductCategoryList = array();
$AllProductCategoryList = ProductCategory::GetActiveProductCategories();

$AllProductList = array();
$AllProductList = Product::GetProductsByProductCategoryID(key($AllProductCategoryList));

$IssueTypeList = array();
$IssueTypeList = array('Staff' => 'Staff', 'Department' => 'Department');

$Clean = array();

$Clean['StockReturnID'] = 0;

$Clean['StaffCategory'] = '';
$Clean['BranchStaffID'] = 0;

$InputErrors = array();

$HasErrors = false;
$ViewOnly = false;

$Clean['Process'] = 0;

$Clean['ProductID'] = (key($AllProductList));

$Clean['IssueType'] = 'Staff';
$Clean['DepartmentID'] = 0;

$Clean['StockReturnDetailsRow'] = array();

$AllBranchStaffList = array();

if (isset($_GET['StockReturnID']))
{
	$Clean['StockReturnID'] = (int) $_GET['StockReturnID'];
}
elseif (isset($_POST['hdnStockReturnID']))
{
	$Clean['StockReturnID'] = (int) $_POST['hdnStockReturnID'];
}

//$Clean['StockReturnID'] = 1;

if ($Clean['StockReturnID'] <= 0)
{
	header('location:../error.php');
	exit;
}

try
{
	$StockReturnObj = new StockReturn($Clean['StockReturnID']);
	$Clean['BranchStaffID'] = $StockReturnObj->GetBranchStaffID();
	$BranchStaffDetails = new BranchStaff($Clean['BranchStaffID']);
	$Clean['StaffCategory'] = $BranchStaffDetails->GetStaffCategory();

	$AllBranchStaffList = BranchStaff::GetActiveBranchStaff($Clean['StaffCategory']);
	
	$Clean['IssueType'] = $StockReturnObj->GetIssueType();
	$Clean['DepartmentID'] = $StockReturnObj->GetDepartmentID();
	$StockReturnObj->FillStockReturnDetails();
	$Clean['StockReturnDetailsRow'] = $StockReturnObj->GetStockReturnDetails();
	
	foreach ($Clean['StockReturnDetailsRow'] as $StockReturnID => $Details)
	{
		$InputErrors[$StockReturnID]['ProductID'] = 0;
		$InputErrors[$StockReturnID]['Quantity'] = 0;

		$InputErrors[$StockReturnID]['ReturnDate'] = 0;
	}
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

require_once('../html_header.php');
?>
<title>View Stock Return</title>
<!-- DataTables CSS -->
<link href="/admin/vendor/jquery-ui/jquery-ui.min.css" rel="stylesheet">
<link href="/admin/vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="/admin/vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
<style type="text/css">
	#AddIssuedProductRow .input-style
	{
		width:100%;
	}
</style>
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
                    <h1 class="page-header">View Stock Return</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
			<form class="form-horizontal" name="AddProductPurchase" action="edit_stock_issue.php" method="post">
				<div class="panel panel-default">
                    <div class="panel-heading">
                        Stock Reterner's Details
                    </div>
                    <div class="panel-body">
						<?php
						if ($HasErrors == true)
						{
							echo $NewRecordValidator->DisplayErrors();
						}
						?>
                        <div class="form-group">
							<label for="StaffCategory" class="col-lg-2 control-label">Staff Category</label>
							<div class="col-lg-4">
								<select class="form-control" name="drdStaffCategory" id="StaffCategory">
									<?php
									foreach ($StaffCategoryList as $StaffCategory => $StaffCategoryName)
									{
										?>
										<option <?php echo ($StaffCategory == $Clean['StaffCategory'] ? 'selected="selected"' : ''); ?> value="<?php echo $StaffCategory; ?>"><?php echo $StaffCategoryName; ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<label for="BranchStaff" class="col-lg-2 control-label">Branch Staff</label>
							<div class="col-lg-4">
								<select class="form-control"  name="drdBranchStaff" id="BranchStaffID">
									<?php
									foreach ($AllBranchStaffList as $BranchStaffID => $BranchStaffName)
									{
										?>
										<option <?php echo ($BranchStaffID == $Clean['BranchStaffID'] ? 'selected="selected"' : ''); ?> value="<?php echo $BranchStaffID; ?>"><?php echo $BranchStaffName['FirstName'] . " " . $BranchStaffName['LastName']; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Enter Stock Issue Details
                    </div>
                    <div class="panel-body">
						<div class="row" id="RecordTable">
							<div class="col-lg-12">
								<table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
									<thead>
										<tr>
											<th>Product Category</th>
											<th>Product</th>
											<th>Returned Quantity</th>
											<th>Return Date</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody id="AddIssuedProductRow">
										<?php
										if (is_array($Clean['StockReturnDetailsRow']) && count($Clean['StockReturnDetailsRow']) > 0)
										{
											foreach ($Clean['StockReturnDetailsRow'] as $StockReturnID => $IssuedStockDetails)
											{
												?>
												<tr>
													<td>
														<select class="form-control ProductCategory input-style" name="StockIssueDetailsRow[<?php echo $StockReturnID; ?>][ProductCategoryID]">
															<?php
															foreach ($AllProductCategoryList as $ProductCategoryID => $ProductCategoryName)
															{
																?>
																<option <?php echo ($IssuedStockDetails['ProductCategoryID'] == $ProductCategoryID) ? 'selected="selected"' : ''; ?> value="<?php echo $ProductCategoryID; ?>"><?php echo $ProductCategoryName; ?></option>
																<?php
															}
															?>
														</select>
													</td>
													<td>
														<select class="form-control ProductList input-style" name="StockIssueDetailsRow[<?php echo $StockReturnID; ?>][ProductID]">
															<?php
															if (!empty($IssuedStockDetails['ProductCategoryID']))
															{
																$AllProductList = Product::GetProductsByProductCategoryID($IssuedStockDetails['ProductCategoryID']);
															}
															else
															{
																$AllProductList = Product::GetProductsByProductCategoryID($Clean['ProductID']);
															}

															foreach ($AllProductList as $ProductID => $ProductName)
															{
																?>
																<option <?php echo ($IssuedStockDetails['ProductID'] == $ProductID) ? 'selected="selected"' : ''; ?> value="<?php echo $ProductID; ?>"><?php echo $ProductName; ?></option>
																<?php
															}
															?>
														</select>
													</td>

													<td <?php echo($InputErrors[$StockReturnID]['Quantity']) ? 'class="has-error"' : ''; ?>>
														<input class="form-control IssuedQuantity" type="text" maxlength="7" name="StockIssueDetailsRow[<?php echo $StockReturnID; ?>][Quantity]" value="<?php echo ($IssuedStockDetails['Quantity']) ? $IssuedStockDetails['Quantity'] : '' ?>" />
													</td>


													<td <?php echo($InputErrors[$StockReturnID]['ReturnDate']) ? 'class="has-error"' : ''; ?>>
														<input class="form-control ReturnDate dtepicker" type="text" maxlength="10" name="StockIssueDetailsRow[<?php echo $StockReturnID; ?>][ReturnDate]" value="<?php echo ($IssuedStockDetails['ReturnDate']) ? date('d/m/Y', strtotime($IssuedStockDetails['ReturnDate'])) : '' ?>" />
													</td>

													<td>
														<button type="button" class="btn btn-danger RemoveRow" style="margin-right: 2%;"> <span class="icon icon-remove">X</span></button>
													</td>
												</tr>
												<?php
											}
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-12 text-right">
								<button type="button" class="btn btn-success" id="AddMoreIssuedProductRow">Add More</button>
							</div>
						</div>
                        <div class="form-group">
							<div class="col-sm-offset-3 col-lg-10">
								<input type="hidden" name="hdnProcess" value="3" />
								<input type="hidden" name="hdnStockReturnID" value="<?php echo $Clean['StockReturnID']; ?>" />
								<button type="submit" class="btn btn-primary" onClick="return ValidateForm();">Save</button>
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

	if (isset($_GET['ViewOnly']))
	{
		$ViewOnly = true;
	}
	?>
	<!-- DataTables JavaScript -->
	<script src="/admin/vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="/admin/vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
	<script src="/admin/vendor/datatables-responsive/dataTables.responsive.js"></script>
	<script src="/admin/vendor/jquery-ui/jquery-ui.min.js"></script>	
	<script type="text/javascript">
	$ (document).ready (function ()
	{
		var ViewOnly = '<?php echo $ViewOnly; ?>';

		if (true)
		{
			$ ('input, select, textarea, button[type="button"]').prop ('disabled', true);
			$ ('#Check').hide ();
			$ ('button[type="submit"]').text ('Close').attr ('onClick', 'window.close();');
		}
	})
	</script>
	<!-- JavaScript To Print A Report -->
</body>
</html>