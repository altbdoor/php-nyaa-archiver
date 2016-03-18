# PHP nyaa archiver

Written on a whim after the banic on February 2016. This script archives [nyaa.se](http://www.nyaa.se/) (or sukebei) entries into JSON objects. Largely inspired from [nyaamagnet](https://github.com/Hamuko/nyaamagnet).

### Usage
```
Usage: php nyaa_archiver.php [OPTION...]

  -s  (start) (Default: 15)
      Sets the starting ID to archive

  -e  (end)   (Default: 20)
      Sets the ending ID to archive

  -f  (file)  (Default: "output.json")
      Sets the JSON output file

  --sukebei
      Sets the URL to archive sukebei instead of nyaa

  --failsleep (Default: 10)
      Sets the time to sleep before retrying when a failure occurs

  --fields    (Default: "id,name,category,timestamp,description,filesize,magnet")
      Sets the fields to archive

Example:
  Archiving sukebei ID 1,940,000 to 1,950,000 into "sukebei.json", with only the
  ID, name and magnet fields.
  
  php nyaa_archiver.php -s=1940000 -e=1950000 -f=sukebei.json --fields=id,name,magnet
```

### JSON Output
```js
// prettified
{
    "id": 65,
    "name": "Shadow Skill",
    "category": ["Anime", "English-translated Anime"],
    "timestamp": 1214209440,
    "description": "None",
    "filesize": "4.35 GiB",
    "magnet": "ac4b06a04ec9dc5169ba3940133950059c404ce8"
},
// original
{"id":74,"name":"Raw Manga xxxHOLiC Vol.1 - 10","category":["Literature","Raw Literature"],"timestamp":1214209440,"description":"someone please SEED!!!","filesize":"344.5 MiB","magnet":"534b478eddf5cb87714702c17735f9ba8e65efae"},

/*
    the "description" field might be notoriously big in sukebei, as they are
    usually filled with Japanese characters which are escaped.
    
    the "magnet" field might contain "#{number}" sometimes, which indicates that
    the uploader has updated the torrent file in ID {number}.
*/
```

### Log Output
```
2016-02-24T13:36:37+08:00 Fetching ID 600213...
2016-02-24T13:36:37+08:00 Parsing ID 600213...
2016-02-24T13:36:37+08:00 Success ID 600213.
2016-02-24T13:36:37+08:00 Fetching ID 600214...
2016-02-24T13:36:38+08:00 Parsing ID 600214...
2016-02-24T13:36:38+08:00 Failed ID 600214. Blame: Does not exist.
```

### Requirements
- [curl](http://php.net/manual/en/book.curl.php)
- [DateTime](http://php.net/manual/en/book.datetime.php)
- [DOM](http://php.net/manual/en/book.dom.php)
- [json](http://php.net/manual/en/book.json.php)

### FAQ

1. **Only shortopts? What about longopts?**

   It was, written on a whim. As long as it works, I guess.

2. **What is `helper.php`?**

   Just some minor efforts to keep the main file (`nyaa_archiver.php`) a little cleaner. If you'd like it as a single, standalone script, just prepend `helper.php` into `nyaa_archiver.php`.

3. **Why JSON? Why the odd output structure?**

   Personally, I find JSON a lot easier to manipulate. As for the odd structure, it was to make it easier to join them in bulk. Something like `cat *.json > combined.json` would seamlessly combine all the JSON output. If you are looking to manipulate them, it might take a few extra steps.
   
   ```php
   // if its a small file
   $content = file_get_contents('output.json'); // load file
   $content = rtrim($content, ','); // remove last comma
   $content = '[' . $content . ']'; // wrap in an array
   $content = json_decode($content); // decode
   
   // if its a huge file
   $handle = fopen('output.json', 'r'); // open file
   if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle, 8192);
            $buffer = rtrim($buffer, ",\n"); // remove last comma and newline
            $buffer = json_decode($buffer); // decode
            
            // process each line
        }
        fclose($handle);
   }
   ```
   
4. **Why not insert it into a database directly?**

   Could not decide which database to use when the banic had seeped in.
   
5. **How do I background this process on a server?**

   `nohup php nyaa_archiver.php -s=10 -e=20 -f=output.json > log.txt &`. Output will be redirected to `log.txt`, and you can view it (live!) with `tail -f log.txt`.
   
6. **How do I archive sukebei instead?**

   <strike>Find and change the `$baseUrl`. Might consider adding it as another opt in future.</strike> We have `--sukebei` now.

7. **How do I search through the JSON files?**

   You `grep -i -P '"name":"(.+?)dragon ball(.+?)"' output.json` it. Regex is fun!
