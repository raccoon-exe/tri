<?php
    require "db.php";
    include('report.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <link rel="SHORTCUT ICON" href="images/favicon.png" />
    <title id="head_title">Toxic Release Inventory - Home</title>
    <link rel="stylesheet" type="text/css" href="stylesheets/core.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/pagination.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/form.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/contents.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/pages.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/containers.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/overrides.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/style.css" />
    <link rel="stylesheet" type="text/css" href="stylesheets/jquery.tablesorter.pager.css" />
    <style type="text/css">
        div.inline { float:left; }
        .clearBoth { clear:both; }
    </style>
    <script type="text/javascript" src="javascript/jquery-latest.js"></script>
    <script type="text/javascript" src="javascript/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="javascript/jquery.metadata.js"></script>
    <script type="text/javascript" src="javascript/jquery.tablesorter.pager.js"></script>
    <script type="text/javascript" id="js">
        $(document).ready(function() {
            $("#results_table").tablesorter({
                widgets:['zebra'],
            }).tablesorterPager({container: $("#pager")});
        });
    </script>
</head>
<body>
<div class="page">
    <div class="left_column">
    </div>
    <div class="headerb">
        <div class="block portlet navigation inline">
            <div class="head">
                <h4>Create Report</h4>
            </div>
            <div id="reportControls" class="body">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <table border="0">
                    <tr>
                        <td>
                            <label for="repCity">City</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select class="reportDrop" id="repCity" name="L.City">
                                <option value=""></option>
<?php
            $cities = mysql_query("SELECT DISTINCT City FROM Location ORDER BY City");
            while($row = mysql_fetch_array($cities))
            {
                echo "\t\t\t\t<option value=\"'" . $row['City'] . "'\">" . $row['City'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table border="0">
                    <tr>
                        <td>
                            <label for="repCounty">County</label>
                        </td>
                        <td>
                            <label for="repZip">Zip</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select class="reportDrop" id="repCounty" name="L.County">
                                <option value=""></option>
<?php
            $counties = mysql_query("SELECT DISTINCT County FROM Location ORDER BY County");
            while($row = mysql_fetch_array($counties))
            {
                echo "\t\t\t\t<option value=\"'" . $row['County'] . "'\" " . "$selected>" . $row['County'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                        <td>
                            <select class="reportDrop" id="repZip" name="L.Zip">
                                <option value=""></option>
<?php
            $zips = mysql_query("SELECT DISTINCT Zip FROM Location ORDER BY Zip");
            while($row = mysql_fetch_array($zips))
            {
                echo "\t\t\t\t<option value=\"'" . $row['Zip'] . "'\">" . $row['Zip'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table border="0">
                    <tr>
                        <td>
                            <label for="repYearFrom">Year From</label>
                        </td>
                        <td>
                            <label for="repYearTo">Year To</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select class="reportDrop" id="repYearFrom" name="T.Year_From">
                                <option value=""></option>
<?php
            $years = mysql_query("SELECT DISTINCT Time_Id, Year FROM Time ORDER BY Year");
            while($row = mysql_fetch_array($years))
            {
                echo "\t\t\t\t<option value=\"" . $row['Year'] . "\">" . $row['Year'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                        <td>
                            <select class="reportDrop" id="repYearTo" name="T.Year_To">
                                <option value=""></option>
<?php
            $years = mysql_query("SELECT DISTINCT Time_Id, Year FROM Time ORDER BY Year");
            while($row = mysql_fetch_array($years))
            {
                echo "\t\t\t\t<option value=\"" . $row['Year'] . "\">" . $row['Year'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table border="0">
                    <tr>
                        <td>
                            <label for="repChem">Chemical</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <select class="reportDrop" id="repChemName" style="width: 210px" name="C.Chemical_Id">
                                <option value=""></option>
                                <option value="-1">(All Carcinogens)</option>
<?php
            $chems = mysql_query("SELECT DISTINCT Chemical_Id, Chemical_Name FROM Chemical ORDER BY Chemical_Name");
            while($row = mysql_fetch_array($chems))
            {
                echo "\t\t\t\t<option value=\"" . $row['Chemical_Id'] . "\">" . $row['Chemical_Name'] . "</option>\n";
            }
?>
                            </select>
                        </td>
                    </tr>
                </table>
                <br />
                <input id="repSubmitButton" type="submit" value="Generate Report" />
            </form>
            </div>
        </div>
        <div class="block portlet navigation inline">
            <div class="head">
                <h4>Quick Report</h4>
            </div>
            <div class="body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
                    <input type="hidden" class="qholder" name="q" value=""/>
                <ul class="navigation_links">
                    <li><input type="submit" onclick="$('.qholder').attr('value', '0');" value="Worst Facilities (By Frequency)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '1');" value="Worst Facilities (By Volume)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '2');" value="Worst Carcinogens (By Frequency)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '3');" value="Worst Carcinogens (By Volume)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '4');" value="Worst Chemicals (By Frequency)" /></li>
                </ul>
                </form>
            </div>
        </div>
        <div class="block portlet navigation inline">
            <div class="head">
                <h4>Quick Report</h4>
            </div>
            <div class="body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
                    <input type="hidden" class="qholder" name="q" value=""/>
                <ul class="navigation_links">
                    <li><input type="submit" onclick="$('.qholder').attr('value', '5');" value="Worst Chemicals (By Volume)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '6');" value="Worst ZIPs (By Frequency)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '7');" value="Worst ZIPs (By Volume)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '8');" value="Best ZIPs (By Frequency)" /></li>
                    <li><input type="submit" onclick="$('.qholder').attr('value', '9');" value="Best ZIPs (By Volume)" /></li>
                </ul>
                </form>
            </div>
        </div>
    </div>
    <div id="main_content" class="mainb clearboth">
<?php
    if ($_POST)
    {
        echo genPostTable($_POST);
    }
    else if ($_GET)
    {
        $query = $cannedQuery[$_GET["q"]];

        echo '
        <div class="portlet block">
            <div class="head">
                <h4>Query</h4>
            </div>
            <div class="body">
                <p style="font-family:monospace">' . $query . '</p>
            </div>
        </div>
        ';

        echo genGetTable($query);
    }
    else
    {
        echo '<div class="portlet block">
            <div class="head">
                <h4>Query</h4>
            </div>
            <div class="body">
                <p>No query to display.  Use the controls to run a report first.</p>
            </div>
        </div>';
    }
?>
    </div>
</div>
</body>
</html>
