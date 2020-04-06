
# User Guide für das Etsy-Plugin

<div class="container-toc"></div>


## Bei Etsy registrieren

**Etsy** ist ein amerikanischer Marktplatz für den Kauf und Verkauf von handgemachten Produkten, Vintage und Künstlerbedarf. Um das Plugin für Etsy einzurichten, registriere dich zunächst als Händler.


## Etsy in plentymarkets installieren

Nachdem du das Etsy-Plugin im plentymarkets Marketplace gekauft hast, installiere den Marktplatz im Menü **Plugins » Plugin-Übersicht**. Wähle den Filter **Nicht installiert** oder **Alle**, um nicht installierte Plugins anzuzeigen und zu installieren.

### Berechtigung erteilen

Im Menü **Einrichtung » Märkte » Etsy » Authentifizierung** muss zunächst die Schnittstelle freigeschaltet werden. Klicke dazu auf **Etsy-Login**. Du wirst direkt zu Etsy weitergeleitet, wo du die Schnittstelle freischaltest.

### Etsy einrichten

Im Menü **Einrichtung » Märkte » Etsy » Einstellungen** aktivierst du den Artikelexport, Bestandsabgleich und Auftragsimport.

**Hinweis:** Bevor du das Etsy-Plugin in plentymarkets einrichtest, muss bei Etsy im Menü **Shop-Manager » Artikel »** _Artikel öffnen_ die Option **Erneuerungsoptionen** auf **Manuell** gestellt werden, da das Etsy-Plugin sonst nicht korrekt funktioniert.


## Auftragsherkunft aktivieren

Damit du Artikel mit Etsy verknüpfen kannst, muss im Menü **Einrichtung » Aufträge » Auftragsherkunft** die Auftragsherkunft Etsy aktiviert werden.

##### Auftragsherkunft für Etsy aktivieren:

1. Öffne das Menü **Einrichtung » Aufträge » Auftragsherkunft**.
2. Setze bei **Etsy** ein Häkchen.
3. **Speichere** die Einstellungen.


## Artikelverfügbarkeit einstellen

Artikel, die du auf Etsy verkaufen möchtest, müssen im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID** im Tab **Verfügbarkeit** aktiviert werden.

##### Artikelverfügbarkeit für Etsy einstellen:

1. Öffne das Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID » Tab: Einstellungen**.
2. Aktiviere die Hauptvariante im Bereich **Verfügbarkeit**.
3. Wechsele in das Tab **Verfügbarkeit**.
4. Klicke im Bereich **Märkte** in das Auswahlfeld.<br/>
→ Eine Liste mit allen verfügbaren Märkten wird angezeigt.
5. Aktiviere die Option **Etsy**.
6. Klicke auf **Hinzufügen**.<br/>
→ Der Marktplatz wird hinzugefügt.
7. **Speichere** die Einstellungen.<br/>
→ Der Artikel ist auf Etsy verfügbar.

Die Verfügbarkeit für Varianten kann im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten » Variante öffnen » Tab: Varianten-ID » Tab: Verfügbarkeit** individuell angepasst werden.

Wenn du bereits auf Etsy listest, hinterlege die Etsy Listing-ID im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID » Tab: Einstellungen » Tab: Verfügbarkeit** als SKU für die Herkunft **Etsy**, damit Artikel beim Artikelexport den bereits auf Etsy gelisteten Artikel zugewiesen werden können und somit keine Überverkäufe entstehen. Die Etsy-Listing-ID muss in folgendem Format hinterlegt werden: **Etsy Listing-ID-plentymarkets-Varianten-ID**, zum Beispiel **708093072-3467**.


## Verkaufspreis festlegen

Gehe wie im Folgenden beschrieben vor, um für die Auftragsherkunft Etsy einen Verkaufspreis festzulegen. Dieser Preis wird auf Etsy angezeigt. 

##### Verkaufspreise für Etsy festlegen:

1. Öffne das Menü **Einrichtung » Artikel » Verkaufspreise » Verkaufspreis öffnen » Tab: Einstellungen**.
2. Setze ein Häkchen bei der Herkunft **Etsy**.
3. **Speichere** die Einstellungen.


## Katalog erstellen

