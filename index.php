<?php

// functions.php einbinden
include_once("functions.php");

// falls GET-Parameter "open" existiert
// Also bei z.B. domain.com/OPEN/xy
if(isset($_GET['open'])) {

    $redirects = file_get_contents("./usercontent/config/redirects.conf");

    // Falls es diesen Open-Link gibt, dann...
    if($redirectURL = getConfig($_GET['open'], $redirects)) {
        // ... wird man zu dem verknüpften Link weitergeleitet
        header("Location: " . $redirectURL);
    } else {
        // falls nicht, wird man zur Startseite weitergeleitet
        header("Location: " . ROOT);
    }

}




// Speichert Datei-Ordner und Datei-Name vom angeforderten Dokument
$preparedFileName = null;
$preparedFileFolder = null;

// falls GET-Parameter "fixedpage" existiert
// Also bei z.B. domain.com/mywork, domain.com/contact, domain.com/legal-notice ODER domain.com/privary-policy
if(isset($_GET['fixedpage'])) {

    $fixedPages = array("contact", "mywork", "privacy-policy", "legal-notice");

    $requestedPage = strtolower($_GET['fixedpage']);

    if (in_array($requestedPage, $fixedPages)) {
        // Wenn eine valide "fixedpage" angefordert wurde:

        // ... wird Datei-Ordner und Datei-Name vom angeforderten Dokument gespeichert
        $preparedFileName = $requestedPage . ".md";
        $preparedFileFolder = "fixedpages";

    } else {

        // falls nicht, wird man zur Startseite weitergeleitet
        header("Location: " . ROOT);

    }

// falls GET-Parameter "custompage" existiert
// Also bei z.B. domain.com/PAGE/xy
} elseif (isset($_GET['custompage'])) {

    // Datei-Ordner und Datei-Name vom angeforderten Dokument gespeichert
    $preparedFileName = $_GET['custompage'].".md";
    $preparedFileFolder = "pages";

    // .. falls diese Datei aber nicht existieren sollte ...
    if(!file_exists("./usercontent/".$preparedFileFolder."/".$preparedFileName)) {

        // ... wird man zur Startseite weitergeleitet
        header("Location: " . ROOT);

    }

// falls GET-Parameter "customproject" existiert
// Also bei z.B. domain.com/PROJECT/xy
} elseif (isset($_GET['customproject'])) {

    // Datei-Ordner und Datei-Name vom angeforderten Dokument gespeichert
    $preparedFileName = $_GET['customproject'].".md";
    $preparedFileFolder = "projects";

    // .. falls diese Datei aber nicht existieren sollte ...
    if(!file_exists("./usercontent/".$preparedFileFolder."/".$preparedFileName)) {

        // ... wird man zur Startseite weitergeleitet
        header("Location: " . ROOT);

    }

}

// Falls keine obigen GET-Parameter existieren, und die beiden unteren Variablen immer noch = null sind...
if($preparedFileName === null || $preparedFileFolder === null) {
    // ... dann wird die Startseite angefordert
    $preparedFileName = "home.md";
    $preparedFileFolder = "fixedpages";
}

// angefordertes Dokument wird geöffnet
openPage($preparedFileFolder, $preparedFileName);

// Meta-Daten werden ausgelesen
$pageType = $meta['Type']; // Seitentyp
$pageTitle = $meta['Title']; // Seitentitel

// Config-Daten werden ausgelesen
$headTitle = getConfigValue("headtitle"); // Webseitentitel, welcher im Browser-Tab-Titel stehen wird

// Falls nicht die Startseite aufgerufen wird, wird der Titel des Dokumentes an den Webseitentitel hinzugefügt
$pageTitleMeta = $headTitle;
if($pageType != "home") {
    $pageTitleMeta = $headTitle . " — " . $pageTitle;
}

?>

<!DOCTYPE html>
<html lang="de">

    <head>

        <!-- Html Metadaten: Charset, Viewport, Icons, Stylesheet und Title -->

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, user-scalable=no">

        <!-- Favicons: https://www.emergeinteractive.com/insights/detail/the-essentials-of-favicons/ -->

        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-32.png" sizes="32x32">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-57.png" sizes="57x57">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-76.png" sizes="76x76">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-96.png" sizes="96x96">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-128.png" sizes="128x128">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-192.png" sizes="192x192">
        <link rel="icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-228.png" sizes="228x228">

        <link rel="shortcut icon" sizes="196x196" href=“<?=ROOT?>usercontent/fixedlogos/favicons/favicon-196.png">

        <link rel="apple-touch-icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-120.png" sizes="120x120">
        <link rel="apple-touch-icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-152.png" sizes="152x152">
        <link rel="apple-touch-icon" href="<?=ROOT?>usercontent/fixedlogos/favicons/favicon-180.png" sizes="180x180">

        <link rel="stylesheet" type="text/css" href="<?=ROOT?>assets/style/app.css"/>
        <title><?=$pageTitleMeta?></title>

    </head>

    <body class="page--<?=$pageType?>">

        <!-- Header-Template aufrufen -->
        <?php include_once("components/header.php"); ?>

        <!-- Body-Template aufrufen -->
        <?php

        // Je nach Seitentyp, wird ein anderes Template aufgerufen

        if($pageType == "home") {
            include_once("templates/home.php");
        } elseif ($pageType == "article" || $pageType == "legal") {
            include_once("templates/article.php");
        } elseif ($pageType == "mywork") {
            include_once("templates/mywork.php");
        } elseif ($pageType == "contact") {
            include_once("templates/contact.php");
        }

        ?>

        <!-- Footer-Template aufrufen -->
        <?php include_once("components/footer.php"); ?>

    </body>
</html>