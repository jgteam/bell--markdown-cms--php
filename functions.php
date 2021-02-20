<?php

// ROOT-Konstante: Root-URL (z.B.: https://domain.com/)
define("ROOT", getConfigValue("ROOT"));

// MAX_DOCUMENT_HEAD_LENGTH-Konstante: Maximale Anzahl an Zeichen, welche der Dokumentenkopf haben darf.
// Das verhindert aussetzungen des Head-Parsers durch zu lange Zeichenketten.
define("MAX_DOCUMENT_HEAD_LENGTH", 1000);

// Einbindung bon Parsedown.php
// Wird genutzt um später Markdown in HTML umzuwandeln.
// https://github.com/erusev/parsedown
include_once("assets/vendors/parsedown/Parsedown.php");
$Parsedown = new Parsedown();


// Variablen in welchen Informationen über die aktuelle Seite gespeichert wird.
$meta = Array(); // Metadaten wie z.B. der Titel
$fileBody = ""; // Zeichenkette des Dokumenten-Body (also ohne den Head)
$fileText = ""; // Zeichenkette des Dokumenten-Body ohne Element-Blöcke, stattdessen mit Platzhaltern (also ohne z.B. Action-Buttons und Head)
$fileElements = Array(); // Speichert alle im Dokument vorhandenen Element-Blöcke
$uniquePlaceholder = ""; // Speichert den einzigartigen Platzhalter


// Öffnet die angeforderte Seite. (z.B. openPage("fixedpages", "home.md");)
function openPage($folder, $name){

    // Gewährt Zugriff und Schreibrechte auf die schon definierten Variablen
    global $meta;
    global $fileBody;
    global $fileText;
    global $fileElements;
    global $uniquePlaceholder;

    // Kompletter Inhalt des Dokumentes
    $fileContent = file_get_contents("./usercontent/".$folder."/".$name);

    // Dokumenten-Head auslesen
    preg_match("/(.)*\[DOCUMENTSTART\]/s", substr($fileContent, 0, MAX_DOCUMENT_HEAD_LENGTH), $matches, PREG_OFFSET_CAPTURE);
    $fileHead = $matches[0][0];

    // Kommentare aus dem Head entfernen
    $fileHead = preg_replace("/\/\/(.)*/", "", $fileHead);


    // Metadaten auslesen

    //Title
    $meta['Title'] = getAttribute("Title", $fileHead);
    //Date
    $meta['Date'] = getAttribute("Date", $fileHead);
    //ShowLatestWork
    $meta['ShowLatestWork'] = getAttribute("ShowLatestWork", $fileHead);
    //LatestWorkHeading
    $meta['LatestWorkHeading'] = getAttribute("LatestWorkHeading", $fileHead);
    //Type
    $meta['Type'] = getAttribute("Type", $fileHead);


    // Dokumenten-Body auslesen


    // Startposition herausfinden
    preg_match("/\[DOCUMENTSTART\]/s", substr($fileContent, 0, MAX_DOCUMENT_HEAD_LENGTH), $matches, PREG_OFFSET_CAPTURE);
    // Startposition + 15 => wegen "[DOCUMENTSTART]"
    $startPosition = $matches[0][1] + 15;

    // Head entfernen
    $fileBody = substr($fileContent, $startPosition);


    // Dokumenten-Text auslesen

    // Note: Die Platzhalter werden genutzt, um später den Platzhalter mit dem generierten HTML-Code zu ersetzen

    // Platzhalter erstellen
    $uniquePlaceholder = "[ELEMENT_PLACEHOLDER_".uniqid()."]";

    // Element-Blöcke durch den Platzhalter ersetzten
    $fileText = preg_replace("/\[ACTION\](.*)\[\/ACTION\]/sU", $uniquePlaceholder, $fileBody);

    // Element-Blöcke auslesen
    preg_match_all("/\[ACTION\](.*)\[\/ACTION\]/sU", $fileContent, $matches, PREG_OFFSET_CAPTURE);
    $fileElements = $matches[1];

    // Array umschreiben, sodass nicht mehr die Startposition gespeichert wird, sondern nur noch der Text
    $newFileElementArray = Array();
    foreach ($fileElements as $element) {
        $newFileElementArray[] = $element[0];
    }
    $fileElements = $newFileElementArray;

    // Kein Rückgabewert, da alles in den Variablen gespeichert wurde

}

