<?php

require 'level.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    switch ($_POST['q']) {
        // CRUD of level object
        case 'c':
            $new_level = new level();
            $new_level->set($_POST['name'], $_POST['author'], $_POST['seed']);
            $new_level->save();
            break;
        case 'r':
            $old_level = new level();
            $successful = $old_level->get($_POST['pk']);
            if ($successful)
                echo json_encode($old_level);
            else
                echo 'ObjectNotFound';
            break;
        case 'u':
            $old_level = new level();
            $successful = $old_level->get($_POST['pk']);
            if ($successful) {
                $old_level->set($_POST['name'], $_POST['author'], $_POST['seed']);
                $old_level->save();
            } else
                echo 'ObjectNotFound';
            break;
        case 'd':
            $old_level = new level();
            $successful = $old_level->get($_POST['pk']);
            if ($successful)
                $old_level->delete();
            else
                echo 'ObjectNotFound';
            break;

        // Misc
        // All
        case 'a':
            echo json_encode(level::h_all());
            break;
        default:
            break;
    }
}