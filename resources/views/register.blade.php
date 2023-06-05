<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <form method="POST" action="/register">
        @csrf
        <label for="name">Name
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus>
        </label>
        <label for="email">email
            <input type="text" id="email" name="email">
        </label>
        <label for="password">password
            <input type="password" id="password" name="password">
        </label>
        <button type="submit">Send</button>
        <a href="/login">Already registered?</a>
    </form>
</body>
</html>