<!DOCTYPE html>
<html lang="en">
<head>







<!--STYLE-->

<style>

.topleft{
  position: fixed;
  top: 45px;
  width: 195px;
  left: 0;
  height: 55px;
  min-width: 195px !important;
}
.topbar{
  padding: 15px;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 9999;
  background-color:white;
  width: 100%;
  overflow: hidden;
  max-height: 15px;
  min-height: 15px;
  line-height: 1.1;
}
.filterbutton{padding: 0}
th {
  vertical-align: top;
  padding: 4px 8px;
  line-height: 1.2;
  background-color: #EEEEEE; 
  position: sticky;
  top: 0;
  z-index: 9999;
  
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  padding: 1rem;
  background-color: #f9f9f9;
  border-radius: 8px;
}

.filter-field {
  display: flex;
  flex-direction: column;
  min-width: 100%;
  max-width: 100%;
}

.filter-field label {
  font-weight: bold;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.filter-field input,
.filter-field select {
  padding: 0.2rem 0.2rem;
  font-size: 0.9rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  height: 22px;
  margin-bottom: -5px;
}

.range-inputs {
  display: flex;
  gap: 0.5rem;
}

button {

  font-size: 0.9rem;
  background-color: #eee;
  border: 1px solid #ccc;
  border-radius: 6px;
  cursor: pointer;
}

button:hover {
  background-color: #ddd;
}

  th, td {
  border: 1px solid #ccc;  /* Light grey border */
}

thead{
  background-color: #EEEEEE;
}
.scrollable {
  width: 100%;
  max-height: calc(1.2em * 3);
  display: block; /* needed to enable scrolling inside <td> */
  overflow-y: clip;
  position: relative;
  white-space: normal;
  word-break: break-word;
  padding-bottom: 0.3em;
}

.scrollable::after {
  content: "hover to show all";
  position: absolute;
  bottom: -0.3em;
  left: 0em;
  font-size: 0.5em;
  color: #999;
  padding-left: 4px;
  display: none;
  pointer-events: none;
  background-color: white;
    width: 100%;
}

.scrollable.scroll-indicator::after {
  display: block;
}



table {
  margin-top: 45px;
  table-layout: auto;
  border-collapse: collapse;
}
    .header{
        position: sticky;
        top: 45px;
        background-color: #EEEEEE;
        z-index: 9900;
        height: 55px;

    }
    .nameda{position: sticky; left: 0; background-color: #EEEEEE;z-index: 9950; min-width: 200px !important}
    td{padding: 5px}
</style>




  <meta charset="UTF-8" />
  <title>Eyetracker-Studies-Collection</title>
</head>


<!--READ DATA-->
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






<!--MAIN HTML-->
<body>
  </div>

  <div id="app" >
    <div class="topbar">
  Use the ">" symbol to sort the columns. | 
  Use the 🔍 symbol to toggle filter on/off. | 
  Report any issues <a href="mailto:info@uhx.ch">here</a> | 
  <button @click="resetFilters" style="">🔄 Reset all filters</button>
  </div>

  <table>
  <thead>
  <tr style="min-height: 50px;">
    <th :style="getTypeBasedWidth(types[key] || types[key.split('_')[0]])" :title="descs[key.split('_')[0]]" :class="index === 0 ? 'topleft' : 'header'" v-for="(value, key, index) in transformed[0]" :key="key">
      <div style="display: flex; flex-direction: column; align-items: flex-start;">
        <div style="display: flex; justify-content: space-between; width: 100%;">
          <span  style="cursor: pointer;">
            {{ key }}
            <span @click="sortBy(key)" v-if="sortField === key">
              {{ sortAsc ? '▲' : '▼' }}
            </span>
            <span @click="sortBy(key)" v-else>
              >
            </span>
            <button v-if="true || types[key]!=='bibtex' && types[key]!=='array of link'" class="filterbutton" @click="toggleFilter(key)" style="">{{ visibleFilters[key] ? '✖️' : '🔍' }}</button>
          </span>
          
        </div>
        <div v-if="visibleFilters[key]" class="filter-field" style="width: 100%;">
          <!-- Textsuche -->
          <input v-if="isTextFilter(types[key])"
                v-model="filters[key]"
                :value="filters[key] || ''"
                @input="applyFilters"
                placeholder="Suchtext..." />
          <!-- Dropdown für 'choice' -->
<select v-else-if="types[key] === 'choice'"
        v-model="filters[key]"
        @change="applyFilters">
  <option value="">-- Alle --</option>
  <option v-for="option in getUniqueOptions(key)" :key="option" :value="option">
    {{ option }}
  </option>
</select>
<!-- Auswahl für array of choice -->
<select v-else-if="types[key] === 'array of choice'" v-model="filters[key]" @change="applyFilters">
  <option value="">-- Alle --</option>
  <option v-for="opt in getUniqueOptions(key)" :key="opt" :value="opt">{{ opt }}</option>
</select>
          <!-- Zahlenfilter -->
          <div v-else-if="types[key] === 'number'" class="range-inputs">
            <input
            style="width: 50px" 
  type="number"
  v-model.number="(filters[key] = filters[key] || { min: null, max: null }).min"
  @input="applyFilters"
  placeholder="min"
/>
            <input style="width: 50px"  type="number" v-model.number="filters[key].max" @input="applyFilters" placeholder="max" />
          </div>

          <!-- Komplexe Werte -->
          <div v-else-if="['array of mean-sd', 'array of min-max', 'array of groupnumber'].includes(types[key])" class="range-inputs">
            <input type="number" v-model.number="filters[key].min" @input="applyFilters" placeholder="min" />
            <input type="number" v-model.number="filters[key].max" @input="applyFilters" placeholder="max" />
          </div>
        </div>
      </div>
    </th>
  </tr>
</thead>

  <tbody>
    <tr v-for="(row, index) in filtered" :key="index">
      <td

      v-overflow-symbol
        v-for="(value, key) in row"
        :key="key"
        :class="key.slice(0, 8).replace(/[^a-zA-Z0-9]/g, '').toLowerCase()"
        :title="Array.isArray(value)
          ? value.map(stripHtml).join(', ')
          : stripHtml(value)"
        :style="getTypeBasedWidth(types[key] || types[key.split('_')[0]]) + ((types[key] || types[key.split('_')[0]]) === 'number' ? '; text-align: right;' : '')"
  >
        <div class="scrollable" v-if="!Array.isArray(value)" v-html="value" v-overflow-symbol></div>
        <div v-overflow-symbol class="scrollable" v-else v-overflow-symbol>
          <span v-for="(entry, index) in value" :key="index">
            <span v-html="entry"></span>
            <span v-if="index < value.length - 1">, </span>
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
          filters: {},
          visibleFilters: {},
        }
      },
      mounted() {
        // Embedded PHP JSON into JavaScript
        this.input = <?= $data ?>;
        
       
        this.types=this.input.types;
        //mean-sd   group: string, mean: number, sd: number
        //min-max   group: string, min:number, max: number
        const typekeys = Object.keys(this.types);

for (const typekey of typekeys) {
  const type = this.types[typekey];
  if (type === "array of mean-sd") {
    //delete this.types[typekey];
    this.types[`${typekey}_group`] = "string";
    this.types[`${typekey}_mean`] = "number";
    this.types[`${typekey}_sd`] = "number";
  } else if (type === "array of min-max") {
    //delete this.types[typekey];
    this.types[`${typekey}_group`] = "string";
    this.types[`${typekey}_min`] = "number";
    this.types[`${typekey}_max`] = "number";
  }
}

        console.log(this.types);
        this.descs=this.input.descs;
        this.input=this.input.data;
        this.transform();
        this.filter({});
        
        //make arrays of: Link (paper), Native language(s), Stimulus language, Source, Comprehension questions, 
        //divide fields with multiple values: Age range, Total # words/chars, Age mean±SD
        //both: Age range
      },
      methods:
      {
toggleFilter(key) {
  if (this.visibleFilters[key]) {
    // Filter ist sichtbar → zurücksetzen und verstecken
    delete this.filters[key];                // Filter entfernen
    this.visibleFilters[key] = false;        // Ausblenden
    this.applyFilters();                     // Filter anwenden
  } else {
    // Filter anzeigen
    this.visibleFilters[key] = true;
    if (this.types[key] === 'number' || this.types[key]?.includes('min-max') || this.types[key]?.includes('mean-sd') || this.types[key]?.includes('groupnumber')) {
      this.filters[key] = { min: null, max: null };
    } else {
      this.filters[key] = '';
    }
  }
},
isTextFilter(type) {
  return ['string', 'unknown', 'array of link', 'bibtex'].includes(type);
},

    getUniqueOptions(key) {
      const values = new Set();
      for (const row of this.transformed) {
        const val = row[key];
        const list = Array.isArray(val) ? val : [val];
        list.forEach(v => {
          if (typeof v === 'string' && v.trim()) values.add(v.trim());
        });
      }
      return Array.from(values).sort();
    },
    updateFilter(key, field, value) {
      if (!this.filters[key]) this.filters[key] = {};
      this.filters[key][field] = value ? parseFloat(value) : null;
      this.applyFilters();
    },


        updateFilter(key, bound, val) {
  if (!this.filters[key]) this.filters[key] = { min: null, max: null };
  this.filters[key][bound] = val !== '' ? Number(val) : null;
  this.applyFilters();
},



        applyFilters() {
    this.filter(this.filters);
  },
resetFilters() {
  this.filters = {};

  // Hide all visible filter inputs
  this.visibleFilters = {};
  
  // Reapply filter (will reset the table view)
  this.applyFilters();
},

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
      return { minWidth: '300px', };
    } else if (longest > 20) {
      return { minWidth: '150px' };
    } else  {
      return { minWidth: '100px' };
    } 
  },

