<!DOCTYPE html>
<html>

<head>
    <title>Google Sign-In Token Tester</title>
</head>

<body>
    <h1>Get Google ID Token for API Testing</h1>
    <p>After you sign in, check the browser's developer console for the ID Token.</p>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <div id="g_id_onload" data-client_id="283271205873-ga6jhpfep5v7kci4lv11dmt00t2q69je.apps.googleusercontent.com"
        data-callback="handleCredentialResponse">
    </div>

    <a href="{{ route('google.auth') }}">Google Login </a>

    <div class="g_id_signin" data-type="standard"></div>

    <script>
        // 4. This function is called when Google returns the token
        function handleCredentialResponse(response) {
            console.log("--- GOOGLE ID TOKEN ---");
            // The response.credential property is the ID Token
            console.log(response.credential);
            console.log("--- END TOKEN ---");
            alert("Success! Check the Developer Console (F12) to copy the ID Token.");
        }
    </script>
</body>

</html>
