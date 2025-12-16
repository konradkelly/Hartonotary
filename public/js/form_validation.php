<?php
$firstName = $email = $message = "";
$nameErr = $emailErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate First Name
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $firstName = htmlspecialchars($_POST["name"]);
    }

    // Validate Email with regex
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = htmlspecialchars($_POST["email"]);
        $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        if (!preg_match($pattern, $email)) {
            $emailErr = "Invalid email format";
        }
    }
}
?>