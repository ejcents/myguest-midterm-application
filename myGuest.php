<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="guest.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="guest.css">
    <title>Guest List</title>
</head>
<body>

<div class="container">
    <h1>Guest List</h1>

    <?php
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Database connection
    $db_server = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "database-cents";

    $connection = new mysqli($db_server, $db_user, $db_password, $db_name);

    if ($connection->connect_error) {
        die("<p class='error'>Could not connect to the database: " . $connection->connect_error . "</p>");
    } else {
        echo '<p class="success">Database connected successfully!</p>';
    }

    // Function to sanitize inputs
    function clean_input($data, $connection) {
        return strtoupper(mysqli_real_escape_string($connection, trim($data)));
    }

    // Handle Add Guest
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
        $firstName = clean_input($_POST['firstName'], $connection);
        $lastName = clean_input($_POST['lastName'], $connection);
        $email = strtolower(mysqli_real_escape_string($connection, trim($_POST['email'])));

        // Handle File Upload
        $photo = "";
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $photo = $targetDir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        $insertSql = "INSERT INTO MyGuests (firstName, lastName, email, photo) VALUES ('$firstName', '$lastName', '$email', '$photo')";
        
        if ($connection->query($insertSql) === TRUE) {
            echo "<p class='success'>New guest added successfully!</p>";
        } else {
            echo "<p class='error'>Error: " . $connection->error . "</p>";
        }
    }

    // Handle Delete Guest
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $deleteSql = "DELETE FROM MyGuests WHERE id = $id";
        
        if ($connection->query($deleteSql) === TRUE) {
            echo "<p class='success'>Guest deleted successfully!</p>";
        } else {
            echo "<p class='error'>Error: " . $connection->error . "</p>";
        }
    }

    // Handle Edit Guest
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $firstName = clean_input($_POST['firstName'], $connection);
        $lastName = clean_input($_POST['lastName'], $connection);
        $email = strtolower(mysqli_real_escape_string($connection, trim($_POST['email'])));

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $photo = $targetDir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
            $updateSql = "UPDATE MyGuests SET firstName = '$firstName', lastName = '$lastName', email = '$email', photo = '$photo' WHERE id = $id";
        } else {
            $updateSql = "UPDATE MyGuests SET firstName = '$firstName', lastName = '$lastName', email = '$email' WHERE id = $id";
        }

        if ($connection->query($updateSql) === TRUE) {
            echo "<p class='success'>Guest updated successfully!</p>";
        } else {
            echo "<p class='error'>Error: " . $connection->error . "</p>";
        }
    }

    // Fetch Guests
    $selectSql = "SELECT id, firstName, lastName, email, photo FROM MyGuests";
    $result = $connection->query($selectSql);

    if ($result->num_rows > 0) {    
        echo '<table>';
        echo '<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Photo</th><th>Actions</th></tr>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . strtoupper($row["id"]) . '</td>';
            echo '<td>' . strtoupper($row["firstName"]) . '</td>';
            echo '<td>' . strtoupper($row["lastName"]) . '</td>';
            echo '<td>' . strtolower($row["email"]) . '</td>';
            echo '<td><img src="' . $row["photo"] . '" width="50" height="50" style="border-radius: 50%;"></td>';
            echo '<td class="action-buttons">';
            echo '<a href="?edit=' . $row["id"] . '"><button class="edit">Edit</button></a>';
            echo '<a href="?delete=' . $row["id"] . '" onclick="return confirm(\'Are you sure you want to delete this guest?\')"><button class="delete">Delete</button></a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<p>No records found in the table.</p>';
    }

    // Edit Form
    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $selectSql = "SELECT id, firstName, lastName, email, photo FROM MyGuests WHERE id = $id";
        $result = $connection->query($selectSql);
        $row = $result->fetch_assoc();

        echo '<h2>Edit Guest</h2>';
        echo '<form method="POST" action="" enctype="multipart/form-data">';
        echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
        echo '<input type="text" name="firstName" value="' . $row["firstName"] . '" required>';
        echo '<input type="text" name="lastName" value="' . $row["lastName"] . '" required>';
        echo '<input type="email" name="email" value="' . $row["email"] . '" required>';
        echo '<input type="file" name="photo" accept="image/*">';
        echo '<button type="submit" name="edit">Update Guest</button>';
        echo '</form>';
    }

    // Add Guest Form
    if (!isset($_GET['edit'])) {
        echo '<h2>Add Guest</h2>';
        echo '<form method="POST" action="" enctype="multipart/form-data">';
        echo '<input type="text" name="firstName" placeholder="First Name" required>';
        echo '<input type="text" name="lastName" placeholder="Last Name" required>';
        echo '<input type="email" name="email" placeholder="Email" required>';
        echo '<input type="file" name="photo" accept="image/*">';
        echo '<button type="submit" name="add">Add Guest</button>';
        echo '</form>';
    }

    $connection->close();
    ?>

</div>

</body>
</html>
