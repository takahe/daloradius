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

    // init logging variables
    $log = "visited page: ";
    $logAction = "";
    $logDebugSQL = "";

    include('include/management/pages_common.php');

    // we import validation facilities
    include_once("library/validation.php");

    // if cleartext passwords are not allowed, 
    // we remove Cleartext-Password from the $valid_passwordTypes array
    if (isset($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) &&
        strtolower($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) !== 'cleartext') {
        $valid_passwordTypes = array_diff($valid_passwordTypes, array("Cleartext-Password"));
    }

    isset($_POST['username']) ? $username = $_POST['username'] : $username = "";
    isset($_POST['password']) ? $password = $_POST['password'] : $password = "";
    isset($_POST['planName']) ? $planName = $_POST['planName'] : $planName = "";
    isset($_POST['profiles']) ? $profiles = $_POST['profiles'] : $profiles = "";
    $passwordType = (array_key_exists('passwordType', $_POST) && isset($_POST['passwordType']) &&
                         in_array($_POST['passwordType'], $valid_passwordTypes)) ? $_POST['passwordType'] : "";
    isset($_POST['notificationWelcome']) ? $notificationWelcome = $_POST['notificationWelcome'] : $notificationWelcome = "";
    

    isset($_POST['bi_contactperson']) ? $bi_contactperson = $_POST['bi_contactperson'] : $bi_contactperson = "";
    isset($_POST['bi_company']) ? $bi_company = $_POST['bi_company'] : $bi_company = "";
    isset($_POST['bi_email']) ? $bi_email = $_POST['bi_email'] : $bi_email = "";
    isset($_POST['bi_phone']) ? $bi_phone = $_POST['bi_phone'] : $bi_phone = "";
    isset($_POST['bi_address']) ? $bi_address = $_POST['bi_address'] : $bi_address = "";
    isset($_POST['bi_city']) ? $bi_city = $_POST['bi_city'] : $bi_city = "";
    isset($_POST['bi_state']) ? $bi_state = $_POST['bi_state'] : $bi_state = "";
    isset($_POST['bi_country']) ? $bi_country = $_POST['bi_country'] : $bi_country = "";
    isset($_POST['bi_zip']) ? $bi_zip = $_POST['bi_zip'] : $bi_zip = "";
    isset($_POST['bi_paymentmethod']) ? $bi_paymentmethod = $_POST['bi_paymentmethod'] : $bi_paymentmethod = "";
    isset($_POST['bi_cash']) ? $bi_cash = $_POST['bi_cash'] : $bi_cash = "";
    isset($_POST['bi_creditcardname']) ? $bi_creditcardname = $_POST['bi_creditcardname'] : $bi_creditcardname = "";
    isset($_POST['bi_creditcardnumber']) ? $bi_creditcardnumber = $_POST['bi_creditcardnumber'] : $bi_creditcardnumber = "";
    isset($_POST['bi_creditcardverification']) ? $bi_creditcardverification = $_POST['bi_creditcardverification'] : $bi_creditcardverification = "";
    isset($_POST['bi_creditcardtype']) ? $bi_creditcardtype = $_POST['bi_creditcardtype'] : $bi_creditcardtype = "";
    isset($_POST['bi_creditcardexp']) ? $bi_creditcardexp = $_POST['bi_creditcardexp'] : $bi_creditcardexp = "";
    isset($_POST['bi_notes']) ? $bi_notes = $_POST['bi_notes'] : $bi_notes = "";
    isset($_POST['bi_lead']) ? $bi_lead = $_POST['bi_lead'] : $bi_lead = "";
    isset($_POST['bi_coupon']) ? $bi_coupon = $_POST['bi_coupon'] : $bi_coupon = "";
    isset($_POST['bi_ordertaker']) ? $bi_ordertaker = $_POST['bi_ordertaker'] : $bi_ordertaker = "";
    isset($_POST['bi_billstatus']) ? $bi_billstatus = $_POST['bi_billstatus'] : $bi_billstatus = "";
    isset($_POST['bi_lastbill']) ? $bi_lastbill = $_POST['bi_lastbill'] : $bi_lastbill = "";
    isset($_POST['bi_nextbill']) ? $bi_nextbill = $_POST['bi_nextbill'] : $bi_nextbill = "";
    isset($_POST['bi_nextinvoicedue']) ? $bi_nextinvoicedue = $_POST['bi_nextinvoicedue'] : $bi_nextinvoicedue = "";
    isset($_POST['bi_billdue']) ? $bi_billdue = $_POST['bi_billdue'] : $bi_billdue = "";
    isset($_POST['bi_postalinvoice']) ? $bi_postalinvoice = $_POST['bi_postalinvoice'] : $bi_postalinvoice = "";
    isset($_POST['bi_faxinvoice']) ? $bi_faxinvoice = $_POST['bi_faxinvoice'] : $bi_faxinvoice = "";
    isset($_POST['bi_emailinvoice']) ? $bi_emailinvoice = $_POST['bi_emailinvoice'] : $bi_emailinvoice = "";
    isset($_POST['changeUserBillInfo']) ? $bi_changeuserbillinfo = $_POST['changeUserBillInfo'] : $bi_changeuserbillinfo = "0";

    isset($_POST['firstname']) ? $firstname = $_POST['firstname'] : $firstname = "";
    isset($_POST['lastname']) ? $lastname = $_POST['lastname'] : $lastname = "";
    isset($_POST['email']) ? $email = $_POST['email'] : $email = "";
    isset($_POST['department']) ? $department = $_POST['department'] : $department = "";
    isset($_POST['company']) ? $company = $_POST['company'] : $company = "";
    isset($_POST['workphone']) ? $workphone = $_POST['workphone'] : $workphone = "";
    isset($_POST['homephone']) ? $homephone = $_POST['homephone'] :  $homephone = "";
    isset($_POST['mobilephone']) ? $mobilephone = $_POST['mobilephone'] : $mobilephone = "";
    isset($_POST['address']) ? $ui_address = $_POST['address'] : $ui_address = "";
    isset($_POST['city']) ? $ui_city = $_POST['city'] : $ui_city = "";
    isset($_POST['state']) ? $ui_state = $_POST['state'] : $ui_state = "";
    isset($_POST['country']) ? $ui_country = $_POST['country'] : $ui_country = "";
    isset($_POST['zip']) ? $ui_zip = $_POST['zip'] : $ui_zip = "";
    isset($_POST['notes']) ? $notes = $_POST['notes'] : $notes = "";
    isset($_POST['changeUserInfo']) ? $ui_changeuserinfo = $_POST['changeUserInfo'] : $ui_changeuserinfo = "0";
    isset($_POST['enableUserPortalLogin']) ? $ui_enableUserPortalLogin = $_POST['enableUserPortalLogin'] : $ui_enableUserPortalLogin = "0";
    isset($_POST['portalLoginPassword']) ? $ui_PortalLoginPassword = $_POST['portalLoginPassword'] : $ui_PortalLoginPassword = "";
    

    function addPlanProfile($dbSocket, $username, $planName) {

        global $logDebugSQL;
        global $configValues;

        // search to see if the plan is associated with any profiles
        $sql = "SELECT profile_name FROM ".
                $configValues['CONFIG_DB_TBL_DALOBILLINGPLANSPROFILES'].
                " WHERE plan_name='$planName'";
        $res = $dbSocket->getCol($sql);
        // $res is an array of all profiles associated with this plan
        
        // if the profile list for this plan isn't empty, we associate it with the user
        if (count($res) != 0) {
    
            // if profiles are associated with this plan, loop through each and add a usergroup entry for each
            foreach($res as $profile_name) {
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
                    " VALUES ('".$dbSocket->escapeSimple($username)."','$profile_name','0')";
                $res = $dbSocket->query($sql);
            }
            
            return true;
        
        }
        
        return false;

    }
    

    function addGroups($dbSocket, $username, $groups) {

        global $logDebugSQL;
        global $configValues;

        // insert usergroup mapping
        if (isset($groups)) {

            foreach ($groups as $group) {

                if (trim($group) != "") {
                    $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADUSERGROUP']." (UserName,GroupName,priority) ".
                            " VALUES ('".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($group)."',0) ";
                    $res = $dbSocket->query($sql);
                    $logDebugSQL .= $sql . "\n";
                }
            }
        }
    }


    
    
    function addUserInfo($dbSocket, $username) {

        global $firstname;
        global $lastname;
        global $email;
        global $department;
        global $company;
        global $workphone;
        global $homephone;
        global $mobilephone;
        global $ui_address;
        global $ui_city;
        global $ui_state;
        global $ui_country;
        global $ui_zip;
        global $notes;
        global $ui_changeuserinfo;
        global $ui_enableUserPortalLogin;
        global $ui_PortalLoginPassword;
        global $logDebugSQL;
        global $configValues;

        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
                        " WHERE username='".$dbSocket->escapeSimple($username)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        // if there were no records for this user present in the userinfo table
        if ($res->numRows() == 0) {
            // insert user information table
            $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERINFO'].
                    " (id, username, firstname, lastname, email, department, company, workphone, homephone, ".
                    " mobilephone, address, city, state, country, zip, notes, changeuserinfo, portalloginpassword, enableportallogin, creationdate, creationby, updatedate, updateby) ".
                    " VALUES (0,
                    '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($firstname)."', '".
                    $dbSocket->escapeSimple($lastname)."', '".$dbSocket->escapeSimple($email)."', '".
                    $dbSocket->escapeSimple($department)."', '".$dbSocket->escapeSimple($company)."', '".
                    $dbSocket->escapeSimple($workphone)."', '".$dbSocket->escapeSimple($homephone)."', '".
                    $dbSocket->escapeSimple($mobilephone)."', '".$dbSocket->escapeSimple($ui_address)."', '".
                    $dbSocket->escapeSimple($ui_city)."', '".$dbSocket->escapeSimple($ui_state)."', '".
                    $dbSocket->escapeSimple($ui_country)."', '".
                    $dbSocket->escapeSimple($ui_zip)."', '".$dbSocket->escapeSimple($notes)."', '".
                    $dbSocket->escapeSimple($ui_changeuserinfo)."', '".
                    $dbSocket->escapeSimple($ui_PortalLoginPassword)."', '".$dbSocket->escapeSimple($ui_enableUserPortalLogin).
                    "', '$currDate', '$currBy', NULL, NULL)";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";
        } //FIXME:
          //if the user already exist in userinfo then we should somehow alert the user
          //that this has happened and the administrator/operator will take care of it
    }



    function addUserBillInfo($dbSocket, $username) {

        global $planName;
        global $bi_contactperson;
        global $bi_company;
        global $bi_email;
        global $bi_phone;
        global $bi_address;
        global $bi_city;
        global $bi_state;
        global $bi_country;
        global $bi_zip;
        global $bi_paymentmethod;
        global $bi_cash;
        global $bi_creditcardname;
        global $bi_creditcardnumber;
        global $bi_creditcardexp;
        global $bi_creditcardverification;
        global $bi_creditcardtype;
        global $bi_notes;
        global $bi_lead;
        global $bi_coupon;
        global $bi_ordertaker;
        global $bi_billstatus;
        global $bi_lastbill;
        global $bi_nextbill;
        global $bi_nextinvoicedue;
        global $bi_billdue;
        global $bi_postalinvoice;
        global $bi_faxinvoice;
        global $bi_emailinvoice;
        global $bi_changeuserbillinfo;
        global $logDebugSQL;
        global $configValues;

        $currDate = date('Y-m-d H:i:s');
        $currBy = $_SESSION['operator_user'];

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
                        " WHERE username='".$dbSocket->escapeSimple($username)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        // if there were no records for this user present in the userbillinfo table
        if ($res->numRows() == 0) {
            
            // calculate the nextbill and other related billing information
            $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                            " WHERE planName='".$dbSocket->escapeSimple($planName)."' LIMIT 1";
            $res = $dbSocket->query($sql);
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
            $logDebugSQL .= $sql . "\n";
                            
            $planRecurring = $row['planRecurring'];
            $planRecurringPeriod = $row['planRecurringPeriod'];
            $planRecurringBillingSchedule = $row['planRecurringBillingSchedule'];
            
            
            // initialize next bill date string (Y-m-d style)
            $nextBillDate = "0000-00-00";
            
            // get next billing date
            if ($planRecurring == "Yes") {
                $nextBillDate = getNextBillingDate($planRecurringBillingSchedule, $planRecurringPeriod);
            }

        
            // if $bi_nextbill was not set to anything (empty)
            if (empty($bi_nextbill))
                $bi_nextbill = $nextBillDate;
                    
            
            
            // insert user billing information table
            $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].
                    " (id, planname, username, contactperson, company, email, phone, ".
                    " address, city, state, country, zip, ".
                    " paymentmethod, cash, creditcardname, creditcardnumber, creditcardverification, creditcardtype, creditcardexp, ".
                    " notes, changeuserbillinfo, ".
                    " `lead`, coupon, ordertaker, billstatus, lastbill, nextbill, nextinvoicedue, billdue, postalinvoice, faxinvoice, emailinvoice, ".
                    " creationdate, creationby, updatedate, updateby) ".
                    " VALUES (0, '".$dbSocket->escapeSimple($planName)."', 
                    '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($bi_contactperson)."', '".
                    $dbSocket->escapeSimple($bi_company)."', '".$dbSocket->escapeSimple($bi_email)."', '".
                    $dbSocket->escapeSimple($bi_phone)."', '".$dbSocket->escapeSimple($bi_address)."', '".
                    $dbSocket->escapeSimple($bi_city)."', '".$dbSocket->escapeSimple($bi_state)."', '".
                    $dbSocket->escapeSimple($bi_country)."', '".
                    $dbSocket->escapeSimple($bi_zip)."', '".$dbSocket->escapeSimple($bi_paymentmethod)."', '".
                    $dbSocket->escapeSimple($bi_cash)."', '".$dbSocket->escapeSimple($bi_creditcardname)."', '".
                    $dbSocket->escapeSimple($bi_creditcardnumber)."', '".$dbSocket->escapeSimple($bi_creditcardverification)."', '".
                    $dbSocket->escapeSimple($bi_creditcardtype)."', '".$dbSocket->escapeSimple($bi_creditcardexp)."', '".
                    $dbSocket->escapeSimple($bi_notes)."', '".
                    $dbSocket->escapeSimple($bi_changeuserbillinfo)."', '".
                    $dbSocket->escapeSimple($bi_lead)."', '".$dbSocket->escapeSimple($bi_coupon)."', '".
                    $dbSocket->escapeSimple($bi_ordertaker)."', '".$dbSocket->escapeSimple($bi_billstatus)."', '".
                    $dbSocket->escapeSimple($bi_lastbill)."', '".$dbSocket->escapeSimple($bi_nextbill)."', '".
                    $dbSocket->escapeSimple($bi_nextinvoicedue)."', '".$dbSocket->escapeSimple($bi_billdue)."', '".
                    $dbSocket->escapeSimple($bi_postalinvoice)."', '".$dbSocket->escapeSimple($bi_faxinvoice)."', '".
                    $dbSocket->escapeSimple($bi_emailinvoice).
                                    "', '$currDate', '$currBy', NULL, NULL)";
            $res = $dbSocket->query($sql);
            $logDebugSQL .= $sql . "\n";
            
            $user_id = $dbSocket->getOne( "SELECT LAST_INSERT_ID() FROM `".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO']."`" );
            return $user_id;
            
        } //FIXME:
          //if the user already exist in userinfo then we should somehow alert the user
          //that this has happened and the administrator/operator will take care of it

    }
    
    
    
    

    if (isset($_POST["submit"])) {
        
        include 'library/opendb.php';

        global $username;
        global $password;
        global $passwordType;

        $sql = "SELECT * FROM ".$configValues['CONFIG_DB_TBL_RADCHECK']." WHERE UserName='".
                        $dbSocket->escapeSimple($username)."'";
        $res = $dbSocket->query($sql);
        $logDebugSQL .= $sql . "\n";

        if ($res->numRows() == 0) {
            
            if (trim($username) != "" and trim($password) != "") {

                // we need to perform the secure method escapeSimple on $dbPassword early because as seen below
                // we manipulate the string and manually add to it the '' which screw up the query if added in $sql
                $password = $dbSocket->escapeSimple($password);

                switch($configValues['CONFIG_DB_PASSWORD_ENCRYPTION']) {
                    case "cleartext":
                            $dbPassword = "'$password'";
                            break;
                    case "crypt":
                            $dbPassword = "ENCRYPT('$password')";
                            break;
                    case "md5":
                            $dbPassword = "MD5('$password')";
                            break;
                    default:
                            $dbPassword = "'$password'";
                }

                // at this stage $dbPassword contains the password string encapsulated by '' and either uses
                // a function to encrypt it like ENCRYPT or it doesn't, it's based on the configuration
                // but here we provide another stage, for Crypt-Password and MD5-Password it's obvious
                // that the password need be encrypted so even if this option is not in the configuration
                // we enforce it.

                // we first check if the password attribute is to be encrypted at all
                if (preg_match("/crypt/i", $passwordType)) {
                    // if we don't find the encrypt function even though we identified
                    // a Crypt-Password attribute
                    if (!(preg_match("/encrypt/i",$dbPassword))) {
                            $dbPassword = "ENCRYPT('$password')";
                    }

                        // we now perform the same check but for an MD5-Password attribute
                } elseif (preg_match("/md5/i", $passwordType)) {
                    // if we don't find the md5 function even though we identified
                    // a MD5-Password attribute
                    if (!(preg_match("/md5/i",$dbPassword))) {
                            $dbPassword = "MD5('$password')";
                    }
                }

                // insert username/password
                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK']." (id,Username,Attribute,op,Value) ".
                                " VALUES (0, '".$dbSocket->escapeSimple($username)."', '".$dbSocket->escapeSimple($passwordType).
                                "', ':=', $dbPassword)";
                $res = $dbSocket->query($sql);
                $logDebugSQL .= $sql . "\n";

                addGroups($dbSocket, $username, $profiles);
                addPlanProfile($dbSocket, $username, $planName);
                addUserInfo($dbSocket, $username);
                $userbillinfo_id = addUserBillInfo($dbSocket, $username);

                // create any invoices if required (meaning, if a plan was chosen)
                if ($planName) {
                    include_once("include/management/userBilling.php");
                    
                    // get plan information
                    $sql = "SELECT id, planCost, planSetupCost, planTax FROM ".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].
                        " WHERE planName='".$dbSocket->escapeSimple($planName)."' LIMIT 1";
                    $res = $dbSocket->query($sql);
                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                    // calculate tax (planTax is the numerical percentage amount) 
                    $calcTax = (float) ($row['planCost'] * (float)($row['planTax'] / 100) );
                    $invoiceItems[0]['plan_id'] = $row['id'];
                    $invoiceItems[0]['amount'] = $row['planCost'];
                    $invoiceItems[0]['tax'] = $calcTax;
                    $invoiceItems[0]['notes'] = 'charge for plan service';
                    
                    if (isset($row['planSetupCost']) && ($row['planSetupCost'] != '') ) {
                        $calcTax = (float) ($row['planSetupCost'] * (float)($row['planTax'] / 100) );
                        $invoiceItems[1]['plan_id'] = $row['id'];
                        $invoiceItems[1]['amount'] = $row['planSetupCost'];
                        $invoiceItems[1]['tax'] = $calcTax;
                        $invoiceItems[1]['notes'] = 'charge for plan setup fee (one time)';
                    }
                                        
                    userInvoiceAdd($userbillinfo_id, array(), $invoiceItems);
                    
                }
                
                
                if ($notificationWelcome == 1) {
                    include("include/common/notificationsWelcome.php");
                    
                }
                
                $successMsg = "Added to database new user: <b> $username </b>";
                $logAction .= "Successfully added new user [$username] on page: ";
            } else {
                $failureMsg = "username or password are empty";
                $logAction .= "Failed adding (possible empty user/pass) new user [$username] on page: ";
            }
        } else { 
            $failureMsg = "user already exist in database: <b> $username </b>";
            $logAction .= "Failed adding new user already existing in database [$username] on page: ";
        }
    
        include 'library/closedb.php';

    }

    include_once('library/config_read.php');
    
    $hiddenPassword = (strtolower($configValues['CONFIG_IFACE_PASSWORD_HIDDEN']) == "yes")
                    ? 'password' : 'text';
    
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
    
    $title = t('Intro','billposnew.php');
    $help = t('helpPage','billposnew');
    
    print_html_prologue($title, $langCode, $extra_css, $extra_js);

    include("menu-bill-pos.php");
    
    echo '<div id="contentnorightbar">';
    print_title_and_help($title, $help);

        // set navbar stuff
    $navbuttons = array(
                            "AccountInfo-tab" => t('title','AccountInfo'),
                            "UserInfo-tab" => t('title','UserInfo'),
                            "BillingInfo-tab" => t('title','BillingInfo'),
                            "Advanced-tab" => t('title','Advanced'),
                       );

    print_tab_navbuttons($navbuttons);

    include_once('include/management/actionMessages.php');
    
    include_once('include/management/populate_selectbox.php');
    
    $input_descriptors = array();
    
    $input_descriptors[] = array(
                                    "id" => "username",
                                    "name" => "username",
                                    "caption" => t('all','Username'),
                                    "type" => "text",
                                    "value" => "",
                                    "random" => true,
                                    "tooltipText" => t('Tooltip','usernameTooltip')
                                 );
                                
    $input_descriptors[] = array(
                                    "id" => "password",
                                    "name" => "password",
                                    "caption" => t('all','Password'),
                                    "type" => $hiddenPassword,
                                    "value" => "",
                                    "random" => true,
                                    "tooltipText" => t('Tooltip','passwordTooltip')
                                );
    
