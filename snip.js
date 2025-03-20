// Function to extract URL parameters
function getUrlParams() {
    const params = {};
    const urlSearchParams = new URLSearchParams(window.location.search);
    for (const [key, value] of urlSearchParams.entries()) {
        params[key] = value;
    }
    return params;
}

var gRoomId = 0;

// Main logic
$(document).ready(function () {
    const params = getUrlParams();
    const hotelId = params.hotel_id;
    const roomId = params.room_id;
    const imageTypeId = params.image_type_id || 1;

    if (!hotelId) {
        alert('Missing required hotel_id parameter.');
        return;
    }
    populateImageTypes(imageTypeId);

    // Fetch hotel details using hotelAjax
    hotelAjax('ajax_get_hotel.php', [{ hotel_id: hotelId }])
        .then((response) => {
            const hotel = response && response[0]; // Assuming response is in array form
            if (hotel && hotel.name) {
                document.getElementById('hotel-title').textContent = hotel.name;
            } else {
                document.getElementById('hotel-title').textContent = 'Hotel Details Not Found';
            }
        })
        .catch((error) => {
            console.error('Error fetching hotel details:', error);
        });

    // Fetch room details using hotelAjax
    hotelAjax('ajax_get_rooms_and_snip_counts.php', [{ hotel_id: hotelId }])
        .then((response) => {
            const rooms = response; // Assuming response is in array form
            const roomStrip = document.getElementById('room-strip');
            roomStrip.innerHTML = ''; // Clear any existing content
            if (rooms && Array.isArray(rooms)) {
                rooms.forEach(room => {
                    // Create a room thumbnail
                    const roomThumbnail = document.createElement('div');
                    roomThumbnail.className = 'room-thumbnail';

                    // Room image (thumbnail)
                    const roomImage = document.createElement('img');
                    roomImage.className = 'room-image';
                    roomImage.src = `/show_image.php?width=200&image_id=${room.image_id}`;
                    roomImage.alt = room.name;

                    // Highlight the room image if it matches room_id
                    if (roomId && roomId === String(room.room_id)) {
                        roomImage.classList.add('selected');
                    }
                    const overlayContainer = document.createElement('div');
                    overlayContainer.className = 'overlay-container';
                    roomThumbnail.appendChild(overlayContainer);

                    // Overlay for the image count
                    const overlay = document.createElement('span');
                    overlay.className = 'overlay';
                    overlay.textContent = room.image_count;
                    overlayContainer.appendChild(overlay);

                    // Make the room image clickable
                    roomThumbnail.addEventListener('click', function () {
                        $("#room-strip .room-image").removeClass("selected");
                        roomImage.classList.add('selected');
                        loadRoom(room.room_id);
                    });

                    // Append the image and overlay to the thumbnail
                    roomThumbnail.appendChild(roomImage);

                    for (let key in room.image_types)
                    {
                        const overlay = document.createElement('span');
                        overlay.className = 'roomTypeCount-' + room.room_id + '-' + key + ' overlay';
                        overlay.style.cssText = "float:right;background-color:" + getTypeColor(key, .6);
                        overlay.textContent = room.image_types[key];
                        overlayContainer.prepend(overlay);
                    }

                    // Add the thumbnail to the room strip
                    roomStrip.appendChild(roomThumbnail);
                });
            } else {
                roomStrip.textContent = 'No rooms available for this hotel.';
            }
        })
        .catch((error) => {
            console.error('Error fetching room details:', error);
        });
});


