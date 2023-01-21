<?php
require_once('../../classes/class.users.php');
require_once('../../classes/class.validation.php');
require_once('../../classes/class.authentication.php');

require_once('../../classes/class.ui_helpers.php');

require_once('../../classes/school_administration/class.classes.php');
require_once('../../classes/school_administration/class.class_sections.php');

require_once('../../classes/school_administration/class.students.php');
require_once('../../classes/school_administration/class.student_details.php');


require_once('../../classes/class.global_settings.php');

require_once('../../includes/global_defaults.inc.php');

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

if ($LoggedUser->GetUserID() != '1000005') {
	header('location:../unauthorized_login_admin.php');
	exit;
}
$HasErrors = false;

$ClassList =  array();
$ClassList = AddedClass::GetActiveClasses();

$Clean = array();

$Clean['Process'] = 0;

$Clean['NewStudentID'] = 0;
$Clean['ClassID'] = 0;
$Clean['ClassSectionID'] = 0;
$Clean['OldStudentID'] = 0;

if (isset($_GET['StudentID'])) {
	$Clean['NewStudentID'] = (int) $_GET['StudentID'];
}

$StudentsList = array();
$ClassSectionsList = array();

$StudentsLists = array();
$StudentsLists = StudentDetail::GetStudent('Active', $Clean['NewStudentID']);

if (isset($_POST['hdnProcess'])) {
	$Clean['Process'] = (int) $_POST['hdnProcess'];
}
switch ($Clean['Process']) {
	case 2:

		if (isset($_POST['NewStudentID'])) {
			$Clean['NewStudentID'] = (int) $_POST['NewStudentID'];
		}

		if (isset($_POST['OldStudentID'])) {
			$Clean['OldStudentID'] = (int) $_POST['OldStudentID'];
		}

		if ($Clean['OldStudentID'] <= 0 && $Clean['NewStudentID'] <= 0) {
			header('location:../error_page.php');
			exit;
		}

		$RecordValidator = new Validator();

		if (!StudentDetail::UpdateAndDeleteStudentFeeStructure($Clean['NewStudentID'], $Clean['OldStudentID'])) {
			$RecordValidator->AttachTextError('Student Fee Structure is not changed.');
			$HasErrors = true;
			break;
		}

		header('location:students_list.php?Mode=UD');
		exit;
		break;
}

require_once('../html_header.php');
?>
<title>Change Student Fee Structure</title>
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
					<h1 class="page-header">Change Student Fee Structure</h1>
				</div>
				<!-- /.col-lg-12 -->
			</div>
			<form class="form-horizontal" name="ChangeStudentFeeStructure" action="change_students_fee_structure.php" method="post">
				<div class="panel panel-default">
					<div class="panel-heading">
						Change Student Fee Structure
					</div>
					<div class="panel-body">
						<?php
						if ($HasErrors == true) {
							echo $RecordValidator->DisplayErrors();
						}
						?>
						<div class="form-group">
							<label for="ClassList" class="col-lg-2 control-label">Class</label>
							<div class="col-lg-4">
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
							<label for="ClassSection" class="col-lg-2 control-label">Section</label>
							<div class="col-lg-4">
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
							<label for="Student" class="col-lg-2 control-label">Select From Student</label>
							<div class="col-lg-4">
								<select class="form-control" name="drdStudent">
									<?php
									if (is_array($StudentsLists) && count($StudentsLists) > 0) {
										foreach ($StudentsLists as $StudentID => $StudentDetails) {
											echo '<option ' . ($Clean['NewStudentID'] == $StudentID ? 'selected="selected"' : '') . ' value="' . $StudentID . '">' . $StudentDetails['FirstName'] . ' ' . $StudentDetails['LastName'] . '(' . $StudentDetails['RollNumber'] . ')</option>';
										}
									}
									?>
								</select>
							</div>
							<label for="Student" class="col-lg-2 control-label">Select To Student</label>
							<div class="col-lg-4">
								<select class="form-control Student" name="OldStudentID">
									<option value="0">Select Student</option>
									<?php
									if (is_array($StudentsList) && count($StudentsList) > 0) {
										foreach ($StudentsList as $StudentID => $StudentDetails) {
											echo '<option ' . ($Clean['OldStudentID'] == $StudentID ? 'selected="selected"' : '') . ' value="' . $StudentID . '">' . $StudentDetails['FirstName'] . ' ' . $StudentDetails['LastName'] . '(' . $StudentDetails['RollNumber'] . ')</option>';
										}
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-lg-10">
								<input type="hidden" name="NewStudentID" value="<?php echo $Clean['NewStudentID']; ?>" />
								<input type="hidden" name="hdnProcess" value="2" />
								<button type="submit" class="btn btn-primary">Save</button>
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
	<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="../vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
	<script src="../vendor/datatables-responsive/dataTables.responsive.js"></script>
	<script src="../vendor/jquery-ui/jquery-ui.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {

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
						$('#ClassSection').html('<option value="0">Select Section</option>' + ResultArray[1]);
					}
				});
			});

			$('#ClassSection').change(function() {

				var ClassSectionID = parseInt($(this).val());

				if (ClassSectionID <= 0) {
					$('.Student').html('<option value="0">Select Student</option>');
					return;
				}

				$.post("/xhttp_calls/get_students_by_class_section.php", {
					SelectedClassSectionID: ClassSectionID
				}, function(data) {
					ResultArray = data.split("|*****|");

					if (ResultArray[0] == 'error') {
						alert(ResultArray[1]);
						return false;
					} else {
						$('.Student').html('<option value="0">Select Student</option>' + ResultArray[1]);
					}
				});
			});
		});
	</script>
</body>

</html>