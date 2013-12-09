<?php
/*********************************************************************
    equipment.php

    Alex P <alexp@xpresstek.net>
    Copyright (c)  2013 XpressTek
    http://www.xpresstek.net

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('ticket_status.inc.php');
require_once(INCLUDE_DIR.'class.ticket_status.php');

$ticket_status=null;
if($_REQUEST['id'] && !($ticket_status=Ticket_Status::lookup($_REQUEST['id'])))
   $errors['err']='Unknown or invalid status';

$inc='ticket_status_list.inc.php'; 
if($ticket_status) {
    $inc='ticket_status.inc.php';
} 
require_once(CLIENTINC_DIR.'header.inc.php');
require_once(CLIENTINC_DIR.$inc);
require_once(CLIENTINC_DIR.'footer.inc.php');
?>
