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
    .nameda{position: sticky; left: 0; background-color: #EEEEEE;}
    </style>
</head>

<?php
function readTSV($filename) {
    $data = ['data'=>[]];

    if (!file_exists($filename) || !is_readable($filename)) {
        echo "Datei nicht lesbar.\n";
        return false;
    }

    $handle = fopen($filename, 'r');
    if (!$handle) {
        echo "Konnte Datei nicht öffnen.\n";
        return false;
    }
    
    $rowcount==0;
    while (($line = fgets($handle)) !== false) {

      $line = rtrim($line, "\r\n");
      $fields = explode("\t", $line);

      if ($rowcount==0){
        //echo $line;
        $headers = $fields;
        if ($headers === false) {
          echo "Keine Headerzeile gefunden.\n";
        return false;
    }
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

        else if ($rowcount==1){
        $data['descs'] = $row;
      }
      else if ($rowcount==2){
        $data['types'] = $row;
      }
      else{
        $data['data'][] = $row;
      }
        
      }
      $rowcount++;
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
      <th class="header" v-for="(value, key) in transformed[0]" :key="key" @click="sortBy(key)"
        style="cursor: pointer;"
>
  {{ key }}
  <span v-if="sortField === key">
    {{ sortAsc ? '▲' : '▼' }}
  </span></th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, index) in transformed" :key="index">
      <td :class="key.slice(0, 8).replace(/[^a-zA-Z0-9]/g, '').toLowerCase()" v-for="(value, key) in row" :key="key">{{ value }}</td>
    </tr>
  </tbody>
</table>

  </div>

  <script type="module">
    import { createApp } from './vue.js'

    createApp({
      data() {
        return {
          descs: {},
          types: {},
          input: [],
          transformed: [],
          filtered: [],
        }
      },
      mounted() {
        // Embedded PHP JSON into JavaScript
        this.input = <?= $data ?>;
        
       
        this.types=this.input.types;
        this.descs=this.input.descs;
        this.input=this.input.data;
        this.transform();
        console.log(this.transformed);
        //make arrays of: Link (paper), Native language(s), Stimulus language, Source, Comprehension questions, 
        //divide fields with multiple values: Age range, Total # words/chars, Age mean±SD
        //both: Age range
      },
      methods:
      {
        transform(){
          for (const [key, value] of Object.entries(this.input)) {
            console.log(this.types[key]);
            this.transformed[key] = value;
          }
          console.log(this.transformed);

        // Do something with key and value
        },
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