<?php
require_once("../../classes/class.users.php");
require_once("../../classes/class.validation.php");
require_once("../../classes/class.authentication.php");

require_once("../../classes/class.ui_helpers.php");

require_once("../../classes/library_management/class.books.php");
require_once("../../classes/library_management/class.book_categories.php");
require_once("../../classes/library_management/class.book_issue.php");

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
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}
catch (Exception $e)
{
    header('location:/admin/unauthorized_login_admin.php');
    exit;
}
// END OF 1. //

if ($LoggedUser->HasPermissionForTask(TASK_VIEW_ISSUE_BOOK_REPORT) !== true)
{
	header('location:/admin/unauthorized_login_admin.php');
	exit;
}

$IssuedBookList = array();
$Filters = array();

$ParentCategoryList = array();
$ParentCategoryList = BookCategory::GetActiveParentCategories();

$BookCategoryList = array();

$BookTypeList = array('Academic' => 'Academic', 'NonAcademic' => 'Non-Academic');

$HasErrors = false;
$TotalRecords = 0;

$Clean = array();
$Clean['Process'] = 0;

$Clean['ParentCategoryID'] = 0;
$Clean['BookCategoryID'] = 0;

$Clean['BookName'] = '';
$Clean['AuthorName'] = '';
$Clean['IsReturned'] = 0;

// paging and sorting variables start here  //
$Clean['CurrentPage'] = 1;
$TotalPages = 0;

$Start = 0;
$Limit = 3;
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
        if (isset($_GET['drdParentCategory']))
        {   
            $Clean['ParentCategoryID'] = (int) $_GET['drdParentCategory'];
        }
        elseif (isset($_GET['ParentCategoryID']))
        {
            $Clean['ParentCategoryID'] = (int) $_GET['ParentCategoryID'];
        }

        if (isset($_GET['drdBookCategory']))
        {
            $Clean['BookCategoryID'] = (int) $_GET['drdBookCategory'];
        }
        elseif (isset($_GET['BookCategoryID']))
        {
            $Clean['BookCategoryID'] = (int) $_GET['BookCategoryID'];
        }

        if (isset($_GET['txtBookName']))
        {
            $Clean['BookName'] = strip_tags(trim($_GET['txtBookName']));
        }
        elseif (isset($_GET['BookName']))
        {
            $Clean['BookName'] = strip_tags(trim($_GET['BookName']));
        }

        if (isset($_GET['txtAuthorName']))
        {
            $Clean['AuthorName'] = strip_tags(trim($_GET['txtAuthorName']));
        }
        elseif (isset($_GET['AuthorName']))
        {
            $Clean['AuthorName'] = strip_tags(trim($_GET['AuthorName']));
        }
        
        if (isset($_GET['optIsReturned']))
        {
            $Clean['IsReturned'] = (int) $_GET['optIsReturned']; 
        }
        elseif (isset($_GET['IsReturned']))
        {
            $Clean['IsReturned'] = (int) $_GET['IsReturned'];
        }

        $SearchValidator = new Validator();

        if ($Clean['ParentCategoryID'] != 0)
        {
            $SearchValidator->ValidateInSelect($Clean['ParentCategoryID'], $ParentCategoryList, 'Unknown Error, Please try again.');

            $BookCategoryList = BookCategory::GetActiveSubCategories($Clean['ParentCategoryID']);
            if ($Clean['BookCategoryID'] != 0)
            {
                $SearchValidator->ValidateInSelect($Clean['BookCategoryID'], $BookCategoryList, 'Unknown Error, Please try again.');
            }
        }

        if ($Clean['BookName'] != '')
        {
            $SearchValidator->ValidateStrings($Clean['BookName'], 'Book name is required and should be between 3 and 150 characters.', 3, 150);
        }
        
        if ($Clean['IsReturned'] < 0 || $Clean['IsReturned'] > 2)
        {
            $SearchValidator->AttachTextError('Unknown Error, Please try again.');
        }

        if ($SearchValidator->HasNotifications())
        {
            $HasErrors = true;
            break;
        }

        //set record filters        
        $Filters['ParentCategoryID'] = $Clean['ParentCategoryID'];
        $Filters['BookCategoryID'] = $Clean['BookCategoryID'];
        $Filters['BookName'] = $Clean['BookName'];        
        $Filters['IsReturned'] = $Clean['IsReturned'];        

        //get records count
        BookIssue::SearchIssuedBooks($TotalRecords, true, $Filters);

        if ($TotalRecords > 0)
        {
            // Paging and sorting calculations start here.
            $TotalPages = (($TotalRecords % $Limit) == 0) ? $TotalRecords / $Limit : floor($TotalRecords / $Limit) + 1;

            if (isset($_GET['CurrentPage']))
            {
                $Clean['CurrentPage'] = (int) $_GET['CurrentPage'];
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
            $IssuedBookList = BookIssue::SearchIssuedBooks($TotalRecords, false, $Filters, $Start, $Limit);
        }
        break;
}

