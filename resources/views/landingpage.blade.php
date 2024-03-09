<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.phptutorial.net/app/css/style.css">
    <title>J-EaseDoc User Sign In</title>
</head>
<body>
<main>
    <form action="api/login" method="post">
        @csrf
        <h1>Log in</h1>
        <div>
            <label for="email">Email:</label>
            <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" id="email">
            @if ($errors->has('email'))
            <span class="invalid-feedback">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password"required class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password">
            @if ($errors->has('password'))
            <span class="invalid-feedback">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif
        </div>
        <button type="submit">Masuk</button>
        <footer>Ingin membuat surat permohonan? <a href="login.php">Buat sekarang</a></footer>
    </form>
</main>
</body>
</html>