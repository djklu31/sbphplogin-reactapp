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
            <img class="logo" src="streambox-logo.svg" />
            <div class="grid">
                <label for="username">Username:</label>
                <input id="username-box" name="username" type="text" />
            </div>
            <div class="grid">
                <label for="password">Password:</label>
                <input id="password-box" name="password" type="password" />
            </div>
            <!-- <input type="submit" /> -->
            <button class="btn" onclick="authenticate()">Submit</button>
            <div id="invalid-pass-div"></div>
        </div>
    </div>
    <!-- </form> -->
    <footer>
        Powered by Streambox, Inc. All rights reserved Version 1.07
    </footer>
</body>

</html>
<script>
    window.addEventListener("keypress", (event) => {
        if (event.keyCode == 13) {
            authenticate()
        }
    })
    async function authenticate() {
        const username = document.getElementById("username-box").value
        const password = document.getElementById("password-box").value

        let formData = new FormData();
        formData.append("username", username);
        formData.append("password", password);
        formData.append("fromreact", 0);

        const response = await fetch("/sbuiauth/auth.php", {
            method: 'POST',
            body: formData
        });

        let json = await response.text()
        if (json.indexOf('Wrote') === 0) {
            alert('The app was run for the first time and needed to initialize the backend (created accounts db).  Please log in again.')
            document.getElementById("username-box").value = ""
            document.getElementById("password-box").value = ""
        } else {
            let [loginStatus, token] = JSON.parse(json)

            if (loginStatus === "login success") {
                //TODO: local server endpoint
                //window.location = `http://localhost:3000?user=${username}&token=${token}`

                //TODO: remote server endpoint
                window.location = `${location.origin}/sbuiapp?user=${username}&token=${token}`
            } else if (loginStatus === "login failure") {
                document.getElementById("invalid-pass-div").textContent = "Username or password is incorrect"
            } else {
                document.getElementById("invalid-pass-div").textContent = "Something went wrong when authenticating"
            }
        }
    }
</script>