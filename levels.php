<?php

require 'level.php';

function validate_seed($seed)
{
    try {
        // Matrix coordinate validation
        $r = $seed['order']['r'];
        $c = $seed['order']['c'];
        $flows = $seed['flows'];
        $colors = array($seed['color']);

        // LessThanOrEqualZero
        if ($r <= 0 or $c <= 0 ) {
            return [false, 'Really! An empty matrix?'];
        }
        if (count($flows) <= 0) {
            return [false, 'At least one flow is required'];
        }

        $matrix = array(array());

        for ($i = 0; $i < $c; ++$i) {
            for ($j = 0; $j < $r; ++$j) {
                $matrix[$i][$j] = 0;
            }
        }

        foreach ($flows as $flow) {
            $e1x = $flow['e1']['x'];
            $e1y = $flow['e1']['y'];
            $e2x = $flow['e2']['x'];
            $e2y = $flow['e2']['y'];
            // OutOfBounds
            if ($c <= $e1x or $e1x < 0 or
                $c <= $e2x or $e2x < 0 or
                $r <= $e1y or $e1y < 0 or
                $r <= $e2y or $e2y < 0
            ) {
                return [false, 'Out of bound Points'];
            }
            // OverLaps
            if ($matrix[$e1x][$e1y] > 0) {
                return [false, 'Overlap of endpoints of flows'];
            } else {
                $matrix[$e1x][$e1y]++;
            }
            // OverLaps
            if ($matrix[$e2x][$e2y] > 0) {
                return [false, 'Overlap of endpoints of flows'];
            } else {
                $matrix[$e2x][$e2y]++;
            }
            // Color Validation
            if (in_array($flow['color'], $colors)) {
                return [false, 'Illegal colors'];
            } else {
                array_push($colors, $flow['color']);
            }
        }

    }
    catch (Exception $e) {
        return [false, 'Some error occurred'];
    }

    return json_encode($seed);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    switch ($_POST['q']) {
        // CRUD of level object
        case 'c':
            // Validate seed
            $jo = json_decode($_POST['level'], true);

            $clean_seed = validate_seed($jo['seed']);
            if ($clean_seed[0] == false) {
                header('Location: studio.php?error_msg="'.$clean_seed[1].'"&context="'.addslashes($_POST['level']).'"');
            }
            else {
                $new_level = new level();
                $new_level->set($jo['name'], $jo['author'], $clean_seed);
                $new_level->save();
                header('Location: game.php?level=' . $new_level->pk);
                die();
            }
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