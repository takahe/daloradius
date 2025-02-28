<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * 
 * Authors:        Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/validation.php') !== false) {
    header('Location: ../index.php');
    exit;
}

// commonly used regexes collection
define("DATE_REGEX", "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/");
define("ORDER_TYPE_REGEX", "/^(de|a)sc$/");
define("IP_REGEX", "/^(((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9]).){3}((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])$/");
define("NETMASK_LENGTH_REGEX", "/^3[0-2]|[1-2][0-9]|[1-9]$/");
define("MACADDR_REGEX", "/^(?:[0-9a-f]{2}([-:]))(?:[0-9a-f]{2}\1){4}[0-9a-f]{2}$/i");
define("PINCODE_REGEX", "/^[a-zA-Z0-9]+$/");


// some parameters can be validated using a whitelist.
// here we collect some useful whitelist.
// this lists can be also used for presentation purpose.
// whitelists naming convention:
// $valid_ [param_name] s
$valid_authTypes = array( "userAuth" => "Username/password based",
                          "macAuth" => "MAC Address base",
                          "pincodeAuth" => "PIN code based" );

$valid_passwordTypes = array(
                                "Cleartext-Password",
                                "NT-Password",
                                "MD5-Password",
                                "SHA1-Password",
                                "User-Password",
                                "Crypt-Password",
                                "CHAP-Password"
                             );

$valid_ops = array(
                    "=", ":=", ":=", "==", "+=", "!=", ">",
                    ">=", "<", "<=", "=~", "!~", "=*", "!*"
                  );

$valid_db_engines = array(
                            "mysql" => "MySQL",
                            "pgsql" => "PostgreSQL",
                            "odbc" => "ODBC",
                            "mssql" => "MsSQL",
                            "mysqli" => "MySQLi",
                            "msql" => "MsQL",
                            "sybase" => "Sybase",
                            "sqlite" => "Sqlite",
                            "oci8" => "Oci8 ",
                            "ibase" => "ibase",
                            "fbsql" => "fbsql",
                            "informix" => "informix"
                         );

$valid_nastypes = array(
                            "other", "cisco", "livingston", "computon", "max40xx",
                            "multitech", "natserver", "pathras",
                            "patton", "portslave", "tc", "usrhiper"
                       );

$acct_custom_query_options_all = array(
                                        "RadAcctId",
                                        "AcctSessionId",
                                        "AcctUniqueId",
                                        "UserName",
                                        "Realm",
                                        "NASIPAddress",
                                        "NASPortId",
                                        "NASPortType",
                                        "AcctStartTime",
                                        "AcctStopTime",
                                        "AcctSessionTime",
                                        "AcctAuthentic",
                                        "ConnectInfo_start",
                                        "ConnectInfo_stop",
                                        "AcctInputOctets",
                                        "AcctOutputOctets",
                                        "CalledStationId",
                                        "CallingStationId",
                                        "AcctTerminateCause",
                                        "ServiceType",
                                        "FramedProtocol",
                                        "FramedIPAddress",
                                        "AcctStartDelay",
                                        "AcctStopDelay"
                                    );
                                    
$acct_custom_query_options_default = array(
                                            "UserName", "Realm", "NASIPAddress", "AcctStartTime", "AcctStopTime",
                                            "AcctSessionTime", "AcctInputOctets", "AcctOutputOctets", "CalledStationId",
                                            "CallingStationId", "AcctTerminateCause", "FramedIPAddress"
                                          );

?>
