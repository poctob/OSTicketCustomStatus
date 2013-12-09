<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canEditTickets() || !$ticket) die('Access Denied');

$info=Format::htmlchars(($errors && $_POST)?$_POST:$ticket->getUpdateInfo());
if ($_POST)
    $info['duedate'] = Format::date($cfg->getDateFormat(),
       strtotime($info['duedate']));
?>
<form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit" method="post" id="save"  enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="a" value="edit">
 <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
 <h2>Update Ticket #<?php echo $ticket->getExtId(); ?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong>Client Information</strong>: Currently selected client</em>
            </th>
        </tr>
    <?php
    $client = User::lookup($info['user_id']);
    ?>
    <tr><td>Client:</td><td>
        <span id="client-info"><?php echo $client->getName(); ?>
        &lt;<?php echo $client->getEmail(); ?>&gt;</span>
        <a class="action-button" style="float:none;overflow:inherit"
            href="ajax.php/users/lookup?id=<?php echo $client->getId(); ?>"
            onclick="javascript:
                $('#overlay').show();
                $('#user-lookup .body').load(this.href);
                $('#user-lookup').show();
                return false;
            "><i class="icon-edit"></i> Change</a>
        <input type="hidden" name="user_id" id="user_id"
            value="<?php echo $info['user_id']; ?>" />
        </td></tr>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong>Ticket Information</strong>: Due date overrides SLA's grace period.</em>
            </th>
        </tr>
        <tr>
            <td width="160" class="required">
                Ticket Source:
            </td>
            <td>
                <select name="source">
                    <option value="" selected >&mdash; Select Source &mdash;</option>
                    <option value="Phone" <?php echo ($info['source']=='Phone')?'selected="selected"':''; ?>>Phone</option>
                    <option value="Email" <?php echo ($info['source']=='Email')?'selected="selected"':''; ?>>Email</option>
                    <option value="Web"   <?php echo ($info['source']=='Web')?'selected="selected"':''; ?>>Web</option>
                    <option value="API"   <?php echo ($info['source']=='API')?'selected="selected"':''; ?>>API</option>
                    <option value="Other" <?php echo ($info['source']=='Other')?'selected="selected"':''; ?>>Other</option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                Help Topic:
            </td>
            <td>
                <select name="topicId">
                    <option value="" selected >&mdash; Select Help Topic &mdash;</option>
                    <?php
                    if($topics=Topic::getHelpTopics()) {
                        foreach($topics as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['topicId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['topicId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                SLA Plan:
            </td>
            <td>
                <select name="slaId">
                    <option value="0" selected="selected" >&mdash; None &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['slaId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['slaId']; ?></font>
            </td>
        </tr>
  <?php if($cfg->isEquipmentEnabled()) { 
      $eq=Equipment::FindByTicket($ticket->getId());
      if($eq)
      {
      ?>
            <tr>
           <td>Equipment Affected:</td>
        <td>
            <select id="equipment_id" name="equipment_id">              
                <?php
                   echo sprintf('<option value="%d" %s>%s</option>',
                                $eq->getId(), 'selected="selected"', $eq->getName());
                    ?>
               
            </select>      
            
              <select id="status_id" name="status_id">
                <option value="" selected="selected">&mdash; Select Status &mdash;</option>
                <?php
                if($status=Equipment_Status::getStatusList(true)) {
                    foreach($status as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($eq->getStatusID()==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" >No Status</option>
                <?php
                } ?>
            </select>           
        </td>
         </tr>
          <?php 
      }
                } ?>
         
      <?php if($cfg->isTicketStatusEnabled()) { 
      $ticket_status_var=Ticket_Status::FindByTicket($ticket->getId());
      echo $ticket->getId();
      ?>
        <tr>
           <td>Ticket Status:</td>
        <td>
            <select id="ticket_status_id" name="ticket_status_id">
                <option value="" selected="selected">&mdash; Select Ticket Status &mdash;</option>
                <?php
                if($ticket_status=Ticket_Status::getStatusList(true)) {
                    foreach($ticket_status as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($ticket_status_var->getID()==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" >No Status</option>
                <?php
                } ?>
            </select>                    
        </td>
         </tr>
         <?php } ?>
        <tr>
            <td width="160">
                Due Date:
            </td>
            <td>
                <input class="dp" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['time'])
                    list($hr, $min)=explode(':', $info['time']);

                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['duedate']; ?>&nbsp;<?php echo $errors['time']; ?></font>
                <em>Time is based on your time zone (GMT <?php echo $thisstaff->getTZoffset(); ?>)</em>
            </td>
        </tr>
        </tbody>
        <tbody id="dynamic-form">
        <?php if ($forms)
            foreach ($forms as $form) {
                $form->render(true);
        } ?>
        </tbody>
        <tbody>
        <tr>
            <th colspan="2">
                <em><strong>Internal Note</strong>: Reason for editing the ticket (required) <font class="error">&nbsp;<?php echo $errors['note'];?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <textarea class="richtext no-bar" name="note" cols="21"
                    rows="6" style="width:80%;"><?php echo $info['note'];
                    ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input type="submit" name="submit" value="Save">
    <input type="reset"  name="reset"  value="Reset">
    <input type="button" name="cancel" value="Cancel" onclick='window.location.href="tickets.php?id=<?php echo $ticket->getId(); ?>"'>
</p>
</form>
<div style="display:none;" class="dialog draggable" id="user-lookup">
    <div class="body"></div>
</div>
