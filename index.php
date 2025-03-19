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
    <?php include('reusables/functions.php'); ?>
    <div class="container">
        <h1 class="hero">All National Parks, Reserves, Marine Conservation Areas</h1>
        
        <!-- get unique values for the filter dropdowns -->
        <?php
            $types = getUniqueValues('Type', 'nationalparks', $connect);
            $regions = getUniqueValues('Region', 'nationalparks', $connect);
            $activities = getUniqueValues('ActivityName', 'activities', $connect);
        ?>
        
        <!-- filter forms -->
        <form method="GET" class="filter-form">
            <div class="filters">

                <!-- Filter by Type of National Park -->
                <div class="options">
                    <label for="type">Type:</label>
                    <select name="type" class="filter-field">
                        <option value="">All</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= (isset($_GET['type']) && $_GET['type'] === $type) ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter by Region -->
                <div class="options">
                    <label for="region">Region:</label>
                    <select name="region" class="filter-field">
                        <option value="">All</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= htmlspecialchars($region) ?>" <?= (isset($_GET['region']) && $_GET['region'] === $region) ? 'selected' : '' ?>><?= htmlspecialchars($region) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter by Activities Available -->
                <div class="options">
                    <label for="activity">Activity:</label>
                    <select name="activity" class="filter-field">
                        <option value="">All</option>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?= htmlspecialchars($activity) ?>" <?= (isset($_GET['activity']) && $_GET['activity'] === $activity) ? 'selected' : '' ?>><?= htmlspecialchars($activity) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">Filter</button>
            </div>
        </form>

        

        <div class="parks-container">
            <?php
            

            // building the query based on the filter criteria
            // references:
            // https://stackoverflow.com/questions/15212081/php-and-mysql-optional-where-conditions
            // https://stackoverflow.com/questions/10339373/php-mysql-real-escape-string-returns-empty-string
            // https://www.codecademy.com/learn/seasp-defending-node-applications-from-sql-injection-xss-csrf-attacks/modules/seasp-preventing-sql-injection-attacks/cheatsheet

            // initialize whereConditions array to store the matching "filter" for the WHERE conditions in the query
            // check if filter type is selected
            // if it is, get the type value and add it to the whereConditions array
            if (!empty($_GET['region'])) {
                $region = mysqli_real_escape_string($connect, $_GET['region']);
                $whereConditions[] = "np.Region = '$region'";
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


            // check if there are results
            // if there are, display the results
            // otherwise display default "No national parks found" message
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
                    
                    // display the park details
                    // park name, type, region, date founded, description, activities
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
