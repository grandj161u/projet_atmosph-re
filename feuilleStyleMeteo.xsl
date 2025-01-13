<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:param name="currentHour" />
    <xsl:param name="airQualityIndex" />
    <xsl:param name="airQualityComponents" />

    <xsl:output method="html" encoding="UTF-8" />

    <xsl:template match="/">
        <html>
            <head>
                <style>
                    .meteo-card {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    }
                    .periode {
                    margin: 10px 0;
                    padding: 10px;
                    background-color: #f5f5f5;
                    border-radius: 5px;
                    }
                    .symbole {
                    font-size: 24px;
                    margin-right: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="meteo-card">
                    <h2>Prévisions météo du jour</h2>

                    <!-- Matin (8h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='8']">
                            <xsl:apply-templates select="//echeance[@hour='8']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Matin (8-12h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <!-- Midi (12h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='12']">
                            <xsl:apply-templates select="//echeance[@hour='12']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Après-midi (12-20h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <!-- Soir (20h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='20']">
                            <xsl:apply-templates select="//echeance[@hour='20']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Soir (20-8h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <!-- Qualité de l'air
                    <xsl:if test="$airQualityIndex">
                        <div class="periode">
                            <h3>Qualité de l'air</h3>
                            <p>
                                <span class="symbole">🌬️</span>
                                Indice de qualité de l'air: 
                                <xsl:choose>
                                    <xsl:when test="$airQualityIndex = 1">Très bon</xsl:when>
                                    <xsl:when test="$airQualityIndex = 2">Bon</xsl:when>
                                    <xsl:when test="$airQualityIndex = 3">Moyen</xsl:when>
                                    <xsl:when test="$airQualityIndex = 4">Mauvais</xsl:when>
                                    <xsl:when test="$airQualityIndex = 5">Très mauvais</xsl:when>
                                    <xsl:otherwise>Non disponible</xsl:otherwise>
                                </xsl:choose>
                                (<xsl:value-of select="$airQualityIndex"/>)
                            </p>
                            
                            <h4>Composants:</h4>
                            <div class="air-components">
                                <p>
                                    <span class="symbole">💨</span>
                                    CO (Monoxyde de carbone): <xsl:value-of select="format-number($airQualityComponents/co, '0.00')"/>
                    µg/m³
                                </p>
                                <p>
                                    <span class="symbole">🌫️</span>
                                    NO₂ (Dioxyde d'azote): <xsl:value-of select="format-number($airQualityComponents/no2, '0.00')"/>
                    µg/m³
                                </p>
                                <p>
                                    <span class="symbole">☁️</span>
                                    O₃ (Ozone): <xsl:value-of select="format-number($airQualityComponents/o3, '0.00')"/> µg/m³
                                </p>
                                <p>
                                    <span class="symbole">🏭</span>
                                    SO₂ (Dioxyde de soufre): <xsl:value-of select="format-number($airQualityComponents/so2, '0.00')"/>
                    µg/m³
                                </p>
                                <p>
                                    <span class="symbole">💨</span>
                                    PM2.5 (Particules fines): <xsl:value-of select="format-number($airQualityComponents/pm2_5,
                    '0.00')"/> µg/m³
                                </p>
                                <p>
                                    <span class="symbole">💨</span>
                                    PM10 (Particules grossières): <xsl:value-of select="format-number($airQualityComponents/pm10,
                    '0.00')"/> µg/m³
                                </p>
                                <p>
                                    <span class="symbole">🌫️</span>
                                    NH₃ (Ammoniac): <xsl:value-of select="format-number($airQualityComponents/nh3, '0.00')"/> µg/m³
                                </p>
                            </div>
                        </div>
                    </xsl:if> -->
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="echeance">
        <div class="periode">
            <h3>
                <xsl:choose>
                    <xsl:when test="@hour='8'">Matin (8-12h)</xsl:when>
                    <xsl:when test="@hour='12'">Après-midi (12-20h)</xsl:when>
                    <xsl:when test="@hour='20'">Soir (20-8h)</xsl:when>
                </xsl:choose>
            </h3>

            <!-- Température -->
            <p>
                <span class="symbole">
                    <xsl:choose>
                        <xsl:when test="number(temperature/level[1]) &lt; 278.15">❄️</xsl:when>
                        <xsl:when test="number(temperature/level[1]) &lt; 288.15">🌡️</xsl:when>
                        <xsl:otherwise>☀️</xsl:otherwise>
                    </xsl:choose>
                </span>
                <xsl:value-of
                    select="format-number(number(temperature/level[1]) - 273.15, '0.0')" />°C </p>

            <!-- Pression -->
            <p>
                <span class="symbole">🌡️</span> Pression: <xsl:value-of
                    select="format-number(number(pression/level), '0')" /> hPa </p>

            <!-- Humidité -->
            <p>
                <span class="symbole">💧</span> Humidité: <xsl:value-of
                    select="format-number(number(humidite/level), '0')" />% </p>

            <!-- Pluie -->
            <xsl:if test="number(pluie) > 0">
                <p>
                    <span class="symbole">🌧️</span> Risque de pluie </p>
            </xsl:if>

            <!-- Neige -->
            <xsl:if test="number(risque_neige) > 50">
                <p>
                    <span class="symbole">🌨️</span> Risque de neige </p>
            </xsl:if>

            <!-- Vent -->
            <p>
                <span class="symbole">💨</span> Vent: <xsl:value-of
                    select="format-number(number(vent_moyen/level), '0.0')" /> km/h <xsl:if
                    test="number(vent_rafales/level) > 0"> (Rafales: <xsl:value-of
                        select="format-number(number(vent_rafales/level), '0.0')" /> km/h) </xsl:if>
                Direction: <xsl:value-of select="vent_direction/level" />° </p>

            <!-- Iso Zéro -->
            <p>
                <span class="symbole">❄️</span> Iso Zéro: <xsl:value-of select="iso_zero" /> m </p>

            <!-- Nébulosité -->
            <p>
                <span class="symbole">☁️</span> Nébulosité: <xsl:value-of
                    select="format-number(number(nebulosite/level[1]), '0')" />% </p>
        </div>
    </xsl:template>

</xsl:stylesheet>