function populateImageTypes(imageTypeId) {
    // Call hotelAjax to fetch image types
    hotelAjax('ajax_get_image_types.php', []).then(imageTypes => {
        const container = document.getElementById('imageTypes');

        // Clear any existing content
        container.innerHTML = '';

        // Create the radio button group
        const formGroup = document.createElement('div');
        formGroup.className = 'btn-group';
        formGroup.setAttribute('role', 'group');
        formGroup.setAttribute('aria-label', 'Image Types');

        if (imageTypes && Array.isArray(imageTypes)) {
            imageTypes.forEach((type, index) => {
                // Create a label for the button
                const label = document.createElement('label');
                label.className = 'btn btn-secondary';
                label.style.backgroundColor = getTypeColor(type.id, 0.2); // Get type color with transparency
                label.style.borderColor = getTypeColor(type.id, 1); // Border with full color
                label.style.color = '#000'; // Ensure text is visible
                label.textContent = type.name;

                const count = document.createElement('span');
                count.className = "typeCount" + type.id + " badge badge-pill badge-light";
                label.appendChild(count);

                // Create the radio input
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'imageType';
                input.value = type.id;
                input.style.display = 'none'; // Use label styling instead of showing input directly

                // Add a change listener to the input
                input.addEventListener('change', () => {
                    // Remove "active" styling from all buttons
                    const allLabels = container.querySelectorAll('.btn');
                    allLabels.forEach(btn => {
                        btn.classList.remove('active');
                        const typeId = btn.querySelector('input').value; // Get the type ID of the button
                        btn.style.backgroundColor = getTypeColor(typeId, 0.2); // Reset to original lighter color
                    });

                    // Add "active" styling to the clicked label
                    label.classList.add('active');
                    label.style.backgroundColor = getTypeColor(type.id, 0.8); // Darker version of the button's color
                });

                // Add the input to the label
                label.appendChild(input);

                // Round corners for the last button
                if (index === imageTypes.length - 1) {
                    label.style.borderTopRightRadius = '0.375rem'; // Bootstrap default rounded corner
                    label.style.borderBottomRightRadius = '0.375rem'; // Bootstrap default rounded corner
                }

                // Append the label to the form group
                formGroup.appendChild(label);
            });
        } else {
            // If no types are returned, display a fallback message
            const noTypesMessage = document.createElement('p');
            noTypesMessage.textContent = 'No image types available.';
            container.appendChild(noTypesMessage);
        }

        // Append the form group to the container
        container.appendChild(formGroup);
        $(`input[name="imageType"][value="${imageTypeId}"]`).trigger('click');
    }).catch(error => {
        console.error('Error fetching image types:', error);
    });
}



function loadRoom(roomId) {
    gRoomId = roomId;
    hotelAjax('ajax_get_total_snips_for_room.php', [{room_id: roomId}])
        .then((response) => {
            const images = response; // Assuming response is in array form
            const imageStrip = document.getElementById('image-strip');
            imageStrip.innerHTML = ''; // Clear any existing content
            if (images && Array.isArray(images)) {
                let totalSnips = {};
                images.forEach(image => {
                    // Create a room thumbnail
                    const imageThumbnail = document.createElement('div');
                    imageThumbnail.className = 'room-thumbnail';

                    // Room image (thumbnail)
                    const imageImage = document.createElement('img');
                    imageImage.className = 'room-image';
                    imageImage.src = `/show_image.php?width=200&image_id=${image.image_id}`;
                    imageImage.alt = image.image_id;

                    const overlayContainer = document.createElement('div');
                    overlayContainer.className = 'overlay-container';
                    imageThumbnail.appendChild(overlayContainer);

                    // Make the room image clickable
                    imageThumbnail.addEventListener('click', function () {
                        $("#image-strip .room-image").removeClass("selected");
                        imageImage.classList.add('selected');
                        loadImage(image.image_id);
                    });

                    // Append the image and overlay to the thumbnail
                    imageThumbnail.appendChild(imageImage);

                    for (let key in image.image_types)
                    {
                        const overlay = document.createElement('span');
                        overlay.className = 'imageTypeCount-' + image.image_id + '-' + key + ' overlay';
                        overlay.style.cssText = "float:right;background-color:" + getTypeColor(key, .6);
                        overlay.textContent = image.image_types[key];
                        overlayContainer.prepend(overlay);
                        if (key in totalSnips)
                            totalSnips[key] += image.image_types[key];
                        else
                            totalSnips[key] = image.image_types[key];
                    }

                    // Add the thumbnail to the room strip
                    imageStrip.appendChild(imageThumbnail);


                });
                for (let key in totalSnips)
                    updateSnipCountTotal(key, 0, true);

                for (let key in totalSnips)
                    updateSnipCountTotal(key, totalSnips[key], true);
            } else {
                imageStrip.textContent = 'No images available for this room.';
            }
        })
        .catch((error) => {
            console.error('Error fetching image details:', error);
        });
}

