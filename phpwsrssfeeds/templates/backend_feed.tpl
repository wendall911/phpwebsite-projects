<?xml version="1.0" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns="http://purl.org/rss/1.0/" 
xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel rdf:about="{ABOUT}">
<title>{PAGE_TITLE}</title>
<link>http://{SOURCE_HTTP}</link>
<description>{FEED_DESCRIPTION}</description>
<dc:date>{ISO8601_DATE}</dc:date>
<!-- BEGIN RESOURCE_THUMB_URL -->
<image rdf:resource="{RESOURCE_THUMB_URL}" />
<!-- END RESOURCE_THUMB_URL -->
<items>
<rdf:Seq>
{RDF_LI}
</rdf:Seq>
</items>
</channel>
<!-- BEGIN ABOUT_THUMB_URL -->
<image rdf:about="{ABOUT_THUMB_URL}">
<title>{PAGE_TITLE}</title>
<link>http://{SOURCE_HTTP}</link>
<url>{THUMB_URL}</url>
</image>
<!-- END ABOUT_THUMB_URL -->
{RDF_ITEMS}
</rdf:RDF>