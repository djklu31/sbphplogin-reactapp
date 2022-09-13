<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streambox Login</title>
</head>
<body>
    <!-- <form action="auth.php"> -->
        <div class="outer-grid">
            <div class="container">
                <img class="logo" src="streambox-logo.svg"/>
                <div class="grid">
                    <label for="username">Username:</label>
                    <input id="username-box" name="username" type="text"/>
                </div>
                <div class="grid">
                    <label for="password">Password:</label>
                    <input id="password-box" name="password" type="password"/>
                </div>
                <!-- <input type="submit" /> -->
                <button class="btn" onclick="authenticate()">Submit</button>
                <div id="invalid-pass-div"></div>
            </div>
        </div>
    <!-- </form> -->
</body>
</html>
<script>
    async function authenticate() {
        const username = document.getElementById("username-box").value
        const password = document.getElementById("password-box").value

        let data = {
            "username": username,
            "password": password
        }

        let formData = new FormData();
        formData.append("username", username);
        formData.append("password", password);

        const response = await fetch("/sbauth/auth.php", {
            method: 'POST', 
            body: formData
        });
        
        const text = await response.text()

        if (text === "login success") {
            window.location = "http://localhost:3000/"
        } else if (text === "login failure") {
            document.getElementById("invalid-pass-div").innerHTML = "Username or password is incorrect"
        } else {
            document.getElementById("invalid-pass-div").innerHTML = "Something went wrong when authenticating"
        }
        // return response.json() // parses JSON response into native JavaScript objects
    }
</script>