function updateSnipCountTotal(typeId,  toAdd, replace=false)
{
    let nel = $(".typeCount" + typeId);
    if (replace || nel.data("count") == undefined)
        $(nel).data("count", 0);
    else
    {
        let selRoom = $(".roomTypeCount-" + gRoomId + "-" + typeId);
        $(selRoom).text(Number($(selRoom).text()) + toAdd);

        let selImage = $(".imageTypeCount-" + gImageId + "-" + typeId);
        $(selImage).text(Number($(selImage).text()) + toAdd);
    }
    $(nel).data("count", $(nel).data("count") + toAdd);
    $(".typeCount" + typeId).text(" (" + ($(nel).data("count")) + ")");
}

function resizeCanvas() {
    if (getTargetCanvas()){
        const bounds = getTargetImage().getBoundingClientRect();
        const offset = $(getTargetImage()).position();
        $(getTargetCanvas())
            .attr('width', bounds.width)
            .attr('height', bounds.height)
            .attr('top', offset.top)
            .attr('left', offset.left)
            .css({
                'width': bounds.width + 'px',
                'height': bounds.height + 'px',
                'top': offset.top + 'px',
                'left': offset.left + 'px'
            });
        redrawPolygons()
        }
    }

function startPolygon(e) {
    gPolygonMode = true;
    gPolygons.push({typeId: $('input[name="imageType"]:checked').val(), points: [canvasToImage(getMousePosition(e))]});
    redrawPolygons(e);
}

function addToPolygon(e) {
    const newPoint = canvasToImage(getMousePosition(e));
    let points = gPolygons.at(-1).points;

    // Check if close to start point
    if (points.length > 1) {
        const startPoint = points[0];
        const distance = Math.hypot(newPoint.x - startPoint.x, newPoint.y - startPoint.y);

        if (distance < 10) {
            completePolygon();
            return;
        }
    }
    if (gPolygons.at(-1).points.at(-1).x != newPoint.x || gPolygons.at(-1).points.at(-1).y != newPoint.y)
        gPolygons.at(-1).points.push(newPoint);
    redrawPolygons(e);
}

function completePolygon() {
    if (gPolygons.at(-1).points.length > 2) {
        gPolygonMode = false;
        gPolygons.at(-1).points.push(gPolygons.at(-1).points[0]); // Close the polygon
        redrawPolygons();
        hotelAjax('ajax_create_area.php', [{'image_room_id': gImageId, 'image_type_id': gPolygons.at(-1).typeId, 'coordinates': JSON.stringify(gPolygons.at(-1).points)}]).then(response =>
        {
            gPolygons.at(-1).id = response;
            console.log(gPolygons.at(-1));
        });
    }
}

function redrawPolygons(e) {
    // Clear canvas
    const ctx = getTargetCanvas().getContext('2d');
    ctx.clearRect(0, 0, getTargetCanvas().width, getTargetCanvas().height);

    for (let i = 0; i < gPolygons.length; i++) {

        // Draw the polygon
        let startPt = imageToCanvas(gPolygons[i].points[0])
        ctx.beginPath();
        ctx.moveTo(startPt.x, startPt.y);

        // Draw lines between points
        for (let j = 1; j < gPolygons[i].points.length; j++) {
            let curPt = imageToCanvas(gPolygons[i].points[j])
            ctx.lineTo(curPt.x, curPt.y);
        }

        // Draw the lines
        ctx.strokeStyle = getTypeColor(gPolygons[i].typeId, 1);
        ctx.lineWidth = 3;
        ctx.stroke();

        // If still drawing, draw line to current mouse position
        if (gPolygonMode && e && i == gPolygons.length - 1) {
            const mousePos = getMousePosition(e);
            ctx.setLineDash([5, 5]); // Dashed line for temporary line
            ctx.lineTo(mousePos.x, mousePos.y);
            ctx.stroke();
            ctx.setLineDash([]); // Reset to solid line
        }
    }
}

function isPointInPolygon(point, polygon) {
    // point = {x: X, y: Y}
    // polygon = [{x: X1, y: Y1}, {x: X2, y: Y2}, ...]

    let inside = false;

    // Loop through each pair of points
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const xi = polygon[i].x;
        const yi = polygon[i].y;
        const xj = polygon[j].x;
        const yj = polygon[j].y;

        // Check if point is on a horizontal polygon boundary
        if (yi === yj && yi === point.y && point.x > Math.min(xi, xj) && point.x < Math.max(xi, xj)) {
            return true;
        }

        // Ray casting algorithm
        if ((yi > point.y) !== (yj > point.y) &&
            point.x < ((xj - xi) * (point.y - yi) / (yj - yi) + xi)) {
            inside = !inside;
        }
    }

    return inside;
}


