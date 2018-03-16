<?php
require_once('udb_user.php');

class dbConnect
{
    var $error='';
    var $connection='';

    function __construct()
    {
        $db_info = new db_udb_user();
        $db_link = mysqli_connect('p:'.
            $db_info->server,
            $db_info->username,
            $db_info->password,
            $db_info->dbname);

        if (!$db_link)
        {
            $this->error=_("Unable to connect to database server.");
            echo $this->error;
            return NULL;
        }
        else
        {
            $this->connection = $db_link;
            return 1;
        }
    }

    function close()
    {
        if (isset($this->db_lk))
        {
            $this->close();
        }
    }

}

// vim: sw=4 ts=4 expandtab