$LandingPageMode = '';
if (isset($_GET['Mode']))
{
    $LandingPageMode = $_GET['Mode'];
}

require_once('../html_header.php');
?>
<title>Issued Book Report</title>
<!-- DataTables CSS -->
<link href="../vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="../vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
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
                    <h1 class="page-header">Issued Book Report</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <form class="form-horizontal" name="frmBookReport" action="issued_book_report.php" method="get">
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
                            else if ($LandingPageMode == 'AS')
                            {
                                echo '<div class="alert alert-success">Record saved successfully.</div>';
                            }                           
                            else if ($LandingPageMode == 'UD')
                            {
                                echo '<div class="alert alert-success">Record updated successfully.</div>';
                            }
?>
                            <div class="form-group">
                                <label for="ParentCategory" class="col-lg-2 control-label">Category</label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="drdParentCategory" id="ParentCategory">
                                        <option value="0">-- All Category --</option>
    <?php
                                    if (is_array($ParentCategoryList) && count($ParentCategoryList) > 0)
                                    {
                                        foreach($ParentCategoryList as $ParentCategoryID => $ParentCategoryName)
                                        {
                                            echo '<option ' . (($Clean['ParentCategoryID'] == $ParentCategoryID) ? 'selected="selected"' : '' ) . ' value="' . $ParentCategoryID . '">' . $ParentCategoryName . '</option>';
                                        }
                                    }
    ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="BookCategory" class="col-lg-2 control-label">Sub Category</label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="drdBookCategory" id="BookCategory">
                                        <option value="">-- All Sub Category --</option>
    <?php
                                    if (is_array($BookCategoryList) && count($BookCategoryList) > 0)
                                    {
                                        foreach($BookCategoryList as $BookCategoryID => $BookCategoryName)
                                        {
                                            echo '<option ' . (($Clean['BookCategoryID'] == $BookCategoryID) ? 'selected="selected"' : '' ) . ' value="' . $BookCategoryID . '">' . $BookCategoryName . '</option>';
                                        }
                                    }
    ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="BookName" class="col-lg-2 control-label">Book Name</label>
                                <div class="col-lg-4">
                                    <input class="form-control" type="text" maxlength="150" id="BookName" name="txtBookName" value="<?php echo $Clean['BookName']; ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="IsReturned" class="col-lg-2 control-label">Status</label>
                                <div class="col-lg-6">
                                    <label class="radio-inline">
                                        <input type="radio" <?php echo (($Clean['IsReturned'] == 0) ? 'checked="checked"' : ''); ?> name="optIsReturned" id="IsReturned" value="0" checked>All
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" <?php echo (($Clean['IsReturned'] == 1) ? 'checked="checked"' : ''); ?> name="optIsReturned" id="IsReturned" value="1">Returned
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" <?php echo (($Clean['IsReturned'] == 2) ? 'checked="checked"' : ''); ?> name="optIsReturned" id="IsReturned" value="2">Non Returned
                                    </label>
                                </div>
                            </div>  
                            
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="hdnProcess" value="7" />
                                    <button type="submit" class="btn btn-primary">Search</button>
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

            if ($Clean['ParentCategoryID'] != 0)
            {
                $ReportHeaderText .= ' Category: ' . $ParentCategoryList[$Clean['ParentCategoryID']] . ',';
            }

            if ($Clean['BookCategoryID'] != 0)
            {
                $ReportHeaderText .= ' Sub-Category: ' . $BookCategoryList[$Clean['BookCategoryID']] . ',';
            }

            if ($Clean['BookName'] != '')
            {
                $ReportHeaderText .= ' Book Name: ' . $Clean['BookName'] . ',';
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
                                        $AllParameters = array('Process' => '7', 'ParentCategoryID' => $Clean['ParentCategoryID'], 'BookCategoryID' => $Clean['BookCategoryID'], 'BookName' => $Clean['BookName']);
                                        echo UIHelpers::GetPager('issued_book_report.php', $TotalPages, $Clean['CurrentPage'], $AllParameters);
                                    }
