<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Vue Test</title>
  <style>
    table {
  table-layout: auto;
  border-collapse: collapse;
}
    .header{
        position: sticky;
        top: 0;
        background-color: #EEEEEE;
    }
    .nameda{position: sticky; left: 0; background-color: #EEEEEE;}
    .scrollable {
    width: fit-content;
  max-height: calc(1.5em * 3); /* 3 lines at 1.5 line-height */
  line-height: 1.5;
  overflow-y: clip;
  display: block; /* needed to enable scrolling inside <td> */
}
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
    
    $rowcount=0;
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
      <th
        class="header"
        v-for="(value, key) in transformed[0]"
        :key="key"
        @click="sortBy(key)"
        style="cursor: pointer;"
      >
        {{ key }}
        <span v-if="sortField === key">
          {{ sortAsc ? '▲' : '▼' }}
        </span>
      </th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, index) in transformed" :key="index">
      <td
      v-overflow-symbol
        v-for="(value, key) in row"
        :key="key"
        :class="key.slice(0, 8).replace(/[^a-zA-Z0-9]/g, '').toLowerCase()"
        :title="Array.isArray(value)
          ? value.map(stripHtml).join(', ')
          : stripHtml(value)"
        :style="getCellStyle(value)"
      >
        <div class="scrollable" v-if="!Array.isArray(value)" v-html="value"></div>
        <div v-overflow-symbol class="scrollable" v-else>
          <span v-for="(entry, index) in value" :key="index">
            <span v-html="entry"></span>
            <span v-if="index < value.length - 1"><br/></span>
          </span>
        </div>
      </td>
    </tr>
  </tbody>
