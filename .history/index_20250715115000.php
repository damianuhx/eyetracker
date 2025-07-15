<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Vue Test</title>
  <style>
    .header{
        position: sticky;
        top: 0;
        background-color: #EEEEEE;
    }
    </style>
</head>

<?php
function readTSV($filename) {
    $data = [];

    if (!file_exists($filename) || !is_readable($filename)) {
        echo "Datei nicht lesbar.\n";
        return false;
    }

    $handle = fopen($filename, 'r');
    if (!$handle) {
        echo "Konnte Datei nicht öffnen.\n";
        return false;
    }

    /*
    // Header einlesen
    $headerLine = fgets($handle);
    if ($headerLine === false) {
        echo "Keine Headerzeile gefunden.\n";
        return false;
    }

    $headers = explode("\t", rtrim($headerLine, "\r\n"));
    $headerCount = count($headers);
*/
    $rowcount=0;
    while (($line = fgets($handle)) !== false) {
      $line = rtrim($line, "\r\n");
      $fields = explode("\t", $line);

      if ($rowcount<3)
      else if ($rowcount++==3){
        $headers = $fields;
        $headerCount = count($headers);
      }
      else{
        // Auffüllen oder Abschneiden
        $fields = array_pad($fields, $headerCount, null);
        if (count($fields) > $headerCount) {
            $fields = array_slice($fields, 0, $headerCount);
        }

        $row = @array_combine($headers, $fields);

        if ($row === false) {
            // Debugging-Ausgabe bei Fehler
            echo "Fehlerhafte Zeile:\n";
            print_r($fields);
            continue;
        }

        $data[] = $row;
      }
        
    }

    fclose($handle);

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    //echo $json;
    return $json;
}

// Use the function with your file
$data = readTSV('input.tsv');

//echo($tsvData);
?>

<body>

  <div id="app" >
    <table>
  <thead class="header">
    <tr class="header">
      <th class="header" v-for="(value, key) in input[0]" :key="key" @click="sortBy(key)"
  style="cursor: pointer;"
>
  {{ key }}
  <span v-if="sortField === key">
    {{ sortAsc ? '▲' : '▼' }}
  </span></th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, index) in input" :key="index">
      <td v-for="(value, key) in row" :key="key">{{ value }}</td>
    </tr>
  </tbody>
</table>

  </div>

  <script type="module">
    import { createApp } from './vue.js'

    createApp({
      data() {
        return {
          input: []
        }
      },
      mounted() {
        // Embedded PHP JSON into JavaScript
        this.input = <?= $data ?>;
        console.log(this.input);
      },
      methods:
      {
        //filter fields
        //sort by fields
        //divide fields
        //make fields a certain datatype:
            //string
            //select
            //select multiple
            //copy (bibtex)
            //link 
            //number
            //array

sortBy(field) {
    if (this.sortField === field) {
      this.sortAsc = !this.sortAsc;
    } else {
      this.sortField = field;
      this.sortAsc = true;
    }

    this.input.sort((a, b) => {
      const valA = a[field] || '';
      const valB = b[field] || '';

      // Numeric sort if both values are numbers
      const numA = parseFloat(valA);
      const numB = parseFloat(valB);
      const bothNumeric = !isNaN(numA) && !isNaN(numB);

      if (bothNumeric) {
        return this.sortAsc ? numA - numB : numB - numA;
      } else {
        return this.sortAsc
          ? String(valA).localeCompare(String(valB))
          : String(valB).localeCompare(String(valA));
      }
    });
  }

      }
    }).mount('#app')
    
  </script>

</body>
</html>