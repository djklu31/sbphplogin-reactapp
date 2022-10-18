<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Logo</title>
</head>

<body>
    <form id="form">
        <input type="file" id="file-input" />
        <input type="submit" id="submit-btn" />
    </form>
</body>

</html>
<script>
    document.getElementById("form").addEventListener("submit", handleClick)

    async function handleClick(e) {
        e.preventDefault()
        let formData = new FormData()
        let fileInput = document.getElementById("file-input")
        formData.append("file", fileInput.files[0])
        console.log(fileInput.files[0])
        fetch("/sbuiauth/receiveFile.php", {
            method: "post",
            body: formData
        }).catch(
            console.error
        )
    }
</script>