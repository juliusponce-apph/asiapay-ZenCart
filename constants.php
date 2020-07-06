<?php


//specify connection to database
define("SERVER",'localhost');
define("USERNAME",'root');
define("PASSWORD",'1234');
define("DATABASE",'zencart');
define("SUCCESSSTATUSNAME",'Processing'); //change to whatever name for accepted status, default is Processing; for custom made accepted statuses go to Admin -> Localization -> Order Status e.g. Accepted or Paid
define("FAILSTATUSNAME",'Pending'); //change to whatever name for failed status, default is reverted to Pending; for custom made accepted statuses go to Admin -> Localization -> Order Status e.g. Rejected

define("EMAIL_SUBJECT_CC_MSG",'Order Status Notification');
define("EMAIL_SUCCESS_CC_MSG",'This email is system generated.  Please do not reply to this email. This letter confirms your SUCCESSFUL status order.'); //Change according to the email content needs for a successful transaction
define("EMAIL_FAIL_CC_MSG",'This email is system generated.  Please do not reply to this email. This letter confirms your FAILED status order. Order is reverted back to Pending Status.'); //Change according to the email content needs for a failed transaction
define("STORE_OWNER",'Owner'); //change to identify sender
define("EMAIL_FROM",'xxx@vyz.com');  //change to identify sender
define("ORDERLINK", 'http://localhost/zencart/index.php?main_page=account_history_info&order_id=');

?>