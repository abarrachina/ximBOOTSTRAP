<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template name="nav-list" match="nav-list">
		<ul class="nav nav-list">
			<xsl:apply-templates/>		
		</ul>
	</xsl:template>
</xsl:stylesheet>
