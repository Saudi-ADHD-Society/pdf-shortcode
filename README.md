# WP 2 PDF Shortcode
Export Formidable Forms Views or Wordpress Pages to PDF with a Shortcode.

A simpler up-to-date replacement for [Formidable Pro PDF Extended](https://github.com/jvarn/formidable-pro-pdf-extended).

NB: This is currently an early alpha plugin and should only be used for testing.

## Requirements
1. PHP 7.4
2. Formidable Forms Pro
3. Formidable Forms Visual Views addon

## Installation

1. Clone or download.
2. Install dependencies with Composer:
```
$ cd ff-views-pdf
$ composer install
```
3. Upload to plugins directory and activate.
4. Set your default options via `options-general.php?page=options-ffviewpdf`.
5. Set inline options in shortcodes (see below).

## Usage

* Export Formidable View as PDF
`[wp2pdf viewid=X type=view]`
* Export current page as PDF
`[wp2pdf type=page]`

## Other options
### Page orientation
1. Landscape
```
[wp2pdf orientation=L]
```
2. Portrait
```
[wp2pdf orientation=P]
```
### Direction
1. Left-to-Right
```
[wp2pdf direction=ltr]
```
2. Right-to-Left
```
[wp2pdf direction=rtl]
```
### Custom filename
Filename without .pdf file extension.
```
[wp2pdf filename=newfile]
```

## Note

Advanced configuration options are available by following the [mPDF documentation](https://mpdf.github.io).
