<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xhtml="http://www.w3.org/1999/xhtml">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>XML Sitemap — GLS Sprachzentrum</title>
        <meta name="robots" content="noindex"/>
        <style>
          body { font: 14px/1.5 -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #1f2937; margin: 0; background: #f9fafb; }
          header { background: #111827; color: #fff; padding: 20px 32px; }
          header h1 { margin: 0 0 4px; font-size: 20px; }
          header p { margin: 0; color: #9ca3af; font-size: 13px; }
          main { padding: 24px 32px; }
          table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
          th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
          th { background: #f3f4f6; font-weight: 600; }
          tr:hover td { background: #f9fafb; }
          a { color: #2563eb; text-decoration: none; }
          a:hover { text-decoration: underline; }
          .hreflang span { display: inline-block; background: #eef2ff; color: #4338ca; border-radius: 3px; padding: 1px 6px; margin-right: 4px; font-size: 11px; }
          .num { text-align: right; }
        </style>
      </head>
      <body>
        <header>
          <h1>XML Sitemap</h1>
          <p>
            <xsl:value-of select="count(s:urlset/s:url)"/> URLs —
            This file is consumed by search engines; this page is a human-readable view.
          </p>
        </header>
        <main>
          <table>
            <tr>
              <th>#</th>
              <th>URL</th>
              <th>Alternates (hreflang)</th>
              <th>Last modified</th>
              <th>Change freq.</th>
              <th class="num">Priority</th>
            </tr>
            <xsl:for-each select="s:urlset/s:url">
              <tr>
                <td><xsl:value-of select="position()"/></td>
                <td>
                  <a href="{s:loc}"><xsl:value-of select="s:loc"/></a>
                </td>
                <td class="hreflang">
                  <xsl:for-each select="xhtml:link[@rel='alternate']">
                    <span><xsl:value-of select="@hreflang"/></span>
                  </xsl:for-each>
                </td>
                <td><xsl:value-of select="s:lastmod"/></td>
                <td><xsl:value-of select="s:changefreq"/></td>
                <td class="num"><xsl:value-of select="s:priority"/></td>
              </tr>
            </xsl:for-each>
          </table>
        </main>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
