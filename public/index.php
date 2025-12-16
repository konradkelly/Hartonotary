<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
</head>
<body>
    <form action="/submit" method="POST">
        <h2>Contact Form</h2>
        
        <label for="firstName">First Name *</label>
  <input type="text" id="firstName" name="firstName" value="<?php echo $firstName; ?>">
  <span class="error"><?php echo $firstNameErr; ?></span>
        <br><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br><br>
        
        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone">
        <br><br>
        
        <label for="subject">Subject:</label>
        <select id="subject" name="subject">
            <option value="">Select a subject</option>
            <option value="general">General Inquiry</option>
            <option value="support">Support</option>
            <option value="feedback">Feedback</option>
        </select>
        <br><br>
        
        <label for="message">Message:</label>
        <br>
        <textarea id="message" name="message" rows="5" cols="30" required></textarea>
        <br><br>
        
        <button type="submit">Submit</button>
    </form>
</body>
</html>