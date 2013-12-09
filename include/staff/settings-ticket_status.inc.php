<?php
/*********************************************************************
    settings_categories.inc.php
 
    Alex P <alexp@xpresstek.net>
    Copyright (c)  2013 XpressTek
    http://www.xpresstek.net

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
 

$ticket_status_installed=false;

$sql='SELECT is_installed FROM '.PLUGIN_TABLE
        .' WHERE name=\'ticket_status\'';

 if (!($res=db_query($sql)) || !db_num_rows($res)) 
 {
            $ticket_status_installed=false;
 }
 else
 {
     $t = db_fetch_array($res);
     if($t[is_installed]=='1')
     {
         $ticket_status_installed=true;
     }
 }


if(!$ticket_status_installed)
{
    ?>
<h2>Ticket Status Plugin Installer</h2>
<form action="ticket_status_install.php?install=1" method="post" id="save">
     <?php csrf_token(); ?>
     Enter database tables prefix:
     <input id="prefix" type="text" size="20" name="prefix" value="">
      &nbsp;<span class="error">*&nbsp;<?php echo $errors['prefix']; ?></span>
      <input id="submit" type="submit" value="Install Now!">
</form>
<?php
}
else
{
?>
<h2>Ticket Status Settings and Options</h2>
<form action="settings.php?t=ticket_status" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="ticket_status" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Ticket Status Settings</h4>
                <em>Disabling Ticket Status disables clients' interface.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180">Ticket status:</td>
            <td>
              <input type="checkbox" name="enable_ticket_status" value="1" <?php echo $config['enable_ticket_status']?'checked="checked"':''; ?>>
              Enable Ticket Status&nbsp;<em>(Client interface)</em>
              &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_ticket_status']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="Save Changes">
    <input class="button" type="reset" name="reset" value="Reset Changes">
</p>
</form>
<?php
}
?>