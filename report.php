<?php
$cannedQuery = array(
0 => "SELECT Facility_Name, Street_address, City, Zip, COUNT(*) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Facility_Name ORDER BY COUNT(*) DESC;",
1 => "SELECT Facility_Name, Street_address, City, Zip, SUM(Release_Amount), Release_Metric FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Facility_Name ORDER BY SUM(Release_Amount) DESC;",
2 => "SELECT Chemical_Name, Cas_Number, Carcinogen, COUNT(*) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) WHERE C.Carcinogen=1 GROUP BY Chemical_Name ORDER BY COUNT(*) DESC;",
3 => "SELECT Chemical_Name, Cas_Number, Carcinogen, SUM(Release_Amount), Release_Metric FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) WHERE C.Carcinogen=1 GROUP BY Chemical_Name ORDER BY SUM(Release_Amount) DESC;",
4 => "SELECT Chemical_Name, Cas_Number, Carcinogen, COUNT(*) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Chemical_Name ORDER BY COUNT(*) DESC;",
5 => "SELECT Chemical_Name, Cas_Number, Carcinogen, SUM(Release_Amount) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Chemical_Name ORDER BY SUM(Release_Amount) DESC;",
6 => "SELECT County, Zip, COUNT(*) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Zip ORDER BY COUNT(*) DESC;",
7 => "SELECT County, Zip, SUM(Release_Amount), Release_Metric FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Zip ORDER BY SUM(Release_Amount) DESC;",
8 => "SELECT County, Zip, COUNT(*) FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Zip ORDER BY COUNT(*) ASC;",
9 => "SELECT County, Zip, SUM(Release_Amount), Release_Metric FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id AND R.Time_Id = T.Time_Id AND R.Location_Id = L.Location_Id) GROUP BY Zip ORDER BY SUM(Release_Amount) ASC;"
);

function genPostTable($post_vars)
{
    $query = "SELECT Document_Number, Year, Street_Address, City, Zip, Cas_Number, Chemical_Name, Carcinogen, Release_Amount, Release_Metric FROM Release_Facts R LEFT JOIN (Chemical C, Location L, Time T) ON (R.Chemical_Id = C.Chemical_Id and R.Time_Id = T.Time_Id and R.Location_Id = L.Location_Id)";
    $conj = " WHERE ";
    $conjCount = 0;
    $kv = array();
    $output = "";

    # Tokenize post variables and reformat as needed.
    foreach ($post_vars as $key => $value)
    {
        if ($value != "")
        {
            $key[1] = '.';

            if ($key == "T.Year_From")
            {
                $key = "T.Year";
                $query = $query . $conj . "$key>=$value";
            }
            else if ($key == "T.Year_To")
            {
                $key = "T.Year";
                $query = $query . $conj . "$key<=$value";
            }
            else if ($key[0] == 'C' && $value == -1)
            {
                $query = $query . $conj . "C.Carcinogen=1";
            }
            else
            {
                $query = $query . $conj . "$key=$value";
            }

            if ($conjCount == 0)
            {
                $conj = " AND ";
            }

            $conjCount++;
        }
    }

    $query = $query . ';';
    $query = stripslashes($query);

    $result = mysql_query($query) or die (mysql_error());

    $output = '
    <div class="portlet block">
        <div class="head">
            <h4>Query</h4>
        </div>
        <div class="body">
            <p style="font-family:monospace">' . $query . '</p>
        </div>
    </div>
    <table class="tablesorter" id="results_table">
        <thead>
        <tr>
            <th>Doc ID</th>
            <th>Year</th>
            <th>Address</th>
            <th>City</th>
            <th>ZIP</th>
            <th>CAS</th>
            <th>Chemical</th>
            <th>Carcinogen</th>
            <th>Release Amount</th>
            <th>Units</th>
        </tr>
        </thead>
        <tbody>
    ';

    while($row = mysql_fetch_array($result))
    {
        $output = $output . "<tr>\n";
        $output = $output . "<td>" . $row['Document_Number'] . "</td>\n";
        $output = $output . "<td>" . $row['Year'] . "</td>\n";
        $output = $output . "<td>" . $row['Street_Address'] . "</td>\n";
        $output = $output . "<td>" . $row['City'] . "</td>\n";
        $output = $output . "<td>" . $row['Zip'] . "</td>\n";
        $output = $output . "<td>" . $row['Cas_Number'] . "</td>\n";
        $output = $output . "<td>" . $row['Chemical_Name'] . "</td>\n";

        if ($row['Carcinogen'] == 1)
        {
            $output = $output . "<td>Yes</td>\n";
        }
        else
        {
            $output = $output . "<td></td>\n";
        }

        $output = $output . "<td>" . number_format($row['Release_Amount'], 2, '.', '') . "</td>\n";
        $output = $output . "<td>" . $row['Release_Metric'] . "</td>\n";
        $output = $output . "</tr>\n";
    }

    $output = $output . '</tbody>
    </table>
    <div id="pager" class="pager">
        <form>
            <img src="images/icons/first.png" class="first"/>
            <img src="images/icons/prev.png" class="prev"/>
            <input type="text" class="pagedisplay"/>
            <img src="images/icons/next.png" class="next"/>
            <img src="images/icons/last.png" class="last"/>
            <select class="pagesize">
                <option selected="selected"  value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option  value="40">40</option>
            </select>
        </form>
    </div>';

    return $output;
}

function genGetTable($q)
{
    $result = mysql_query($q) or die(mysql_error());
    $iCarcinogen = -1;
    $iAmount = -1;

    $output = '<table class="tablesorter" id="results_table">
        <thead>
        <tr>';
        for ($i = 0; $i < mysql_num_fields($result); $i++)
        {
            $output = $output . "<th>" . mysql_field_name($result, $i) . "</th>\n";

            if (mysql_field_name($result, $i) == "Carcinogen")
            {
                $iCarcinogen = $i;
            }
            else if ((mysql_field_name($result, $i) == "Release_Amount") || (mysql_field_name($result, $i) == "SUM(Release_Amount)"))
            {
                $iAmount = $i;
            }
        }
    $output .= '    </tr>
        </thead>
        <tbody>
    ';
    while($row = mysql_fetch_array($result, MYSQL_BOTH))
    {
        $output .= "<tr>\n";

        for ($i = 0; $i < mysql_num_fields($result); $i++)
        {
            if ($i == $iCarcinogen)
            {
                if ($row[$i] == 1)
                {
                    $output = $output . "<td>Yes</td>\n";
                }
                else
                {
                    $output = $output . "<td></td>\n";
                }
            }
            else if ($i == $iAmount)
            {
                $output = $output . "<td>" . number_format($row[$i], 2, '.', '') . "</td>\n";
            }
            else
            {
                $output = $output . "<td>" . $row[$i] . "</td>\n";
            }
        };

        $output = $output . "</tr>\n";
    }
    $output = $output . '</tbody>
    </table>
    <div id="pager" class="pager">
        <form>
            <img src="images/icons/first.png" class="first"/>
            <img src="images/icons/prev.png" class="prev"/>
            <input type="text" class="pagedisplay"/>
            <img src="images/icons/next.png" class="next"/>
            <img src="images/icons/last.png" class="last"/>
            <select class="pagesize">
                <option selected="selected"  value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option  value="40">40</option>
            </select>
        </form>
    </div>';

    return $output;
}
?>
