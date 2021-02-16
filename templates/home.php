<main>
    <div class="container">
        <div class="wrapper">

            <div class="left">

                <div class="top">

                    <?php

                        printDocumentHere();

                        //echo $Parsedown->text($fileText);

                    ?>

                </div>

                <div class="bottom">

                    <?php /*actionButton(ROOT."github", "Github", "github.svg"); */ ?>
                    <?php /*actionButton(ROOT."linkedin", "Linked In", "linkedin.svg"); */ ?>

                </div>

            </div>

            <?php if($meta['ShowLatestWork'] != "false") :?>

            <div class="right">

                <h2><?php echo $meta["LatestWorkHeading"]; ?></h2>

                <?php projectCard(getLatestProject()); ?>

                <a class="anchor-button view-all" href="<?= ROOT ?>mywork">View all</a>

            </div>

            <?php endif; ?>

        </div>
    </div>

</main>