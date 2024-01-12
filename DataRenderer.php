<?php

require_once 'vendor/autoload.php';

class DataRenderer {

    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Fetches data from the database using the given SQL query.
     *
     * @param string $sql The SQL query to execute
     * @return array An array of fetched data rows
     */
    public function fetchData($sql) {
        try {
            $statement = $this->db->query($sql);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching data: " . $e->getMessage());
        }
    }

    /**
     * Generates a PDF using the provided dataset.
     *
     * @param array $data The dataset to include in the PDF
     * @param string $filename The name of the PDF file to generate
     */

     public function generatePDF($data, $filename) {
        // require_once('tcpdf/tcpdf.php');
    
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Data Report');
        $pdf->AddPage();
    
        // Create a table
        $table = '<table border="1" cellpadding="5">';
    
        // Add headers
        $table .= '<tr>';
        foreach ($data[0] as $key => $value) {
            $table .= '<th>' . htmlspecialchars($key) . '</th>';
        }
        $table .= '</tr>';
    
        // Add data rows
        foreach ($data as $row) {
            $table .= '<tr>';
            foreach ($row as $value) {
                $table .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $table .= '</tr>';
        }
    
        $table .= '</table>';
    
        // Write the table to the PDF
        $pdf->writeHTML($table, true, false, false, false, '');

        // $pdf->Output($filename, 'F');
    
        // Send headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    
        // Output the PDF directly to the browser
        $pdf->Output( $filename,'D');
    }
    
    /**
     * Exports the data as a CSV file.
     *
     * @param array $data The dataset to export
     * @param string $filename The name of the CSV file to create
     */
    public function exportCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($data[0])); // Write headers
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }

    //  generate a function to display the dataset in tabulate format
    public function displayData($data) {
        $table = '<table border="1" cellpadding="5">';
        $table .= '<tr>';
        foreach ($data[0] as $key => $value) {
            $table .= '<th>' . htmlspecialchars($key) . '</th>';
        }
        $table .= '</tr>';
        foreach ($data as $row) {
            $table .= '<tr>';
            foreach ($row as $value) {
                $table .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';
        echo $table;
    }

}
