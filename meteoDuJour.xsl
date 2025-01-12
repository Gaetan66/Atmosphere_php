<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    
    <!-- Template principal -->
    <xsl:template match="previsions">
                
                <table border="1">
                    <thead>
                        <tr>
                            <th>Heure</th>
                            <th>Température (°C)</th>
                            <th>Précipitations (mm)</th>
                            <th>Risque de neige</th>
                            <th>Vent (km/h)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Appliquer les templates uniquement pour les heures spécifiques -->
                        <xsl:apply-templates select="echeance[@hour='6']"/>
                        <xsl:apply-templates select="echeance[@hour='12']"/>
                        <xsl:apply-templates select="echeance[@hour='18']"/>
                    </tbody>
                </table>
    </xsl:template>

    <!-- Template pour une tranche spécifique -->
    <xsl:template match="echeance">
        <tr>
            <!-- Heure de la tranche -->
            <td>
                <xsl:value-of select="@hour"/> h
            </td>
            <!-- Température (conversion Kelvin à Celsius) -->
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
            <!-- Précipitations -->
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
            <!-- Risque de neige -->
            <td>
                <xsl:choose>
                    <xsl:when test="number(risque_neige) > 0">
                        Oui <img src="assets/snow.png" alt="Neige"/>
                    </xsl:when>
                    <xsl:otherwise>
                        Non <img src="assets/no-snow.png" alt="Pas de neige"/>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <!-- Vent moyen -->
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
        </tr>
    </xsl:template>
</xsl:stylesheet>