$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        if (gPolygonMode && gPolygons.length > 0) {
            gPolygonMode = false;
            gPolygons.pop();
            redrawPolygons();
        }
    }
});

function canvasToImage(point)
{
    return {
        x: point.x * getImageMultipler(),
        y: point.y * getImageMultipler()
    }
}
function imageToCanvas(point)
{
    return {
        x: point.x / getImageMultipler(),
        y: point.y / getImageMultipler()
    }
}

function getMousePosition(e) {
    const rect = getTargetCanvas().getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}



var gPolygonMode = false;
var gPolygons = [];
var gImageId = 0;
function loadImage(imageId) {
    gImageId = imageId;
    $("#imageContainer").empty();
    $("#imageContainer").append("<div id='targetParent' style='display:flex;justify-content;center;gap: 20px;align-items: center;flex-direction:column'/>");
    $("#targetParent").append("<img id='targetImage' src='/show_image.php?image_id=" + imageId + "'/>");
    $("#targetParent").append("<canvas id='targetCanvas' style='position: absolute;left:0;top:0;pointer-events: all'/>");
    $("#targetParent").append("<button id='deleteImageBtn' class='btn btn-danger delete-btn'\"'>Delete</button>");

    $('#targetImage').on('load', function () {
        hotelAjax('ajax_get_snips_for_image.php', [{image_id: imageId}]).then(response => {
            response.forEach(snip => {
                makeSquare(snip);
            });
        });
        hotelAjax('ajax_get_areas_for_image.php', [{image_id: imageId}]).then(response => {
            console.log(response);
            gPolygons = [];
            response.forEach(area => {
               gPolygons.push({id: area.id, typeId: area.image_type_id, points: JSON.parse(area.coordinates)});
            });
            resizeCanvas();
        });
    });

    // delete button
    $("#deleteImageBtn").on('click', function(event) {
        hotelAjax('ajax_delete_image.php', [{id: imageId}]).then(response => {
            gImageId = null;
            $("#imageContainer").empty();
            if (response.deletedRoom)
                window.location.reload(true);
            else
                loadRoom(gRoomId);
        });
    });

    let canvas = document.getElementById('targetCanvas');

    // Right click to start drawing
    $(canvas).on('click', function(e) {

        // delete polygon
        if (e.shiftKey && !gPolygonMode) {
            e.preventDefault();
            for (var i = 0;i < gPolygons.length;i++)
                if (isPointInPolygon(canvasToImage(getMousePosition(e)), gPolygons[i].points)) {
                    hotelAjax('ajax_delete_area.php', [{id: gPolygons[i].id}]);
                    gPolygons.splice(i, 1);
                    redrawPolygons();
                    break;
                }
        }

        // square snip.
        else if (e.ctrlKey && !gPolygonMode) {
            const elementOffset = $(this).offset();

            // Calculate mouse coordinates relative to the element
            const relativeX = event.pageX - elementOffset.left;
            const relativeY = event.pageY - elementOffset.top;

            // start polygon
            addSquare(relativeX, relativeY);
        }

        // build polygon
        else
        {
            e.preventDefault();
            if (!gPolygonMode)
                startPolygon(e);
            else
                addToPolygon(e);
        }
    });

    // Double click to finish
    $(canvas).on('dblclick', function(e) {
        if (gPolygonMode) {
            e.preventDefault();
            completePolygon();
        }
    });

    // Track mouse movement
    $(canvas).on('mousemove', function(e) {
        if (gPolygonMode) {
            redrawPolygons(e);
        }
    });
}

function getTargetImage()   {
    return document.getElementById('targetImage');
}

function getTargetCanvas()  {
    return document.getElementById('targetCanvas');
}

// Function to scale the square dimensions and positions based on the current image dimensions
function updateSquares() {
    if (!getTargetImage())
        return;
    $(".square").each(function(index, element) {
        updateSquare(element);
    });
}

