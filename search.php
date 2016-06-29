<html>
<head>
    <title>Search Results</title>
</head>
<body>
<?php
    include_once("dbconnect.inc.php");
    include_once("porter.php");
    //Get query from index
    $query = $_POST['query'];
    //Check for empty query
    $query = trim($query);
    if ($query == "") {
        print "Don't execute empty queries!";
        die();
    }
    //Get stopwords from database

    $result = mysql_query("SELECT * FROM StopWords");
    if (!$result) {
        print "Error: " . mysql_error();
        die();
    }

    $stopwords = Array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $stopwords[$row['Stopword']] = $row['Stopword'];
    }

    $indicator = 0;
    if (strpos($query, "OR") !== FALSE)
        $indicator = 1;
    else if (strpos($query, "or") !== FALSE)
        $indicator = 1;
    else if (strpos($query, "oR") !== FALSE)
        $indicator = 1;
    else if (strpos($query, "Or") !== FALSE)
        $indicator = 1;
    else
        $indicator = 0;
    if ($indicator == 0) {
        $termList = array();
        $terms = explode(' ', $query);
        foreach ($terms as $term) {
            if (!in_array($term, $stopwords)) {
                $term = PorterStemmer::Stem($term);
                $term = str_replace(array(";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\\", "/", "."), '', $term);
                $term = strtolower($term);
                if (!in_array($term, $termList))
                    $termList[$term] = $term;
            }
        }
        $builtQuery = "SELECT * FROM StemmedTerms WHERE ";
        $i = 0;
        foreach ($termList as $term) {
            if ($i < count($termList) - 1)
                $builtQuery .= "StemmedTerm = \"" . $term . "\" OR ";
            else
                $builtQuery .= "StemmedTerm = \"" . $term . "\" ORDER BY DocumentID";
            $i++;
        }
        $results = mysql_query($builtQuery);
        $docIDs = array();
        $docIDsTerms = array();
        $docIDsTFs = array();
        $i = 0;
        if ($result = mysql_query($builtQuery)) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $docIDs[$i] = $row['DocumentID'];
                $docIDsTerms[$i] = $row['StemmedTerm'];
                $docIDsTFs[$i] = $row['TermFrequency'];
                $i++;
            }
        } else {
            print "Query returned 0 results";
            die();
        }
        $numTerms = count($termList) - 1;
        $termsFound = 0;
        $lastDocID = -1;
        $goodDocs = array();
        $docRankings = array();
        $docRanking = 1;
        $k = 0;
        $j = 0;
        while ($j < count($docIDs)) {
            if ($docIDs[$j] != $lastDocID) {
                if ($numTerms == $termsFound) {
                    if ($lastDocID == -1)
                        $goodDocs[$docIDs[$j]] = $docIDs[$j];
                    else
                        $goodDocs[$lastDocID] = $lastDocID;
                    $docRankings[$lastDocID] = $docRanking;
                    $k++;
                }
                $lastDocID = $docIDs[$j];
                $termsFound = 0;
                $docRanking = $docIDsTFs[$j];
            } else {
                $docRanking *= $docIDsTFs[$j];
                $termsFound++;
            }
            $j++;
        }
        $i = 0;
        arsort($docRankings, SORT_NUMERIC);
        $table = "<table border=\"double\">\n";
        $headerCount = 0;
        foreach ($docRankings as $key => $value) {
            if ($headerCount % 10 == 0)
                $table .= "<tr><td>Title</td><td>Ranking</td></tr>\n";
            if ($key > -1) {
                $result = mysql_query("SELECT DocumentTitle FROM Documents where DocumentID = " . $key);

                $row = mysql_fetch_row($result);
                foreach ($row as $field) {
                    foreach ($termList as $highlight) {
                        $field = str_ireplace($highlight, "<span style=\"background-color:#FF8888\">" . $highlight . "</span>", $field);
                    }
                    $table .= "<td><a target=\"_blank\" href=\"view.php?docid=" . $key . "&terms=" . $term . "\">" . $field . "</a></td>\n";
                }
                $table .= "<td>" . $docRankings[$key] . "</td>\n";
                $table .= "</tr>\n";
                $headerCount++;
            }
        }
        $table .= "</table>\n";
        print "Displaying " . $headerCount . " Documents<br />";
        print $table;
    } else {
        $query1 = stristr($query, " or ", TRUE);

        $query2 = stristr($query, " or ");
        $termList = array();
        $terms = explode(' ', $query1);
        foreach ($terms as $term) {
            if (!in_array($term, $stopwords)) {
                $term = PorterStemmer::Stem($term);
                $term = str_replace(array(";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\\", "/", "."), '', $term);
                $term = strtolower($term);
                if (!in_array($term, $termList))
                    $termList[$term] = $term;
            }
        }
        $builtQuery = "SELECT * FROM StemmedTerms WHERE ";
        $i = 0;
        foreach ($termList as $term) {
            if ($i < count($termList) - 1)
                $builtQuery .= "StemmedTerm = \"" . $term . "\" OR ";
            else
                $builtQuery .= "StemmedTerm = \"" . $term . "\" ORDER BY DocumentID";
            $i++;
        }
        $results = mysql_query($builtQuery);
        $docIDs = array();
        $docIDsTerms = array();
        $docIDsTFs = array();
        $i = 0;
        if ($result = mysql_query($builtQuery)) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $docIDs[$i] = $row['DocumentID'];
                $docIDsTerms[$i] = $row['StemmedTerm'];
                $docIDsTFs[$i] = $row['TermFrequency'];
                $i++;
            }
        } else {
        }
        $numTerms = count($termList) - 1;
        $termsFound = 0;
        $lastDocID = -1;
        $goodDocs = array();
        $docRankings = array();
        $docRanking = 1;
        $k = 0;
        $j = 0;
        while ($j < count($docIDs)) {
            if ($docIDs[$j] != $lastDocID) {
                if ($numTerms == $termsFound) {
                    if ($lastDocID == -1)
                        $goodDocs[$docIDs[$j]] = $docIDs[$j];
                    else
                        $goodDocs[$lastDocID] = $lastDocID;
                    $docRankings[$lastDocID] = $docRanking;
                    $k++;
                }
                $lastDocID = $docIDs[$j];
                $termsFound = 0;
                $docRanking = $docIDsTFs[$j];
            } else {
                $docRanking *= $docIDsTFs[$j];
                $termsFound++;
            }
            $j++;
        }
        $query1Docs = $goodDocs;
        $query1Rankings = $docRankings;

        //THAT WAS THE FIRST QUERY
        $query2 = trim($query2);
        $termList = array();
        $terms = explode(' ', $query2);
        foreach ($terms as $term) {
            if (!in_array($term, $stopwords)) {
                $term = PorterStemmer::Stem($term);
                $term = str_replace(array(";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\\", "/", "."), '', $term);
                $term = strtolower($term);
                if (!in_array($term, $termList))
                    $termList[$term] = $term;
            }
        }
        $builtQuery = "SELECT * FROM StemmedTerms WHERE ";
        $i = 0;
        foreach ($termList as $term) {
            if ($i < count($termList) - 1)
                $builtQuery .= "StemmedTerm = \"" . $term . "\" OR ";
            else
                $builtQuery .= "StemmedTerm = \"" . $term . "\" ORDER BY DocumentID";
            $i++;
        }
        $results = mysql_query($builtQuery);
        $docIDs = array();
        $docIDsTerms = array();
        $docIDsTFs = array();
        $i = 0;
        if ($result = mysql_query($builtQuery)) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $docIDs[$i] = $row['DocumentID'];
                $docIDsTerms[$i] = $row['StemmedTerm'];
                $docIDsTFs[$i] = $row['TermFrequency'];
                $i++;
            }
        } else {

        }
        $numTerms = count($termList) - 1;
        $termsFound = 0;
        $lastDocID = -1;
        $goodDocs = array();
        $docRankings = array();
        $docRanking = 1;
        $k = 0;
        $j = 0;
        while ($j < count($docIDs)) {
            if ($docIDs[$j] != $lastDocID) {
                if ($numTerms == $termsFound) {
                    if ($lastDocID == -1)
                        $goodDocs[$docIDs[$j]] = $docIDs[$j];
                    else
                        $goodDocs[$lastDocID] = $lastDocID;
                    $docRankings[$lastDocID] = $docRanking;
                    $k++;
                }
                $lastDocID = $docIDs[$j];
                $termsFound = 0;
                $docRanking = $docIDsTFs[$j];
            } else {
                $docRanking *= $docIDsTFs[$j];
                $termsFound++;
            }
            $j++;
        }
        $query2Docs = $goodDocs;
        $query2Rankings = $docRankings;

        foreach ($query2Docs as $key => $val) {
            if (isset($query1Docs[$key]))
                $query1Rankings[$key] += $query2Rankings[$key];
            else {
                $query1Docs[$key] = $query2Docs[$key];
                $query1Rankings[$key] = $query2Rankings[$key];
            }
        }
        $term = str_ireplace(" or ", ' ', $query);
        $term = trim($term);
        $i = 0;
        arsort($query1Rankings, SORT_NUMERIC);
        $table = "<table border=\"double\">\n";
        $headerCount = 0;
        foreach ($query1Rankings as $key => $value) {
            if ($key != -1) {
                if ($headerCount % 10 == 0)
                    $table .= "<tr><td>Title</td><td>Ranking</td></tr>\n";
                $result = mysql_query("SELECT DocumentTitle FROM Documents where DocumentID = " . $key);
                $row = mysql_fetch_row($result);
                foreach ($row as $field) {
                    foreach ($termList as $highlight) {
                        $field = str_ireplace($highlight, "<span style=\"background-color:#FF8888\">" . $highlight . "</span>", $field);
                    }
                    $table .= "<td><a target=\"_blank\" href=\"view.php?docid=" . $key . "&terms=" . $term . "\">" . $field . "</a></td>\n";
                }
                $table .= "<td>" . $query1Rankings[$key] . "</td>\n";
                $table .= "</tr>\n";
                $headerCount++;
            }
        }
        print "Displaying " . $headerCount . " Documents<br />";
        print $table;
    }
?>
</body>
</html>
