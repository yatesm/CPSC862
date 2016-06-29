# Document Rank and Search with Porter Stemming, Developed for CPSC 862: Advanced DBMS

## Description

This repository contains the final milestone for CPSC 862: Advanced DBMS.  CPSC 862 was a semester long course taught by Juan Gilbert on the design and implementation of database management systems and user interfaces.

The milestone itself is an implementation of document rank and search using porter stemming and stop words, with a sample database taken from oshumed87.

## Installation

1. Clone the code to your computer.
2. Install your favorite MySQL / WebServer combo with PHP and PHP-Mysql support.
3. Run build.sh

## Usage
1. Navigate to index.php
2. Enter a series of search terms and hit submit.
3. See what the results are.

## In-Depth Description

This program begins with a database of "stop words" or words that should be ignored when searching documents via text.  Examples of stop words include articles and other helping words in English (e.g., a, an, the, or, etc) as well as other words that provide little contextual meaning (e.g., almost, every, etc).  The purpose of stop words is to remove any words that don't provide any semantic information to both the text being searched (haystack) as well as the text strings that are used in searching (needle).  

The next part of the program is the sample input file for the database to be used in the application.  The sample input file is the canonical ohsumed87 input file, containing several hundred references to scholarly articles in an annotated bibliography.

To set up the program, the first step is to generate the database containing both the articles that are searched as well as stop-words utilized by the search engine.  To do this, we create a database and insert into a table all of the stop words from the stop words input file.  The next step is to create a database table storing the information contained within the ohsumed87 input file.  Once these two tables are created, we execute Porter's stemming algorithm on the ohsumed87 file, associating stemmed keywords from each entry in the bibliography with that entry in the database.  Stemming is a method of reducing a word to its core, component part (e.g., 'fisher', 'fishing' and 'fishy' all feature the stem 'fish').  Stemming allows for more streamlined organization, search and retrieval of data.

The ohsumed87 file is processed using stemming on the description of each article, and a table is created associating each stemmed term with the article it came from.  The init.php file generates a SQL file which creates the necessary tables and inserts all of the above information into the tables.

Finally, the project has three php files, index, search, view which provide the functionality for allowing a user to search our toy database.  Users are provided a text input box which supports 'and' and 'or' conjunctives on search terms with parenthetical priority (i.e., (cancer or pneumonia) AND (survival) ).  Upon entering a search string, the string is split by the and/or conjunctives.  Any stopwords are removed from the separate clauses of the search string.  The search strings are then run through Porter's stemming algorithm.  The database is then queried for the stemmed search string.  A list of articles is returned via view.php, ranked by the count of stemmed search terms found within each article.  Any searched terms highlighted in the text of the resulting articles.

## Notes on Implementation

This project is a pedagogical implementation of document rank and search using stemmed keyword search and stopwords. It was developed in 2012.  Around this time, PHP began deprecating the mysql_ family of functions.  As a result, this code contains deprecated code which probably should not be used in a production context.  If I were to modernize the code, I would probably use PDO queries, which provide a more streamlined, object-oriented and secure method of interacting with databases.

## Contributing

Please do not contribute to this project.  This compiler was a pedagogical proof of concept to illustrate the language theoretic constructs delivered in CPSC 862: Advanced DBMS.  This compiler and this project should not be used for anything besides an exhibition of the work I did that semester.

## Credits

Credit goes to me, myself, Yates Monteith, for this project.

## License

GNU GPL v2.0.  See License