// Druckt das Dokument an dieser Stelle
function printDocumentHere() {

    // Gewährt Zugriff und Schreibrechte auf die schon definierten Variablen
    global $fileText;
    global $Parsedown;
    global $fileElements;
    global $uniquePlaceholder;

    // Platzhalter um Paragraphen-HTML-Tags erweitern
    $uniquePlaceholder = "<p>".$uniquePlaceholder."</p>";

    // HTML aus dem MarkdownText generieren
    $html = $Parsedown->text($fileText);

    // Jedes Element ersetzt nun nach der Reihe einen Platzhalter
    foreach ($fileElements as $element) {

        // Attribute des Elementes auslesen
        $href = getAttribute("href", $element);
        $href = str_replace("\$ROOT\$", ROOT, $href);
        $text = getAttribute("text", $element);
        $icon = getAttribute("icon", $element);

        // Generiert den Aktion-Button (aktuell einziges verfügbares Element-Block)
        $elementHtml = actionButton($href, $text, $icon);

        // Ersetzt den nächsten Platzhalter mit dem Aktionbutton
        $html = preg_replace("/".preg_quote($uniquePlaceholder, "/")."/", $elementHtml, $html, 1);

    }

    // Druckt das HTML
    echo $html;

}

// Filtert ein Attribut-Wert aus einem Source-String mittels des Attributen-Schlüssels
function getAttribute($key, $source) {

    // Attribute, welche Groß- und Kleinschreibung nicht beachten müssen (werden später in Kleinbuchstaben umgewandelt)
    $incasesensitiveKeys = array("showlatestwork", "type");

    // Nach dem Attribut mittels des Schlüssels suchen
    if(preg_match("/".$key.":(.)*/i", $source, $matches, PREG_OFFSET_CAPTURE)){

        // Attributwert Filtern
        $out = trim(preg_replace("/".$key.":/i", "", $matches[0][0]));

        // Wert in Kleinbuchstaben umwandeln, falls erwünscht
        if (in_array(strtolower($key), $incasesensitiveKeys))
            return strtolower($out);

        // Wert zurückgeben
        return $out;

    }

    // Null zurückgeben, falls es kein Treffer gab
    return null;

}

// Filtert ein Config-Attribut-Wert aus einem Source-String mittels des Attributen-Schlüssels
// (Unterschied zu getAttribute(): Hier wird des Schlüssel mit zwei Doppelpunkten vom Wert getrennt)
function getConfig($key, $source) {

    if(preg_match("/".$key."::(.)*/i", $source, $matches, PREG_OFFSET_CAPTURE)){

        return trim(preg_replace("/".$key."::/i", "", $matches[0][0]));

    }

    return false;

}

// Filtert ein Config-Attribut-Wert aus den Config.conf-Datei mittels des Attributen-Schlüssels
function getConfigValue($key) {

    $config = file_get_contents("./usercontent/config/config.conf");
    return getConfig($key, $config);

}

// Liefert alles Projektnamen (Dateinamen) und Projektdaten (Datum) in einem Array zurück (nach Datum sortiert)
function getAllProjects() {

    // Alle Dateinamen aus dem Ordner holen
    $files = scandir("./usercontent/projects/");
    // "." und ".." entfernen
    $files = array_slice($files, 2);

    // Daten auslesen und in ein geeignetes Array schreiben (gespeichert werden Dateinamen und Datum)
    $filesWithDates = Array();
    foreach($files as $file) {

        $filePath = "./usercontent/projects/" . $file;

        $fileDate =  getAttribute("Date", file_get_contents($filePath));
        $filesWithDates[] = Array(
            'fileName' => $file,
            'fileDate' => $fileDate
        );

    }

    // Sortieralgorithmus, welcher nach einem Array-Key den Array sortieren kann
    // https://stackoverflow.com/a/2699110
    function aasort (&$array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
    }

    // Array nach dem Datumsschlüssel (fileDate) sortieren
    aasort($filesWithDates,"fileDate");

    // Array umkehren, damit der neuste Eintrag an erster Stelle steht
    $filesSortedByDates = array_reverse($filesWithDates);

    // Array zurückgeben
    return $filesSortedByDates;

}

