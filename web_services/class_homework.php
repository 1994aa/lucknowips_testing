<?php
header ("Access-Control-Allow-Origin: *");
header("Content-Type:application/json");

require_once("../classes/class.ui_helpers.php");

require_once("../classes/school_administration/class.classes.php");
require_once('../classes/school_administration/class.class_sections.php');

require_once("../classes/academic_supervision/class.chapters.php");
require_once("../classes/academic_supervision/class.chapter_topics.php");
require_once("../classes/academic_supervision/class.student_assignment.php");

$Clean = array();

$Clean['ClassID'] = 0;
$Clean['SectionID'] = 0;
$TotalRecords = 0;

$Filters = array();
$AssignmentsList = array();

if (isset($_REQUEST['ClassID']))
{
	$Clean['ClassID'] = (int) $_REQUEST['ClassID'];
}

if (isset($_REQUEST['SectionID']))
{
	$Clean['SectionID'] = (int) $_REQUEST['SectionID'];
}

if ($Clean['ClassID'] < 1 || $Clean['SectionID'] < 1)
{
    echo json_encode(array('error' => 'Unknown error, please try again.'));
    exit;
}

try
{   
    $CurrentClassSection = new ClassSections(0, $Clean['ClassID'], $Clean['SectionID']);
    
    $Filters['ClassID'] = $Clean['ClassID'];
    $Filters['ClassSectionID'] = $CurrentClassSection->GetClassSectionID();
    
	//get records count
    StudentAssignment::SearchAssignments($TotalRecords, true, $Filters);
        
	$AssignmentsList = StudentAssignment::SearchAssignments($TotalRecords, false, $Filters, 0, $TotalRecords);
}
catch (ApplicationDBException $e)
{
	echo json_encode(array('error' => 'Unknown error, please try again.'));
    exit;
}
catch (Exception $e)
{
	echo json_encode(array('error' => 'Unknown error, please try again.'));
    exit;
}

?>
<table width="100%" class="table table-striped table-bordered table-hover" id="DataTableRecords">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Class</th>
                                                    <th>Subject</th>
                                                    <th>Chapter</th>
                                                    <th>Topic</th>
                                                    <th>Heading</th>
                                                    <td>HomeWork</td>
                                                    <th>Issue Date</th>
                                                    <th>End Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
                                    if (is_array($AssignmentsList) && count($AssignmentsList) > 0)
                                    {
                                        $Counter = $Start;
                                        
                                      
                                        
                                        
                                        foreach ($AssignmentsList as $AssignmentID => $AssignmentDetails)
                                        {
                                              //echo '<pre>';
                                        //print_R($AssignmentDetails['AssignmentImage']);
                                           
                                            
                                           // echo . $AssignmentDetails['ImageName']; ?>
                                                <tr>
                                                    <td><?php echo ++$Counter; ?></td>
                                                    <td><?php echo $AssignmentDetails['ClassName']; ?></td>
                                                    <td><?php echo $AssignmentDetails['SubjectName']; ?></td>
                                                    <td><?php echo $AssignmentDetails['ChapterName']; ?></td>
                                                    <td><?php echo $AssignmentDetails['TopicName']; ?></td>
                                                    <td><?php echo $AssignmentDetails['AssignmentHeading']; ?></td>
                                                    <td align="center">
                                                 <?php 
                                               //  echo '<pre>';
                                                // print_R($AssignmentDetails['AssignmentImage']);
                                                // exit;
                                                 
                                                 foreach($AssignmentDetails['AssignmentImage'] as $imgkey => $imgvalue)
                                                 { 
                                                     //echo '<pre>';
                                                  // print_R($imgvalue['ImageName']);
                                                   $href = 'https://lucknowips.addedschools.com/site_images/student_assignment/'. $AssignmentID.'/'.$imgvalue['ImageName'] ;
                                                  ?> 
                                                 <a href="<?php echo $href;  ?>" target="blank" title="Download HomeWork"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download HomeWork</a>
                                                 <?php } 
                                                  
                                                 ?>
                                                 </td>
                                                    <td><?php echo $AssignmentDetails['IssueDate']; ?></td>
                                                    <td><?php echo $AssignmentDetails['EndDate']; ?></td>
                                                </tr>
<?php
                                        }
                                    }
                                    
                                    else
                                    {
?>                                        
                                                <tr>
                                                    <td colspan="9">No Records Found</td>
                                                </tr>
<?php                                               
                                    }
?>
                                            </tbody>
                                        </table>