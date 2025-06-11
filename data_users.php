<?php
include "koneksi.php";
$db = new database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Data User</title>
</head>
<body>
    <h2>Data User</h2>
    <table border = "1">
        <tr>
            <th>ID<th>
            <th>Username<th>
            <th>Password<th>
            <th>Option<th>
        </tr>
        <?php
        $no = 1;
        foreach($db->tampil_data_user() as $x){
        ?>
        <tr>
            <td><?php echo $x['id']; ?><td>
            <td><?php echo $x['username']; ?><td>
            <td><?php echo $x['password']; ?><td>
            <td>
                <a href="edit_user.php?username=<?php echo $x['username']; ?>&aksi=edit">Edit</a>
                <a href="proses.php?username=<?php echo $x['username']; ?>&aksi=hapus">Hapus</a>
        </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>