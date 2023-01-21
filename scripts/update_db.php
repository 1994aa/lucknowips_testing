<?php
require_once('/home/addedschools/public_html/lucknowips/classes/class.db_connect.php');
require_once('/home/addedschools/public_html/lucknowips/classes/fee_management/class.fee_collection_cron.php');

// require_once('../classes/class.db_connect.php');
// require_once('../classes/fee_management/class.fee_collection.php');

$file_path = __FILE__;
try {
    FeeCollection::GetUpdateDB();
    FeeCollection::UpdateIsLastInStudentStatus();
    FeeCollection::UpdateFeeCollectionDetailsColumn();
    
    FeeCollection::searchAllPreviousYearDue();
    
    // mail("mevishnusingh@gmail.com", "Success lucknowips cron", $file_path);
} catch (Exception $e) {
	mail("mevishnusingh@gmail.com", "Error in lucknowips cron", $file_path);
}
