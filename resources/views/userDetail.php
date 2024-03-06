<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.phptutorial.net/app/css/style.css">
    <title>Detail Pengguna</title>
</head>
<body>
    <?php
    $user=$_SESSION['user'];?>
<main>
<h1><?php echo $user['name']; ?></h1>
<ul>
    <?php 
        echo "<li>" . $user['nomorpegawai'] ."</li>";
        echo "<li>" . $user['email'] ."</li>";
        echo "<li>" . $user['role'] . "</li>";
    ?>
</ul>

<a href="/api/user"> Kembali </a>
</main>
</body>
    </html>