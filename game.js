/**
 * Builds html strings which can be directly appended to a DOM object
 * */
function _element(tags, attributes, contents, closings) {
    var $element = "";// returns string
    var iter;
    for (iter = 0; iter < tags.length; ++iter) {

        $element += "<" + tags[iter];

        for (var key in attributes[iter]) {
            if (attributes[iter].hasOwnProperty(key)) {
                //# adds an attribute to the tags[i]#
                $element += ( " " + key + "=\'" + attributes[iter][key] + "\'");
            }
        }

        $element += ">";
        $element += contents[iter];
    }

    for (iter = tags.length - 1; iter > -1; --iter) {
        if (closings[iter]) {
            $element += "</" + tags[iter] + ">"
        }
    }

    return $element;
}

/**
 * Matrix Constructor
 *
 * Assert:
 * ======
 * All distinct colors (including base color and flow colors)
 * All end points of all flows are distinct (in terms of coordinates)
 * All flow endpoints have coordinates on the matrix
 * No elements with id matrix
 * No elements with ids in format x-y-cell
 * All params are in the specified format
 *
 * Format
 * ======
 * order.r = rows, order.c = columns
 * color = rgb(r<space>,g<space>,b) (for any color, base color and flow color)
 *
 * */
function Matrix(container, order, color) {
    var m_self = this;

    const MATRIX_UNIQUE_ID = "matrix";
    const CELL_ID_SUFFIX = "cell";

    function prepare_new_id(coordinates) {
        /*
         * coordinates.x = x co-ordinate
         * coordinates.y = y co-ordinate
         * */

        return String(coordinates.x) + "-" + String(coordinates.y) + "-" + CELL_ID_SUFFIX;
    }

    /**
     * Cell Constructor
     * */
    m_self.Cell = function (index_i, index_j, left_px, top_px, size_px) {
        var parent_matrix = m_self;
        var self = this;

        // Basic Info
        self.co_ordiantes = {x: index_i, y: index_j};
        // This format is important for parses coordinates from id later
        self.id = prepare_new_id(self.co_ordiantes);

        // Renders Cell
        var _cell = _element(
            ["div"],
            [
                {
                    "id": self.id,
                    "class": "cell",
                    "style": "width:" + size_px + "px;" +
                    "height:" + size_px + "px;" +
                    "position:absolute;" +
                    "left:" + left_px + "px;" +
                    "top:" + top_px + "px;" +
                    "background-color:" + parent_matrix.color + ";"
                }
            ],
            [""],
            [true]
        );
        $(parent_matrix.$obj).append(_cell);
        // Keeps track of it
        self.$obj = $(parent_matrix.$obj).find('#' + self.id);

        /* Graphics -------------------------------------- */
        self.set_bg = function (_color) {
            self.$obj.css({
                'background-color': _color
            });
        };

        self.mark_flow_end_point = function (_color) {
            // Cannot change bg color
            self.$obj.css({
                'background-color': _color,
                'border': '10px dashed white'
            });
        };

        // the mark and un mark methods should be exact opposites of each other
        self.mark_cell = function (_color) {
            self.$obj.css({
                'background-color': _color
            });
        };

        self.un_mark_cell = function () {
            self.$obj.css({
                'background-color': parent_matrix.color
            });
        };
    };

    /**
     * Flow Constructor
     * */
    m_self.Flow = function (pos1, pos2, color, index) {
        /*
         * pos.x = x co-ordinate
         * pos.y = y co-ordinate
         * */
        var parent_matrix = m_self;
        var self = this;

        // Keeps track of index
        // this index is the index of the parent's array which keeps track all flows
        self.index = index;

        // Keeps track of color
        self.color = color;

        // Keeps track of end points
        self.end_point1 = parent_matrix.get_cell(pos1);
        self.end_point2 = parent_matrix.get_cell(pos2);

        // Apply flow end graphics
        self.end_point1.mark_flow_end_point(color);
        self.end_point2.mark_flow_end_point(color);

        /* Path ----------------------------------------------- */
        // Keeps track of drawn flow path
        self.path = [];

        self.start = undefined;

        self.end = undefined;

        self.steps = 0;

        self.initialize_flow = function (start) {
            // Pushes the selected point's coordinates into the path
            // Notice that we are not changing the end point graphically
            self.path.push(start);
            self.start = start;

            if (self.start.x === self.end_point1.co_ordiantes.x &&
                self.start.y === self.end_point1.co_ordiantes.y)
                self.end = {
                    x: self.end_point2.co_ordiantes.x,
                    y: self.end_point2.co_ordiantes.y
                };
            else if (self.start.x === self.end_point2.co_ordiantes.x &&
                self.start.y === self.end_point2.co_ordiantes.y)
                self.end = {
                    x: self.end_point1.co_ordiantes.x,
                    y: self.end_point1.co_ordiantes.y
                };

        };

        self.on_complete_callback = function () {
            // Update globals like score etc ...
            Materialize.toast("Flow completed", 2000);
            // Check for game completion
            parent_matrix.check_game_status();
        };

        self.reset_flow = function () {
            // Undo all the graphic changes
            var path_len = self.path.length;
            // Notice the > 0, i.e. we are undoing the graphical effects until we reach flow end point
            // We are not touching graphics of flow end point because it's graphic were not changed
            //                                                          when it was pushed into path array
            for (var i = path_len - 1; i > 0; --i) {
                var latest_cell = parent_matrix.get_cell(self.path[i]);
                // Undo graphics
                latest_cell.un_mark_cell();
            }
            // Release the memory;
            self.path = [];
            // Reset the start of path
            self.start = undefined;
            // Reset the end of path
            self.end = undefined;
        };

        self.push_into_flow = function (new_coordinates) {
            var new_cell = parent_matrix.get_cell(new_coordinates);
            var new_cell_bg = new_cell.$obj.css('background-color');

            // If new coordinates does not have a colored cell already
            // Put a new cell there
            if (new_cell_bg === parent_matrix.color) {
                // Graphic changes
                new_cell.mark_cell(self.color);
                // Adds new cell coordinates in the path
                self.path.push(new_cell.co_ordiantes);
                // Increment Steps
                self.steps++;
            }
            // Reached it's destination
            else if ((new_cell.co_ordiantes.x === self.end.x && new_cell.co_ordiantes.y === self.end.y)) {
                // This flow is completed
                parent_matrix.completed_flows[self.index] = true;
                // This flow becomes inactive
                parent_matrix.active_flow_index = -1;
                self.on_complete_callback();
            }
        };

        self.pop_from_flow = function () {
            if (self.path.length > 0) {
                var last_cell = parent_matrix.get_cell(self.path[self.path.length - 1]);
                // Undo all the graphic changes
                last_cell.un_mark_cell();
                // Release the memory of last_cell's coordinates
                self.path.pop();
                // Decrement Steps
                self.steps--;
            }
        };

        // Movements
        // Push or Pop or None
        self.move = function (new_coordinates) {
            // Either removing a cell
            // Or Adding a Cell
            if (self.path.length > 1) {
                var pen_ultimate_cell_coordinates = self.path[self.path.length - 2];
                if (new_coordinates.x === pen_ultimate_cell_coordinates.x &&
                    new_coordinates.y === pen_ultimate_cell_coordinates.y) {
                    // User went back
                    self.pop_from_flow();
                }
                else {
                    self.push_into_flow(new_coordinates);
                }
            }
            else {
                self.push_into_flow(new_coordinates);
            }
        };

        self.left = function () {
            if (self.path.length === 0) {
                alert("Select a flow to build");
            }
            else {
                // length > 0
                var latest_point = self.path[self.path.length - 1];
                if (latest_point.x === 0) {
                    // Pass
                }
                else {
                    // Go Left i.e. x--
                    var new_coordinates = {
                        x: latest_point.x - 1,
                        y: latest_point.y
                    };
                    // Move to new coordinates
                    self.move(new_coordinates);
                }
            }
        };

        self.right = function () {
            if (self.path.length === 0) {
                alert("Select a flow to build");
            }
            else {
                // length > 0
                var latest_point = self.path[self.path.length - 1];
                if (latest_point.x === parent_matrix.order.c - 1) {
                    // Pass
                }
                else {
                    // Go Right i.e. x++
                    var new_coordinates = {
                        x: latest_point.x + 1,
                        y: latest_point.y
                    };
                    // Move to new coordinates
                    self.move(new_coordinates);
                }
            }
        };

        self.up = function () {
            if (self.path.length === 0) {
                alert("Select a flow to build");
            }
            else {
                // length > 0
                var latest_point = self.path[self.path.length - 1];
                if (latest_point.y === 0) {
                    // Pass
                }
                else {
                    // Go Up i.e. y--
                    var new_coordinates = {
                        x: latest_point.x,
                        y: latest_point.y - 1
                    };
                    // Move to new coordinates
                    self.move(new_coordinates);
                }
            }
        };

        self.down = function () {
            if (self.path.length === 0) {
                alert("Select a flow to build");
            }
            else {
                // length > 0
                var latest_point = self.path[self.path.length - 1];
                if (latest_point.y === parent_matrix.order.r - 1) {
                    // Pass
                }
                else {
                    // Go Down i.e. y++
                    var new_coordinates = {
                        x: latest_point.x,
                        y: latest_point.y + 1
                    };
                    // Move to new coordinates
                    self.move(new_coordinates);
                }
            }
        };

        /* Listeners -------------------------------------------*/
        $(document).ready(function () {
            $(container)
                .on(
                    'click',
                    '#' + self.end_point1.id +
                    ',' +
                    '#' + self.end_point2.id,
                    function () {
                        // Empty the previous flow path
                        if (parent_matrix.active_flow_index !== -1) {
                            parent_matrix.flows[parent_matrix.active_flow_index].reset_flow();
                        }
                        // If this flow not complete initialize flow
                        if (parent_matrix.completed_flows[self.index] === false) {
                            // Update the active flow index
                            parent_matrix.active_flow_index = self.index;
                            var start = parent_matrix.get_coordinates($(this).attr('id'));
                            self.initialize_flow(start);
                        }
                    }
                )
        });
    };

    // Unique Id
    m_self.id = MATRIX_UNIQUE_ID;
    m_self.order = order;
    // Floors order
    m_self.order.c = Math.floor(m_self.order.c);
    m_self.order.r = Math.floor(m_self.order.r);

    /* Rendering ------------------------------------------------------------------ */

    // Color
    m_self.color = color;

    // Default Configuration
    var width_container = $(container).width();
    var height_container = $(container).height();
    var center_px = {x: width_container / 2, y: height_container / 2};
    var cell_size_px = (Math.min(width_container, height_container) / Math.max(order.r, order.c)) * (9 / 10); // min padding

    // Geometry math
    var width_px = m_self.order.c * cell_size_px; // no of columns
    var height_px = m_self.order.r * cell_size_px; // no of rows
    var left_px = center_px.x - width_px / 2;
    var top_px = center_px.y - height_px / 2;

    // The container must have position = relative
    $(container).css({
        "position": "relative"
    });

    // Renders Matrix Base
    var _matrix = _element(
        ["div"],
        [
            {
                "id": m_self.id,
                "class": "matrix",
                "style": "width:" + width_px + "px;" +
                "height:" + height_px + "px;" +
                "position:absolute;" +
                "left:" + left_px + "px;" +
                "top:" + top_px + "px;"
            }
        ],
        [""],
        [true]
    );
    // Empties container
    $(container).empty();
    // Appends Matrix
    $(container).append(_matrix);
    // Keeps track of it
    m_self.$obj = $(container).find("#" + m_self.id);

    /* Cells ----------------------------------------------------------------------- */

    // Keeps track of cells according to cartesian co-ordinate system
    // (0,0) (1,0) (2,0) (3,0) ....
    // (0,1) ....
    // (0,2) ....
    // (0,3) ....
    m_self.cells = [];

    // Instantiates Cells
    var top_px_ij = 0;
    for (var i = 0; i < m_self.order.c; i++) {
        var left_px_ij = 0;
        var new_row = [];
        for (var j = 0; j < m_self.order.r; j++) {
            // Push to array
            new_row.push(new m_self.Cell(i, j, top_px_ij, left_px_ij, cell_size_px));
            left_px_ij += cell_size_px;
        }
        m_self.cells.push(new_row);
        top_px_ij += cell_size_px;
    }

    // Getters
    m_self.get_coordinates = function (_id) {
        /*
         * id format = (x_co_ordinate)-(y_co_ordinate)-(*)
         * */
        var co_ordinates = _id.split("-");
        var cols = parseInt(co_ordinates[0]);
        var rows = parseInt(co_ordinates[1]);

        // returns undefined on illegal id
        if (cols <= m_self.order.c - 1 && rows <= m_self.order.r - 1) {
            return {
                x: parseInt(co_ordinates[0]),
                y: parseInt(co_ordinates[1])
            };
        } else {
            return undefined;
        }
    };

    m_self.get_cell = function (coordinates) {
        /*
         * coordinates.x = x co-ordinate
         * coordinates.y = y co-ordinate
         * */

        // returns undefined on illegal coordinates
        if (coordinates.x <= m_self.order.c - 1 && coordinates.y <= m_self.order.r - 1) {
            return m_self.cells[coordinates.x][coordinates.y];
        } else {
            return undefined;
        }
    };

    /* Flows --------------------------------------------------------------------------- */

    // Keeps track of flows
    m_self.flows = [];

    // ith is True iff ith flow is completed
    m_self.completed_flows = [];

    m_self.active_flow_index = -1;

    m_self.add_flow = function (pos1, pos2, color) {
        m_self.flows.push(new m_self.Flow(pos1, pos2, color, m_self.flows.length));
        m_self.completed_flows.push(false);
    };

    /* Listeners ------------------------------------------------------------------------- */

    // Listeners
    $(document).on(
        'keydown',
        function (e) {
            switch (e.keyCode) {
                // Left arrow
                case 37:
                    if (m_self.active_flow_index === -1) {
                        alert("Select a flow to build");
                        return;
                    }
                    else {
                        m_self.flows[m_self.active_flow_index].left();
                    }
                    break;
                // Up arrow
                case 38:
                    if (m_self.active_flow_index === -1) {
                        alert("Select a flow to build");
                        return;
                    }
                    else {
                        m_self.flows[m_self.active_flow_index].up();
                    }
                    break;
                // Right arrow
                case 39:
                    if (m_self.active_flow_index === -1) {
                        alert("Select a flow to build");
                        return;
                    }
                    else {
                        m_self.flows[m_self.active_flow_index].right();
                    }
                    break;
                // Down arrow
                case 40:
                    if (m_self.active_flow_index === -1) {
                        alert("Select a flow to build");
                        return;
                    }
                    else {
                        m_self.flows[m_self.active_flow_index].down();
                    }
                    break;
                default:
                    break;
            }
        }
    );

    /* Game  -------------------------------------------------------------------------------- */

    m_self.check_game_status = function () {
        var game_finished = true;
        for (var i = 0; i < m_self.flows.length; ++i) {
            if (m_self.completed_flows[i] !== true) {
                game_finished = false;
                break;
            }
        }

        if (game_finished === true) {
            Materialize.toast("Game over", 3000);
        }
    };

    m_self.serialize = function () {
        var seed = {
            order: {
                r: m_self.order.r,
                c: m_self.order.c
            },
            color: m_self.color,
            flows: []
        };

        for (var i = 0; i < m_self.flows.length; i++) {
            var flow = m_self.flows[i];
            seed.flows.push({
                e1: {
                    x: flow.end_point1.co_ordiantes.x,
                    y: flow.end_point1.co_ordiantes.y
                },
                e2: {
                    x: flow.end_point2.co_ordiantes.x,
                    y: flow.end_point2.co_ordiantes.y
                },
                color: flow.color
            });
        }

        return JSON.stringify(seed);
    };

    m_self.destroy = function () {
        m_self.$obj.remove();
    }

}

function StartFromParams(container, order, color, flows) {

    var matrix = new Matrix(container, order, color);

    for (var i = 0; i < flows.length; i++) {
        var flow = flows[i];
        matrix.add_flow(flow.e1, flow.e2, flow.color);
    }

    return matrix;
}

function StartFromSeed(container, seed) {
    StartFromParams(container, seed.order, seed.color, seed.flows);
}