# Pico Page Output

Output the page content or data as raw, html, json or xml with `?output` in [Pico CMS](http://picocms.org).

## Installation

Copy `PicoPageOutput.php` to the `plugins/` directory of your Pico Project.

## Usage

Enable the plugin and the output formats in Pico `config.php`.

```php
$config['PicoPageOutput.enabled'] = true; // by default
$config['PicoPageOutput.enabledFormats'] = array('content', 'prepared');
```

Then add `?output=format` to any url.

format|desc.|example
---|---|---
`content`|The html content.|`<p>Some content. <emph>base_url</emph>: http://monsite.com</p>`
`raw`|The raw page, with meta header and raw variables.|`---\nTitle:My title\n---\nSome content. *base_url*: %base_url%`
`prepared`|The page content without the meta header and with parsed variables.|`Some content. *base_url*: http://monsite.com`
`json`|The page data in json format.|`{"id":"index","url":"http:\/\/monsite.com\/index","title":"My title","author":"","time":"","date":"","date_formatted":"","raw_content":"---\nTitle:My title\n---\nSome content. *base_url*: %base_url%","content":"<p>Some content. <emph>base_url</emph>: http://monsite.com</p>"}`
`xml`|The page data in xml format.|`<page><id>index</id><url>http://monsite.com/index</url><title>My title</title><author/><time/><date/><date_formatted/><raw_content>---\nTitle:My title\n---\nSome content. *base_url*: %base_url%</raw_content><content><p>Some content. <emph>base_url</emph>: http://monsite.com</p></content></page>"}`

Note that some formats output the page meta or basic internal data.
