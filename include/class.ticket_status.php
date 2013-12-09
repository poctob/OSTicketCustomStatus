<?php
/*********************************************************************
    class.ticket_status.php

    Backend support for ticket status.

    Copyright (c)  2013 XpressTek
    http://www.xpresstek.net

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

class Ticket_Status {
    var $id;
    var $ht;

    function Ticket_Status($id) {
        $this->id=0;
        $this->load($id);
    }

    function load($id) {

        $sql=' SELECT status.*,count(ticket.ticket_id) as tickets '
            .' FROM '.TICKET_STATUS_TABLE.' status '
            .' LEFT JOIN '.TICKET_TABLE.' ticket ON(ticket.ticket_status_id=status.status_id) '
            .' WHERE status.status_id='.db_input($id)
            .' GROUP BY status.status_id';

        if (!($res=db_query($sql)) || !db_num_rows($res)) 
            return false;

        $this->ht = db_fetch_array($res);
        $this->id = $this->ht['status_id'];

        return true;
    }

    function reload() {
        return $this->load($this->getId());
    }

    /* ------------------> Getter methods <--------------------- */
    function getId() { return $this->id; }
    function getName() { return $this->ht['name']; }
    function getNumTickets() { return  $this->ht['tickets']; }
    function getDescription() { return $this->ht['description']; }
    function getColor() { return $this->ht['color']; }
    function getClosed() { return $this->ht['closed']; }
    function getDefault() { return $this->ht['default']; }
  
    function getHashtable() { return $this->ht; }
    
    /* ------------------> Setter methods <--------------------- */
    function setName($name) { $this->ht['name']=$name; }
    function setDescription($desc) { $this->ht['description']=$desc; }
    function setColor($color) { $this->ht['color']=$color; }
    function setClosed($closed) { $this->ht['closed']=$closed; }
    function setDefautl($default) { $this->ht['default']=$default; }
    

    /* --------------> Database access methods <---------------- */
    function update($vars, &$errors) { 

        if(!$this->save($this->getId(), $vars, $errors))
            return false;
        $this->reload();

        return true;
    }

    function delete() {

        if($this->getClosed() || $this->getDefault())
        {
            echo "Can't delete default or closed status!";
            return false;
        }
        
        $sql='DELETE FROM '.TICKET_STATUS_TABLE
            .' WHERE status_id='.db_input($this->getId())
            .' LIMIT 1';
        
        echo $sql;
        
        return db_query($sql) && db_affected_rows();
    }

    /* ------------------> Static methods <--------------------- */

    function lookup($id) {
        return ($id && is_numeric($id) && ($c = new Ticket_Status($id)))?$c:null;
    }

    function findIdByName($name) {
        $sql='SELECT status_id FROM '.TICKET_STATUS_TABLE.' WHERE name='.db_input($name);
        list($id) = db_fetch_row(db_query($sql));

        return $id;
    }

    function findByName($name) {
        if(($id=self::findIdByName($name)))
            return new Ticket_Status($id);

        return false;
    }
    
      function findByTicket($ticket)
    {
         
        $sql='SELECT ticket_status_id FROM '.TICKET_TABLE
            .' WHERE ticket_id='.db_input($ticket);

        list($id) =db_fetch_row(db_query($sql));
        
        if($id)
        {
            return self::lookup($id);
        }
        return null;
    }

    function validate($vars, &$errors) {
         return self::save(0, $vars, $errors,true);
    }

    function create($vars, &$errors) {
        return self::save(0, $vars, $errors);
    }
       

     function getStatusList($non_closed=false) {

        $status_list=array();
        $sql='SELECT status_id, name '
            .' FROM '.TICKET_STATUS_TABLE;
        
          if($non_closed)
            $sql.=' WHERE closed=0';

        $sql.=' ORDER BY name';

        if(($res=db_query($sql)) && db_num_rows($res))
            while(list($id, $name)=db_fetch_row($res))
                $status_list[$id]=$name;
        return $status_list;
    }
    
     function getClosedStatus() {
        $sql='SELECT status_id '
            .' FROM '.TICKET_STATUS_TABLE;
        $sql.=' WHERE closed=1';
        $sql.=' ORDER BY name';
        $sql.=' LIMIT 1';
        
        list($id) =db_fetch_row(db_query($sql));

        if($id)
            return self::lookup($id);

        return false;
    }
    
     function getDefaultStatus() {
        $sql='SELECT status_id '
            .' FROM '.TICKET_STATUS_TABLE;
        $sql.=' WHERE `default`=1';
        $sql.=' ORDER BY name';
        $sql.=' LIMIT 1';
        
        list($id) =db_fetch_row(db_query($sql));

        if($id)
            return self::lookup($id);

        return false;
    }
      
    
    function save($id, $vars, &$errors, $validation=false) {

        if(isset($vars['default']) && self::getDefaultStatus())
        {
             $errors['err']='There can only be one default status!';
             return false;
        }
        
        if(isset($vars['closed']) && self::getClosedStatus())
        {
             $errors['err']='There can only be one closed status!';
             return false;
        }
        //Cleanup.
        $vars['name']=Format::striptags(trim($vars['name']));
      
        //validate
        if($id && $id!=$vars['id'])
            $errors['err']='Internal error. Try again';
      
        if(!$vars['name'])
            $errors['name']='Status name is required';
        elseif(strlen($vars['name'])<3)
            $errors['name']='Name is too short. 3 chars minimum';
        elseif(($cid=self::findIdByName($vars['name'])) && $cid!=$id)
            $errors['name']='Status already exists';

        if(!$vars['description'])
            $errors['description']='Status description is required';

        if($errors) return false;

        /* validation only */
        if($validation) return true;

        //save
        $sql=' name='.db_input($vars['name']).
             ',description='.db_input(Format::safe_html($vars['description'])).
             ',color='.db_input(Format::safe_html($vars['color'])).
             ',`default`='.db_input(isset($vars['default'])?1:0).
             ',closed='.db_input(isset($vars['closed'])?1:0);
            

        if($id) {
            $sql='UPDATE '.TICKET_STATUS_TABLE.' SET '.$sql.' WHERE status_id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']='Unable to update ticket Status.';

        } else {
            $sql='INSERT INTO '.TICKET_STATUS_TABLE.' SET '.$sql;
            if(db_query($sql) && ($id=db_insert_id()))
                return $id;

            $errors['err']='Unable to create Ticket Status. Internal error';
        }

        return false;
    }
    
       
    function getTickets($id)
    {
           $ticket_ids=array();
           $sql='SELECT et.ticket_id'
            .' FROM '.TICKET_TABLE.' et '          
            .' WHERE et.ticket_status_id='.db_input($id);
        if(($res=db_query($sql)) && db_num_rows($res))
            while(list($id)=db_fetch_row($res))
                $ticket_ids[$id]=$id;

            return $ticket_ids; 
    }
}
?>
