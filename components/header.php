<div class="header">

    <div class="container">
        <div class="wrapper">

            <div class="left">
                <nav class="navigation">
                    <?php

                    printHeaderMenuHere();

                    ?>

                </nav>
                <h1><?=$pageTitle?></h1>
                <div class="homelogo"></div>
            </div>

            <div class="right">
                <nav class="socials">
                    <a class="logo-anchor" href="<?=ROOT?>"><div class="smalllogo"></div></a>
                    <div class="divider"></div>
                    <a class="anchor-button" target="_blank" href="<?php echo getConfigValue("firstsociallink") ?>"><?php echo getConfigValue("firstsocialtext") ?></a>
                    <a class="anchor-button" target="_blank" href="<?php echo getConfigValue("secondsociallink") ?>"><?php echo getConfigValue("secondsocialtext") ?></a>
                </nav>
            </div>

        </div>
    </div>

</div>