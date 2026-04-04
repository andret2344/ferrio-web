<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="sitemap xhtml">

	<xsl:output method="html" indent="yes" encoding="UTF-8"/>

	<xsl:template match="/">
		<html lang="en">
		<head>
			<title>
				<xsl:choose>
					<xsl:when test="sitemap:sitemapindex">Sitemap Index</xsl:when>
					<xsl:otherwise>Sitemap</xsl:otherwise>
				</xsl:choose>
				<xsl:text> — Ferrio</xsl:text>
			</title>
			<style>
				body { font-family: system-ui, -apple-system, sans-serif; margin: 2rem; color: #1a1a1a; background: #fafaf9; }
				h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: .25rem; }
				p { color: #666; margin-bottom: 1.5rem; font-size: .875rem; }
				table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
				th { text-align: left; padding: .5rem .75rem; background: #f5f5f4; border-bottom: 2px solid #e7e5e4; font-weight: 600; color: #44403c; }
				td { padding: .5rem .75rem; border-bottom: 1px solid #e7e5e4; }
				a { color: #b45309; text-decoration: none; }
				a:hover { text-decoration: underline; }
				tr:hover td { background: #fefce8; }
				.count { color: #999; font-weight: normal; font-size: .875rem; }
			</style>
		</head>
		<body>
			<xsl:apply-templates select="sitemap:sitemapindex"/>
			<xsl:apply-templates select="sitemap:urlset"/>
		</body>
		</html>
	</xsl:template>

	<xsl:template match="sitemap:sitemapindex">
		<h1>Sitemap Index</h1>
		<p>
			<span class="count"><xsl:value-of select="count(sitemap:sitemap)"/> sitemaps</span>
		</p>
		<table>
			<tr><th>Sitemap</th><th>Last Modified</th></tr>
			<xsl:for-each select="sitemap:sitemap">
				<tr>
					<td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
					<td><xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>

	<xsl:template match="sitemap:urlset">
		<h1>Sitemap</h1>
		<p>
			<span class="count"><xsl:value-of select="count(sitemap:url)"/> URLs</span>
		</p>
		<table>
			<tr><th>URL</th><th>Alternates</th><th>Frequency</th><th>Priority</th></tr>
			<xsl:for-each select="sitemap:url">
				<tr>
					<td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
					<td>
						<xsl:for-each select="xhtml:link[@rel='alternate']">
							<xsl:if test="position() > 1"><xsl:text>, </xsl:text></xsl:if>
							<xsl:value-of select="@hreflang"/>
						</xsl:for-each>
					</td>
					<td><xsl:value-of select="sitemap:changefreq"/></td>
					<td><xsl:value-of select="sitemap:priority"/></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>

</xsl:stylesheet>