?>                                        
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="print-btn-container"><button id="PrintButton" type="submit" class="btn btn-primary">Print</button></div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTableHeading">
                                    <div class="col-lg-12">
                                        <div class="report-heading-container"><strong>Issued Book Report on <?php echo date('d-m-Y h:i A') . $ReportHeaderText; ?></strong></div>
                                    </div>
                                </div>
                                <div class="row" id="RecordTable">
                                    <div class="col-lg-12">
                                        <table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Book Name</th>
                                                    <th>Author</th>
                                                    <th>Issued To</th>
                                                    <th>User Type</th>                                                   
                                                    <th>Issue Date</th>
                                                    <th>Return Date</th>
                                                    <th>Issued By</th>
                                                    <th>Create Date</th>
                                                    <th class="print-hidden">Operations</th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
                                    if (is_array($IssuedBookList) && count($IssuedBookList) > 0)
                                    {
                                        $Counter = $Start;
                                        foreach ($IssuedBookList as $BookIssueID => $BookDetails)
                                        {
?>
                                            <tr>
                                                <td><?php echo ++$Counter; ?></td>
                                                <td><?php echo $BookDetails['BookName']; ?></td>
                                                <td><?php echo $BookDetails['AuthorName']; ?></td>
                                                <td><?php echo $BookDetails['UserName']; ?></td>
                                                <td><?php echo $BookDetails['UserType'] .'<br>'. $BookDetails['Class']; ?></td>
                                                
                                                <td><?php echo date('d/m/Y', strtotime($BookDetails['IssueDate'])); ?></td>
                                                <td><?php echo (($BookDetails['ActualReturnDate'] != '0000-00-00') ? date('d/m/Y', strtotime($BookDetails['ActualReturnDate'])) : 'N/A'); ?></td>                                                    
                                                <td><?php echo $BookDetails['CreateUserName']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($BookDetails['CreateDate'])); ?></td>
                                                <td class="print-hidden">
<?php
                                                if ($BookDetails['IsReturned'] == 1) 
                                                {
                                                    echo 'Returned';
                                                }
                                                else if ($LoggedUser->HasPermissionForTask(TASK_RETURN_BOOK) === true)
                                                {
                                                    echo '<a href="return_book.php?Process=7&amp;BooksCopyID=' . $BookDetails['BooksCopyID'] . '" class="btn btn-primary">Return</a>';
                                                }
                                                else
                                                {
                                                    echo 'Return';
                                                }
                                                
?>
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
<script type="text/javascript">
$(document).ready(function() {
    
    $('#DataTableRecords').DataTable({
        responsive: true,
        bPaginate: false,
        bSort: false,
        searching: false, 
        info: false
    });
    $('#ParentCategory').change(function(){

        var ParentCategoryID = parseInt($(this).val());
        
        if (ParentCategoryID <= 0)
        {
            $('#BookCategory').html('<option value="0">-- Select Sub Category --</option>');
            return false;
        }
        
        $.post("/xhttp_calls/get_sub_category_by_parent_category.php", {SelectedParentCategoryID:ParentCategoryID}, function(data)
        {
            ResultArray = data.split("|*****|");
            
            if (ResultArray[0] == 'error')
            {
                alert (ResultArray[1]);
                return false;
            }
            else
            {
                $('#BookCategory').html('<option value="">-- All Sub Category --</option>' + ResultArray[1]);
            }
        });
    });
});
</script>
<!-- JavaScript To Print A Report -->
<script src="/admin/js/print-report.js"></script>
</body>
</html>