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
        #flows input {
            color: black;
            margin: 0;
        }
    </style>
</head>

<body>

<div class="row" style="margin: 0">

    <div class="col l6 m12 s12">
        <div id="matrix_mould_div"
             class="teal"
             style="width: 100%;height: 100vh"></div>
    </div>

    <div class="col l6 m12 s12">

        <div id="headings" class="row" style="margin: 20px;display: none">
            <span class="flow-text" id="name_h"></span>
            <span class="">/</span>
            <span class="pink-text" id="author_h"></span>
        </div>

        <div style="position: relative;top: 30vh;">
            <form action="levels.php" method="post" id="c_form">
                <input type="text" name="q" value="c" hidden/>
                <input id="c_form_level" type="text" name="level" hidden/>
            </form>

            <script type="application/javascript" src="studio.js"></script>
            <script type="application/javascript">
                var $matrix_mould_div;
                var matrix_mould;
                var $citation_div;
                var $matrix_meta_data_div;
                var $next_btn;
                var $submit;
                var $rows;
                var $columns;
                var $m_color;
                var $f_color;
                var $flows;
                var $add_flow;
                var $e1;
                var $e2;
                var $e1x;
                var $e1y;
                var $e2x;
                var $e2y;

                function hex_to_rgb(hex) {
                    var mul_map = {
                        '0': 0,
                        '1': 1,
                        '2': 2,
                        '3': 3,
                        '4': 4,
                        '5': 5,
                        '6': 6,
                        '7': 7,
                        '8': 8,
                        '9': 9,
                        'a': 10,
                        'b': 11,
                        'c': 12,
                        'd': 13,
                        'e': 14,
                        'f': 15
                    };
                    var red = mul_map[hex.charAt(1)] * 16 + mul_map[hex.charAt(2)];
                    var green = mul_map[hex.charAt(3)] * 16 + mul_map[hex.charAt(4)];
                    var blue = mul_map[hex.charAt(5)] * 16 + mul_map[hex.charAt(6)];

                    if (isNaN(red) || isNaN(green) || isNaN(blue)) {
                        return false;
                    }
                    else {
                        return 'rgb(' + red + ', ' + green + ', ' + blue + ')'
                    }
                }

                // Assert format rgb(r, g, b)
                function rgb_to_hex(rgb) {
                    var values = rgb.split(", ");
                    var red = Number(values[0].substring(4, values[0].length));
                    var green = Number(values[1]);
                    var blue = Number(values[2].substring(0, values[2].length - 1));

                    if (red < 10) {
                        red = '0' + red.toString(16);
                    } else {
                        red = red.toString(16);
                    }

                    if (green < 10) {
                        green = '0' + green.toString(16);
                    } else {
                        green = green.toString(16);
                    }

                    if (blue < 10) {
                        blue = '0' + blue.toString(16);
                    } else {
                        blue = blue.toString(16);
                    }

                    return '#' + red + green + blue;
                }

                function flash_effect($obj) {
                    $($obj).css({
                        'background-color': 'yellow'
                    });
                }

                // Alternate setting of flow end points by click on matrix mould
                var e1_e2 = 1;
                function set_e1_e2_coordinates(coordinates) {
                    if (e1_e2 === 1) {
                        // set e1
                        $($e1x).val(coordinates.x);
                        $($e1y).val(coordinates.y);
                        // show indication
                        flash_effect($e1);
                        // switch to e2
                        e1_e2 = 2;
                    }
                    else if (e1_e2 === 2) {
                        // set e2
                        $($e2x).val(coordinates.x);
                        $($e2y).val(coordinates.y);
                        // show indication
                        flash_effect($e2);
                        // switch to e1
                        e1_e2 = 1;
                    }
                }

                $(document).ready(function () {
                    $matrix_mould_div = $('#matrix_mould_div');
                    $citation_div = $('#citation_div');
                    $matrix_meta_data_div = $('#matrix_meta_data_div');
                    $next_btn = $('#next_btn');
                    $submit = $('#submit');
                    $rows = $('#rows');
                    $columns = $('#columns');
                    $m_color = $('#m_color');
                    $f_color = $('#f_color');
                    $flows = $('#flows');
                    $add_flow = $('#add_flow');
                    $e1 = $('#e1');
                    $e2 = $('#e2');
                    $e1x = $('#e1x');
                    $e1y = $('#e1y');
                    $e2x = $('#e2x');
                    $e2y = $('#e2y');

                    var level_data = {
                        name: "",
                        author: "",
                        seed: {
                            order: {},
                            color: "",
                            flows: []
                        }
                    };

                    function add_flow(e1, e2, color) {
                        // Validate new flow
                        for (var i = 0; i < level_data.seed.flows.length; i++) {
                            var ith_e1 = level_data.seed.flows[i].e1;
                            var ith_e2 = level_data.seed.flows[i].e2;
                            var ith_color = level_data.seed.flows[i].color;

                            if (e1.x === ith_e1.x && e1.y === ith_e1.y) {
                                Materialize.toast("Overlap of endpoints!", 2000);
                                return;
                            }

                            if (e2.x === ith_e2.x && e2.y === ith_e2.y) {
                                Materialize.toast("Overlap of endpoints!", 2000);
                                return;
                            }

                            if (e2.x === ith_e1.x && e2.y === ith_e1.y) {
                                Materialize.toast("Overlap of endpoints!", 2000);
                                return;
                            }

                            if (e1.x === ith_e2.x && e1.y === ith_e2.y) {
                                Materialize.toast("Overlap of endpoints!", 2000);
                                return;
                            }

                            if (color === ith_color) {
                                Materialize.toast("Illegal colors", 2000);
                                return;
                            }
                        }

                        if (color === level_data.seed.color) {
                            Materialize.toast("Illegal colors", 2000);
                            return;
                        }

                        level_data.seed.flows.push({
                            e1: e1,
                            e2: e2,
                            color: color
                        });

                        matrix_mould.get_cell(e1).mark_flow_end_point(color);
                        matrix_mould.get_cell(e2).mark_flow_end_point(color);
                    }

                    /**
                     * Abstraction for sequential form fill up
                     * Initialize form maker with states
                     * Start form maker by calling start() method
                     * On next button stimulus call go_to_next_state() method
                     * */
                    var form_maker = {
                        /**
                         * States : array of state objects
                         * State : 2 arrays of elements and 1 callback function
                         * 1st array = elements to be hidden
                         * 2st array = elements to be shown
                         * callback = a function called on next event,
                         *          If current state is not last state
                         *              next state is reached only when current callback returns true,
                         *          Else if current state is last state
                         *              callback's return value is ignored
                         * */
                        states: [
                            [
                                [$matrix_meta_data_div, $flows],
                                [$citation_div, $next_btn],
                                function () {
                                    var name = $('#name').val();
                                    var author = $('#author').val();
                                    // add to json object
                                    level_data.name = name;
                                    level_data.author = author;
                                    // indicate values
                                    $('#name_h').html(name);
                                    $('#author_h').html(author);
                                    $('#headings').show();
                                    return true;
                                }
                            ],
                            [
                                [$citation_div],
                                [$matrix_meta_data_div],
                                function () {
                                    var r = $($rows).val();
                                    var c = $($columns).val();
                                    var color = hex_to_rgb($($m_color).val());
                                    // Validate r, c, color
                                    if (color === false) {
                                        Materialize.toast("Illegal Color", 2000);
                                        return false;
                                    }
                                    if (r <= 0 || c <= 0) {
                                        Materialize.toast("Seriously?! Empty Matrix?", 2000);
                                        return false;
                                    }
                                    // add to json object
                                    level_data.seed.order = {
                                        r: r,
                                        c: c
                                    };
                                    level_data.seed.color = color;
                                    // Create a matrix mould
                                    matrix_mould = new MatrixMould(
                                        $matrix_mould_div,
                                        {
                                            r: r,
                                            c: c
                                        },
                                        color
                                    );
                                    // Create listeners
                                    // Click listener on cells
                                    $('#' + matrix_mould.id).on(
                                        'click',
                                        '.cell',
                                        function () {
                                            set_e1_e2_coordinates(matrix_mould.get_coordinates($(this).attr('id')));
                                        }
                                    );
                                    // Click listener on add
                                    $($add_flow).on(
                                        'click',
                                        function () {
                                            // Limit on flows
                                            if (level_data.seed.flows.length > 15) {
                                                Materialize.toast("Too many flows", 2000);
                                                return;
                                            }
                                            var e1 = {
                                                x: $($e1x).val(),
                                                y: $($e1y).val()
                                            };
                                            var e2 = {
                                                x: $($e2x).val(),
                                                y: $($e2y).val()
                                            };
                                            var color = hex_to_rgb($($f_color).val());
                                            if (color === false) {
                                                Materialize.toast("Illegal Color", 2000);
                                                return;
                                            }
                                            add_flow(e1, e2, color);
                                        }
                                    );
                                    // Change next btn symbol to submit
                                    $($next_btn).find('i').html('done');
                                    $($next_btn).addClass('blue');
                                    return true;
                                }
                            ],
                            [
                                [$matrix_meta_data_div],
                                [$flows],
                                function () {
                                    // Global validations
                                    if (level_data.seed.flows.length <= 0) {
                                        Materialize.toast("At least one flow required", 2000);
                                        return;
                                    }
                                    $('#c_form_level').val(JSON.stringify(level_data));
                                    $('#c_form').submit();
                                }
                            ]
                        ],
                        state: -1,
                        apply_state: function () {
                            // callback from previous
                            var hide_e = form_maker.states[form_maker.state][0];
                            var show_e = form_maker.states[form_maker.state][1];
                            // hide
                            for (var j = 0; j < hide_e.length; j++) {
                                $(hide_e[j]).hide();
                            }
                            // show
                            for (var i = 0; i < show_e.length; i++) {
                                $(show_e[i]).show();
                            }
                        },
                        start: function () {
                            if (form_maker.states.length > 0) {
                                form_maker.state = 0;
                                form_maker.apply_state();
                            }
                        },
                        go_to_next_state: function () {
                            if (form_maker.state !== -1) {
                                // If the present state is not the last state
                                if (form_maker.state + 1 < form_maker.states.length) {
                                    // callback from the current state
                                    var validated = form_maker.states[form_maker.state][2]();
                                    // if current state validated
                                    if (validated === true) {
                                        // go to next state
                                        form_maker.state = form_maker.state + 1;
                                        // apply next state
                                        form_maker.apply_state();
                                    }
                                }
                                // Else If the present state is the last state
                                else if (form_maker.state + 1 === form_maker.states.length) {
                                    // callback from the current state (which is last state of states)
                                    // finishing call back
                                    form_maker.states[form_maker.state][2]();
                                }
                            }
                        }
                    };

                    form_maker.start();
                    $($next_btn).click(function () {
                        form_maker.go_to_next_state();
                    });
                });
            </script>
            <!--NEXT-->
            <a href="#"
               id="next_btn"
               class="btn-floating btn-large waves-effect pink waves-light"
               style="position:absolute;right: 20px">
                <i class="material-icons">keyboard_arrow_right</i>
            </a>

            <!--CITATION-->
            <div id="citation_div" class="row">
                <div class="input-field col l4 m4 s4">
                    <input id="name" type="text" value="New One!" class="validate">
                    <label for="name">name</label>
                </div>
                <div class="input-field col l4 m4 s4">
                    <input id="author" type="text" value="John Doe" class="validate">
                    <label for="author">Author</label>
                </div>
            </div>

            <!--MATRIX-->
            <div id="matrix_meta_data_div" class="row">
                <div class="input-field col l4 m4 s4">
                    <input id="rows" type="number" value="5" class="validate">
                    <label for="rows">Rows</label>
                </div>
                <div class="input-field col l4 m4 s4">
                    <input id="columns" type="number" value="5" class="validate">
                    <label for="columns">Columns</label>
                </div>
                <div class="input-field col l4 m4 s4">
                    <input id="m_color" type="color" value="#ffffff" style="width: 80px;height: 40px">
                </div>
            </div>

            <!--FLOWS-->
            <div id="flows">
                <!--COUNT-->
                <div class="row">
                    <div class="col l3 m3 s3 flow-text">
                        <span id="no_flows">0</span> flows
                    </div>
                </div>
                <div class="row">
                    <div id="e1" class="row col l4 m4 s4">
                        <div class="input-field col l6 m6 s6">
                            <label>x<input type="number" id="e1x" value="0"></label>
                        </div>
                        <div class="input-field col l6 m6 s6">
                            <label>y<input type="number" id="e1y" value="0"></label>
                        </div>
                    </div>
                    <div id="e2" class="row col l4 m4 s4">
                        <div class="input-field col l6 m6 s6">
                            <label>x<input type="number" id="e2x" value="1"></label>
                        </div>
                        <div class="input-field col l6 m6 s6">
                            <label>y<input type="number" id="e2y" value="1"></label>
                        </div>
                    </div>
                    <div class="row col l4 m4 s4">
                        <div class="input-field row">
                            <input id="f_color" type="color" value="#009866" style="width:80px;height: 50px;">
                        </div>
                    </div>
                    <!--ADD FLOW-->
                    <a href="#"
                       id="add_flow"
                       class="btn-floating btn-large waves-effect waves-light"
                       style="position:absolute;right: 20px;top: 80px">
                        <i class="material-icons">add</i>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<a href="game.php" class="btn-floating btn-large yellow darken-4" style="position:fixed; bottom: 20px; right: 20px;">
    <i class="material-icons">games</i>
</a>

</body>
</html>