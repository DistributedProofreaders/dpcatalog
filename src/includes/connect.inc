<?php
require_once('udb_user.php');

class dbConnect
{
    var $error='';
    var $connection='';

    function dbConnect()
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
//            echo "Host information: " . mysqli_get_host_info($db_link) . PHP_EOL;
            $this->connection = $db_link;
            return 1;
        }

/*
        // Not necessary to select db any more since it's done in the mysqli connection
        if (!mysql_select_db($db_info->dbname,$this->db_lk))
        {
            $this->error=_("Unable to locate database.");
            return 0;
        }
        else
        {
            return 1;
        }
*/

    }

    function close()
    {
        if (isset($this->db_lk))
        {
//            mysql_close($this->db_lk);
            $this->close();
        }
    }

}

// vim: sw=4 ts=4 expandtab
