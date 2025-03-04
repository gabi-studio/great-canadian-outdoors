<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Great Canadian Outdoors</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.png">
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <?php include('reusables/nav.php'); ?>
    <div class="container">
        <h1 class="hero">All National Parks, Reserves, Marine Conservation Areas</h1>
        
        <!-- filter forms -->
        <form method="GET" class="filter-form">
            <div class="filters">

                <!-- filter by type of national park -->
                <div class="options">
                    <label for="type">Type:</label>
                    <select name="type" class="filter-field">
                        <option value="">All</option>
                        <option value="National Park">National Park</option>
                        <option value="National Park Reserve">National Park Reserve</option>
                        <option value="National Marine Conservation Area">National Marine Conservation Area</option>
                    </select>
                </div>

                <!-- filter by region (provice or territory) -->
                <div class="options">
                    <label for="region">Region:</label>
                    <select name="region" class="filter-field">
                        <option value="">All</option>
                        <option value="Ontario">Ontario</option>
                        <option value="British Columbia">British Columbia</option>
                        <option value="Alberta">Alberta</option>
                        <option value="Quebec">Quebec</option>
                        <option value="Nova Scotia">Nova Scotia</option>
                        <option value="Manitoba">Manitoba</option>
                        <option value="Saskatchewan">Saskatchewan</option>
                        <option value="New Brunswick">New Brunswick</option>
                        <option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
                        <option value="Prince Edward Island">Prince Edward Island</option>
                        <option value="Northwest Territories">Northwest Territories</option>
                        <option value="Yukon">Yukon</option>
                        <option value="Nunavut">Nunavut</option>
                    </select>
                </div>

                <!-- filter by activities available -->
                <div class="options">
                    <label for="activity">Activity:</label>
                    <select name="activity" class="filter-field">
                        <option value="">All</option>
                        <option value="Scuba Diving">Scuba Diving</option>
                        <option value="Canoeing">Canoeing</option>
                        <option value="Skiing">Skiing</option>
                        <option value="Whitewater Rafting">Whitewater Rafting</option>
                        <option value="Backpacking">Backpacking</option>
                        <option value="Bird Watching">Bird Watching</option>
                        <option value="Hiking">Hiking</option>
                        <option value="Cycling">Cycling</option>
                        <option value="Snowshoeing">Snowshoeing</option>
                        <option value="Fishing">Fishing</option>
                        <option value="Wildlife Viewing">Wildlife Viewing</option>
                        <option value="Photography">Photography</option>
                        <option value="Rock Climbing">Rock Climbing</option>
                        <option value="Kayaking">Kayaking</option>
                        <option value="Camping">Camping</option>
                        <option value="Whale Watching">Whale Watching</option>
                        <option value="Surfing">Surfing</option>
                        <option value="Beachcombing">Beachcombing</option>
                        <option value="Hot Springs">Hot Springs</option>
                    </select>
                </div>
                <button type="submit" class="btn">Filter</button>
            </div>
            <!-- <button type="submit" class="btn">Filter</button> -->
        </form>
        

        <div class="parks-container">
            <?php
            include('reusables/connection.php');

            // building the query based on the filter criteria
            // references:
            // https://stackoverflow.com/questions/15212081/php-and-mysql-optional-where-conditions
            // https://stackoverflow.com/questions/10339373/php-mysql-real-escape-string-returns-empty-string
            // https://www.codecademy.com/learn/seasp-defending-node-applications-from-sql-injection-xss-csrf-attacks/modules/seasp-preventing-sql-injection-attacks/cheatsheet

            // initialize whereConditions array to store the matching "filter" for the WHERE conditions in the query
            // check if filter type is selected
            // if it is, get the type value and add it to the whereConditions array
            if (!empty($_GET['type'])) {
                $type = mysqli_real_escape_string($connect, $_GET['type']);
                $whereConditions[] = "np.Type = '$type'";
            }

            // if region is selected, get the region value and add it to the whereConditions array
            if (!empty($_GET['region'])) {
                $region = mysqli_real_escape_string($connect, $_GET['region']);
                $whereConditions[] = "np.Region = '$region'";
            }

            // if activity is selected, get the activity value and add it to the whereConditions array
            if (!empty($_GET['activity'])) {
                $activity = mysqli_real_escape_string($connect, $_GET['activity']);
                $whereConditions[] = "na.ActivityName = '$activity'";
            }

            // build the WHERE clause of the query:
            // if there are conditions in the whereConditions array
            // then add the WHERE keyword and join the conditions with AND
            $whereSQL = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";


            // constructing the query
            // joing the nationalparks table with the activities table
            // concatenating the activity names and seasons into a single string called "activities"
            // $whereSQL from above is the WHERE clause of the query
            $query = "SELECT np.ID, np.ParkName, np.Type, np.Description, np.DateFounded, np.Region, np.ImagePath, np.ImageSource, 
                    GROUP_CONCAT(CONCAT(na.ActivityName, ' (', na.Season, ')') SEPARATOR ', ') AS activities 
                    FROM nationalparks np 
                    LEFT JOIN activities na ON np.ID = na.ParkID 
                    $whereSQL
                    GROUP BY np.ID, np.ParkName, np.Type, np.Description, np.DateFounded, np.Region, np.ImagePath, np.ImageSource";

            // execute the query
            $parks = mysqli_query($connect, $query);



            
            if (mysqli_num_rows($parks) > 0) {
                foreach ($parks as $park) {
                    echo '<div class="park-card">';
                    echo '  <div class="card">';
                    
                    // check if there is an image path available; 
                    // if not, use the default image path
                    if (!empty($park['ImagePath'])) {
                        $imageSource = htmlspecialchars(ltrim($park['ImagePath'], '/'));
                    } else {
                        $imageSource= 'images/default.png'; 
                    }               
                    echo '    <img src="' . $imageSource . '" 
                                class="card-image" 
                                style="width: 100%; height: 200px; object-fit: cover;"
                                alt="Image Source: ' . htmlspecialchars($park['ImageSource']) . '">';              
                    
                    
                    echo '    <div class="card-body">';
                    echo '      <h2>' . htmlspecialchars($park['ParkName']) . '</h2>';
                    echo '      <p><strong>Type:</strong> ' . htmlspecialchars($park['Type']) . '</p>';
                    echo '      <p><strong>Location:</strong> ' . htmlspecialchars($park['Region']) . '</p>';
                    echo '      <p><strong>Date Founded:</strong> ' . htmlspecialchars($park['DateFounded']) . '</p>';

                    // check if there is a description available;
                    // if not, display a default no description message
                    echo '      <p><strong>Description:</strong> ';
                                if (!empty($park['Description'])) {
                                    echo htmlspecialchars($park['Description']);
                                } else {
                                    echo 'No description available';
                                }
                                echo '</p>';

                    // check if there are activities available;
                    //  if not, display a defaul no activities message
                    echo '      <p><strong>Activities:</strong> ';
                                if (!empty($park['activities'])) {
                                    echo htmlspecialchars($park['activities']);
                                } else {
                                    echo 'No activities available';
                                }
                                echo '</p>';

                                
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center">No national parks found matching your criteria.</p>';
            }
            ?>
        </div>
    </div>

    

    <footer>
        <p class="copyright">&copy; 2025 The Great Canadian Outdoors</p>
    </footer>
</body>


</html>