function updateSquare(element){
    $(element).css(snipToContainer($(element).data('snip')));
}

let gSize   = 100;

function getImageMultipler()
{
    return getTargetImage().naturalWidth / getTargetImage().getBoundingClientRect().width;
}

function mouseToSnip(x, y) {
    let multipler = getImageMultipler();
    return {left: multipler * (x - gSize / 2),
        top: multipler * (y - gSize / 2),
        width: gSize,
        height: gSize};
}

function snipToContainer(snipRect) {
    let imageRect   = getTargetImage().getBoundingClientRect();
    let divisor = getImageMultipler();
    let offset = $("#targetImage").offset();
    return {left: (snipRect.left / divisor  + $(getTargetImage()).position().left) + "px",
        top: (snipRect.top / divisor)  + $(getTargetImage()).position().top + "px",
        width: (snipRect.width / divisor) + "px",
        height: (snipRect.height / divisor) + "px"};
}

// Function to add a new square
function addSquare(x, y) {
    let snipRect = mouseToSnip(x, y);
    snipRect.image_type_id = $('input[name="imageType"]:checked').val();
    snipRect.image_room_id = gImageId;

    // clamp square to edges.
    if (snipRect.top < 0)
        snipRect.top = 0;
    if (snipRect.left < 0)
        snipRect.left = 0;
    if (snipRect.top + snipRect.height > getTargetImage().naturalHeight)
        snipRect.top = getTargetImage().naturalHeight - snipRect.height;
    if (snipRect.left + snipRect.width > getTargetImage().naturalWidth)
        snipRect.left = getTargetImage().naturalWidth - snipRect.width;

    makeSquare(snipRect);
    updateSnipCountTotal(snipRect.image_type_id, 1);

    hotelAjax('ajax_create_snip.php', [snipRect]).then(response => {
        snipRect.id = response;
    });
}

function makeSquare(snipRect)
{
    const squareRect    = snipToContainer(snipRect);
    // Otherwise, create and add a new square
    const newSquare = document.createElement('div');
    newSquare.classList.add('square');
    $(newSquare).css(squareRect);
    $(newSquare).css({"border-color": getTypeColor(snipRect.image_type_id, 1),
        "background-color": getTypeColor(snipRect.image_type_id, 0.2)});
    $(newSquare).data('snip', snipRect);
    // Append the square element to the image container
    $("#imageContainer").append(newSquare);

    // delete square
    $(newSquare).on('click', function(event){
        event.preventDefault();
        let snip = $(this).data('snip');
        $(this).remove();

        updateSnipCountTotal(snipRect.image_type_id, -1);
        hotelAjax('ajax_delete_snip.php', [{id: snip.id}]);
    });

    // resize square
    $(newSquare).on('wheel', function(event) {
        // Prevent default scrolling
        event.preventDefault();

        let snip = $(this).data('snip');
        if (!("id" in snip) || !snip.id)
            return;

        // save old dimensions
        let oldWidth = snip.width;
        let oldHeight = snip.height;
        let oldLeft = snip.left;
        let oldTop = snip.top;

        // Access the scroll direction
        const delta = event.originalEvent.deltaY > 0 ? 5 : -5;
        let newWidth = snip.width + delta;
        if (newWidth > 500)
            newWidth = 500;
        else if (newWidth < 50)
            newWidth = 50;
        let actualDelta = newWidth - snip.width;
        snip.width = snip.height = newWidth;
        snip.left -= actualDelta / 2;
        snip.top -= actualDelta / 2;

        // see if this was on an edge of the picture
        let imageWidth  = getTargetImage().naturalWidth;
        let imageHeight = getTargetImage().naturalHeight;
        if (oldLeft < 1)
            snip.left = 0;
        if (oldTop < 1)
            snip.top = 0;
        if (Math.abs(oldLeft + oldWidth - imageWidth) < 1)
            snip.left = imageWidth - snip.width;
        if (Math.abs(oldTop + oldHeight - imageHeight) < 1)
            snip.top = imageHeight - snip.height;

        updateSquare($(this));
        hotelAjax('ajax_update_snip.php', [snip]);
    });
}


// Update square positions and sizes if the window resizes
window.addEventListener('resize', function(){
    updateSquares();
    resizeCanvas();
});
