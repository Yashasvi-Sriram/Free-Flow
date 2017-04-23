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

<body class="grey lighten-2">

<script type="application/javascript">

    function _flow(e1x, e1y, e2x, e2y, color) {
        e1x = e1x === undefined ? 0 : e1x;
        e1y = e1y === undefined ? 0 : e1y;
        e2x = e2x === undefined ? 1 : e2x;
        e2y = e2y === undefined ? 1 : e2y;
        color = color === undefined ? "#009866" : color;

        var _html =
            '<div class="row flow"><div class="row col l4 m4 s4"><div class="input-field col l6 m6 s6"><label>x<input type="number" class=" e1x" value="' + e1x + '"></label></div><div class="input-field col l6 m6 s6"><label>y<input type="number" class=" e1y" value="' + e1y + '"></label></div></div>' +
            '<div class="row col l4 m4 s4"><div class="input-field col l6 m6 s6"><label>x<input type="number" class=" e2x" value="' + e2x + '"></label></div><div class="input-field col l6 m6 s6"><label>y<input type="number" class=" e2y" value="' + e2y + '"></label></div></div>' +
            '<div class="row col l4 m4 s4">' +
            '<div class="input-field row"><input class="jscolor f_color" type="color" value="' + color + '" style="width:80px;height: 40px;"></div>' +
            '<div class="input-field row"><a href="#" class="btn red waves-effect waves-light remove_flow"><i class="material-icons">remove</i></a></div></div></div>';

        return _html;
    }

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
        var red = mul_map[hex.charAt(1)] + mul_map[hex.charAt(2)] * 16;
        var green = mul_map[hex.charAt(3)] + mul_map[hex.charAt(4)] * 16;
        var blue = mul_map[hex.charAt(5)] + mul_map[hex.charAt(6)] * 16;

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

    function apply_context(context) {
        $('#name').val(context.name);
        $('#author').val(context.author);
        $('#m_color').val(rgb_to_hex(context.seed.color));
        $('#rows').val(context.seed.order.r);
        $('#columns').val(context.seed.order.c);

        $('#no_flows').html(context.seed.flows.length);
        for (var i = 0; i < context.seed.flows.length; i++) {
            var ith = context.seed.flows[i];
            $('#flows').append(_flow(
                ith.e1.x,
                ith.e1.y,
                ith.e2.x,
                ith.e2.y,
                rgb_to_hex(ith.color)
            ));
        }
    }

</script>

<div class="row">
    <div class="col l6 m12 s12"></div>
    <div class="col l6 m12 s12">
        <div id="tools" class="card">
            <form action="levels.php" method="post" id="c_form">
                <input type="text" name="q" value="c" hidden/>
                <input id="level" type="text" name="level" hidden/>
            </form>
            <div class="card-content">
                <div class="card-title">Studio</div>
                <!--SUBMIT-->
                <a href="#"
                   id="submit"
                   class="btn-floating btn-large blue waves-effect waves-light"
                   style="position:absolute;top: 20px;right: 20px">
                    <i class="material-icons">done</i>
                </a>

                <!--ADD FLOW-->
                <a href="#"
                   id="add_flow"
                   class="btn-floating btn-large waves-effect waves-light"
                   style="position:absolute;top: 20px;right: 100px">
                    <i class="material-icons">add</i>
                </a>

                <!--CITATION-->
                <div class="row">
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
                <div class="row">
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

                <!--HEADING-->
                <div class="row">
                    <div class="col l3 m3 s3">
                        <div class="flow-text">One</div>
                    </div>
                    <div class="col l3 m3 s3">
                        <div class="flow-text">Two</div>
                    </div>
                    <div class="col l3 m3 s3">
                        <div class="flow-text">Color</div>
                    </div>
                    <div class="col l3 m3 s3">
                        <div id="no_flows" class="flow-text">0</div>
                    </div>
                </div>

                <!--FLOWS-->
                <script type="application/javascript">
                    $(document).ready(function () {
                        $('#no_flows').html(0);

                        $('#add_flow').click(function () {
                            $('#flows').append(_flow());
                            $('#no_flows').html(Number($('#no_flows').html()) + 1);
                        });

                        $('#flows').on(
                            'click',
                            '.remove_flow',
                            function () {
                                $(this).parent().parent().parent().remove();
                                $('#no_flows').html(Number($('#no_flows').html()) - 1);
                            }
                        );

                        $('#submit').click(function () {
                            var order = {
                                r: $('#rows').val(),
                                c: $('#columns').val()
                            };
                            var m_color = hex_to_rgb($('#m_color').val());
                            if (m_color === false) {
                                Materialize.toast('Illegal Colors', 2000);
                                return;
                            }
                            var flows = [];

                            $('#flows').find('.flow').each(function () {
                                var e1 = {
                                    x: $(this).find('.e1x').val(),
                                    y: $(this).find('.e1y').val()
                                };
                                var e2 = {
                                    x: $(this).find('.e2x').val(),
                                    y: $(this).find('.e2y').val()
                                };
                                var f_color = hex_to_rgb($(this).find('.f_color').val());
                                if (f_color === false) {
                                    Materialize.toast('Illegal Colors', 2000);
                                    return;
                                }

                                flows.push({
                                    e1: e1,
                                    e2: e2,
                                    color: f_color
                                });
                            });
                            if (flows.length > 10) {
                                Materialize.toast("Too many flows!", 5000);
                                return;
                            }

                            var seed = {
                                order: order,
                                color: m_color,
                                flows: flows
                            };

                            var level = {
                                name: $('#name').val(),
                                author: $('#author').val(),
                                seed: seed
                            };

                            $('#level').val(JSON.stringify(level));

                            $('#c_form').submit();
                        });

                        <?php
                        if (isset($_GET['error_msg'])) {
                            echo 'Materialize.toast("Error occurred", 5000);';
                            echo 'Materialize.toast(' . $_GET["error_msg"] . ', 5000);';
                            echo 'var context = JSON.parse(' . $_GET["context"] . ');';
                            echo 'apply_context(context);';
                        }
                        ?>
                    });
                </script>
                <div id="flows">

                </div>
            </div>
        </div>
    </div>
</div>

<a href="game.php" class="btn-floating btn-large red" style="position:fixed; bottom: 20px; right: 20px;">
    <i class="material-icons">fast_rewind</i>
</a>

</body>
</html>