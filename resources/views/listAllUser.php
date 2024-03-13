<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.phptutorial.net/app/css/style.css">
    <title>Daftar Seluruh Pengguna J-EaseDoc</title>
</head>
<body>
<main>
<h1>Semua Pengguna J-EaseDoc</h1>
<table>
    <tr>
        <th>
            No
        </th>
        <th>
            Nama Pegawai
        </th>
        <th>
            NPWP
        </th>
        <th>
            Jabatan
        </th>
        <!-- <th>
            Divisi
        </th> -->
    </tr>
    <?php 
    $allUser=$_SESSION['allUser'];
    foreach($allUser as $user){
        echo '<tr>';
        echo "<td>" . $user['id'] . "</td><td><a href='/api/user-detail/". $user['id'] ."'>" . strtolower(trim(($user['name']))) . "</api></td><td>" . strtolower(trim($user['nomorpegawai'])) . "</td>";
        echo "</tr>";
    }
    ?>
</table>
</main>
</body>
    </html>
