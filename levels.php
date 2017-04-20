<?php

class Level
{
    // Json db
    private static $LEVELS_FILE_URL = 'levels.json';
    private static $is_connected = false;
    private static $objects;

    private static function connect()
    {
        if (Level::$is_connected == false) {
            // Connect
            Level::$objects = json_decode(file_get_contents(Level::$LEVELS_FILE_URL), true);
            Level::$is_connected = true;
        }
    }

    // Fields
    public $pk = 0;
    public $name = "";
    public $author = "";
    public $time_stamp = null;
    public $data = ""; // level's raw data, by itself a json parsable string

    function __construct()
    {
    }

    public function set($name, $author, $data)
    {
        $this->name = $name;
        $this->author = $author;
        $this->data = $data;
        $this->time_stamp = new DateTime();
    }

    public function read($pk)
    {
        // Connect
        Level::connect();
        // Read
        $level = Level::$objects[$pk];
        if ($level != null) {
            $this->pk = $pk;
            $this->name = $level['name'];
            $this->author = $level['author'];
            $this->data = $level['data'];
            $this->time_stamp = null;
            return true;
        } else {
            return false;
        }
    }

    public function save()
    {
        // Connect
        Level::connect();
        // Touch
        if ($this->pk == 0) {
            // Determines Pk
            $new_pk = Level::$objects['meta']['new_pk'];
            // Create
            Level::$objects[$new_pk] = array(
                'name' => $this->name,
                'author' => $this->author,
                'data' => $this->data,
                'time_stamp' => $this->time_stamp);

            // Update meta data
            // Increment the stored new_pk
            Level::$objects['meta']['new_pk']++;
            // Increment the stored no_levels
            Level::$objects['meta']['no_levels']++;

        } else {
            // Update
            Level::$objects[$this->pk] = array(
                'name' => $this->name,
                'author' => $this->author,
                'data' => $this->data,
                'time_stamp' => $this->time_stamp);
        }

        // Write
        file_put_contents(Level::$LEVELS_FILE_URL, json_encode(Level::$objects));
    }

    public function delete()
    {
        // Connect
        Level::connect();
        // Delete
        if ($this->pk != 0) {
            unset(Level::$objects[$this->pk]);

            // Update meta data
            // Decrement the stored no_levels
            Level::$objects['meta']['no_levels']--;

        }
        // Write
        file_put_contents(Level::$LEVELS_FILE_URL, json_encode(Level::$objects));
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    switch ($_POST['q']) {
        case 'c':
            $new_level = new Level();
            $new_level->set($_POST['name'], $_POST['author'], $_POST['data']);
            $new_level->save();
            break;
        case 'r':
            $old_level = new Level();
            $successful = $old_level->read($_POST['pk']);
            if ($successful)
                echo json_encode($old_level);
            else
                echo 'ObjectNotFound';
            break;
        case 'u':
            $old_level = new Level();
            $successful = $old_level->read($_POST['pk']);
            if ($successful) {
                $old_level->set($_POST['name'], $_POST['author'], $_POST['data']);
                $old_level->save();
            } else
                echo 'ObjectNotFound';
            break;
        case 'd':
            $old_level = new Level();
            $successful = $old_level->read($_POST['pk']);
            if ($successful)
                $old_level->delete();
            else
                echo 'ObjectNotFound';
            break;
        default:
            break;
    }
}