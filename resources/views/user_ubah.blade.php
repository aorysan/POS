<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form method="post" action="../ubah_simpan/{{$data->user_id}}">

        {{csrf_field()}}
        {{method_field('post')}}
        
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" value="{{$data->username}}"><br>
        <label>Nama</label>
        <input type="text" name="nama" placeholder="Masukkan nama" value="{{$data->nama}}"><br>
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" value="{{$data->password}}"><br>
        <label>Level Id</label>
        <input type="number" name="level_id" placeholder="Masukkan level id" value="{{$data->level_id}}"><br><br>
        <input type="submit" class="btn btn-success" value="Ubah">
    </form>
</body>
</html>