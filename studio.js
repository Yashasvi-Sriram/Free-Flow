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
 * Matrix Maker Constructor
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
function MatrixMould(container, order, color) {
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
                    "background-color:" + parent_matrix.color + ";" +
                    "outline: 1px solid #185207"
                }
            ],
            [""],
            [true]
        );
        $(parent_matrix.$obj).append(_cell);
        // Keeps track of it
        self.$obj = $(parent_matrix.$obj).find('#' + self.id);

        /* Graphics -------------------------------------- */

        self.mark_flow_end_point = function (_color) {
            // Cannot change bg color
            self.$obj.css({
                'background-color': _color,
                'border': '10px dashed ' + parent_matrix.color
            });
        };
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
                "top:" + top_px + "px;" +
                "outline:" + "1px solid white;"
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

}
