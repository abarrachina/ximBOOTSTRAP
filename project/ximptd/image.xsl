<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
   <xsl:template name="image" match="image">
 	<xsl:choose>
 		<xsl:when test="../image-list">
 			<li class="span{@cols}">
 			    <img src="@@@RMximdex.pathto({@src})@@@" uid="{@uid}"/>
 			</li>
         </xsl:when>
         <xsl:otherwise>
 			    <img class="span{@cols}" src="@@@RMximdex.pathto({@src})@@@" uid="{@uid}"/>
         </xsl:otherwise>
     </xsl:choose>
   </xsl:template>
</xsl:stylesheet>
