
<?php
session_start();

if ( !isset( $_SESSION[ 'userID' ] ) ){
    include('header.html');
}
else{
    include('logged_header.php');
}
?>

<script type="text/javascript" src="js/reserve.js"></script>

<body>
	<form class="form-inline" method="post">
        <div class="alert alert-info">
                    <h4>
                        FIND A ROOM
                    </h4> <strong>RESERVES:</strong> Fill out the capacity, start date, end date and room type. This way we will provide avaiable rooms for you.
        </div>
        <br />
		<div class="input-group input-group-sm">
		    <div class="col-xs-3">
	    		<span>Start Date: <input class="form-control" type="text" name="startDate" id="startDate" required><span>
	    	</div>
	    	<div class="col-xs-3">
				<span>End Date: <input class="form-control" type="text" name="endDate" id="endDate" required></span>
			</div>
			<div class="col-xs-2">
				<p>Room Capacity: 
					<select class="form-control" name="capacity">
						<option value"">Capacity</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
					</select>
				</p>
			</div>
			<div class="col-xs-2">
				<p>Room Type: 
					<select class="form-control" name="rtype">
						<option value"">Room type</option>
						<option value="Standard">Standard</option>
						<option value="Prime">Prime</option>
					</select>
				</p>
			</div>
			<div class="col-xs-2">
				<br>
				<button type="submit" name="searchBtn" value"search" class="btn btn-default">Search Rooms</button>
			</div>
		</div>
	</form>

<?php
if (isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['capacity'])) {
    $_SESSION['startDate'] = $_POST['startDate'];
    $_SESSION['endDate'] = $_POST['endDate'];
    $_SESSION['capacity'] = $_POST['capacity'];
    $_SESSION['rtype'] = $_POST['rtype'];
    //convert to compare with database
    $stDateField = strtotime($_SESSION['startDate']);
    $stDateFieldSQL = date('Y-m-d H:i:s', $stDateField);
    $endDateField = strtotime($_SESSION['endDate']);
    $endDateFieldSQL = date('Y-m-d H:i:s', $endDateField);

	//connect to MySQL (host, user_name, password)
	require('connect_db.php');
	
	$query = 'select room.room_number, capacity, description, room_type, price, start_date, end_date
			from room
			left join reservation on room.room_number = reservation.room_number
			where capacity = ' . "'" . $_SESSION['capacity'] . "' and room_type = '" . $_SESSION['rtype'] . "'";

	if(!($result = mysqli_query($dbc, $query)))
	{
	    print ("Coudnot execute query! <br/>");
	    die(mysql_error());
	}

	print '<div class="panel panel-default">';
    	print '<div class="panel-heading">Search Results</div>';
			print "<table class='table'>";
				while($row = mysqli_fetch_array($result)){
					//filter rooms available by dates
			    	filterByDates($row, $stDateFieldSQL, $endDateFieldSQL);      	
			    }
			print"</table>";
		print "</div>";
	print "</div>";

	mysqli_close($dbc);
    exit;

}else{
    if(isset($_SESSION['startDate']) && isset($_SESSION['endDate']) && isset($_SESSION['capacity'])) {
        unset($_SESSION['startDate']);
        unset($_SESSION['endDate']);
        unset($_SESSION['capacity']);
        print "";
	}
}

// function to filter the table by dates
function filterByDates($row, $stDateFieldSQL, $endDateFieldSQL){
    
        $img = filterImage($row['capacity'], $row['room_type'], $row['room_number']);
        
	if($row['start_date'] == NULL){
            
            echo '<tr><td rowspan="7"><img src="' . $img . '" class="img-rounded" width="350" height="250"/></td></tr>'
                 . '<tr><td colspan="6">ROOM: ' . $row['room_number'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                 . '<tr><td colspan="6">CAPACITY: ' . $row['capacity'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                 . '<tr><td colspan="6">DESCRIPTION: ' . $row['description'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                 . '<tr><td colspan="6">TYPE: ' . $row['room_type'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                 . '<tr><td colspan="6">PRICE: $' . $row['price'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                 . '<tr><td colspan="6"><a href="store_reserve.php?id=' . $row['room_number']. '">Make your reserve</a></td><td></td><td></td><td></td><td></td><td></td></tr>';
		     
	}else{
		if((($row['start_date'] > $stDateFieldSQL) && ($row['start_date'] > $endDateFieldSQL)) || (($row['end_date'] < $stDateFieldSQL) && ($row['end_date'] < $endDateFieldSQL))){
                    
                echo '<tr><td rowspan="7"><img src="' . $img . '" class="img-rounded" width="350" height="250"/></td></tr>'
                    . '<tr><td colspan="6">ROOM: ' . $row['room_number'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                    . '<tr><td colspan="6">CAPACITY: ' . $row['capacity'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                    . '<tr><td colspan="6">DESCRIPTION: ' . $row['description'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                    . '<tr><td colspan="6">TYPE: ' . $row['room_type'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                    . '<tr><td colspan="6">PRICE: $' . $row['price'] . '</td><td></td><td></td><td></td><td></td><td></td></tr>'
                    . '<tr><td colspan="6"><a href="store_reserve.php?id=' . $row['room_number']. '">Make your reserve</a></td><td></td><td></td><td></td><td></td><td></td></tr>';
                    
	   		
		}

	}
}

# function to filter room pictures
function filterImage($capacity, $room_type, $room_number){

    if($room_type == 'Standard'){
        
        switch($capacity){
            
            case 1:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/standard_room1_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/standard_room1_2.jpg';
                }
                else{
                    $img = 'img/rooms/standard_room1_3.jpg';
                }
            
                return $img;
            
            case 2:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/standard_room2_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/standard_room2_2.jpg';
                }
                else{
                    $img = 'img/rooms/standard_room2_3.jpg';
                }
            
                return $img;
                
            case 3:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/standard_room3_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/standard_room3_2.jpg';
                }
                else{
                    $img = 'img/rooms/standard_room3_3.jpg';
                }
            
                return $img;
            
            case 4:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/standard_room4_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/standard_room4_2.jpg';
                }
                else{
                    $img = 'img/rooms/standard_room4_3.jpg';
                }
            
                return $img;;
                
        }
    }
    else{
        switch($capacity){
            
            case 1:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/prime_room1_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/prime_room1_2.jpg';
                }
                else{
                    $img = 'img/rooms/prime_room1_3.jpg';
                }
            
                return $img;
                
            case 2:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/prime_room2_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/prime_room2_2.jpg';
                }
                else{
                    $img = 'img/rooms/prime_room2_3.jpg';
                }
            
                return $img;
            
            case 3:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/prime_room3_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/prime_room3_2.jpg';
                }
                else{
                    $img = 'img/rooms/prime_room3_3.jpg';
                }
            
                return $img;
            
            case 4:
                if($room_number > 100 and $room_number < 300){
                    $img = 'img/rooms/prime_room4_1.jpg';
                }
                elseif($room_number > 300 and $room_number < 600){
                    $img = 'img/rooms/prime_room4_2.jpg';
                }
                else{
                    $img = 'img/rooms/prime_room4_3.jpg';
                }
            
                return $img;
                
        }
    }
    
    
}

?>
</body>

</html>
