<?php
/**
 * Assert for any instance created
 *      if it is changed, either save() or delete() is called on it
 */
class level
{
    // Json db
    private static $LEVELS_FILE_URL = 'levels.json';
    private static $is_connected = false;
    private static $objects;

    private static function connect()
    {
        if (level::$is_connected == false) {
            // Connect
            level::$objects = json_decode(file_get_contents(level::$LEVELS_FILE_URL), true);
            level::$is_connected = true;
        }
    }

    public static function h_all()
    {
        // Connect
        level::connect();
        // All Headers
        $all_level_headers = array();
        foreach (level::$objects as $pk => $level) {
            $_level = new level();
            $_level->get($pk);
            array_push($all_level_headers, $_level->h_format());
        }
        return $all_level_headers;
    }

    // Fields
    public $pk = 0;
    public $name = "";
    public $author = "";
    public $time_stamp = null;
    public $seed = ""; // level's raw seed, by itself a json parsable string

    function __construct()
    {
    }

    public function set($name, $author, $seed)
    {
        $this->name = $name;
        $this->author = $author;
        $this->seed = $seed;
        $this->time_stamp = new DateTime();
    }

    public function get($pk)
    {
        // Connect
        level::connect();
        // Read
        $level = level::$objects[$pk];
        if ($level != null) {
            $this->pk = $pk;
            $this->name = $level['name'];
            $this->author = $level['author'];
            $this->seed = $level['seed'];
            $this->time_stamp = null;
            return true;
        } else {
            return false;
        }
    }

    public function save()
    {
        // Connect
        level::connect();
        // Touch
        if ($this->pk == 0) {
            // Determines Pk
            $this->pk = level::$objects['meta']['new_pk'];
            // Create
            level::$objects[$this->pk] = array(
                'name' => $this->name,
                'author' => $this->author,
                'seed' => $this->seed,
                'time_stamp' => $this->time_stamp);

            // Update meta data
            // Increment the stored new_pk
            level::$objects['meta']['new_pk']++;
            // Increment the stored no_levels
            level::$objects['meta']['no_levels']++;

        } else {
            // Update
            level::$objects[$this->pk] = array(
                'name' => $this->name,
                'author' => $this->author,
                'seed' => $this->seed,
                'time_stamp' => $this->time_stamp);
        }

        // Write
        file_put_contents(level::$LEVELS_FILE_URL, json_encode(level::$objects));
    }

    public function delete()
    {
        // Connect
        level::connect();
        // Delete
        if ($this->pk != 0) {
            unset(level::$objects[$this->pk]);

            // Update meta data
            // Decrement the stored no_levels
            level::$objects['meta']['no_levels']--;

        }
        // Write
        file_put_contents(level::$LEVELS_FILE_URL, json_encode(level::$objects));
    }

    public function h_format()
    {
        return array(
            'pk' => $this->pk,
            'name' => $this->name,
            'author' => $this->author,
            'time_stamp' => $this->time_stamp
        );
    }

    public function b_format()
    {
        return array(
            'pk' => $this->pk,
            'seed' => $this->seed
        );
    }
}