?>

<form method="POST">
    <div class="tabber">
        <div class="tabcontent" id="AccountInfo-tab" style="display: block">
            <fieldset>

                <h302><?= t('title','AccountInfo') ?></h302>

                <ul>
<?php
                    foreach ($input_descriptors as $input_descriptor) {
                        print_form_component($input_descriptor);
                    }
?>



                    <li class='fieldset'>
                    <label for='planName' class='form'><?= t('all','PlanName') ?></label>
                            <?php
                                   populate_plans("Select Plan","planName","form");
                            ?>
                    <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('planNameTooltip')" /> 
                    
                    <div id='planNameTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                        <img src='images/icons/comment.png' alt='Tip' border='0' />
                        <?= t('Tooltip','planNameTooltip') ?>
                    </div>
                    </li>
    

                    <li class='fieldset'>
                    <label for='profile' class='form'><?= t('all','Profile')?></label>
                    <?php
                            populate_groups("Select Profile","profiles[]");
                    ?>

                    <a class='tablenovisit' href='#'
                            onClick="javascript:ajaxGeneric('include/management/dynamic_groups.php','getGroups','divContainerProfiles',genericCounter('divCounter')+'&elemName=profiles[]');">Add</a>

                    <img src='images/icons/comment.png' alt='Tip' border='0' onClick="javascript:toggleShowDiv('groupTooltip')" />

                    <div id='divContainerProfiles'>
                    </div>

                    <div id='groupTooltip'  style='display:none;visibility:visible' class='ToolTip'>
                            <img src='images/icons/comment.png' alt='Tip' border='0' />
                            <?= t('Tooltip','groupTooltip') ?>
                    </div>
                    </li>

        <li class='fieldset'>
        <label for='userupdate' class='form'><?= t('all','SendWelcomeNotification')?></label>
        <input type='checkbox' class='form' name='notificationWelcome' value='1' checked/>
            <br/>
        </li>


        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?= t('buttons','apply') ?>' tabindex=10000 class='button' />
        </li>

        </ul>

    </fieldset>

    </div>


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


     <div class="tabcontent" id="Advanced-tab">

        <fieldset>

                <h302> <?= t('title','AccountInfo'); ?> </h302>

                <ul>

                <li class='fieldset'>
                <label for='passwordType' class='form'><?= t('all','PasswordType')?> </label>
                <select class='form' tabindex=102 name='passwordType' >
                        <option value='Cleartext-Password'>Cleartext-Password</option>
                        <option value='User-Password'>User-Password</option>
                        <option value='Crypt-Password'>Crypt-Password</option>
                        <option value='MD5-Password'>MD5-Password</option>
                        <option value='SHA1-Password'>SHA1-Password</option>
                        <option value='CHAP-Password'>CHAP-Password</option>
                </select>
                </li>

        <li class='fieldset'>
        <br/>
        <hr><br/>
        <input type='submit' name='submit' value='<?= t('buttons','apply') ?>' tabindex=10000 class='button' />
        </li>

        </ul>

    </fieldset>

    </div>

</div>

    </form>

<?php
    include('include/config/logging.php');
    print_footer_and_html_epilogue();
?>
