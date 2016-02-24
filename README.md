# PHP nyaa archiver

Written on a whim after the banic on February 2016. This script archives [nyaa.se](http://www.nyaa.se/) (or sukebei) entries into JSON objects. Largely inspired from [nyaamagnet](https://github.com/Hamuko/nyaamagnet).

### Usage
```
php nyaa_archiver.php -s=10 -e=20 -f=archive.json

  -s  (start) Sets the starting ID to archive (Default: 15)
  -e  (end) Sets the ending ID to archive (Default: 20)
  -f  (file) Sets the JSON output file (Default: archive)
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
```

### Log Output
```
2016-02-24T13:36:37+08:00 Fetching ID 600213...
2016-02-24T13:36:37+08:00 Parsing ID 600213...
2016-02-24T13:36:37+08:00 Success 600213.
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
   $content = file_get_contents('archive.json'); // load file
   $content = rtrim($content, ','); // remove last comma
   $content = '[' . $content . ']'; // wrap in an array
   $content = json_decode($content); // decode
   ```
   
4. **Why not insert it into a database directly?**

   Could not decide which database to use when the banic had seeped in.
   
5. **How do I background this process on a server?**

   `nohup php nyaa_archiver.php -s=10 -e=20 -f=archive.json > log.txt &`. Output will be redirected to `log.txt`, and you can view it (live!) with `tail -f log.txt`.
   
6. **How do I archive sukebei instead?**

   Find and change the `$baseUrl`. Might consider adding it as another opt in future.

7. **How do I search through the JSON files?**

   You `grep -i -P '"name":"(.+?)dragon ball(.+?)"' archive.json` it. Regex is fun!
