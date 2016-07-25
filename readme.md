# SilverStripe JSON Connector

A JSON feed consumer for the external content module. Makes use of JSONPath
for selecting the data to be included from the feed, and how to map those items
to data for import. 


## Usage

* Create a new JSON content source
* Enter the feed URL
* Enter a JSONPath expression that represents the 'collection' of data
* Specify some property mappings, in particular ID and Title

For example, enter the following information

* Feed URL: https://www.reddit.com/.json
* JSONPath expression: $.data.children[:9].data
* Selectors:
  * ID : $.id
  * Title: $.title