getTypeBasedWidth(type) {
  console.log("Type for width:", type);
  let px;

  switch (type) {
    case 'number':
      px = '150px';
      break;
    case 'string':
    case 'choice':
      px = '150px';
      break;
    case 'array of choice':
    case 'array of link':
      px = '200px';
      break;
    case 'array of min-max':
    case 'array of mean-sd':
    case 'array of groupnumber':
      px = '180px';
      break;
    case 'bibtex':
      px = '50px';
      break;
    default:
      px = '150px'; // fallback
  }

  return `width: ${px}; min-width: ${px}; max-width: ${px};`;
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
        },
        filter(criteria ) {
  this.filtered = this.transformed.filter(entry => {
    for (const [key, rule] of Object.entries(criteria)) {
      const type = this.types[key];
      const value = entry[key];

      // 🆕 Hilfsfunktion: prüft, ob `value` (oder Elemente davon) den `rule` erfüllen
      const matches = (val) => {
        if (Array.isArray(val)) {
          return val.some(v => String(v).toLowerCase().includes(String(rule).toLowerCase()));
        } else {
          return String(val).toLowerCase().includes(String(rule).toLowerCase());
        }
      };

      // --- TYPE-BASED FILTERING ---
      if (type === 'string' || type === 'unknown' || type === 'choice' || type == 'array of link' || type == 'bibtex') {
        if (!matches(value)) return false;
      }

      else if (type === 'array of choice') {
        if (!matches(value)) return false;
      }

      else if (type === 'number') {
        const num = parseFloat(value);
        if (isNaN(num)) return false;
        if (rule.min != null && num < rule.min) return false;
        if (rule.max != null && num > rule.max) return false;
      }

      else if (type === 'array of mean-sd' || type === 'array of groupnumber') {
        const means = entry[key + '_mean'];
        if (!Array.isArray(means)) return false;
        const valid = means.some(m => {
          const num = parseFloat(m);
          if (isNaN(num)) return false;
          if (rule.min != null && num < rule.min) return false;
          if (rule.max != null && num > rule.max) return false;
          return true;
        });
        if (!valid) return false;
      }

      else if (type === 'array of min-max') {
        const mins = entry[key + '_min'];
        const maxs = entry[key + '_max'];
        if (!Array.isArray(mins) || !Array.isArray(maxs)) return false;
        const valid = mins.some((min, i) => {
          const minVal = parseFloat(min);
          const maxVal = parseFloat(maxs[i]);
          if (isNaN(minVal) || isNaN(maxVal)) return false;
          if (rule.min != null && maxVal < rule.min) return false;
          if (rule.max != null && minVal > rule.max) return false;
          return true;
        });
        if (!valid) return false;
      }

      // TODO: andere Typen (links, bibtex) bei Bedarf ergänzen
    }

    return true;
  });
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

    this.filtered.sort((a, b) => {
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
      if (el.scrollHeight > el.clientHeight) {
        el.classList.add('scroll-indicator');
      }
    });
  },
  updated(el) {
    if (el.scrollHeight > el.clientHeight) {
      el.classList.add('scroll-indicator');
    } else {
      el.classList.remove('scroll-indicator');
    }
  }
});




//REGEX METHOD: splits and trims values
app._component.methods.regex = function (value, key, type){
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
            if (value.length>10){
              returnvalue = {[key]: '<button onclick="navigator.clipboard.writeText(\''+this.escapeForOnclick(value)+'\')">Copy</button>'};
            }
            else{
              returnvalue = {[key]: ''}
            }
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
        };

app.mount('#app');

    
  </script>

</body>
</html>