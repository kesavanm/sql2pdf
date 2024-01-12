<!DOCTYPE html>
<html>
    <head>
        <title>SQL2PDF</title>

    </head>

    <style>
            .highlight { color: blue; font-weight: bold; }
    </style>

    <script>
        function highlightKeywords() {
            var textarea = document.getElementById("sql-textarea");
            var keywords = ["SELECT","CONCAT", "LIMIT", "ON", "ALTER", "LEFT","RIGHT", "FROM", "WHERE", "JOIN", "ORDER BY", "GROUP BY", "INSERT", "UPDATE", "DELETE"];
            var highlightedText = textarea.value;

            for (var i = 0; i < keywords.length; i++) {
                var regex = new RegExp("\\b" + keywords[i] + "\\b", "ig");
                highlightedText = highlightedText.replace(regex, '<span class="highlight">$&</span>');
            }
            highlightedText = beautifySQL(highlightedText);

            const targetDiv = document.getElementById("highlighted-output");
            targetDiv.innerHTML = highlightedText;
            targetDiv.style.fontFamily = "Courier, monospace";
            targetDiv.style.width = "800px";
        }

        function beautifySQL(sql) {
            var beautifiedSql = "";
            var indentLevel = 0;
            var singleLineCommentRegex = /--.*$/gm;
            var multiLineCommentRegex = /\/\*(\*(?!\/)|[^*])*\*\//g;
            var sqlStatementsRegex = /\b(SELECT|FROM|WHERE|JOIN|ON|INNER JOIN|LEFT JOIN|RIGHT JOIN|OUTER JOIN|ORDER BY|GROUP BY|HAVING|INSERT INTO|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE|INDEX|PRIMARY KEY|FOREIGN KEY|UNIQUE|LIMIT)\b/g;
            
            // Remove single line comments
            sql = sql.replace(singleLineCommentRegex, "");
            
            // Remove multi-line comments
            sql = sql.replace(multiLineCommentRegex, "");
            
            // Add line breaks before and after SQL statements
            sql = sql.replace(sqlStatementsRegex, "\n$&\n");
            
            // Split the SQL code into lines
            var lines = sql.split("\n");
            
            // Indent each line based on the indent level
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
            
                if (line === "") {
                continue;
                }
            
                if (line.startsWith(")")) {
                indentLevel--;
                }
            
                beautifiedSql += "  ".repeat(indentLevel) + line + "\n";
            
                if (line.endsWith("(")) {
                indentLevel++;
                }
            }
            
            return beautifiedSql.trim();
        }

    </script>

<?php
    $sql ="";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['sql-textarea'])) {
            $sql = $_POST['sql-textarea'];
        }
    }
?>

    <body onload="highlightKeywords()">
        <form action="" method="post">
            <label for="sql-textarea">Your SQL:</label> <br>
            <textarea id="sql-textarea" name="sql-textarea" rows="10" cols="50"  oninput="highlightKeywords()">
                <?=trim($sql)?>
            </textarea>
            <br>

            <p id="highlighted-output" style="border-width:3px; border-style:solid; border-color:#FF0000; padding: 1em;">
            <?=trim($sql)?>
            </p>
            <button type="submit">Check SQL</button>
        </form>

        <hr>
        <p>

<?
    if (!empty($sql)) {

        include_once('DataRenderer.php');
        $db = new PDO('mysql:host=localhost;dbname=finalresult', 'kesavan', 'password');

        // Create an instance of the DataRenderer class
        $renderer = new DataRenderer($db);

        try{
            $data = $renderer->fetchData($sql);
        }catch(Exception $e){
            throw new Exception("Error fetching data: " . $e->getMessage());
        }

        if( isset($_POST['genCSV'])) {
            ob_clean(); ob_start();
            $renderer->exportCSV($data, 'data_export.csv');
            exit;
        }elseif(isset($_POST['genPDF'])){
            ob_clean(); ob_start();
            $renderer->generatePDF($data, 'data_report.pdf');
            exit;
        }
        else {
            $renderer->displayData($data);
        }

        echo '<table> <tr>';
        echo '<form action="" method="post"><br>';
        echo '  <input type="hidden" name="sql-textarea" id="sql-textarea" value="'.$sql.'">';

        echo '  <td>';
        echo '  <button id="genCSV" name="genCSV" type="submit" >Generate CSV</button><br>';
        echo '  </td>';

        echo '  <td>';
        echo '  </td>';
        
        echo '  <td>';
        echo '  <button id="genPDF" name="genPDF" type="submit" >Generate PDF</button><br>';  
        echo '  </td>';   

        echo '</form><br>';
        echo '</tr> </table>';
        // Generate a PDF
        // $renderer->generatePDF($data, 'data_report.pdf');

        // Export data as CSV
        // $renderer->exportCSV($data, 'data_export.csv');
    }
?>    

    </body>

</html>