</table>

  </div>

  <script type="module">
    import { createApp } from './vue.js'

    const app = createApp({
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
        //make arrays of: Link (paper), Native language(s), Stimulus language, Source, Comprehension questions, 
        //divide fields with multiple values: Age range, Total # words/chars, Age mean±SD
        //both: Age range
      },
      methods:
      {
        getLongestTextLength(htmlArray) {
    let maxLength = 0;

    for (const html of htmlArray) {
      const wrapper = document.createElement("div");
      wrapper.innerHTML = html;

      // Check each top-level element in the HTML string
      for (const child of wrapper.childNodes) {
        if (child.nodeType === 1 || child.nodeType === 3) {
          const text = child.textContent?.trim() || '';
          if (text.length > maxLength) {
            maxLength = text.length;
          }
        }
      }
    }

    return maxLength;
  },

  getCellStyle(value) {
    let longest = 0;

    if (Array.isArray(value)) {
      longest = this.getLongestTextLength(value);
    } else {
      const wrapper = document.createElement("div");
      wrapper.innerHTML = value || '';
      longest = Array.from(wrapper.childNodes)
        .map((el) => el.textContent?.trim().length || 0)
        .reduce((a, b) => Math.max(a, b), 0);
    }

    if (longest > 60) {
      return { minWidth: '300px' };
    } else if (longest > 20) {
      return { minWidth: '150px' };
    } else  {
      return { minWidth: '50px' };
    } 
  },

        stripHtml(html) {
    const div = document.createElement("div");
    div.innerHTML = html || '';
    return div.textContent || '';
  },
        transform(){
          for (const [key, value] of Object.entries(this.input)) {
            this.transformed[key]={};
            Object.entries(value).forEach(([key2, value2]) => {
              //this.transformed[key][key2] = value2;
              let buffer = this.split(key2, value2, this.types[key2]);
              Object.entries(buffer).forEach(([key3, value3]) => {
                this.transformed[key][key3]=value3;
              });
            });
          }

        // Do something with key and value
        },
        split(key, value, type){ //teilt spalten
          
          let returnvalue ={};
          if (type.slice(0, 8)=='array of'){
            type=type.slice(9);
            if (type=='choice' || type=='links'){
              value = value.split(', ');
            }
            else{
              value = value.split('; ');
            }
          }
//chatGPT
if (Array.isArray(value)) {
  value.forEach( (subvalue, index) => {
    //console.log(subvalue);
  try {
    const result = this.regex(subvalue, key, type);

    if (typeof result !== 'object' || result === null || Array.isArray(result)) {
      throw new Error("Expected this.regex(...) to return an object.");
    }

    for (let [subKey, subValue] of Object.entries(result)) {
      if (key === subKey){
        subKey='';
      }
      else{
        subKey='_'+subKey;
      }
      if (!Array.isArray(returnvalue[key+''+subKey])) {
        returnvalue[key+''+subKey] = [];
      }
      returnvalue[key+''+subKey].push(subValue);
      //console.log(subValue); //why is only one instance shown for: 47-50 (2 expert linguists); 20-30 (12 post-graduates)
    }

  } catch (error) {
    console.error("Error processing result from regex:", error);
  }
   });
}
//end chatGPT
          else{
            returnvalue = this.regex(value, key, type);
          }
         
          return returnvalue;
        },

        //returns 
        regex(value, key, type){
          
          let returnvalue = {[key]: value.trim()};
          if (type == 'mean-sd'){
            const pattern = /^(?:(?<group>[^:]+):\s*)?(?<mean>[\d.]*)?(?:\s*±\s*(?<sd>[\d.]*))?$/;
            const match = value.match(pattern);
            const { group = "", mean = "", sd = ""} = match.groups;
            returnvalue= {
              group: group.trim(), // will be '' if not matched
              mean: mean.trim(),
              sd: sd.trim()
            };
          }
          else if (type == 'min-max'){
            //console.log('test');
            const pattern = /^(?:\s*(?<min>[\d.]+))?(?:\s*-\s*(?<max>[\d.]+))?(?:\s*\((?<group>[^)]+)\))?\s*$/;
            const match = value.match(pattern);
            const { group = "", min = "", max = ""} = match.groups;
            returnvalue= {
              group: group.trim(), // will be '' if not matched
              min: min.trim(),
              max: max.trim()
            };
          }

          //choice
          //link
          else if (type == 'link'){
          returnvalue = {[key]: '<a href="'+value.trim()+'">Click</a>'};
          }
          else if (type == 'bibtex'){
            returnvalue = {[key]: '<button onclick="navigator.clipboard.writeText(\''+this.escapeForOnclick(value)+'\')">Copy Text</button>'};
          }
          else if (type == 'number'){
            const str = String(value); // safely convert to string
            const match = str.match(/-?\d+(\.\d+)?/);
            returnvalue = {[key]: match ? parseFloat(match[0]) : null};
            //returnvalue = {[key]: String(value).match(/\d+/g).map(Number)};
          }
          else if (type == 'groupnumber'){
            const pattern = /^\s*(?<mean>[\d.+-eE]+)?\s*\(\s*(?<group>[^)]+)\s*\)\s*$/;
            const match = value.match(pattern);
            if (!match || !match.groups) {
              return { mean: "", group: "" };
            }

            const { mean = "", group = "" } = match.groups;

            returnvalue= {
              mean: mean.trim(),
              group: group.trim()
            };
          }
          return returnvalue;
        },


escapeForOnclick(str) {
  return str
    .replace(/\\/g, '\\\\')      // JS: Backslash escapen
    .replace(/'/g, '&#39;')       // HTML-safe für onclick='...'
    .replace(/"/g, '&quot;')      // optional: falls onclick="..."
    .replace(/\n/g, '\\n')        // JS: \n statt echter Linebreaks
    .replace(/\r/g, '')           // \r entfernen
    .replace(/</g, '&lt;')        // HTML-safe
    .replace(/>/g, '&gt;');       // HTML-safe
},

sortBy(field) {
    if (this.sortField === field) {
      this.sortAsc = !this.sortAsc;
    } else {
      this.sortField = field;
      this.sortAsc = true;
    }

    this.transformed.sort((a, b) => {
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
    });

    app.directive('overflow-symbol', {
  mounted(el) {
    requestAnimationFrame(() => {
      const hasOverflow = el.scrollHeight > el.clientHeight;
      if (hasOverflow) {
        el.classList.add('scroll-indicator');
      }
    });
  },
  updated(el) {
    const hasOverflow = el.scrollHeight > el.clientHeight;
    el.classList.toggle('scroll-indicator', hasOverflow);
  }
});

    app.mount('#app');
    
  </script>

</body>
</html>