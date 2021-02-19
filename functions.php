<?php

define("ROOT", getConfigValue("ROOT"));
define("MAX_DOCUMENT_HEAD_LENGTH", 1000);

// PARSEDOWN.php
// https://github.com/erusev/parsedown
include_once("assets/vendors/parsedown/Parsedown.php");

$Parsedown = new Parsedown();

// PAGE VARIABLES:
$meta = Array();
$fileBody = "";
$fileText = "";
$fileElements = Array();
$uniquePlaceholder = "";

function openPage($folder, $name){

    // USE GLOBAL META VARIBALE
    global $meta;
    global $fileBody;
    global $fileText;
    global $fileElements;
    global $uniquePlaceholder;

    $fileContent = file_get_contents("./usercontent/".$folder."/".$name);

    // GET FILE HEAD/METADATA
    preg_match("/(.)*\[DOCUMENTSTART\]/s", substr($fileContent, 0, MAX_DOCUMENT_HEAD_LENGTH), $matches, PREG_OFFSET_CAPTURE);


    $fileHead = $matches[0][0];



    // REMOVE COMMENTS IN HEAD
    $fileHead = preg_replace("/\/\/(.)*/", "", $fileHead);


    // READ DATA

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


    // GET FILE BODY


    // --- GET DOCUMENTSTARTPOSITION
    preg_match("/\[DOCUMENTSTART\]/s", substr($fileContent, 0, MAX_DOCUMENT_HEAD_LENGTH), $matches, PREG_OFFSET_CAPTURE);
    // 15 => "[DOCUMENTSTART]"
    $startPosition = $matches[0][1] + 15;

    // REMOVING HEAD
    $fileBody = substr($fileContent, $startPosition);


    // GET FILE TEXT

    $uniquePlaceholder = "[ELEMENT_PLACEHOLDER_".uniqid()."]";


    $fileText = preg_replace("/\[ACTION\](.*)\[\/ACTION\]/sU", $uniquePlaceholder, $fileBody);

    // GET FILE ELEMENTS

    preg_match_all("/\[ACTION\](.*)\[\/ACTION\]/sU", $fileContent, $matches, PREG_OFFSET_CAPTURE);

    // 1 => only get inner Text
    $fileElements = $matches[1];

    // getting only text, removing position
    $newFileElementArray = Array();
    foreach ($fileElements as $element) {
        $newFileElementArray[] = $element[0];
    }
    $fileElements = $newFileElementArray;

}

function printDocumentHere() {

    global $fileText;
    global $Parsedown;
    global $fileElements;
    global $uniquePlaceholder;

    $uniquePlaceholder = "<p>".$uniquePlaceholder."</p>";

    $html = $Parsedown->text($fileText);

    foreach ($fileElements as $element) {

        $href = getAttribute("href", $element);
        $href = str_replace("\$ROOT\$", ROOT, $href);
        $text = getAttribute("text", $element);
        $icon = getAttribute("icon", $element);

        $elementHtml = actionButton($href, $text, $icon);

        $html = preg_replace("/".preg_quote($uniquePlaceholder, "/")."/", $elementHtml, $html, 1);

    }

    echo $html;

}

function getAttribute($key, $source) {

    $incasesensitiveKeys = array("showlatestwork", "type");

    if(preg_match("/".$key.":(.)*/i", $source, $matches, PREG_OFFSET_CAPTURE)){

        $out = trim(preg_replace("/".$key.":/i", "", $matches[0][0]));

        if (in_array(strtolower($key), $incasesensitiveKeys))
            return strtolower($out);

        return $out;

    }

    return null;

}

function getConfig($key, $source) {

    if(preg_match("/".$key."::(.)*/i", $source, $matches, PREG_OFFSET_CAPTURE)){

        return trim(preg_replace("/".$key."::/i", "", $matches[0][0]));

    }

    return false;

}

function getConfigValue($key) {

    $config = file_get_contents("./usercontent/config/config.conf");
    return getConfig($key, $config);

}

function getAllProjects() {

    // Alle Dateinamen aus dem Ordner holen
    $files = scandir("./usercontent/projects/");
    // "." und ".." entfernen
    $files = array_slice($files, 2);

    // Daten auslesen und in ein geeignetes Array schreiben
    $filesWithDates = Array();
    foreach($files as $file) {

        $filePath = "./usercontent/projects/" . $file;

        $fileDate =  getAttribute("Date", file_get_contents($filePath));
        $filesWithDates[] = Array(
            'fileName' => $file,
            'fileDate' => $fileDate
        );

    }


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


    return $filesSortedByDates;

}

function getLatestProject() {
    return getAllProjects()[0]['fileName'];
}

function actionButton($href, $text, $icon = null) {

    $html = "";

    if($icon == null) {
        $html .= '<a class="action-button no-icon" href="'.$href.'" target="_blank">';
    } else {
        $html .= '<a class="action-button" href="'.$href.'" target="_blank">';
    }
    $html .= '    <div class="button-wrapper">';

    if($icon != null)
        $html .= '        <div class="icon" style="background-image: url(\''.ROOT.'usercontent/icons/'.$icon.'\')"></div>';

    $html .= '        <span>'.$text.'</span>';
    $html .= '    </div>';
    $html .= '</a>';

    return $html;

}

function projectCard($projectFile) {

    $href = ROOT."project/".substr($projectFile, 0, -3);

    $filePath = "./usercontent/projects/" . $projectFile;

    $fileContent = file_get_contents($filePath);

    $fileTitle =  getAttribute("Title", $fileContent);
    $filePreviewImage = getAttribute("PreviewImage", $fileContent);


    echo '<a class="project-card" href="'.$href.'">';

    echo '    <div class="card-wrapper">';



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

function readMenuConfig() {

    $links = Array();

    $menuConfigFile = "./usercontent/config/menu.conf";

    // READ LINE BY LINE
    // https://stackoverflow.com/a/13246630
    $handle = fopen($menuConfigFile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {

            if(preg_match("/(.)+::(.)+/i", $line)){

                $explodedLine = explode("::", $line, 2);

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

    return $links;

}

function printHeaderMenuHere() {

    $menu = readMenuConfig();

    foreach ($menu as $menuItem) {

        echo '<a class="anchor-button" href="'.$menuItem["href"].'">'.$menuItem['text'].'</a>';

    }

}

function printProjectCardGalleryHere() {

    foreach (getAllProjects() as $projectFileName) {
        projectCard($projectFileName['fileName']);
    }

}