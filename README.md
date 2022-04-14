# Formidable Views PDF 
Export Formidable Forms Views to PDF with a Shortcode.

This is a work in progress and is intended to be a simpler up-to-date replacement for [Formidable Pro PDF Extended](https://github.com/jvarn/formidable-pro-pdf-extended).

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

## Usage

* Export Formidable View as PDF
`[ffviewpdf viewid=X type=view]`
* Export current page as PDF
`[ffviewpdf type=page]`

## Note

Most configuration options are hard-coded at the moment, but can be modified by following the [mPDF documentation](https://mpdf.github.io).
