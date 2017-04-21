<?php
require 'level.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>FreeFlow</title>
    <link rel="icon" href="icon.png"/>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
    <style>
        .cell {
            background-color: white;
            outline: 1px solid lightgrey;
        }

        .cell:hover {
            background-color: whitesmoke;
        }

        .matrix {
            outline: 3px solid white;
        }
    </style>
</head>

<body>

<script type="application/javascript" src="game.js"></script>
<script type="application/javascript">
    // Gets references
    var $universe;
    var $level_list;
    var level_pk;
    <?php
    if (isset($_GET['level']))
        echo 'level_pk = ' . $_GET['level'];
    else
        echo 'undefined';
    ?>

    function SyncLevelList() {
        function RenderLevelBar(container, levels) {
            // Empty level-bar
            $(container).empty();
            // Add levels from param
            // Assumption : 1st one is meta data
            for (var i = 1; i < levels.length; i++) {
                var name = levels[i].name;
                var author = levels[i].author;
                var pk = levels[i].pk;

                var _level = _element(
                    ['li', 'a', 'i'],
                    [
                        {
                            'id': pk
                        },
                        {
                            'href': 'game.php?level=' + pk,
                            'class': 'waves-effect waves-teal level'
                        },
                        {
                            'class': 'material-icons'
                        }
                    ],
                    ['', name + "/" + author, 'play_arrow'],
                    [true, true, true]
                );
                $(container).append(_level);
            }

            $(container).find('#' + level_pk).css({
                'background-color': 'lightblue'
            });
        }

        $.ajax({
            url: 'levels.php',
            type: 'POST',
            data: {
                q: 'a'
            },
            error: function () {
                alert('Error while fetching level_list');
            },
            success: function (r) {
                r = JSON.parse(r);
                RenderLevelBar($level_list, r);
            }
        });

    }

    $(document).ready(function () {
        // Gets references
        $universe = $("#universe");
        $level_list = $('#level_list');

        // Materialize CSS
        $(".button-collapse").sideNav();
        $('.modal').modal();

        // Sync level list
        SyncLevelList();

        // Start game
        <?php
        $old_level = new level();
        $successful = $old_level->get($_GET['level']);
        if ($successful)
            echo 'StartFromSeed($universe,' . $old_level->seed . ');';
        else {
            echo 'Materialize.toast("Select a level ... ", 5000);';
            echo '$(\'.button-collapse\').sideNav(\'show\');';
        }
        ?>

    });

</script>

<!-- NAV BAR -->
<ul id="level-bar" class="side-nav" style="overflow-y: auto">
    <li><a href="#" class="waves-effect waves-yellow"><i class="material-icons">store</i>Levels</a></li>
    <li class="divider" style="margin: 0;"></li>
    <div id="level_list"></div>
</ul>

<a href="#"
   id="level-bar-fab"
   data-activates="level-bar"
   class="btn-floating btn-large button-collapse z-depth-4"
   style="position: fixed;bottom: 0;left: 0;margin: 20px;z-index: 200">
    <i class="material-icons">view_carousel</i>
</a>

<!-- UNIVERSE -->
<div id="universe" class="grey lighten-2" style="width: 100vw;height: 100vh;"></div>


<!--HELP-->
<a class="modal-trigger waves-effect waves-light btn-floating btn-large"
   href="#help"
   style="position:fixed;bottom: 20px;right: 20px">
    <i class="material-icons">help</i>
</a>

<div id="help" class="modal bottom-sheet">
    <div class="modal-content">
        <h4>How to play?</h4>
        <p>See those colored cells, You need to connect the pairs</p>
        <p>Click on any colored cells, and build it using arrow keys</p>
        <p>A completed path is called a Flow</p>
        <p>No two flows can intersect</p>
        <p>That's it!!</p>
        <p>Complete all the levels</p>
        <p>Create your own levels using Flow Studio</p>
        <p>Have fun!</p>
        <br>
        <p>PS: as of now you can only play with keyboard</p>
    </div>
    <div class="modal-footer">
        <a href="#" class="modal-action modal-close waves-effect waves-green btn-flat ">CLOSE</a>
    </div>
</div>

</body>
</html>