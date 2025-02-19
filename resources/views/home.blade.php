<!DOCTYPE html>
<html lang="en">

<head>
    <title>POS - Home</title>
</head>

<body>
    <h1>Selamat Datang di Aplikasi POS</h1>

    <!-- Menu Navigasi -->
    <nav>
        <ul>
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ url('/category/food-beverage') }}">Food & Beverage</a></li>
            <li><a href="{{ url('/category/beauty-health') }}">Beauty & Health</a></li>
            <li><a href="{{ url('/category/home-care') }}">Home Care</a></li>
            <li><a href="{{ url('/category/baby-kid') }}">Baby & Kid</a></li>
            <li><a href="{{ url('/user/5/name/Bambang') }}">User Profile</a></li>
            <li><a href="{{ url('/sales') }}">Sales (POS)</a></li>
        </ul>
    </nav>

    <p>Silakan pilih menu di atas untuk menjelajahi aplikasi Point of Sales.</p>

</body>

</html>