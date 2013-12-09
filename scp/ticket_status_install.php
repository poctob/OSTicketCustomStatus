<?php
require('staff.inc.php');
require_once INCLUDE_DIR.'class.ticket_status_install.php';

if($_REQUEST['install']=='1')
{
    $installer=new TicketStausInstaller();
    $installer->install($_POST);
    echo 'Done';
    
}
?>