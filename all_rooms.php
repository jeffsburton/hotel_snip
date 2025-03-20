
<html>
<head>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>



<script>

    function loadImagesSequentially(rooms) {
        // Create a promise chain to load images one after another
        let chain = Promise.resolve();

        rooms.forEach(room => {
            chain = chain.then(() => {
                return new Promise((resolve) => {
                    // Create container div
                    const containerDiv = document.createElement('div');

                    // Create inner div with hotel and room info
                    const infoDiv = document.createElement('div');
                    infoDiv.textContent = `${room.hotel_id} ${room.room_id}`;
                    containerDiv.appendChild(infoDiv);

                    // Create image element
                    const img = document.createElement('img');

                    // Set up load handler before setting src
                    img.onload = () => {
                        resolve(); // Resolve promise when image loads
                    };

                    img.onerror = () => {
                        resolve(); // Resolve promise even if image fails to load
                    };

                    // Append image to container
                    containerDiv.appendChild(img);

                    // Append container to body
                    document.body.appendChild(containerDiv);

                    // Set image source to trigger loading
                    img.src = `show_image.php?&image_id=${room.image_id}`;
                });
            });
        });

        // Handle any errors in the chain
        chain.catch(error => {
            console.error('Error loading images:', error);
        });
    }

	let rooms   = [
<?php

include_once "ajax/db.php";

$sql = "SELECT hotel_id, room_id, MIN(image_room.id) AS image_id FROM room INNER JOIN image_room ON image_room.room_id=room.id GROUP BY room_id";

$records = select($sql);

for ($i = 0;$i < count($records);$i++) {
	$hotelId = $records[$i]["hotel_id"];
	$roomId = $records[$i]["room_id"];
	$imageId = $records[$i]["image_id"];
	echo "{hotel_id: $hotelId, room_id: $roomId, image_id: $imageId}";
	if ($i < count($records) - 1)
		echo ",";
}
?>
];

    loadImagesSequentially(rooms);
</script>
</body>
</html>

