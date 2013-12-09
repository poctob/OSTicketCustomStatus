<?php
/*********************************************************************
    ticket_status.php

    Ticket status

    Copyright (c)  2013 xpresstek
    http://www.xpresstek.net

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
include_once(INCLUDE_DIR.'class.ticket_status.php');

/* check permission */
if(!$thisstaff || !$thisstaff->canManageTicketStatus()) {
    header('Location: ticket.php');
    exit;
}

$status=null;
if($_REQUEST['id'] && !($status=Ticket_Status::lookup($_REQUEST['id'])))
    $errors['err']='Unknown or invalid status ID.';

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$status) {
                $errors['err']='Unknown or invalid status.';
            } elseif($status->update($_POST,$errors)) {
                $msg='Status updated successfully';
            } elseif(!$errors['err']) {
                $errors['err']='Error updating status. Try again!';
            }
            break;
        case 'create':
            if(($id=Ticket_Status::create($_POST,$errors))) {
                $msg='Ticket status added successfully';
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err']='Unable to add status. Correct error(s) below and try again.';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']='You must select at least one status';
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.TICKET_STATUS_TABLE.' SET ispublic=1 '
                            .' WHERE status_id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected status made PUBLIC';
                            else
                                $warn = "$num of $count selected status made PUBLIC";
                        } else {
                            $errors['err'] = 'Unable to enable selected status public.';
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.TICKET_STATUS_TABLE.' SET ispublic=0 '
                            .' WHERE status_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected status made PRIVATE';
                            else
                                $warn = "$num of $count selected status made PRIVATE";
                        } else {
                            $errors['err'] = 'Unable to disable selected status PRIVATE';
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($c=Ticket_Status::lookup($v)) && $c->delete())
                                $i++;
                        }

                        if($i==$count)
                            $msg = 'Selected ticket status deleted successfully';
                        elseif($i>0)
                            $warn = "$i of $count selected status deleted";
                        elseif(!$errors['err'])
                            $errors['err'] = 'Unable to delete selected Ticket status';
                        break;
                    default:
                        $errors['err']='Unknown action/command';
                }
            }
            break;
        default:
            $errors['err']='Unknown action';
            break;
    }
}

$page='ticket_statuses.inc.php';

if($_REQUEST['sid'] && $ticket_status=Ticket_Status::lookup($_REQUEST['sid']))
{
     $page='ticket_status_view.inc.php';
}
else if($status || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='ticket_status.inc.php';

$nav->setTabActive('ticket_status');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