// Gibt den Dateinamen des neusten Projektes zurück
function getLatestProject() {
    return getAllProjects()[0]['fileName'];
}

// Generiert das HTML für einen Aktionbutton
function actionButton($href, $text, $icon = null) {

    $html = "";

    // "no-icon"-Klasse, wenn kein Icon übergeben wurde
    if($icon == null) {
        $html .= '<a class="action-button no-icon" href="'.$href.'" target="_blank">';
    } else {
        $html .= '<a class="action-button" href="'.$href.'" target="_blank">';
    }
    $html .= '    <div class="button-wrapper">';

    // Icon platzieren, falls eins übergeben wurde
    if($icon != null)
        $html .= '        <div class="icon" style="background-image: url(\''.ROOT.'usercontent/icons/'.$icon.'\')"></div>';

    $html .= '        <span>'.$text.'</span>';
    $html .= '    </div>';
    $html .= '</a>';

    // HTML zurückgeben
    return $html;

}

// Generiert das HTML für eine Projektkarte und gibt den Text direkt aus
function projectCard($projectFile) {

    // Link zu dem Projekt
    $href = ROOT."project/".substr($projectFile, 0, -3);

    // Pfad zu der Markdown-Datei des Projektes
    $filePath = "./usercontent/projects/" . $projectFile;

    // Dokumentinhalt des Projektes
    $fileContent = file_get_contents($filePath);

    // Titel und Vorschaubild auslesen
    $fileTitle =  getAttribute("Title", $fileContent);
    $filePreviewImage = getAttribute("PreviewImage", $fileContent);

    //... HTML generieren

    echo '<a class="project-card" href="'.$href.'">';

    echo '    <div class="card-wrapper">';


    // Fügt das Logo anstelle eines Vorschaubildes ein, falls keins vorhanden ist
    if($filePreviewImage === null) {

        $filePreviewImage = ROOT."usercontent/fixedlogos/smalllogo.svg";
        echo '        <div class="card-preview-container"><div class="card-preview empty" style="background-image: url(\''.$filePreviewImage.'\')"></div></div>';

    } else {

        $filePreviewImage = ROOT."usercontent/images/".$filePreviewImage;
        echo '        <div class="card-preview-container"><div class="card-preview" style="background-image: url(\''.$filePreviewImage.'\')"></div></div>';

    }




    echo '        <h3 class="card-title">'.$fileTitle.'</h3>';
    echo '    </div>';
    echo '</a>';

}

// Liest die Menu.conf-Datei aus
function readMenuConfig() {

    // Beinhaltet später die Links
    $links = Array();

    $menuConfigFile = "./usercontent/config/menu.conf";

    // List die Datei Zeile für Zeile ein
    // https://stackoverflow.com/a/13246630
    $handle = fopen($menuConfigFile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            // ... jede Zeile:


            if(preg_match("/(.)+::(.)+/i", $line)){

                // Trennt den Text vom Link
                $explodedLine = explode("::", $line, 2);

                // Schreibt den Text und den Link in ein Array, welcher im $links-Array gespeichert wird
                $links[] = Array(
                    'text' => $explodedLine[0],
                    'href' => str_replace("\$ROOT\$", ROOT, $explodedLine[1])
                );

            }

        }

        fclose($handle);
    } else {
        return null;
    }

    // Gibt die Links zurück
    return $links;

}

// Druckt das Headermenu an dieser Stelle
function printHeaderMenuHere() {

    // Beinhaltet alle Links
    $menu = readMenuConfig();

    foreach ($menu as $menuItem) {

        // Generiert alle Links
        echo '<a class="anchor-button" href="'.$menuItem["href"].'">'.$menuItem['text'].'</a>';

    }

}

// Druckt die Projekt-Galerie
function printProjectCardGalleryHere() {

    // ... für alle Projekte
    foreach (getAllProjects() as $projectFileName) {

        // Generiert eine ProjektCard
        projectCard($projectFileName['fileName']);

    }

}