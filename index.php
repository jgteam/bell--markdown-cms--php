<?php

include_once("functions.php");

if(isset($_GET['open'])) {

    $redirects = file_get_contents("./usercontent/config/redirects.conf");

    if($redirectURL = getConfig($_GET['open'], $redirects)) {
        header("Location: " . $redirectURL);
    } else {
        header("Location: " . ROOT);
    }

}





$preparedFileName = null;
$preparedFileFolder = null;

if(isset($_GET['fixedpage'])) {

    $fixedPages = array("contact", "mywork", "privacy-policy", "legal-notice");

    $requestedPage = strtolower($_GET['fixedpage']);

    if (in_array($requestedPage, $fixedPages)) {

        $preparedFileName = $requestedPage.".md";
        $preparedFileFolder = "fixedpages";

    } else {

        header("Location: " . ROOT);

    }

} elseif (isset($_GET['custompage'])) {

    $preparedFileName = $_GET['custompage'].".md";
    $preparedFileFolder = "pages";

    if(!file_exists("./usercontent/".$preparedFileFolder."/".$preparedFileName)) {

        header("Location: " . ROOT);

    }

} elseif (isset($_GET['customproject'])) {

    $preparedFileName = $_GET['customproject'].".md";
    $preparedFileFolder = "projects";

    if(!file_exists("./usercontent/".$preparedFileFolder."/".$preparedFileName)) {

        header("Location: " . ROOT);

    }

}

if($preparedFileName === null || $preparedFileFolder === null) {
    $preparedFileName = "home.md";
    $preparedFileFolder = "fixedpages";
}

openPage($preparedFileFolder, $preparedFileName);


$pageType = $meta['Type'];
$pageTitle = $meta['Title'];

$headTitle = getConfigValue("headtitle");

$pageTitleMeta = $headTitle;
if($pageType != "home") {

    $pageTitleMeta = $headTitle." — " . $pageTitle;

}

?>

<!DOCTYPE html>
<html lang="de">

    <head>
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




        <link rel="stylesheet" type="text/css" href="assets/style/app.css"/>
        <title><?=$pageTitleMeta?></title>

    </head>

    <body class="page--<?=$pageType?>">

        <?php include_once("components/header.php"); ?>

        <?php

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


        <?php include_once("components/footer.php"); ?>




    </body>
</html>