Artikeldaten werden über Katalog-Templates zu Etsy übertragen. Damit du deine Artikel zu Etsy übertragen kannst, musst du im Menü **Daten » Kataloge** ein Katalog-Template erstellen. Weitere Informationen zu Katalogen findest du auf der Handbuchseite [Kataloge verwalten](https://knowledge.plentymarkets.com/daten/daten-exportieren/kataloge-verwalten).

Verwende für Etsy das Katalog-Template **Listing - Etsy**.

→ **Hinweis**: Beachte, dass das Menü **Daten » Kataloge** erst sichtbar ist, wenn du ein Plugin installierst, das ein Katalog-Template verfügbar macht.

Damit du deine Artikel zu Etsy exportieren kannst, musst du einen Katalog erstellen. Verknüpfe anschließend im Katalog-Template die von Etsy vorgegebenen Felder mit in plentymarkets hinterlegten Artikeldaten. Die Katalog-Vorlagen werden automatisch einmal täglich zu Etsy exportiert, wenn du unter **Einrichtung » Märkte » Etsy » Einstellungen** den **Artikelexport** aktiviert hast.

#### Katalog erstellen:

1. Öffne das Menü **Daten » Kataloge**.
2. Klicke auf **Katalog erstellen**.
3. Gib einen Namen für den Katalog ein.
4. Wähle das Katalog-Template **Listing - Etsy** aus der Dropdown-Liste.
5. **Speichere** die Einstellungen.<br/>
→ Der Katalog wird erstellt.<br/>
→ Der Katalog wird zur weiteren Bearbeitung geöffnet.

Nachdem du einen Katalog erstellt hast, verknüpfe die Datenfelder des Marktplatzes mit in plentymarkets gespeicherten Artikeldaten. Weitere Informationen zu den plentymarkets Datenquellen, die du zuordnen kannst, findest du im Kapitel [Datenquellen zuordnen](https://knowledge.plentymarkets.com/daten/daten-exportieren/kataloge-verwalten#_datenquellen_zuordnen).

#### Datenfelder im Katalog verknüpfen:

1. Öffne das Menü **Daten » Kataloge**.
2. Klicke auf den erstellten Katalog.<br/>
→ Der Katalog wird geöffnet.<br/>
→ Links werden die Datenfelder des Marktplatzes angezeigt.<br/>
→ **Tipp**: Pflichtfelder sind mit Sternchen gekennzeichnet.
3. Wähle für die Datenfelder des Marktplatzes eine plentymarkets Datenquelle aus der Dropdown-Liste.
4. Um einem Datenfeld eine weitere plentmarkets Datenquelle zuzuordnen, klicke auf **Quelle hinzufügen**.<br/>
→ Eine neue Zeile wird eingeblendet.<br/>
→ **Hinweis**: Auch wenn du einem Datenfeld mehr als eine Datenquelle zuordnest, wird nur ein Wert übertragen. Die Daten werden in der Reihenfolge der Zuordnung geprüft. Wenn also das erste Datenfeld keinen Wert liefert, wird das zweite Datenfeld übertragen usw.
5. Nimm alle gewünschten Zuordnungen vor.<br/>
→ Alle Pflichtfelder müssen mit einer plentymarkets Datenquelle verknüpft werden.
6. **Speichere** die Einstellungen.

#### Besonderheiten des Etsy-Katalog-Templates:

Im Datenfeld **Shop-Abteilung** des **Listing - Etsy**-Templates stehen alle Shop-Abteilungen, die du bei Etsy angelegt hast, in der Dropdown-Liste zur Verfügung und können mit Daten aus plentymarkets verknüpft werden.


## Etsy-Kategorien aktualisieren

Um Etsy-Kategorien in plentymarkets zu aktualisieren, lösche deine Credentials im Menü **Einrichtung » Märkte » Etsy » Authentifizierung** und füge diese neu hinzu.

#### Etsy-Kategorien aktualisieren:

1. Öffne das Menü **Einrichtung » Märkte » Etsy » Authentifizierung**.
2. Klicke auf **Löschen**, um die Credentials zu löschen.
3. Klicke auf das Weltkugel-Icon, um neue Credentials hinzuzufügen.<br/>
→ Du wirst zu Etsy weitergeleitet.
4. Klicke bei Etsy auf **Zugang gewähren**.<br/>
→ Neue Credentials werden erstellt und in plentymarkets hinterlegt.<br/>
→ Die Etsy-Kategorien werden aktualisiert.


## Rechtliche Hinweise übertragen 

Um rechtliche Hinweise an den Marktplatz Etsy zu übertragen, hinterlege diese Hinweise für jede Sprache, für die du dein Sortiment anbietest. Die Hinweise werden der Artikelbeschreibung hinzugefügt. 

##### Rechtliche Hinweise hinterlegen: 

1. Öffne das Menü **Einrichtung » Märkte » Etsy » Rechtliche Hinweise**.<br/> 
→ Das Fenster **Rechtliche Hinweise** wird geöffnet. 
2. Wähle die Sprache aus, für die rechtliche Hinweise hinterlegt werden sollen. 
3. Gib deinen Text ein. 
4. **Speichere** die Einstellungen.


## Zahlungsbestätigung automatisch senden

Richte eine Ereignisaktion ein, um Zahlungsbestätigungen automatisch an Etsy zu senden, nachdem ein Zahlungseingang gebucht wurde.

##### Ereignisaktion einrichten:

1. Öffne das Menü **Einrichtung » Aufträge » Ereignisaktionen**.
2. Klicke auf **Ereignisaktion hinzufügen**.<br/>
→ Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Gib einen Namen ein.
4. Wähle das Ereignis gemäß Tabelle 1.
5. **Speichere** die Einstellungen.
6. Nimm die Einstellungen gemäß Tabelle 1 vor.
7. Setze ein Häkchen bei **Aktiv**.
8. **Speichere** die Einstellungen.

|Einstellung  |Option                                          |Auswahl |
|:---         |:---                                            |:--- |
|**Ereignis** |**Zahlung: Vollständig**                        | |
|**Filter 1** |**Auftrag > Auftragstyp**                       |**Auftrag** |
|**Filter 2** |**Auftrag > Herkunft**                          |**Etsy** |
|**Aktion**   |**Plugin > Zahlungsbestätigung an Etsy senden** | |

_Tab. 1: Zahlungsbestätigungen automatisch an Etsy senden_


## Versandbestätigung automatisch senden

Richte eine Ereignisaktion ein, um Versandbestätigungen automatisch an Etsy zu senden, nachdem ein Warenausgang gebucht wurde.

##### Ereignisaktion einrichten:

1. Öffne das Menü **Einrichtung » Aufträge » Ereignisaktionen**.
2. Klicke auf **Ereignisaktion hinzufügen**.<br/>
→ Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Gib einen Namen ein.
4. Wähle das Ereignis gemäß Tabelle 2.
5. **Speichere** die Einstellungen.
6. Nimm die Einstellungen gemäß Tabelle 2 vor.
7. Setze ein Häkchen bei **Aktiv**.
8. **Speichere** die Einstellungen.

|Einstellung  |Option                                         |Auswahl |
|:---         |:---                                           |:--- |
|**Ereignis** |**Auftragsänderung: Warenausgang gebucht**     | |
|**Filter 1** |**Auftrag > Auftragstyp**                      |**Auftrag** |
|**Filter 2** |**Auftrag > Herkunft**                         |**Etsy** |
|**Aktion**   |**Plugin > Versandbestätigung an Etsy senden** | |

_Tab. 2: Versandbestätigungen automatisch an Etsy senden_


## Benutzerrechte vergeben

Damit Benutzer mit dem Zugang **Backend** das **Etsy-Plugin** nutzen können, müssen Benutzerrechte vergeben werden. Benutzerrechte werden im Menü **Einrichtung » Einstellungen » Benutzer » Rechte » Benutzer** zugewiesen.

 #### Benutzerrechte für Backend-Benutzer vergeben:
 
 1. Öffne das Menü **Einrichtung » Einstellungen » Benutzer » Rechte » Benutzer**.
 2. Nutze die Suchfunktion und öffne den zu bearbeitenden Benutzer.
 3. Klappe das Menü **Berechtigungen: Märkte** auf.
 4. Wähle die Einstellung **Zugangsdaten**.
 5. **Speichere** die Einstellungen.


## Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-etsy/blob/master/LICENSE.md).


## Hinweis

Der Begriff 'Etsy' ist ein Markenzeichen von Etsy, Inc. Diese Anwendung verwendet die Etsy-API, sie wurde jedoch von Etsy weder befürwortet noch zertifiziert.

