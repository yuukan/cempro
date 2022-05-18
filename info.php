<?php
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

// config ldap
$ldapserver = '10.0.0.93';
$ldapuser      = 'testfirmas@firmas.local'; 
$ldappass     = 'C3ntrumSilv3r!';
$ldaptree    = "OU=Members,DC=firmas,DC=local";
// config ldap

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

$str = $_GET['us'];
if($ldapconn) {
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
    // verify binding
    if ($ldapbind) {
        // echo "LDAP bind successful...<br /><br />";
       
       
        $justthese = array("ou", "sn", "givenname", "mail","title","physicaldeliveryofficename","company","telephonenumber","mobile","wwwhomepage");
        // $justthese = array();
        // $str = "ccatalan";
        $filter="(&(objectClass=user)(objectCategory=person)(|(mail=*{$str}*)))";
        $result = ldap_search($ldapconn,$ldaptree, $filter,$justthese) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);
       
        // var_dump($data);
        // exit();
       
        // iterate over array and print data for each entry
        // echo '<h1>Show me the users</h1>';
        $ret = [];
        for ($i=0; $i<$data["count"]; $i++) {
            //echo "dn is: ". $data[$i]["dn"] ."<br />";
            $array = array(
                'nombre' => utf8_encode($data[$i]["givenname"][0])." ".utf8_encode($data[$i]["sn"][0]),
                'posicion' => utf8_encode($data[$i]["title"][0]),
                'email' => $data[$i]["mail"][0],
                'office' => utf8_encode($data[$i]['physicaldeliveryofficename'][0]),
                'company' => utf8_encode($data[$i]['company'][0]),
                'ext' => $data[$i]['telephonenumber'][0],
                'cel' => $data[$i]['mobile'][0],
                'page' => $data[$i]['wwwhomepage'][0],
            );
            $ret[] = $array;
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret[0]);
    } else {
        echo "LDAP bind failed...";
    }

}

// all done? clean up
ldap_close($ldapconn);