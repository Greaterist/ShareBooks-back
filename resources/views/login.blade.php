<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="/login">
        @csrf
        <label for="email">Email
            <input type="text" name="email" autofocus>
        </label>
        <label for="password">password
            <input type="password" name="password" autofocus>
        </label>
        <button type="submit">login</button>
        <a href="/register">or sign in</a>

  
        {{   dd(csrf_token()); }}
    </form>
</body>
</html>