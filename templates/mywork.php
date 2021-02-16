<main>
    <div class="container">
        <div class="wrapper">

            <?php

            printDocumentHere();

            ?>

            <div class="project-gallery">

                <?php

                foreach (getAllProjects() as $projectFileName) {
                    projectCard($projectFileName['fileName']);
                }

                ?>

            </div>


        </div>
    </div>

</main>