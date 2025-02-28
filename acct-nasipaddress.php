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
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */
 
    include("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

    include('library/check_operator_perm.php');
    
    include_once('library/config_read.php');

    // validate this parameter before including menu
    $onlyactive = (array_key_exists('only-active', $_GET) && isset($_GET['only-active']));

    // this regex allows input like (e.g.) 127, 127., 127.0, 127.0., 127.0.0, 127.0.0 and 127.0.0.1
    $ip_regex = '/^(((2(5[0-5]|[0-4][0-9]))|1[0-9]{2}|[1-9]?[0-9])\.?){1,4}$/';
    
    // validate this parameter before including menu
    $nasipaddress = (array_key_exists('nasipaddress', $_GET) && isset($_GET['nasipaddress']) &&
                  preg_match($ip_regex, $_GET['nasipaddress'], $m) !== false) ? $_GET['nasipaddress'] : "";
    $nasipaddress_enc = (!empty($nasipaddress)) ? htmlspecialchars($nasipaddress, ENT_QUOTES, 'UTF-8') : "";

    //feed the sidebar variables
    $accounting_nasipaddress = $nasipaddress_enc;

    include("menu-accounting.php");

    $cols = array(
                    "radacctid" => t('all','ID'),
                    "hotspot" => t('all','HotSpot'),
                    "username" => t('all','Username'),
                    "framedipaddress" => t('all','IPAddress'),
                    "acctstarttime" => t('all','StartTime'),
                    "acctstoptime" => t('all','StopTime'),
                    "acctsessiontime" => t('all','TotalTime'),
                    "acctinputoctets" => t('all','Upload') . " (" . t('all','Bytes') . ")",
                    "acctoutputoctets" => t('all','Download') . " (" . t('all','Bytes') . ")",
                    "acctterminatecause" => t('all','Termination'),
                    "nasipaddress" => t('all','NASIPAddress')
                 );

    $colspan = count($cols);
    $half_colspan = intdiv($colspan, 2);
    
    $param_cols = array();
    foreach ($cols as $k => $v) { if (!is_int($k)) { $param_cols[$k] = $v; } }

    // validating user passed parameters

    // whenever possible we use a whitelist approach
    $orderBy = (array_key_exists('orderBy', $_GET) && isset($_GET['orderBy']) &&
                in_array($_GET['orderBy'], array_keys($param_cols)))
             ? $_GET['orderBy'] : array_keys($param_cols)[0];

    $orderType = (array_key_exists('orderType', $_GET) && isset($_GET['orderType']) &&
                  in_array(strtolower($_GET['orderType']), array( "desc", "asc" )))
               ? strtolower($_GET['orderType']) : "desc";
?>
        <div id="contentnorightbar">
            <h2 id="Intro">
                <a href="#" onclick="javascript:toggleShowDiv('helpPage')">
                    <?= t('Intro','acctnasipaddress.php') ?>
                    <h144>&#x2754;</h144>
                </a>
            </h2>

            <div id="helpPage" style="display:none;visibility:visible"><?= t('helpPage','acctnasipaddress') ?><br></div>
            <br>



<?php

    // we can only use the $dbSocket after we have included 'library/opendb.php' which initialzes the connection and the $dbSocket object
    include('library/opendb.php');
    include('include/management/pages_common.php');

    $sql_WHERE = (!empty($nasipaddress))
               ? sprintf(" WHERE NASIPAddress LIKE '%s%%'", $dbSocket->escapeSimple($nasipaddress))
               : "";

    // setup php session variables for exporting
    $_SESSION['reportTable'] = $configValues['CONFIG_DB_TBL_RADACCT'];
    $_SESSION['reportQuery'] = $sql_WHERE;
    $_SESSION['reportType'] = "accountingGeneric";

    $sql = "SELECT ra.RadAcctId, dh.name as hotspot, ra.UserName, ra.FramedIPAddress, ra.AcctStartTime, ra.AcctStopTime,
                   ra.AcctSessionTime, ra.AcctInputOctets, ra.AcctOutputOctets, ra.AcctTerminateCause, ra.NASIPAddress
              FROM %s AS ra LEFT JOIN %s AS dh ON ra.calledstationid=dh.mac";
    
    $sql = sprintf($sql, $configValues['CONFIG_DB_TBL_RADACCT'], $configValues['CONFIG_DB_TBL_DALOHOTSPOTS']) . $sql_WHERE;
    $res = $dbSocket->query($sql);
    $numrows = $res->numRows();
    
    if ($numrows > 0) {
        /* START - Related to pages_numbering.php */
        
        // when $numrows is set, $maxPage is calculated inside this include file
        include('include/management/pages_numbering.php');    // must be included after opendb because it needs to read
                                                              // the CONFIG_IFACE_TABLES_LISTING variable from the config file
        
        // here we decide if page numbers should be shown
        $drawNumberLinks = strtolower($configValues['CONFIG_IFACE_TABLES_LISTING_NUM']) == "yes" && $maxPage > 1;
        
        /* END */
                     
        // we execute and log the actual query
        $sql .= sprintf(" ORDER BY %s %s LIMIT %s, %s", $orderBy, $orderType, $offset, $rowsPerPage);
        $res = $dbSocket->query($sql);
        $logDebugSQL = "$sql;\n";
        
        $per_page_numrows = $res->numRows();
        
        // the partial query is built starting from user input
        // and for being passed to setupNumbering and setupLinks functions
        $partial_query_string = (!empty($nasipaddress_enc) ? "&nasipaddress=$nasipaddress_enc" : "");
?>
    <table border="0" class="table1">
        <thead>
            <tr style="background-color: white">
<?php
        // page numbers are shown only if needed
        if ($drawNumberLinks) {
            printf('<td style="text-align: left" colspan="%s">go to page: ', $half_colspan + ($colspan % 2));
            setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType, $partial_query_string);
            echo '</td>';
        }
?>
                <td colspan="<?= ($drawNumberLinks) ? $half_colspan : $colspan ?>" style="text-align: right">
                    <input class="button" type="button" value="CSV Export"
                        onclick="location.href='include/management/fileExport.php?reportFormat=csv'">
                </td>
            </tr>
            <tr>
<?php
        // second line of table header
        printTableHead($cols, $orderBy, $orderType, $partial_query_string);
?>
            </tr>
        </thead>
        
        <tbody>
<?php
        while ($row = $res->fetchRow()) {
            $hotspot_enc = htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8');
            
            $hotspot_js = sprintf("javascript:ajaxGeneric('include/management/retHotspotInfo.php','retHotspotGeneralStat','divContainerHotspotInfo',"
                                . "'hotspot=%s');return false;", $hotspot_enc);
            
            $hotspot_tooltip = sprintf('<a class="toolTip" href="mng-hs-edit.php?name=%s">%s</a>&nbsp;'
                                     . '<a class="toolTip" href="acct-hotspot-compare.php">%s</a><br><br>'
                                     . '<div id="divContainerHotspotInfo">Loading...</div><br>',
                                       $hotspot_enc, t('Tooltip','HotspotEdit'), t('all','Compare'));
            
            $username_enc = htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8');

            $username_js = sprintf("javascript:ajaxGeneric('include/management/retUserInfo.php','retBandwidthInfo','divContainerUserInfo',"
                                 . "'username=%s');return false;", $username_enc);
            
            $username_tooltip = sprintf('<a class="toolTip" href="mng-edit.php?username=%s">%s</a><br><br>'
                                      . '<div id="divContainerUserInfo">Loading...</div><br>', $username_enc, t('Tooltip','UserEdit'));
            
?>
            <tr>
                <td><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a class="tablenovisit" href="#" onclick="<?= $hotspot_js ?>" tooltipText='<?= $hotspot_tooltip ?>'>
                        <?= $hotspot_enc ?>
                    </a>
                </td>
                <td>
                    <a class="tablenovisit" href="#" onclick="<?= $username_js ?>" tooltipText='<?= $username_tooltip ?>'>
                        <?= $username_enc ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[4], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[5], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(time2str($row[6]), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(toxbyte($row[7]), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(toxbyte($row[8]), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[9], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row[10], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
<?php
        }
?>
        </tbody>

<?php
        // tfoot
        $links = setupLinks_str($pageNum, $maxPage, $orderBy, $orderType, $partial_query_string);
        printTableFoot($per_page_numrows, $numrows, $colspan, $drawNumberLinks, $links);
?>

    </table>
<?php
    } else {
        $failureMsg = "Nothing to display";
        include_once("include/management/actionMessages.php");
    }
    
    include('library/closedb.php');

?>
        </div>
        
        <div id="footer">
        
<?php
    $log = "visited page: ";
    $logQuery = sprintf("performed query for %s on page: ",
                        ((!empty($nasipaddress)) ? "NAS IP address [$nasipaddress]" : "all NAS IP addresses"));

    include('include/config/logging.php');
    include('page-footer.php');
?>
        </div>
    </div>
</div>

<script>
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>

</body>
</html>
