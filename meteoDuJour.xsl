<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    
    <!-- Template principal -->
    <xsl:template match="previsions">
        <table border="1">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Matin</th>
                    <th>Midi</th>
                    <th>Soir</th>
                </tr>
            </thead>
            <tbody>
                <!-- Température -->
                <tr>
                    <td>Température (°C)</td>
                    <xsl:apply-templates select="echeance[@hour='6']" mode="temperature"/>
                    <xsl:apply-templates select="echeance[@hour='12']" mode="temperature"/>
                    <xsl:apply-templates select="echeance[@hour='18']" mode="temperature"/>
                </tr>
                <!-- Précipitations -->
                <tr>
                    <td>Pluie (mm)</td>
                    <xsl:apply-templates select="echeance[@hour='6']" mode="precipitations"/>
                    <xsl:apply-templates select="echeance[@hour='12']" mode="precipitations"/>
                    <xsl:apply-templates select="echeance[@hour='18']" mode="precipitations"/>
                </tr>
                <!-- Risque de neige -->
                <tr>
                    <td>Risque de neige</td>
                    <xsl:apply-templates select="echeance[@hour='6']" mode="neige"/>
                    <xsl:apply-templates select="echeance[@hour='12']" mode="neige"/>
                    <xsl:apply-templates select="echeance[@hour='18']" mode="neige"/>
                </tr>
                <!-- Vent moyen -->
                <tr>
                    <td>Vent (km/h)</td>
                    <xsl:apply-templates select="echeance[@hour='6']" mode="vent"/>
                    <xsl:apply-templates select="echeance[@hour='12']" mode="vent"/>
                    <xsl:apply-templates select="echeance[@hour='18']" mode="vent"/>
                </tr>
            </tbody>
        </table>
    </xsl:template>

    <!-- Templates pour les catégories avec icônes -->
    <xsl:template match="echeance" mode="temperature">
        <td>
            <xsl:value-of select="format-number(number(temperature/level[@val='2m']) - 273.15, '0.0')"/> °C
            <xsl:choose>
                <xsl:when test="number(temperature/level[@val='2m']) - 273.15 &gt;= 30">
                    <img src="assets/hot.png" alt="Chaud"/>
                </xsl:when>
                <xsl:when test="number(temperature/level[@val='2m']) - 273.15 &lt;= 0">
                    <img src="assets/cold.png" alt="Froid"/>
                </xsl:when>
                <xsl:otherwise>
                    <img src="assets/mild.png" alt="Tempéré"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </xsl:template>

    <xsl:template match="echeance" mode="precipitations">
        <td>
            <xsl:value-of select="pluie"/> mm
            <xsl:choose>
                <xsl:when test="number(pluie) &gt; 5">
                    <img src="assets/rain.png" alt="Pluie"/>
                </xsl:when>
                <xsl:otherwise>
                    <img src="assets/dry.png" alt="Sec"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </xsl:template>

    <xsl:template match="echeance" mode="neige">
        <td>
            <xsl:choose>
                <xsl:when test="number(risque_neige) > 0">
                    Oui
                </xsl:when>
                <xsl:otherwise>
                    Non
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </xsl:template>

    <xsl:template match="echeance" mode="vent">
        <td>
            <xsl:value-of select="format-number(number(vent_moyen/level[@val='10m']), '0.0')"/> km/h
            <xsl:choose>
                <xsl:when test="number(vent_moyen/level[@val='10m']) &gt; 50">
                    <img src="assets/windy.png" alt="Vent fort"/>
                </xsl:when>
                <xsl:otherwise>
                    <img src="assets/calm.png" alt="Vent léger"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </xsl:template>
</xsl:stylesheet>
