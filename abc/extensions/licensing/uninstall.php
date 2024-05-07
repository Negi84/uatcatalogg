<?php

$this->db->query(
    "DELETE 
    FROM ".$this->db->table_name("ac_settings")." 
    WHERE `group`= 'licensing';"
);
$this->db->query("DROP TABLE IF EXISTS ".$this->db->table_name("licenses"));