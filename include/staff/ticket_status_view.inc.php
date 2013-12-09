<?php
/*********************************************************************
    equipment_view.inc.php
 
    Alex P <alexp@xpresstek.net>
    Copyright (c)  2013 XpressTek
    http://www.xpresstek.net

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
if(!defined('OSTSTAFFINC') || !$ticket_status || !$thisstaff) die('Access Denied');
?>
<h2>Ticket Status</h2>

<div style="width:700;padding-top:2px; float:left;">
<strong style="font-size:16px;"><?php echo $ticket_status->getName() ?></strong>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
<?php
if($thisstaff->canManageTicketStatus()) {
    echo sprintf('<a href="ticket_status.php?id=%d&a=edit" class="Icon newHelpTopic">Edit Status</a>',
            $ticket_status->getId());
}
?>
&nbsp;
</div>
<div class="clear"></div>

<hr>
<?php
if($thisstaff->canManageTicketStatus()) {
    //TODO: add js confirmation....
    ?>
   <div>
    <form action="ticket_status.php?id=<?php echo  $ticket_status->getId(); ?>" method="post">
	 <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo  $ticket_status->getId(); ?>">                
    </form>
   </div>
<?php
} 
?>
<div class="clear"></div>
<div style="width:700;padding-top:2px; float:left;"><p>
    <strong style="font-size:14px;">Tickets:</strong>.
    <table>
        <thead>
            <tr>
                <th width="70">Ticket</th>
                <th width="70">Date</th>
                <th width="70">Subject</th>
                <th width="70">From</th>
                <th width="70">Priority</th>
                <th width="100">Assigned To</th>
            </tr>
        </thead>
        <tbody>
      <?php 
        $open_tickets=  Ticket_Status::getTickets($ticket_status->getId());
        foreach($open_tickets as &$ticket_id)
        {
            $ticket=Ticket::lookup($ticket_id);
            if(isset($ticket))
            {?>                
              <tr>
                  <td align="center" >
                 <a class="Icon Ticket ticketPreview" title="Preview Ticket" 
                    href="tickets.php?id=<?php echo $ticket->getId(); ?>"><?php echo $ticket->getNumber(); ?></a>
                  </td>
                   <td align="center" >
                       <?php echo Format::db_date($ticket->getCreateDate()); ?>
                   </td>
                    <td align="center" >
                       <?php echo $ticket->getSubject(); ?>
                   </td>
                    <td align="center" >
                       <?php echo $ticket->getName(); ?>
                   </td>
                     <td align="center" >
                       <?php echo $ticket->getPriority(); ?>
                   </td>
                    <td align="center" >
                       <?php 
                                $staff=$ticket->getStaff();
                                if(isset($staff))
                                    echo $staff->getName();
                       ?>
                   </td>
              </tr>
            <?php }
        }
    ?>
             </tbody> </table>
</div>


<div class="clear"></div>

