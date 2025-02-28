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
 * Authors:    Liran Tal <liran@enginx.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    include_once('library/config_read.php');

    // set session's page variable
    $_SESSION['PREV_LIST_PAGE'] = $_SERVER['REQUEST_URI'];

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";
    
    // we import validation facilities
    include_once("library/validation.php");

    // if cleartext passwords are not allowed, 
    // we remove Cleartext-Password from the $valid_passwordTypes array
    if (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) &&
        strtolower($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) !== 'cleartext') {
        $valid_passwordTypes = array_diff($valid_passwordTypes, array("Cleartext-Password"));
        $valid_passwordTypes = array_values($valid_passwordTypes);
    }
    
    include('library/opendb.php');
    
    $sql = sprintf("SELECT id, name FROM %s", $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']);
    $res = $dbSocket->query($sql);
    $logDebugSQL .= "$sql;\n";
    
    $valid_hotspots = array( );
    while ($row = $res->fetchrow()) {
        list($id, $name) = $row;
        
        $valid_hotspots["hotspot-$id"] = $name;
    }

    include_once('include/management/populate_selectbox.php');
    $valid_groups = get_groups();
    $valid_planNames = get_plans();
    
    
    include("include/management/functions.php");
    include('include/management/pages_common.php');


    function addUserBatchHistory($dbSocket) {
        
        global $batch_name;
        global $batch_description;
        global $hotspot_id;
        global $logDebugSQL;
        global $configValues;
        
        // the returned id of last insert batch_history record
        $batch_id = 0;
        
        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];
        
        $sql = sprintf("INSERT INTO %s (id, batch_name, batch_description, hotspot_id, creationdate, creationby, updatedate, updateby) 
                                VALUES (0, '%s', '%s', '%s', '%s', '%s', NULL, NULL)",
                       $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'],
                       $dbSocket->escapeSimple($batch_name), $dbSocket->escapeSimple($batch_description),
                       $dbSocket->escapeSimple($hotspot_id), $currDate, $currBy);
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
        
        $sql = sprintf("SELECT id FROM %s WHERE batch_name = '%s'", 
                       $configValues['CONFIG_DB_TBL_DALOBATCHHISTORY'], $dbSocket->escapeSimple($batch_name));
        $res = $dbSocket->query($sql);
        $logDebugSQL .= "$sql;\n";
    
        // if the INSERT to the batch_history table was succesful and there exist
        // only 1 record (meaning, we don't have a duplicate) then we return the id
        return ($res->numRows() == 1) ? intval($res->fetchRow()[0]) : 0;        
    }
    
    include_once("lang/main.php");
    
    include("library/layout.php");

    // print HTML prologue
    $extra_css = array(
        // css tabs stuff
        "css/tabs.css"
    );
    
    $extra_js = array(
        "library/javascript/ajax.js",
        "library/javascript/ajaxGeneric.js",
        "library/javascript/productive_funcs.js",
        "library/javascript/dynamic_attributes.js",
        // js tabs stuff
        "library/javascript/tabs.js"
    );
    
    $title = t('Intro','mngbatch.php');
    $help = t('helpPage','mngbatch');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-mng-batch.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

    $exportForm = "";
    $detailedInfo = array();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        /* variables for batch_history */
        $batch_name = (array_key_exists('batch_name', $_POST) && !empty(trim($_POST['batch_name'])) &&
                       preg_match("/^[\w\-. ]+$/", trim($_POST['batch_name']) !== false)) ? trim($_POST['batch_name']) : "";
        
        if (empty($batch_name)) {
            // batch name required
            $failureMsg = "Failure creating batch - please provide a batch name";
            $logAction .= "Failure creating batch - missing field [batch_name] on page: ";
        } else {
        
            $batch_description = (array_key_exists('batch_description', $_POST) && isset($_POST['batch_description']))
                               ? trim(str_replace("%", "", $_POST['batch_description'])) : "";
            
            $hotspot_id = (array_key_exists('hotspot_id', $_POST) && !empty(trim($_POST['hotspot_id'])) &&
                           in_array(trim($_POST['hotspot_id']), array_keys($valid_hotspots)))
                        ? intval(str_replace("hotspot-", "", $_POST['hotspot_id'])) : "";

            /* variables for userbillinfo */
            $bi_contactperson = (array_key_exists('bi_contactperson', $_POST) && isset($_POST['bi_contactperson'])) ? $_POST['bi_contactperson'] : "";
            $bi_company = (array_key_exists('bi_company', $_POST) && isset($_POST['bi_company'])) ? $_POST['bi_company'] : "";
            $bi_email = (array_key_exists('bi_email', $_POST) && isset($_POST['bi_email'])) ? $_POST['bi_email'] : "";
            $bi_phone = (array_key_exists('bi_phone', $_POST) && isset($_POST['bi_phone'])) ? $_POST['bi_phone'] : "";
            $bi_address = (array_key_exists('bi_address', $_POST) && isset($_POST['bi_address'])) ? $_POST['bi_address'] : "";
            $bi_city = (array_key_exists('bi_city', $_POST) && isset($_POST['bi_city'])) ? $_POST['bi_city'] : "";
            $bi_state = (array_key_exists('bi_state', $_POST) && isset($_POST['bi_state'])) ? $_POST['bi_state'] : "";
            $bi_country = (array_key_exists('bi_country', $_POST) && isset($_POST['bi_country'])) ? $_POST['bi_country'] : "";
            $bi_zip = (array_key_exists('bi_zip', $_POST) && isset($_POST['bi_zip'])) ? $_POST['bi_zip'] : "";
            $bi_paymentmethod = (array_key_exists('bi_paymentmethod', $_POST) && isset($_POST['bi_paymentmethod'])) ? $_POST['bi_paymentmethod'] : "";
            $bi_cash = (array_key_exists('bi_cash', $_POST) && isset($_POST['bi_cash'])) ? $_POST['bi_cash'] : "";
            $bi_creditcardname = (array_key_exists('bi_creditcardname', $_POST) && isset($_POST['bi_creditcardname'])) ? $_POST['bi_creditcardname'] : "";
            $bi_creditcardnumber = (array_key_exists('bi_creditcardnumber', $_POST) && isset($_POST['bi_creditcardnumber'])) ? $_POST['bi_creditcardnumber'] : "";
            $bi_creditcardverification = (array_key_exists('bi_creditcardverification', $_POST) && isset($_POST['bi_creditcardverification'])) ? $_POST['bi_creditcardverification'] : "";
            $bi_creditcardtype = (array_key_exists('bi_creditcardtype', $_POST) && isset($_POST['bi_creditcardtype'])) ? $_POST['bi_creditcardtype'] : "";
            $bi_creditcardexp = (array_key_exists('bi_creditcardexp', $_POST) && isset($_POST['bi_creditcardexp'])) ? $_POST['bi_creditcardexp'] : "";
            $bi_notes = (array_key_exists('bi_notes', $_POST) && isset($_POST['bi_notes'])) ? $_POST['bi_notes'] : "";
            
            $bi_lead = (array_key_exists('bi_lead', $_POST) && isset($_POST['bi_lead'])) ? $_POST['bi_lead'] : "";
            $bi_coupon = (array_key_exists('bi_coupon', $_POST) && isset($_POST['bi_coupon'])) ? $_POST['bi_coupon'] : "";
            $bi_ordertaker = (array_key_exists('bi_ordertaker', $_POST) && isset($_POST['bi_ordertaker'])) ? $_POST['bi_ordertaker'] : "";
            $bi_billstatus = (array_key_exists('bi_billstatus', $_POST) && isset($_POST['bi_billstatus'])) ? $_POST['bi_billstatus'] : "";
            $bi_lastbill = (array_key_exists('bi_lastbill', $_POST) && isset($_POST['bi_lastbill'])) ? $_POST['bi_lastbill'] : "";
            $bi_nextbill = (array_key_exists('bi_nextbill', $_POST) && isset($_POST['bi_nextbill'])) ? $_POST['bi_nextbill'] : "";
            $bi_nextinvoicedue = (array_key_exists('bi_nextinvoicedue', $_POST) && isset($_POST['bi_nextinvoicedue'])) ? $_POST['bi_nextinvoicedue'] : "";
            $bi_billdue = (array_key_exists('bi_billdue', $_POST) && isset($_POST['bi_billdue'])) ? $_POST['bi_billdue'] : "";
            $bi_postalinvoice = (array_key_exists('bi_postalinvoice', $_POST) && isset($_POST['bi_postalinvoice'])) ? $_POST['bi_postalinvoice'] : "";
            $bi_faxinvoice = (array_key_exists('bi_faxinvoice', $_POST) && isset($_POST['bi_faxinvoice'])) ? $_POST['bi_faxinvoice'] : "";
            $bi_emailinvoice = (array_key_exists('bi_emailinvoice', $_POST) && isset($_POST['bi_emailinvoice'])) ? $_POST['bi_emailinvoice'] : "";
            
            $bi_changeuserbillinfo = (array_key_exists('changeUserBillInfo', $_POST) && isset($_POST['changeUserBillInfo'])) ? $_POST['changeUserBillInfo'] : "0";

            /* variables for userinfo */
            $firstname = (array_key_exists('firstname', $_POST) && isset($_POST['firstname'])) ? $_POST['firstname'] : "";
            $lastname = (array_key_exists('lastname', $_POST) && isset($_POST['lastname'])) ? $_POST['lastname'] : "";
            $email = (array_key_exists('email', $_POST) && isset($_POST['email'])) ? $_POST['email'] : "";
            $department = (array_key_exists('department', $_POST) && isset($_POST['department'])) ? $_POST['department'] : "";
            $company = (array_key_exists('company', $_POST) && isset($_POST['company'])) ? $_POST['company'] : "";
            $workphone = (array_key_exists('workphone', $_POST) && isset($_POST['workphone'])) ? $_POST['workphone'] : "";
            $homephone = (array_key_exists('homephone', $_POST) && isset($_POST['homephone'])) ? $_POST['homephone'] : "";
            $mobilephone = (array_key_exists('mobilephone', $_POST) && isset($_POST['mobilephone'])) ? $_POST['mobilephone'] : "";
            $address = (array_key_exists('address', $_POST) && isset($_POST['address'])) ? $_POST['address'] : "";
            $city = (array_key_exists('city', $_POST) && isset($_POST['city'])) ? $_POST['city'] : "";
            $state = (array_key_exists('state', $_POST) && isset($_POST['state'])) ? $_POST['state'] : "";
            $country = (array_key_exists('country', $_POST) && isset($_POST['country'])) ? $_POST['country'] : "";
            $zip = (array_key_exists('zip', $_POST) && isset($_POST['zip'])) ? $_POST['zip'] : "";
            $notes = (array_key_exists('notes', $_POST) && isset($_POST['notes'])) ? $_POST['notes'] : "";
            $ui_changeuserinfo = (array_key_exists('changeuserinfo', $_POST) && isset($_POST['changeuserinfo'])) ? $_POST['changeuserinfo'] : "0";
            $ui_enableUserPortalLogin = (array_key_exists('enableUserPortalLogin', $_POST) && isset($_POST['enableUserPortalLogin'])) ? $_POST['enableUserPortalLogin'] : "0";
            $ui_PortalLoginPassword = (array_key_exists('portalLoginPassword', $_POST) && isset($_POST['portalLoginPassword'])) ? $_POST['portalLoginPassword'] : "";

            $username_prefix = (array_key_exists('username_prefix', $_POST) && !empty(trim(str_replace("%", "", $_POST['username_prefix']))))
                             ? trim(str_replace("%", "", $_POST['username_prefix'])) : "";
            $number = (array_key_exists('number', $_POST) && !empty(trim($_POST['number'])) && intval(trim($_POST['number'])) > 0)
                    ? intval(trim($_POST['number'])) : 4;
            
            $length_pass = (array_key_exists('length_pass', $_POST) && !empty(trim($_POST['length_pass'])) && intval(trim($_POST['length_pass'])) > 0)
                         ? intval(trim($_POST['length_pass'])) : 8;
            $length_user = (array_key_exists('length_user', $_POST) && !empty(trim($_POST['length_user'])) && intval(trim($_POST['length_user'])) > 0)
                         ? intval(trim($_POST['length_user'])) : 8;
            
            $passwordType = (array_key_exists('passwordType', $_POST) && !empty(trim($_POST['passwordType'])) &&
                          in_array(trim($_POST['passwordType']), $valid_passwordTypes))
                       ? trim($_POST['passwordType']) : $valid_passwordTypes[0];
            
            $group = (array_key_exists('group', $_POST) && !empty($_POST['group']) && in_array($_POST['group'], $valid_groups))
                   ? $_POST['group'] : "";
            $group_priority = (array_key_exists('group_priority', $_POST) && !empty(trim($_POST['group_priority'])) &&
                               intval(trim($_POST['group_priority'])) >= 0)
                            ? intval(trim($_POST['group_priority'])) : 0;

            $planName = (array_key_exists('planName', $_POST) && !empty($_POST['planName']) && in_array($_POST['planName'], $valid_planNames))
                   ? $_POST['planName'] : "";
            
            $startingIndex = (array_key_exists('startingIndex', $_POST) && !empty(trim($_POST['startingIndex'])) &&
                              intval(trim($_POST['startingIndex'])) > 0)
                           ? intval(trim($_POST['startingIndex'])) : 1000;
            
            $createBatchUsersType = (array_key_exists('createBatchUsersType', $_POST) && !empty(trim($_POST['createBatchUsersType'])) &&
                                     in_array(trim($_POST['createBatchUsersType']), array("createRandomUsers", "createIncrementUsers")))
                                  ? trim($_POST['createBatchUsersType']) : "createRandomUsers";
            
            
            $currDate = date('Y-m-d H:i:s');            // current date and time to enter as creationdate field
            $currBy = $_SESSION['operator_user'];


            // before looping through all generated batch users we create the batch_history entry
            // to associate the created users with a batch_history entry
            $sql_batch_id = addUserBatchHistory($dbSocket);
            
            if ($sql_batch_id == 0) {
                // 0 may be returned in the case of failure in adding the batch_history record due
                // to SQL related issues or in case where there is a duplicate record of the batch_history,
                // meaning, the same batch_name is used to identify the batch entry
                $failureMsg = "Failure creating batch users due to an error or possible duplicate entry: <b> $batch_name </b>";
                $logAction .= "Failure creating a batch_history entry on page: ";
            } else {
                
                $actionMsgBadUsernames = "";
                $actionMsgGoodUsernames = "";

                $exportCSV = "Username,Password||";
                
                $inserted_usernames = array( "Username" );
                $inserted_passwords = array( "Password" );
                
                if ($number > 0) {
                    for ($i = 0; $i < $number; $i++) {

                        // create the username
                        switch ($createBatchUsersType) {
                            default:
                            case "createRandomUsers":
                                $username_suffix = createPassword($length_user, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
                                break;
                                
                            case "createIncrementUsers":
                                $username_suffix = $startingIndex + $i;
                                break;
                        }
                        
                        $username = $username_prefix . $username_suffix;
                        $password = createPassword($length_pass, $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
                        
                        if (user_exists($dbSocket, $username)) {
                            // $username skipped
                            $detailedInfo[] = sprintf("cannot insert username %s, username exists",
                                                      htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
                            continue;
                        }
                        
                        // username does not exist
                        include_once("library/attributes.php");
                        
                        $hash = hashPasswordAttribute($passwordType, $password);
                        
                        if (!insert_single_attribute($dbSocket, $username, $passwordType, ':=', $hash)) {
                            // if we fail to insert this user, we skip other queries
                            $detailedInfo[] = sprintf("cannot insert username %s, db error",
                                                      htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
                            continue;
                        }
                        
                        // if a group was defined to add the user to in the form let's add it to the database
                        if (!empty($group)) {
                            if (!insert_single_user_group_mapping($dbSocket, $username, $group, $group_priority)) {
                                $detailedInfo[] = sprintf("cannot insert user-group mapping %s-%s",
                                                      htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), 
                                                      htmlspecialchars($group, ENT_QUOTES, 'UTF-8'));
                            }
                        }
                        
                        // adding user info
                        $params = array(
                                            "firstname" => $firstname,
                                            "lastname" => $lastname,
                                            "email" => $email,
                                            "department" => $department,
                                            "company" => $company,
                                            "workphone" => $workphone,
                                            "homephone" => $homephone,
                                            "mobilephone" => $mobilephone,
                                            "address" => $address,
                                            "city" => $city,
                                            "state" => $state,
                                            "country" => $country,
                                            "zip" => $zip,
                                            "notes" => $notes,
                                            "changeuserinfo" => $ui_changeuserinfo,
                                            "enableuserportallogin" => $ui_enableUserPortalLogin,
                                            "portalloginpassword" => $ui_PortalLoginPassword,
                                       );
                        
                        if (add_user_info($dbSocket, $username, $params) === false) {
                            $detailedInfo[] = sprintf("cannot insert userinfo for user %s",
                                                      htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
                        }
                        
                        
                        // adding billing info
                        $params = array(
                                            "contactperson" => $bi_contactperson,
                                            "company" => $bi_company,
                                            "email" => $bi_email,
                                            "phone" => $bi_phone,
                                            "address" => $bi_address,
                                            "city" => $bi_city,
                                            "state" => $bi_state,
                                            "country" => $bi_country,
                                            "zip" => $bi_zip,
                                            "paymentmethod" => $bi_paymentmethod,
                                            "cash" => $bi_cash,
                                            "creditcardname" => $bi_creditcardname,
                                            "creditcardnumber" => $bi_creditcardnumber,
                                            "creditcardexp" => $bi_creditcardexp,
                                            "creditcardverification" => $bi_creditcardverification,
                                            "creditcardtype" => $bi_creditcardtype,
                                            "notes" => $bi_notes,
                                            "lead" => $bi_lead,
                                            "coupon" => $bi_coupon,
                                            "ordertaker" => $bi_ordertaker,
                                            "billstatus" => $bi_billstatus,
                                            "lastbill" => $bi_lastbill,
                                            "nextbill" => $bi_nextbill,
                                            "postalinvoice" => $bi_postalinvoice,
                                            "faxinvoice" => $bi_faxinvoice,
                                            "emailinvoice" => $bi_emailinvoice,
                                            "changeuserbillinfo" => $bi_changeuserbillinfo,
                                            "planName" => $planName,
                                            "hotspot_id" => $hotspot_id,
                                            "batch_id" => $sql_batch_id,
                                            "creationdate" => $currDate,
                                            "creationby" => $operator
                                       );
                        
                        if (add_user_billing_info($dbSocket, $username, $params) === false) {
                            $detailedInfo[] = sprintf("cannot insert billing info for user %s",
                                                      htmlspecialchars($username, ENT_QUOTES, 'UTF-8'));
                        }
                        
                        // adding attributes
                        $skipList = array( 
                                           "username_prefix", "passwordType", "length_pass", "length_user", "number", "plan",
                                           "submit", "group", "group_priority", "createBatchUsersType", "startingIndex",
                                           "firstname", "lastname", "email", "department", "company", "workphone", "homephone",
                                           "mobilephone", "address", "city", "state", "country", "zip", "notes", "bi_contactperson",
                                           "bi_company", "bi_email", "bi_phone", "bi_address", "bi_city", "bi_state", "bi_country",
                                           "bi_zip", "bi_paymentmethod", "bi_cash", "bi_creditcardname", "bi_creditcardnumber",
                                           "bi_creditcardverification", "bi_creditcardtype", "bi_creditcardexp", "bi_notes", "bi_lead",
                                           "bi_coupon", "bi_ordertaker", "bi_billstatus", "bi_lastbill", "bi_nextbill", "bi_postalinvoice",
                                           "bi_faxinvoice", "bi_emailinvoice", "bi_batch_id", "changeUserBillInfo", "changeUserInfo",
                                           "batch_description", "batch_name", "hotspot", "hotspot_id", "copycontact",
                                           "enableUserPortalLogin", "portalLoginPassword", "csrf_token"
                                         );
                        
                        $count = handleAttributes($dbSocket, $username, $skipList);
                        
                        $inserted_usernames[] = $username;
                        $inserted_passwords[] = $password;
                        
                        $exportCSV .= "$username,$password||";
                        
                    } // end for
                    
                    $form_id = "export-users-form";
                    $exportForm .= sprintf('<form target="_blank" style="display: none" id="%s" ', $form_id)
                                 . 'method="POST">'
                                 . sprintf('<input type="hidden" name="batch_name" value="%s">',
                                           htmlspecialchars($batch_name, ENT_QUOTES, 'UTF-8'))
                                 . '<input type="hidden" name="type" value="batch">';
                                 
                    if (!empty($planName)) {
                        $exportForm .= sprintf('<input type="hidden" name="plan" value="%s">',
                                               htmlspecialchars($planName, ENT_QUOTES, 'UTF-8'));
                    }
                    
                    for ($i = 0; $i < count($inserted_usernames); $i++) {
                        $u = $inserted_usernames[$i];
                        $p = $inserted_passwords[$i];
                        $exportForm .= sprintf('<input type="hidden" name="accounts[%d][0]" value="%s">', $i, htmlspecialchars($u, ENT_QUOTES, 'UTF-8'))
                                     . sprintf('<input type="hidden" name="accounts[%d][1]" value="%s">', $i, htmlspecialchars($p, ENT_QUOTES, 'UTF-8'));
                    }
                    $exportForm .= '</form>';
                    $onclick = "batch_export('include/common/fileExportCSV.php')";
                    $exportForm .= sprintf('<input type="button" onclick="%s" value="CSV Download">', $onclick);
                    $onclick = "batch_export('include/common/printTickets.php')";
                    $exportForm .= sprintf('<input type="button" onclick="%s" value="Printable Tickets">', $onclick);
                            
                            // if batch_history record was created successfuly
                            //~ if ($sql_batch_id != 0) {
                                //~ // remove the last || chars to sanitize it for proper format
                                //~ $exportCSV = substr($exportCSV, 0, -2);
                                //~ $successMsg = "Exported Usernames - ".
                                                    //~ "<a href='include/common/fileExportCSV.php?csv_output=$exportCSV'>download</a><br/>".
                                                //~ "Printable Tickets - ".
                                                    //~ "<a href='include/common/printTickets.php?type=batch&plan=$planName&accounts=$exportCSV'>view</a><br/>".
                                                //~ "Added to database new user(s): <b> $actionMsgGoodUsernames </b><br/>";
                    $successMsg = sprintf("Created %d user(s) (batch name: <strong>%s</strong>)", count($inserted_usernames)-1, $batch_name);
                    $logAction .= sprintf("Successfully added to database new users [%s] with prefix [%s] on page: ",
                                          implode(", ", $inserted_usernames), $username_prefix);
                            //~ }
                
                
                } else {// $number > 0
                    $failureMsg = "specify a valid number of accounts";
                    $logAction = "specified an invalid number of accounts on page: ";
                }
            }
        }
    }

    include('library/closedb.php');

    include_once('include/management/actionMessages.php');
    
    if (!empty($exportForm)) {
        echo $exportForm;
        
        if (count($detailedInfo) > 0) {
            echo "<h4>Detailed info</h4>"
               . "<pre>"
               . implode("\n", $detailedInfo)
               . "</pre>";
        }
        
    } else {
    
        // set navbar stuff
        $navbuttons = array(
                                "AccountInfo-tab" => t('title','AccountInfo'),
                                "UserInfo-tab" => t('title','UserInfo'),
                                "BillingInfo-tab" => t('title','BillingInfo'),
                                "Attributes-tab" => t('title','Attributes'),
                           );

        print_tab_navbuttons($navbuttons);
        
        $input_descriptors1 = array();
        $input_descriptors1[] = array(
                                        "name" => "batch_name",
                                        "caption" => t('all','batchName'),
                                        "type" => "text",
                                        "value" => ((isset($failureMsg)) ? $batch_name : ""),
                                        "tooltipText" => t('Tooltip','batchNameTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "batch_description",
                                        "caption" => t('all','batchDescription'),
                                        "type" => "text",
                                        "value" => ((isset($failureMsg)) ? $batch_description : ""),
                                        "tooltipText" => t('Tooltip','batchDescriptionTooltip')
                                     );
                                     
        $options = $valid_hotspots;
        array_unshift($options , '');
        $input_descriptors1[] = array(
                                        "type" =>"select",
                                        "name" => "hotspot_id",
                                        "caption" => t('all','HotSpot'),
                                        "options" => $options,
                                        "onchange" => "switchAuthType()",
                                        "selected_value" => ((isset($failureMsg) && intval($hotspot_id) > 0) ? "hotspot-$hotspot_id" : ""),
                                        "tooltipText" => t('Tooltip','hotspotTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "username_prefix",
                                        "caption" => t('all','UsernamePrefix'),
                                        "type" => "text",
                                        "value" => ((isset($failureMsg)) ? $username_prefix : ""),
                                        "tooltipText" => t('Tooltip','usernamePrefixTooltip'),
                                        "random" => true
                                     );
                                     
        $input_descriptors1[] = array(
                                        "type" => "radio",
                                        "value" => "createRandomUsers",
                                        "name" => "createBatchUsersType",
                                        "onclick" => "toggleRandomUsers()",
                                        "caption" => t('all','CreateRandomUsers'),
                                        "checked" => true
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "length_user",
                                        "caption" => t('all','UsernameLength'),
                                        "type" => "number",
                                        "value" => ((isset($failureMsg)) ? $length_user : "8"),
                                        "tooltipText" => t('Tooltip','lengthOfUsernameTooltip'),
                                        "min" => "4",
                                     );
                                     
        $input_descriptors1[] = array(
                                        "type" => "radio",
                                        "value" => "createIncrementUsers",
                                        "name" => "createBatchUsersType",
                                        "onclick" => "toggleIncrementUsers()",
                                        "caption" => t('all','CreateIncrementingUsers'),
                                        "checked" => false
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "startingIndex",
                                        "caption" => t('all','StartingIndex'),
                                        "type" => "number",
                                        "value" => ((isset($failureMsg)) ? $startingIndex : "1000"),
                                        "tooltipText" => t('Tooltip','startingIndexTooltip'),
                                        "min" => "1",
                                        "disabled" => true,
                                     );
                                     
        $input_descriptors1[] = array(
                                        "type" =>"select",
                                        "name" => "passwordType",
                                        "caption" => t('all','PasswordType'),
                                        "options" => $valid_passwordTypes,
                                        "selected_value" => ((isset($failureMsg)) ? $passwordType : ""),
                                        "tooltipText" => t('Tooltip','passwordTypeTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "length_pass",
                                        "caption" => t('all','PasswordLength'),
                                        "type" => "number",
                                        "value" => ((isset($failureMsg)) ? $length_pass : "8"),
                                        "tooltipText" => t('Tooltip','lengthOfPasswordTooltip'),
                                        "min" => "8",
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "number",
                                        "caption" => t('all','NumberInstances'),
                                        "type" => "number",
                                        "value" => ((isset($failureMsg)) ? $number : "4"),
                                        "tooltipText" => t('Tooltip','instancesToCreateTooltip'),
                                        "min" => "4",
                                        "step" => "4"
                                     );
                                     
        $options = $valid_groups;
        array_unshift($options, '');
        $input_descriptors1[] = array(
                                        "type" =>"select",
                                        "name" => "group",
                                        "caption" => t('all','Group'),
                                        "options" => $options,
                                        "selected_value" => ((isset($failureMsg)) ? $group : ""),
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "name" => "group_priority",
                                        "caption" => t('all','GroupPriority'),
                                        "type" => "number",
                                        "value" => ((isset($failureMsg)) ? $group_priority : "0"),
                                        "min" => "0",
                                     );
        
        $options = $valid_planNames;
        array_unshift($options, '');
        $input_descriptors1[] = array(
                                        "type" =>"select",
                                        "name" => "planName",
                                        "caption" => t('all','PlanName'),
                                        "options" => $options,
                                        "selected_value" => ((isset($failureMsg)) ? $planName : ""),
                                        "tooltipText" => t('Tooltip','planTooltip')
                                     );
                                     
        $input_descriptors1[] = array(
                                        "type" => "submit",
                                        "name" => "submit",
                                        "value" => t('buttons','apply')
                                      );
?>

<form name="batchuser" method="POST">
    <div class="tabcontent" id="AccountInfo-tab" style="display: block">
        <fieldset>
            <h302><?= t('title','AccountInfo') ?></h302>
            <ul>

<?php
                foreach ($input_descriptors1 as $input_descriptor) {
                    print_form_component($input_descriptor);
                }
?>
            </ul>
        </fieldset>
    </div><!-- #AccountInfo-tab -->

    <div class="tabcontent" id="UserInfo-tab">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userinfo.php');
?>
    </div>

    <div class="tabcontent" id="BillingInfo-tab">
<?php
        $customApplyButton = "<input type='submit' name='submit' value=".t('buttons','apply')." class='button' />";
        include_once('include/management/userbillinfo.php');
?>
    </div>

    <div class="tabcontent" id="Attributes-tab">
<?php
        include_once('include/management/attributes.php');
?>
    </div>
</form>

<?php
    }

    include('include/config/logging.php');
    
    if (!empty($exportForm) && !empty($form_id)) {
        $inline_extra_js = 'function batch_export(action) {' . "\n";
        $inline_extra_js .= sprintf("var f = document.getElementById('%s');", $form_id);
        $inline_extra_js .= '
f.action = action;
f.submit();
}
';
    } else {
        $inline_extra_js = "";
    }
    
    print_footer_and_html_epilogue($inline_extra_js);
?>
