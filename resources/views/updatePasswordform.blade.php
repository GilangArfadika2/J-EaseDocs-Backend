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
        <form action="/api/user/password" method="POST">
        @csrf
        {{ method_field('patch') }}
            <div>
            <label for="old">Password lama:</label>
            <input type="password" name="current_password" id="current_password">
            </div>
            <div>
            <label for="new">Password baru:</label>
            <input type="password" name="new_password" id="new_password">
            </div>
            <div>
            <label for="new-confirm">Konfirmasi password baru:</label>
            <input type="password" name="new_confirm" id="new_confirm">
            </div>
            <div>
            <label for="agree">
                <input type="checkbox" name="agree" id="agree" value="yes"/> I agree
                with the
                <a href="#" title="term of services">term of services</a>
            </label>
        </div>
            <button type="submit">Ubah password</button>
        </form>
</main>
</body>
</html>