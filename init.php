<?php
    include_once("dbconnect.inc.php");
    include_once("porter.php");
    ini_set('auto_detect_line_endings', true);

    if (($trecFile = fopen("ohsumed87.txt", "r")) == NULL)
        die() . "Unable to open file ohsumed87.txt";

    if (($initFile = fopen("init.sql", "w")) == NULL)
        die() . "Unable to write to init.sql, check permissions";

    /* Take Care of Stopwords First */

    if (($stopWordsFile = fopen("stopwords.txt", "r")) == NULL)
        die() . "Unable to open stopwords.txt";
    fprintf($initFile, "DROP TABLE IF EXISTS StopWords;\n");
    fprintf($initFile, "CREATE TABLE StopWords (Stopword char(20));\n");
    $stopWords = array();
    $stopwordIndex = 0;
    while (!feof($stopWordsFile)) {
        $line = fgets($stopWordsFile, "1024");
        $line = str_replace(array("\n", "\r"), "", $line);
        if ($line != "")
            fprintf($initFile, "INSERT INTO StopWords (Stopword) VALUES (\"%s\");\n", $line);
        $stopWords[$stopwordIndex] = $line;
        $stopwordIndex++;
    }
    fclose($stopWordsFile);

    /* Documents Are Next */
    fprintf($initFile, "DROP TABLE IF EXISTS StemmedTerms;\n");
    fprintf($initFile, "CREATE TABLE StemmedTerms (DocumentID int, StemmedTerm char(20), TermFrequency int, primary key (DocumentID, StemmedTerm, TermFrequency));\n");
    fprintf($initFile, "DROP TABLE IF EXISTS Documents;\n");
    fprintf($initFile, "CREATE TABLE Documents (DocumentID int, MedlineID int, DocumentTitle varchar(1024), PublicationType char(100), DocumentAbstract BLOB, DocumentAuthor char(255), DocumentSource char(255), primary key (DocumentID));\n");
    $documentID = -1;
    $query = "";
    $medlineID = "";
    $meshTerms = "";
    $title = "";
    $publicationType = "";
    $abstract = "";
    $authors = "";
    $source = "";
    while (!feof($trecFile)) {
        $line = fgets($trecFile, "1024");
        $line = str_replace("\n", '', $line);
    //		print "$line = ".$line;
        if (strpos($line, "<I>") !== FALSE) {
            //New Document
            $documentID++;
        } else if (strpos($line, "<U>") !== FALSE) {
            //Medline Identifier
            $line = str_replace("<U>", '', $line);
            $medlineID = $line;
        } else if (strpos($line, "<S>") !== FALSE) {
            $line = str_replace("<S>", '', $line);
            $source = addslashes($line);
        } else if (strpos($line, "<T>") !== FALSE) {
            //Document Title
            $line = str_replace("<T>", '', $line);
            $title = addslashes($line);
        } else if (strpos($line, "<P>") !== FALSE) {
            //Publication Type
            $line = str_replace("<P>", '', $line);
            $publicationType = addslashes($line);
        } else if (strpos($line, "<W>") !== FALSE) {
            //Abstract
            $line = str_replace("<W>", '', $line);
            $abstract = $line;
            $line = fgets($trecFile, "1024");
            $abstract .= $line;
            $abstract = str_replace(array("\\n", "\\r", "</W>"), '', $abstract);
            $abstract = addSlashes($abstract);
        } else if (strpos($line, "<A>") !== FALSE) {
            //Authors
            $line = str_replace("<A>", '', $line);
            $authors = addslashes($line);
        } else if (strpos($line, '</I>') !== FALSE) {
            //End of Document
            $query = "INSERT INTO Documents (DocumentID, MedlineID, DocumentTitle, PublicationType, DocumentAbstract, DocumentAuthor, DocumentSource) VALUES (";
            $query .= $documentID . ", " . $medlineID . ", \"" . $title . "\", \"" . $publicationType . "\", \"" . $abstract . "\", \"" . $authors . "\", \"" . $source . "\");";
            fprintf($initFile, "%s\n", $query);
            $stems = Array();
            /* Get the stopwords from Abstract */
            $abstract = str_replace("-", ' ', $abstract);
            $abstract = str_replace(array("-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", ".",), ' ', $abstract);
            $terms = explode(' ', $abstract);
            foreach ($terms as $term) {
                if (!in_array($term, $stopWords)) {
                    $term = PorterStemmer::Stem($term);
                    $term = str_replace(array("< w>", "-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", "."), ' ', $term);
                    $term = strtolower($term);
                    $term = trim($term);
                    if (!in_array($term, $stopWords)) {
                        if (!isset($stems[$term]))
                            $stems[$term] = 1;
                        else
                            $stems[$term]++;
                    }
                }
            }

            /* Get the stopwords from Title */
            $title = str_replace("-", ' ', $title);
            $titleTerms = explode(' ', $title);
            foreach ($titleTerms as $term) {
                if (!in_array($term, $stopWords)) {
                    $term = str_replace(array("-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", "."), ' ', $term);
                    $term = PorterStemmer::Stem($term);
                    $term = str_replace(array("-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", "."), ' ', $term);
                    $term = strtolower($term);
                    $term = str_replace(' ', '', $term);
                    if (!in_array($term, $stopWords)) {
                        if (!isset($stems[$term]))
                            $stems[$term] = 1;
                        else
                            $stems[$term]++;
                    }
                }
            }
            $authors = str_replace("-", ' ', $authors);
            $authorTerms = explode(' ', $authors);
            foreach ($authorTerms as $term) {
                if (!in_array($term, $stopWords)) {
                    $term = str_replace(array("-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", "."), ' ', $term);
                    $term = PorterStemmer::Stem($term);
                    $term = str_replace(array("-", ";", ":", ",", "(", ")", "{", "}", "[", "]", "'", "\"", "\\", "/", "."), ' ', $term);
                    $term = strtolower($term);
                    $term = str_replace(' ', '', $term);
                    if (!in_array($term, $stopWords)) {
                        if (!isset($stems[$term]))
                            $stems[$term] = 1;
                        else
                            $stems[$term]++;
                    }
                }
            }
            foreach ($stems as $key => $value)
                fprintf($initFile, "INSERT INTO StemmedTerms (DocumentID, StemmedTerm, TermFrequency) VALUES (%d, \"%s\", %d);\n", $documentID, $key, $value);
            $query = "";
            $medlineID = "";
            $meshTerms = "";
            $title = "";
            $publicationType = "";
            $abstract = "";
            $authors = "";
            $source = "";
        }
    }
    fclose($trecFile);
?>
